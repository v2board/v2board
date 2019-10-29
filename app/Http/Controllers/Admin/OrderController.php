<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;

class OrderController extends Controller
{
    public function index (Request $request) {
        $current = $request->input('current') ? $request->input('current') : 1;
        $pageSize = $request->input('pageSize') >= 10 ? $request->input('pageSize') : 10;
        $orderModel = Order::orderBy('created_at', 'DESC');
        if ($request->input('trade_no')) {
            $orderModel->where('trade_no', $request->input('trade_no'));
        }
        $total = $orderModel->count();
        return response([
            'data' => $orderModel->forPage($current, $pageSize)
                ->get(),
            'total' => $total
        ]);
    }

    public function repair (Request $request) {
        if (empty($request->input('trade_no'))) {
            abort(500, '参数错误');
        }
        $order = Order::where('trade_no', $request->input('trade_no'))
            ->where('status', 0)
            ->first();
        if (!$order) {
            abort(500, '订单不存在或订单已支付');
        }
        $order->status = 1;
        if (!$order->save()) {
            abort(500, '保存失败');
        }
        return response([
            'data' => true
        ]);
    }
}
