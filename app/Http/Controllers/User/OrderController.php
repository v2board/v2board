<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\OrderSave;
use App\Services\CouponService;
use App\Services\OrderService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Utils\Helper;
use Omnipay\Omnipay;
use Stripe\Stripe;
use Stripe\Source;
use Library\BitpayX;
use Library\MGate;
use Library\Epay;

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

    public function save(OrderSave $request)
    {
        $userService = new UserService();
        if ($userService->isNotCompleteOrderByUserId($request->session()->get('id'))) {
            abort(500, '您有未付款或开通中的订单，请稍后或取消再试');
        }

        $plan = Plan::find($request->input('plan_id'));
        $user = User::find($request->session()->get('id'));

        if (!$plan) {
            abort(500, '该订阅不存在');
        }

        if ((!$plan->show && !$plan->renew) || (!$plan->show && $user->plan_id !== $plan->id)) {
            if ($request->input('cycle') !== 'reset_price') {
                abort(500, '该订阅已售罄');
            }
        }

        if (!$plan->renew && $user->plan_id == $plan->id) {
            abort(500, '该订阅无法续费，请更换其他订阅');
        }

        if ($plan[$request->input('cycle')] === NULL) {
            if ($request->input('cycle') === 'reset_price') {
                abort(500, '该订阅当前不支持重置流量');
            }
            abort(500, '该订阅周期无法进行购买，请选择其他周期');
        }

        if ($request->input('cycle') === 'reset_price' && !$user->plan_id) {
            abort(500, '必须存在订阅才可以购买流量重置包');
        }

        if ($request->input('cycle') === 'reset_price' && $user->expired_at <= time()) {
            abort(500, '当前订阅已过期，无法购买重置包');
        }

        DB::beginTransaction();
        $order = new Order();
        $orderService = new OrderService($order);
        $order->user_id = $request->session()->get('id');
        $order->plan_id = $plan->id;
        $order->cycle = $request->input('cycle');
        $order->trade_no = Helper::guid();
        $order->total_amount = $plan[$request->input('cycle')];

        if ($request->input('coupon_code')) {
            $couponService = new CouponService($request->input('coupon_code'));
            if (!$couponService->use($order)) {
                DB::rollBack();
                abort(500, '优惠券使用失败');
            }
        }

        $orderService->setVipDiscount($user);
        $orderService->setOrderType($user);
        $orderService->setInvite($user);

        if ($user->balance && $order->total_amount > 0) {
            $remainingBalance = $user->balance - $order->total_amount;
            $userService = new UserService();
            if ($remainingBalance > 0) {
                if (!$userService->addBalance($order->user_id, - $order->total_amount)) {
                    DB::rollBack();
                    abort(500, '余额不足');
                }
                $order->balance_amount = $order->total_amount;
                $order->total_amount = 0;
            } else {
                if (!$userService->addBalance($order->user_id, - $user->balance)) {
                    DB::rollBack();
                    abort(500, '余额不足');
                }
                $order->balance_amount = $user->balance;
                $order->total_amount = $order->total_amount - $user->balance;
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
            return response([
                'type' => -1,
                'data' => true
            ]);
        }
        switch ($method) {
            // return type => 0: QRCode / 1: URL / 2: No action
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
                if (!(int)config('v2board.mgate_enable')) {
                    abort(500, '支付方式不可用');
                }
                return response([
                    'type' => 1,
                    'data' => $this->mgate($order)
                ]);
            case 6:
                if (!(int)config('v2board.stripe_card_enable')) {
                    abort(500, '支付方式不可用');
                }
                return response([
                    'type' => 2,
                    'data' => $this->stripeCard($order, $request->input('token'))
                ]);
            case 7:
                if (!(int)config('v2board.epay_enable')) {
                    abort(500, '支付方式不可用');
                }
                return response([
                    'type' => 1,
                    'data' => $this->epay($order)
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
            $bitpayX->name = config('v2board.bitpayx_name', '在线支付');
            $bitpayX->method = 4;
            $bitpayX->icon = 'wallet';
            array_push($data, $bitpayX);
        }

        if ((int)config('v2board.mgate_enable')) {
            $obj = new \StdClass();
            $obj->name = config('v2board.mgate_name', '在线支付');
            $obj->method = 5;
            $obj->icon = 'wallet';
            array_push($data, $obj);
        }

        if ((int)config('v2board.stripe_card_enable')) {
            $obj = new \StdClass();
            $obj->name = '信用卡';
            $obj->method = 6;
            $obj->icon = 'card';
            array_push($data, $obj);
        }

        if ((int)config('v2board.epay_enable')) {
            $obj = new \StdClass();
            $obj->name = config('v2board.epay_name', '在线支付');
            $obj->method = 7;
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
        $orderService = new OrderService($order);
        if (!$orderService->cancel()) {
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
        $currency = config('v2board.stripe_currency', 'hkd');
        $exchange = Helper::exchange('CNY', strtoupper($currency));
        if (!$exchange) {
            abort(500, '货币转换超时，请稍后再试');
        }
        Stripe::setApiKey(config('v2board.stripe_sk_live'));
        $source = Source::create([
            'amount' => floor($order->total_amount * $exchange),
            'currency' => $currency,
            'type' => 'alipay',
            'statement_descriptor' => $order->trade_no,
            'metadata' => [
                'user_id' => $order->user_id,
                'out_trade_no' => $order->trade_no,
                'identifier' => ''
            ],
            'redirect' => [
                'return_url' => config('v2board.app_url', env('APP_URL')) . '/#/order'
            ]
        ]);
        if (!$source['redirect']['url']) {
            abort(500, '支付网关请求失败');
        }
        return $source['redirect']['url'];
    }

    private function stripeWepay($order)
    {
        $currency = config('v2board.stripe_currency', 'hkd');
        $exchange = Helper::exchange('CNY', strtoupper($currency));
        if (!$exchange) {
            abort(500, '货币转换超时，请稍后再试');
        }
        Stripe::setApiKey(config('v2board.stripe_sk_live'));
        $source = Source::create([
            'amount' => floor($order->total_amount * $exchange),
            'currency' => $currency,
            'type' => 'wechat',
            'metadata' => [
                'user_id' => $order->user_id,
                'out_trade_no' => $order->trade_no,
                'identifier' => ''
            ],
            'redirect' => [
                'return_url' => config('v2board.app_url', env('APP_URL')) . '/#/order'
            ]
        ]);
        if (!$source['wechat']['qr_code_url']) {
            abort(500, '支付网关请求失败');
        }
        return $source['wechat']['qr_code_url'];
    }

    private function stripeCard($order, string $token)
    {
        $currency = config('v2board.stripe_currency', 'hkd');
        $exchange = Helper::exchange('CNY', strtoupper($currency));
        if (!$exchange) {
            abort(500, '货币转换超时，请稍后再试');
        }
        Stripe::setApiKey(config('v2board.stripe_sk_live'));
        try {
            $charge = \Stripe\Charge::create([
                'amount' => floor($order->total_amount * $exchange),
                'currency' => $currency,
                'source' => $token,
                'metadata' => [
                    'user_id' => $order->user_id,
                    'out_trade_no' => $order->trade_no,
                    'identifier' => ''
                ]
            ]);
        } catch (\Exception $e) {
            abort(500, '遇到了点问题，请刷新页面稍后再试');
        }
        info($charge);
        if (!$charge->paid) {
            abort(500, '扣款失败，请检查信用卡信息');
        }
        return $charge->paid;
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
        // Log::info('bitpayXSubmit: ' . json_encode($result));
        return isset($result['payment_url']) ? $result['payment_url'] : false;
    }

    private function mgate($order)
    {
        $mgate = new MGate(config('v2board_mgate_url'), config('v2board.mgate_app_id'), config('v2board.mgate_app_secret'));
        $result = $mgate->pay([
            'app_id' => config('v2board.mgate_app_id'),
            'out_trade_no' => $order->trade_no,
            'total_amount' => $order->total_amount,
            'notify_url' => url('/api/v1/guest/order/mgateNotify'),
            'return_url' => config('v2board.app_url', env('APP_URL')) . '/#/order'
        ]);
        return $result;
    }

    private function epay($order)
    {
        $epay = new Epay(config('v2board.epay_url'), config('v2board.epay_pid'), config('v2board.epay_key'));
        return $epay->pay([
            'money' => $order->total_amount / 100,
            'name' => $order->trade_no,
            'notify_url' => url('/api/v1/guest/order/epayNotify'),
            'return_url' => config('v2board.app_url', env('APP_URL')) . '/#/order',
            'out_trade_no' => $order->trade_no
        ]);
    }
}
