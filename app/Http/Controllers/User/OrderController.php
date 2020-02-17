<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\OrderSave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Coupon;
use App\Utils\Helper;
use Omnipay\Omnipay;
use Stripe\Stripe;
use Stripe\Source;
use Library\BitpayX;
use Library\PayTaro;

class OrderController extends Controller
{
    public function fetch(Request $request)
    {
        $model = Order::where('user_id', $request->session()->get('id'))
            ->orderBy('created_at', 'DESC');
        if ($request->input('status') !== null) {
            $model->where('status', $request->input('status'));
        }
        $order = $model->get();
        $plan = Plan::get();
        for ($i = 0; $i < count($order); $i++) {
            for ($x = 0; $x < count($plan); $x++) {
                if ($order[$i]['plan_id'] === $plan[$x]['id']) {
                    $order[$i]['plan'] = $plan[$x];
                }
            }
        }
        return response([
            'data' => $order
        ]);
    }

    public function details(Request $request)
    {
        $order = Order::where('user_id', $request->session()->get('id'))
            ->where('trade_no', $request->input('trade_no'))
            ->first();
        if (!$order) {
            abort(500, '订单不存在');
        }
        $order['plan'] = Plan::find($order->plan_id);
        $order['try_out_plan_id'] = (int)config('v2board.try_out_plan_id');
        if (!$order['plan']) {
            abort(500, '订阅不存在');
        }
        return response([
            'data' => $order
        ]);
    }

    private function isNotCompleteOrderByUserId($userId)
    {
        $order = Order::whereIn('status', [0, 1])
            ->where('user_id', $userId)
            ->first();
        if (!$order) {
            return false;
        }
        return true;
    }

    // surplus value
    private function getSurplusValue(User $user)
    {
        $plan = Plan::find($user->plan_id);
        $dayPrice = 0;
        if ($plan->month_price) {
            $dayPrice = $plan->month_price / 30;
        } else if ($plan->quarter_price) {
            $dayPrice = $plan->quarter_price / 91;
        } else if ($plan->half_year_price) {
            $dayPrice = $plan->half_year_price / 183;
        } else if ($plan->year_price) {
            $dayPrice = $plan->year_price / 365;
        }
        $remainingDay = ($user->expired_at - time()) / 86400;
        return $remainingDay * $dayPrice;
    }

    public function save(OrderSave $request)
    {
        if ($this->isNotCompleteOrderByUserId($request->session()->get('id'))) {
            abort(500, '存在未付款订单，请取消后再试');
        }

        $plan = Plan::find($request->input('plan_id'));
        $user = User::find($request->session()->get('id'));

        if (!$plan) {
            abort(500, '该订阅不存在');
        }

        if ((!$plan->show && !$plan->renew) || (!$plan->show && $user->plan_id !== $plan->id)) {
            abort(500, '该订阅已售罄');
        }

        if (!$plan->renew && $user->plan_id == $plan->id) {
            abort(500, '该订阅无法续费，请更换其他订阅');
        }

        if ($plan[$request->input('cycle')] === NULL) {
            abort(500, '该订阅周期无法进行购买，请选择其他周期');
        }

        if ($request->input('coupon_code')) {
            $coupon = Coupon::where('code', $request->input('coupon_code'))->first();
            if (!$coupon) {
                abort(500, '优惠券无效');
            }
            if ($coupon->limit_use <= 0 && $coupon->limit_use !== NULL) {
                abort(500, '优惠券已无可用次数');
            }
            if (time() < $coupon->started_at) {
                abort(500, '优惠券还未到可用时间');
            }
            if (time() > $coupon->ended_at) {
                abort(500, '优惠券已过期');
            }
        }

        DB::beginTransaction();
        $order = new Order();
        $order->user_id = $request->session()->get('id');
        $order->plan_id = $plan->id;
        $order->cycle = $request->input('cycle');
        $order->trade_no = Helper::guid();
        $order->total_amount = $plan[$request->input('cycle')];
        // renew and change subscribe process
        if ($user->expired_at > time() && $order->plan_id !== $user->plan_id) {
            if (!(int)config('v2board.plan_change_enable', 1)) abort(500, '目前不允许更改订阅，请联系管理员');
            $order->type = 3;
            $order->surplus_amount = $this->getSurplusValue($user);
            if ($order->surplus_amount >= $order->total_amount) {
                $order->refund_amount = $order->surplus_amount - $order->total_amount;
                $order->total_amount = 0;
            } else {
                $order->total_amount = $order->total_amount - $order->surplus_amount;
            }
        } else if ($user->expired_at > time() && $order->plan_id == $user->plan_id) {
            $order->type = 2;
        } else {
            $order->type = 1;
        }
        // discount start
        // coupon
        if (isset($coupon)) {
            switch ($coupon->type) {
                case 1:
                    $order->discount_amount = $coupon->value;
                    break;
                case 2:
                    $order->discount_amount = $order->total_amount * ($coupon->value / 100);
                    break;
            }
            if ($coupon->limit_use !== NULL) {
                $coupon->limit_use = $coupon->limit_use - 1;
                if (!$coupon->save()) {
                    DB::rollback();
                    abort(500, '优惠券使用失败');
                }
            }
        }
        // user
        if ($user->discount) {
            $order->discount_amount = $order->discount_amount + ($order->total_amount * ($user->discount / 100));
        }
        // discount complete
        $order->total_amount = $order->total_amount - $order->discount_amount;
        // discount end
        // invite process
        if ($user->invite_user_id && $order->total_amount > 0) {
            $order->invite_user_id = $user->invite_user_id;
            $inviter = User::find($user->invite_user_id);
            if ($inviter && $inviter->commission_rate) {
                $order->commission_balance = $order->total_amount * ($inviter->commission_rate / 100);
            } else {
                $order->commission_balance = $order->total_amount * (config('v2board.invite_commission', 10) / 100);
            }
        }
        if (!$order->save()) {
            DB::rollback();
            abort(500, '订单创建失败');
        }

        DB::commit();

        return response([
            'data' => $order->trade_no
        ]);
    }

