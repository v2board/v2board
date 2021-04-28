<?php

namespace App\Http\Controllers\Guest;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function notify($method, Request $request)
    {
        $paymentService = new PaymentService($method);
        $verify = $paymentService->notify($request->input());
        if ($verify) abort(500, 'verify error');
        if (!$this->handle($verify['trade_no'], $verify['callback_no'])) {
            abort(500, 'handle error');
        }
        die('success');
    }

    private function handle($tradeNo, $callbackNo)
    {
        $order = Order::where('trade_no', $tradeNo)->first();
        if ($order->status === 1) return true;
        if (!$order) {
            abort(500, 'order is not found');
        }
        $orderService = new OrderService($order);
        if (!$orderService->success($callbackNo)) {
            return false;
        }
        $telegramService = new TelegramService();
        $message = sprintf(
            "💰成功收款%s元\n———————————————\n订单号：%s",
            $order->total_amount / 100,
            $order->trade_no
        );
        $telegramService->sendMessageWithAdmin($message);
        return true;
    }
}
