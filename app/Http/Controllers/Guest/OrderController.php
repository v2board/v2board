<?php

namespace App\Http\Controllers\Guest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Omnipay\Omnipay;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function alipayNotify (Request $request) {
        Log::info('alipayNotifyData: ' . json_encode($_POST));
        $gateway = Omnipay::create('Alipay_AopF2F');
        $gateway->setSignType('RSA2'); //RSA/RSA2
        $gateway->setAppId(config('v2board.alipay_appid'));
        $gateway->setPrivateKey(config('v2board.alipay_privkey')); // 可以是路径，也可以是密钥内容
        $gateway->setAlipayPublicKey(config('v2board.alipay_pubkey')); // 可以是路径，也可以是密钥内容
        $request = $gateway->completePurchase();
        $request->setParams($_POST); //Optional
        try {
            /** @var \Omnipay\Alipay\Responses\AopCompletePurchaseResponse $response */
            $response = $request->send();
            
            if($response->isPaid()){
                $order = Order::where('trade_no', $_POST['out_trade_no'])->first();
                if (!$order) {
                    abort(500, 'fail');
                }
                if ($order->status !== 0) {
                    die('success');
                }
                $order->status = 1;
                $order->callback_no = $_POST['trade_no'];
                if (!$order->save()) {
                    abort(500, 'fail');
                }
                /**
                 * Payment is successful
                 */
                die('success'); //The response should be 'success' only
            }else{
                /**
                 * Payment is not successful
                 */
                die('fail');
            }
        } catch (Exception $e) {
            /**
             * Payment is not successful
             */
            die('fail');
        }
    }

    public function stripeNotify (Request $request) {
        Log::info('stripeNotifyData: ' . json_encode($request->input()));

        \Stripe\Stripe::setApiKey(config('v2board.stripe_sk_live'));
        try {
            $event = \Stripe\Webhook::constructEvent(
                file_get_contents('php://input'),
                $_SERVER['HTTP_STRIPE_SIGNATURE'],
                config('v2board.stripe_webhook_key')
            );
        } catch (\Stripe\Error\SignatureVerification $e) {
            abort(400);
        }

        $obj = $event->data->object;
        if ($obj['status'] == 'succeeded') {
            $order = Order::where('callback_no', $obj['source']['id'])->first();
            if (!$order) {
                abort(500, 'fail');
            }
            if ($order->status !== 0) {
                die('success');
            }
            $order->status = 1;
            if (!$order->save()) {
                abort(500, 'fail');
            }
            die('success');
        }
    }

    public function stripeReturn (Request $request) {
        Log::info('stripeReturnData: ' . json_encode($request->input()));
        header('Location:' . '/#/dashboard');
    }
}
