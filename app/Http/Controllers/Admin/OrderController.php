<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\OrderAssign;
use App\Http\Requests\Admin\OrderUpdate;
use App\Http\Requests\Admin\OrderFetch;
use App\Services\OrderService;
use App\Utils\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private function filter(Request $request, &$builder)
    {
        if ($request->input('filter')) {
            foreach ($request->input('filter') as $filter) {
                if ($filter['key'] === 'email') {
                    $user = User::where('email', "%{$filter['value']}%")->first();
                    if (!$user) continue;
                    $builder->where('user_id', $user->id);
                    continue;
                }
                if ($filter['condition'] === '模糊') {
                    $filter['condition'] = 'like';
                    $filter['value'] = "%{$filter['value']}%";
                }
                $builder->where($filter['key'], $filter['condition'], $filter['value']);
            }
        }
    }

    public function fetch(OrderFetch $request)
    {
        $current = $request->input('current') ? $request->input('current') : 1;
        $pageSize = $request->input('pageSize') >= 10 ? $request->input('pageSize') : 10;
        $orderModel = Order::orderBy('created_at', 'DESC');
        if ($request->input('is_commission')) {
            $orderModel->where('invite_user_id', '!=', NULL);
            $orderModel->whereNotIn('status', [0, 2]);
            $orderModel->where('commission_balance', '>', 0);
        }
        $this->filter($request, $orderModel);
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
        $params = $request->only([
            'status',
            'commission_status'
        ]);

        $order = Order::where('trade_no', $request->input('trade_no'))
            ->first();
        if (!$order) {
            abort(500, '订单不存在');
        }

        if (isset($params['status']) && (int)$params['status'] === 2) {
            $orderService = new OrderService($order);
            if (!$orderService->cancel()) {
                abort(500, '更新失败');
            }
            return response([
                'data' => true
            ]);
        }

        try {
            $order->update($params);
        } catch (\Exception $e) {
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

    public function assign(OrderAssign $request)
    {
        $plan = Plan::find($request->input('plan_id'));
        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            abort(500, '该用户不存在');
        }

        if (!$plan) {
            abort(500, '该订阅不存在');
        }

        DB::beginTransaction();
        $order = new Order();
        $orderService = new OrderService($order);
        $order->user_id = $user->id;
        $order->plan_id = $plan->id;
        $order->cycle = $request->input('cycle');
        $order->trade_no = Helper::guid();
        $order->total_amount = $request->input('total_amount');

        if ($order->cycle === 'reset_price') {
            $order->type = 4;
        } else if ($user->plan_id !== NULL && $order->plan_id !== $user->plan_id) {
            $order->type = 3;
        } else if ($user->expired_at > time() && $order->plan_id == $user->plan_id) {
            $order->type = 2;
        } else {
            $order->type = 1;
        }

        $orderService->setInvite($user);

        if (!$order->save()) {
            DB::rollback();
            abort(500, '订单创建失败');
        }

        DB::commit();

        return response([
            'data' => $order->trade_no
        ]);
    }
}
