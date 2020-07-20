<?php

namespace App\Http\Controllers\Guest;

use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Library\Epay;
use Omnipay\Omnipay;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Library\BitpayX;
use Library\MGate;

class OrderController extends Controller
{
    public function alipayNotify(Request $request)
    {
        // Log::info('alipayNotifyData: ' . json_encode($_POST));
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
        // Log::info('stripeNotifyData: ' . json_encode($request->input()));

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
                $object = $event->data->object;
                \Stripe\Charge::create([
                    'amount' => $object->amount,
                    'currency' => $object->currency,
                    'source' => $object->id,
                    'metadata' => json_decode($object->metadata, true)
                ]);
                die('success');
                break;
            case 'charge.succeeded':
                $object = $event->data->object;
                if ($object->status === 'succeeded') {
                    $metaData = isset($object->metadata->out_trade_no) ? $object->metadata : $object->source->metadata;
                    $tradeNo = $metaData->out_trade_no;
                    if (!$tradeNo) {
                        abort(500, 'trade no is not found in metadata');
                    }
                    if (!$this->handle($tradeNo, $object->balance_transaction)) {
                        abort(500, 'fail');
                    }
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
        // Log::info('bitpayXNotifyData: ' . $inputString);
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
        die(json_encode([
            'status' => 200
        ]));
    }

    public function mgateNotify(Request $request)
    {
        $mgate = new MGate(config('v2board.mgate_url'), config('v2board.mgate_app_id'), config('v2board.mgate_app_secret'));
        if (!$mgate->verify($request->input())) {
            abort(500, 'fail');
        }
        if (!$this->handle($request->input('out_trade_no'), $request->input('trade_no'))) {
            abort(500, 'fail');
        }
        die('success');
    }

    public function epayNotify(Request $request)
    {
        $epay = new Epay(config('v2board.epay_url'), config('v2board.epay_pid'), config('v2board.epay_key'));
        if (!$epay->verify($request->input())) {
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
        $orderService = new OrderService($order);
        return $orderService->success($callbackNo);
    }
}