    public function checkout(Request $request)
    {
        $tradeNo = $request->input('trade_no');
        $method = $request->input('method');
        $order = Order::where('trade_no', $tradeNo)
            ->where('user_id', $request->session()->get('id'))
            ->where('status', 0)
            ->first();
        if (!$order) {
            abort(500, '订单不存在或已支付');
        }
        // free process
        if ($order->total_amount <= 0) {
            $order->total_amount = 0;
            $order->status = 1;
            $order->save();
            exit();
        }
        switch ($method) {
            // return type => 0: QRCode / 1: URL
            case 0:
                // alipayF2F
                if (!(int)config('v2board.alipay_enable')) {
                    abort(500, '支付方式不可用');
                }
                return response([
                    'type' => 0,
                    'data' => $this->alipayF2F($tradeNo, $order->total_amount)
                ]);
            case 2:
                // stripeAlipay
                if (!(int)config('v2board.stripe_alipay_enable')) {
                    abort(500, '支付方式不可用');
                }
                return response([
                    'type' => 1,
                    'data' => $this->stripeAlipay($order)
                ]);
            case 3:
                // stripeWepay
                if (!(int)config('v2board.stripe_wepay_enable')) {
                    abort(500, '支付方式不可用');
                }
                return response([
                    'type' => 0,
                    'data' => $this->stripeWepay($order)
                ]);
            case 4:
                // bitpayX
                if (!(int)config('v2board.bitpayx_enable')) {
                    abort(500, '支付方式不可用');
                }
                return response([
                    'type' => 1,
                    'data' => $this->bitpayX($order)
                ]);
            case 5:
                if (!(int)config('v2board.paytaro_enable')) {
                    abort(500, '支付方式不可用');
                }
                return response([
                    'type' => 1,
                    'data' => $this->payTaro($order)
                ]);
            default:
                abort(500, '支付方式不存在');
        }
    }

    public function check(Request $request)
    {
        $tradeNo = $request->input('trade_no');
        $order = Order::where('trade_no', $tradeNo)
            ->where('user_id', $request->session()->get('id'))
            ->first();
        if (!$order) {
            abort(500, '订单不存在');
        }
        return response([
            'data' => $order->status
        ]);
    }

    public function getPaymentMethod()
    {
        $data = [];
        if ((int)config('v2board.alipay_enable')) {
            $alipayF2F = new \StdClass();
            $alipayF2F->name = '支付宝';
            $alipayF2F->method = 0;
            $alipayF2F->icon = 'alipay';
            array_push($data, $alipayF2F);
        }

        if ((int)config('v2board.stripe_alipay_enable')) {
            $stripeAlipay = new \StdClass();
            $stripeAlipay->name = '支付宝';
            $stripeAlipay->method = 2;
            $stripeAlipay->icon = 'alipay';
            array_push($data, $stripeAlipay);
        }

        if ((int)config('v2board.stripe_wepay_enable')) {
            $stripeWepay = new \StdClass();
            $stripeWepay->name = '微信';
            $stripeWepay->method = 3;
            $stripeWepay->icon = 'wechat';
            array_push($data, $stripeWepay);
        }

        if ((int)config('v2board.bitpayx_enable')) {
            $bitpayX = new \StdClass();
            $bitpayX->name = '聚合支付';
            $bitpayX->method = 4;
            $bitpayX->icon = 'wallet';
            array_push($data, $bitpayX);
        }

        if ((int)config('v2board.paytaro_enable')) {
            $obj = new \StdClass();
            $obj->name = '聚合支付';
            $obj->method = 5;
            $obj->icon = 'wallet';
            array_push($data, $obj);
        }

        return response([
            'data' => $data
        ]);
    }

