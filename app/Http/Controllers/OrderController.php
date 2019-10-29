<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderSave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Utils\Helper;
use Omnipay\Omnipay;
use Stripe\Stripe;
use Stripe\Source;

class OrderController extends Controller
{
    public function index (Request $request) {
        $order = Order::where('user_id', $request->session()->get('id'))
            ->orderBy('created_at', 'DESC')
            ->get();
        $plan = Plan::get();
        for($i = 0; $i < count($order); $i++) {
            for($x = 0; $x < count($plan); $x++) {
                if ($order[$i]['plan_id'] === $plan[$x]['id']) {
                    $order[$i]['plan'] = $plan[$x];
                }
            }
        }
        return response([
            'data' => $order
        ]);
    }
    
    public function details (Request $request) {
        $order = Order::where('user_id', $request->session()->get('id'))
            ->where('trade_no', $request->input('trade_no'))
            ->first();
        if (!$order) {
            abort(500, '订单不存在');
        }
        $order['plan'] = Plan::find($order->plan_id);
        if (!$order['plan']) {
            abort(500, '订阅不存在');
        }
        return response([
            'data' => $order
        ]);
    }
    
    public function save (OrderSave $request) {
        $plan = Plan::find($request->input('plan_id'));
        $user = User::find($request->session()->get('id'));
        
        if (!$plan) {
            abort(500, '该订阅不存在');
        }
        
        if (!($plan->show || $user->plan_id == $plan->id)) {
            abort(500, '该订阅已售罄');
        }

        if (!$plan->show && !$plan->renew) {
            abort(500, '该订阅无法续费，请更换其他订阅');
        }
        
        $order = new Order();
        $order->user_id = $request->session()->get('id');
        $order->plan_id = $plan->id;
        $order->cycle = $request->input('cycle');
        $order->trade_no = Helper::guid();
        $order->total_amount = $plan[$request->input('cycle')];
        if ($user->invite_user_id) {
            $order->invite_user_id = $user->invite_user_id;
            $order->commission_balance = $order->total_amount * (config('v2board.invite_commission', env('DEFAULT_INVITE_COMMISSION')) / 100);
        }
        if (!$order->save()) {
            abort(500, '订单创建失败');
        }
        
        return response([
            'data' => $order->trade_no
        ]);
    }

    public function checkout (Request $request) {
        $tradeNo = $request->input('trade_no');
        $method = $request->input('method');
        $order = Order::where('trade_no', $tradeNo)
            ->where('user_id', $request->session()->get('id'))
            ->where('status', 0)
            ->first();
        if (!$order) {
            abort(500, '订单不存在或以支付');
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
            default:
                abort(500, '支付方式不存在');
        }
    }

    public function check (Request $request) {
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

    public function getPaymentMethod () {
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

        return response([
            'data' => $data
        ]);
    }

    private function alipayF2F ($tradeNo, $totalAmount) {
        $gateway = Omnipay::create('Alipay_AopF2F');
        $gateway->setSignType('RSA2'); //RSA/RSA2
        $gateway->setAppId(config('v2board.alipay_appid'));
        $gateway->setPrivateKey(config('v2board.alipay_privkey')); // 可以是路径，也可以是密钥内容
        $gateway->setAlipayPublicKey(config('v2board.alipay_pubkey')); // 可以是路径，也可以是密钥内容
        $gateway->setNotifyUrl(config('v2board.app_url', env('APP_URL')) . '/api/v1/guest/order/alipayNotify');
        $request = $gateway->purchase();
        $request->setBizContent([
            'subject'      => config('v2board.app_name') . ' - 订阅',
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

    private function stripeAlipay ($order) {
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
                'return_url' => config('v2board.app_url', env('APP_URL')) . '/api/v1/guest/order/stripeReturn'
            ]
        ]);
        if (!$source['redirect']['url']) {
            abort(500, '支付网关请求失败');
        }
        $order->callback_no = $source['id'];
        if (!$order->save()) {
            abort(500, '订单更新失败');
        }
        return $source['redirect']['url'];
    }

    private function stripeWepay ($order) {
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
                'return_url' => config('v2board.app_url', env('APP_URL')) . '/api/v1/guest/order/stripeReturn'
            ]
        ]);
        if (!$source['wechat']['qr_code_url']) {
            abort(500, '支付网关请求失败');
        }
        $order->callback_no = $source['id'];
        if (!$order->save()) {
            abort(500, '订单更新失败');
        }
        return $source['wechat']['qr_code_url'];
    }
}
