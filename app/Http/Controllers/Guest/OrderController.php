<?php

namespace App\Http\Controllers\Guest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
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
                abort(500, 'ERROR');
            }
            if ($order->status !== 0) {
                die('SUCCESS');
            }
            $order->status = 1;
            if (!$order->save()) {
                abort(500, 'ERROR');
            }
            die('SUCCESS');
        }
    }

    public function stripeReturn (Request $request) {
        Log::info('stripeReturnData: ' . json_encode($request->input()));
        header('Location:' . '/#/dashboard');
    }
}
