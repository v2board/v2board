<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\OrderUpdate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Plan;

class OrderController extends Controller
{
    public function fetch(Request $request)
    {
        $current = $request->input('current') ? $request->input('current') : 1;
        $pageSize = $request->input('pageSize') >= 10 ? $request->input('pageSize') : 10;
        $orderModel = Order::orderBy('created_at', 'DESC');
        if ($request->input('trade_no')) {
            $orderModel->where('trade_no', $request->input('trade_no'));
        }
        if ($request->input('is_commission')) {
            $orderModel->where('invite_user_id', '!=', NULL);
            $orderModel->where('status', 3);
        }
        if ($request->input('id')) {
            $orderModel->where('id', $request->input('id'));
        }
        if ($request->input('user_id')) {
            $orderModel->where('user_id', $request->input('user_id'));
        }
        $total = $orderModel->count();
        $res = $orderModel->forPage($current, $pageSize)
            ->get();
        $plan = Plan::get();
        for ($i = 0; $i < count($res); $i++) {
            for ($k = 0; $k < count($plan); $k++) {
                if ($plan[$k]['id'] == $res[$i]['plan_id']) {
                    $res[$i]['plan_name'] = $plan[$k]['name'];
                }
            }
        }
        return response([
            'data' => $res,
            'total' => $total
        ]);
    }

    public function update(OrderUpdate $request)
    {
        $updateData = $request->only([
            'status',
            'commission_status'
        ]);

        $order = Order::where('trade_no', $request->input('trade_no'))
            ->first();
        if (!$order) {
            abort(500, '订单不存在');
        }

        if (!$order->update($updateData)) {
            abort(500, '更新失败');
        }

        return response([
            'data' => true
        ]);
    }

    public function repair(Request $request)
    {
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
