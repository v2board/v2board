<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function cancel():bool
    {
        $order = $this->order;
        DB::beginTransaction();
        $order->status = 2;
        if (!$order->save()) {
            DB::rollBack();
            return false;
        }
        if ($order->balance_amount) {
            $userService = new UserService();
            if (!$userService->addBalance($order->user_id, $order->balance_amount)) {
                DB::rollBack();
                return false;
            }
        }
        DB::commit();
        return true;
    }

    public function create()
    {

    }

    public function setOrderType(User $user)
    {
        $order = $this->order;
        if ($order->cycle === 'reset_price') {
            $order->type = 4;
        } else if ($user->plan_id !== NULL && $order->plan_id !== $user->plan_id && $user->expired_at > time()) { // 用户订阅存在且用户订阅与购买订阅不同且用户订阅未过期 === 更换
            if (!(int)config('v2board.plan_change_enable', 1)) abort(500, '目前不允许更改订阅，请联系客服或提交工单操作');
            $order->type = 3;
            $this->getSurplusValue($user, $order);
            if ($order->surplus_amount >= $order->total_amount) {
                $order->refund_amount = $order->surplus_amount - $order->total_amount;
                $order->total_amount = 0;
            } else {
                $order->total_amount = $order->total_amount - $order->surplus_amount;
            }
        } else if ($user->expired_at > time() && $order->plan_id == $user->plan_id) { // 用户订阅未过期且购买订阅与当前订阅相同 === 续费
            $order->type = 2;
        } else { // 新购
            $order->type = 1;
        }
    }

    public function setVipDiscount(User $user)
    {
        $order = $this->order;
        if ($user->discount) {
            $order->discount_amount = $order->discount_amount + ($order->total_amount * ($user->discount / 100));
        }
        $order->total_amount = $order->total_amount - $order->discount_amount;
    }

    public function setInvite(User $user)
    {
        $order = $this->order;
        if ($user->invite_user_id && $order->total_amount > 0) {
            $order->invite_user_id = $user->invite_user_id;
            $commissionFirstTime = (int)config('v2board.commission_first_time_enable', 1);
            if (!$commissionFirstTime || ($commissionFirstTime && !Order::where('user_id', $user->id)->where('status', 3)->first())) {
                $inviter = User::find($user->invite_user_id);
                if ($inviter && $inviter->commission_rate) {
                    $order->commission_balance = $order->total_amount * ($inviter->commission_rate / 100);
                } else {
                    $order->commission_balance = $order->total_amount * (config('v2board.invite_commission', 10) / 100);
                }
            }
        }
    }

    private function getSurplusValue(User $user, Order $order)
    {
        if ($user->expired_at === NULL) {
            $this->getSurplusValueByOneTime($user, $order);
        } else {
            $this->getSurplusValueByCycle($user, $order);
        }
    }


    private function getSurplusValueByOneTime(User $user, Order $order)
    {
        $plan = Plan::find($user->plan_id);
        $trafficUnitPrice = $plan->onetime_price / $plan->transfer_enable;
        if ($user->discount && $trafficUnitPrice) {
            $trafficUnitPrice = $trafficUnitPrice - ($trafficUnitPrice * $user->discount / 100);
        }
        $notUsedTrafficPrice = $plan->transfer_enable - (($user->u + $user->d) / 1073741824);
        $result = $trafficUnitPrice * $notUsedTrafficPrice;
        $orderModel = Order::where('user_id', $user->id)->where('cycle', '!=', 'reset_price')->where('status', 3);
        $order->surplus_amount = $result > 0 ? $result : 0;
        $order->surplus_order_ids = json_encode(array_map(function ($v) { return $v['id'];}, $orderModel->get()->toArray()));
    }

    private function getSurplusValueByCycle(User $user, Order $order)
    {
        $strToMonth = [
            'month_price' => 1,
            'quarter_price' => 3,
            'half_year_price' => 6,
            'year_price' => 12
        ];
        $orderModel = Order::where('user_id', $user->id)
            ->where('cycle', '!=', 'reset_price')
            ->where('status', 3);
        $orderSurplusMonth = 0;
        $orderSurplusAmount = 0;
        $userSurplusMonth = ($user->expired_at - time()) / 2678400;
        foreach ($orderModel->get() as $item) {
            // 兼容历史余留问题
            if ($item->cycle === 'onetime_price') continue;
            $orderSurplusMonth = $orderSurplusMonth + $strToMonth[$item->cycle];
            $orderSurplusAmount = $orderSurplusAmount + ($item['total_amount'] + $item['balance_amount']);
        }
        if (!$orderSurplusMonth || !$orderSurplusAmount) return;
        $monthUnitPrice = $orderSurplusAmount / $orderSurplusMonth;
        // 如果用户过期月大于订单过期月
        if ($userSurplusMonth > $orderSurplusMonth) {
            $orderSurplusAmount = $orderSurplusMonth * $monthUnitPrice;
        } else {
            $orderSurplusAmount = $userSurplusMonth * $monthUnitPrice;
        }
        if (!$orderSurplusAmount) {
            return;
        }
        $order->surplus_amount = $orderSurplusAmount > 0 ? $orderSurplusAmount : 0;
        $order->surplus_order_ids = json_encode(array_map(function ($v) { return $v['id'];}, $orderModel->get()->toArray()));
    }

    public function success(string $callbackNo)
    {
        $order = $this->order;
        if ($order->status !== 0) {
            return true;
        }
        $order->status = 1;
        $order->callback_no = $callbackNo;
        return $order->save();
    }
}