    public function cancel(Request $request)
    {
        if (empty($request->input('trade_no'))) {
            abort(500, '参数有误');
        }
        $order = Order::where('trade_no', $request->input('trade_no'))
            ->where('user_id', $request->session()->get('id'))
            ->first();
        if (!$order) {
            abort(500, '订单不存在');
        }
        if ($order->status !== 0) {
            abort(500, '只可以取消待支付订单');
        }
        $order->status = 2;
        if (!$order->save()) {
            abort(500, '取消失败');
        }
        return response([
            'data' => true
        ]);
    }

    private function alipayF2F($tradeNo, $totalAmount)
    {
        $gateway = Omnipay::create('Alipay_AopF2F');
        $gateway->setSignType('RSA2'); //RSA/RSA2
        $gateway->setAppId(config('v2board.alipay_appid'));
        $gateway->setPrivateKey(config('v2board.alipay_privkey')); // 可以是路径，也可以是密钥内容
        $gateway->setAlipayPublicKey(config('v2board.alipay_pubkey')); // 可以是路径，也可以是密钥内容
        $gateway->setNotifyUrl(url('/api/v1/guest/order/alipayNotify'));
        $request = $gateway->purchase();
        $request->setBizContent([
            'subject' => config('v2board.app_name', 'V2Board') . ' - 订阅',
            'out_trade_no' => $tradeNo,
            'total_amount' => $totalAmount / 100
        ]);
        /** @var \Omnipay\Alipay\Responses\AopTradePreCreateResponse $response */
        $response = $request->send();
        $result = $response->getAlipayResponse();
        if ($result['code'] !== '10000') {
            abort(500, $result['sub_msg']);
        }
        // 获取收款二维码内容
        return $response->getQrCode();
    }

    private function stripeAlipay($order)
    {
        $exchange = Helper::exchange('CNY', 'HKD');
        if (!$exchange) {
            abort(500, '货币转换超时，请稍后再试');
        }
        Stripe::setApiKey(config('v2board.stripe_sk_live'));
        $source = Source::create([
            'amount' => floor($order->total_amount * $exchange),
            'currency' => 'hkd',
            'type' => 'alipay',
            'redirect' => [
                'return_url' => config('v2board.app_url', env('APP_URL')) . '/#/order'
            ]
        ]);
        if (!$source['redirect']['url']) {
            abort(500, '支付网关请求失败');
        }

        if (!Cache::put($source['id'], $order->trade_no, 3600)) {
            abort(500, '订单创建失败');
        }
        return $source['redirect']['url'];
    }

    private function stripeWepay($order)
    {
        $exchange = Helper::exchange('CNY', 'HKD');
        if (!$exchange) {
            abort(500, '货币转换超时，请稍后再试');
        }
        Stripe::setApiKey(config('v2board.stripe_sk_live'));
        $source = Source::create([
            'amount' => floor($order->total_amount * $exchange),
            'currency' => 'hkd',
            'type' => 'wechat',
            'redirect' => [
                'return_url' => config('v2board.app_url', env('APP_URL')) . '/#/order'
            ]
        ]);
        if (!$source['wechat']['qr_code_url']) {
            abort(500, '支付网关请求失败');
        }
        if (!Cache::put($source['id'], $order->trade_no, 3600)) {
            abort(500, '订单创建失败');
        }
        return $source['wechat']['qr_code_url'];
    }

    private function bitpayX($order)
    {
        $bitpayX = new BitpayX(config('v2board.bitpayx_appsecret'));
        $params = [
            'merchant_order_id' => $order->trade_no,
            'price_amount' => $order->total_amount / 100,
            'price_currency' => 'CNY',
            'title' => '支付单号：' . $order->trade_no,
            'description' => '充值：' . $order->total_amount / 100 . ' 元',
            'callback_url' => url('/api/v1/guest/order/bitpayXNotify'),
            'success_url' => config('v2board.app_url', env('APP_URL')) . '/#/order',
            'cancel_url' => config('v2board.app_url', env('APP_URL')) . '/#/order'
        ];
        $strToSign = $bitpayX->prepareSignId($params['merchant_order_id']);
        $params['token'] = $bitpayX->sign($strToSign);
        $result = $bitpayX->mprequest($params);
        Log::info('bitpayXSubmit: ' . json_encode($result));
        return isset($result['payment_url']) ? $result['payment_url'] : false;
    }

    private function payTaro($order)
    {
        $payTaro = new PayTaro(config('v2board.paytaro_app_id'), config('v2board.paytaro_app_secret'));
        $result = $payTaro->pay([
            'app_id' => config('v2board.paytaro_app_id'),
            'out_trade_no' => $order->trade_no,
            'total_amount' => $order->total_amount,
            'notify_url' => url('/api/v1/guest/order/payTaroNotify'),
            'return_url' => config('v2board.app_url', env('APP_URL')) . '/#/order'
        ]);
        return $result;
    }
}
