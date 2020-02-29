<?php

namespace App\Http\Controllers\Guest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Omnipay\Omnipay;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Library\BitpayX;
use Library\PayTaro;

class OrderController extends Controller
{
    public function alipayNotify(Request $request)
    {
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

            if ($response->isPaid()) {
                /**
                 * Payment is successful
                 */
                if (!$this->handle($_POST['out_trade_no'], $_POST['trade_no'])) {
                    abort(500, 'fail');
                }

                die('success'); //The response should be 'success' only
            } else {
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

    public function stripeNotify(Request $request)
    {
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
        switch ($event->type) {
            case 'source.chargeable':
                $source = $event->data->object;
                $charge = \Stripe\Charge::create([
                    'amount' => $source['amount'],
                    'currency' => $source['currency'],
                    'source' => $source['id'],
                    'description' => config('v2board.app_name', 'V2Board') . $source['metadata']['invoice_id']
                ]);
                if ($charge['status'] == 'succeeded') {
                    $trade_no = Cache::get($source['id']);
                    if (!$trade_no) {
                        abort(500, 'redis is not found trade no by stripe source id');
                    }
                    if (!$this->handle($trade_no, $source['id'])) {
                        abort(500, 'fail');
                    }
                    Cache::forget($source['id']);
                    die('success');
                }
                break;
            default:
                abort(500, 'event is not support');
        }
    }

    public function bitpayXNotify(Request $request)
    {
        $inputString = file_get_contents('php://input', 'r');
        Log::info('bitpayXNotifyData: ' . $inputString);
        $inputStripped = str_replace(array("\r", "\n", "\t", "\v"), '', $inputString);
        $inputJSON = json_decode($inputStripped, true); //convert JSON into array

        $bitpayX = new BitpayX(config('v2board.bitpayx_appsecret'));
        $params = [
            'status' => $inputJSON['status'],
            'order_id' => $inputJSON['order_id'],
            'merchant_order_id' => $inputJSON['merchant_order_id'],
            'price_amount' => $inputJSON['price_amount'],
            'price_currency' => $inputJSON['price_currency'],
            'pay_amount' => $inputJSON['pay_amount'],
            'pay_currency' => $inputJSON['pay_currency'],
            'created_at_t' => $inputJSON['created_at_t']
        ];
        $strToSign = $bitpayX->prepareSignId($inputJSON['merchant_order_id']);
        if (!$bitpayX->verify($strToSign, $inputJSON['token'])) {
            abort(500, 'sign error');
        }
        if ($params['status'] !== 'PAID') {
            abort(500, 'order is not paid');
        }
        if (!$this->handle($params['merchant_order_id'], $params['order_id'])) {
            abort(500, 'order process fail');
        }
        die('success');
    }

    public function payTaroNotify(Request $request)
    {
        Log::info('payTaroNotify: ' . json_encode($request->input()));

        $payTaro = new PayTaro(config('v2board.paytaro_app_id'), config('v2board.paytaro_app_secret'));
        if (!$payTaro->verify($request->input())) {
            abort(500, 'fail');
        }
        if (!$this->handle($request->input('out_trade_no'), $request->input('trade_no'))) {
            abort(500, 'fail');
        }
        die('success');
    }

    private function handle($tradeNo, $callbackNo)
    {
        $order = Order::where('trade_no', $tradeNo)->first();
        if (!$order) {
            abort(500, 'order is not found');
        }
        if ($order->status !== 0) {
            return true;
        }
        $order->status = 1;
        $order->callback_no = $callbackNo;
        return $order->save();
    }
}
