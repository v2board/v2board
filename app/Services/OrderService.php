<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    CONST STR_TO_TIME = [
        'month_price' => 1,
        'quarter_price' => 3,
        'half_year_price' => 6,
        'year_price' => 12,
        'two_year_price' => 24,
        'three_year_price' => 36
    ];
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function open(): bool
    {
        $order = $this->order;
        $user = User::find($order->user_id);
        $plan = Plan::find($order->plan_id);

        if ($order->refund_amount) {
            $user->balance = $user->balance + $order->refund_amount;
        }
        DB::beginTransaction();
        if ($order->surplus_order_ids) {
            try {
                Order::whereIn('id', json_decode($order->surplus_order_ids))->update([
                    'status' => 4
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return false;
            }
        }
        switch ((string)$order->cycle) {
            case 'onetime_price':
                $this->buyByOneTime($user, $plan);
                break;
            case 'reset_price':
                $this->buyByResetTraffic($user);
                break;
            default:
                $this->buyByCycle($order, $user, $plan);
        }

        if ((int)config('v2board.renew_reset_traffic_enable', 0)) $this->buyByResetTraffic($user);

        if (!$user->save()) {
            DB::rollBack();
            return false;
        }
        $order->status = 3;
        if (!$order->save()) {
            DB::rollBack();
            return false;
        }

        DB::commit();
        return true;
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

    public function setOrderType(User $user)
    {
        $order = $this->order;
        if ($order->cycle === 'reset_price') {
            $order->type = 4;
        } else if ($user->plan_id !== NULL && $order->plan_id !== $user->plan_id && ($user->expired_at > time() || $user->expired_at === NULL)) {
            if (!(int)config('v2board.plan_change_enable', 1)) abort(500, '目前不允许更改订阅，请联系客服或提交工单操作');
            $order->type = 3;
            if ((int)config('v2board.surplus_enable', 1)) $this->getSurplusValue($user, $order);
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
            if (!$commissionFirstTime || ($commissionFirstTime && !$this->haveValidOrder($user))) {
                $inviter = User::find($user->invite_user_id);
                if ($inviter && $inviter->commission_rate) {
                    $order->commission_balance = $order->total_amount * ($inviter->commission_rate / 100);
                } else {
                    $order->commission_balance = $order->total_amount * (config('v2board.invite_commission', 10) / 100);
                }
            }
        }
    }

    private function haveValidOrder(User $user)
    {
        return Order::where('user_id', $user->id)
            ->whereIn('status', [3, 4])
            ->first();
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
        $notUsedTraffic = $plan->transfer_enable - (($user->u + $user->d) / 1073741824);
        $result = $trafficUnitPrice * $notUsedTraffic;
        $orderModel = Order::where('user_id', $user->id)->where('cycle', '!=', 'reset_price')->where('status', 3);
        $order->surplus_amount = $result > 0 ? $result : 0;
        $order->surplus_order_ids = json_encode(array_column($orderModel->get()->toArray(), 'id'));
    }

    private function orderIsUsed(Order $order):bool
    {
        $month = self::STR_TO_TIME[$order->cycle];
        $orderExpireDay = strtotime('+' . $month . ' month', $order->created_at->timestamp);
        if ($orderExpireDay < time()) return true;
        return false;
    }

    private function getSurplusValueByCycle(User $user, Order $order)
    {
        $orderModel = Order::where('user_id', $user->id)
            ->where('cycle', '!=', 'reset_price')
            ->where('status', 3);
        $orders = $orderModel->get();
        $orderSurplusMonth = 0;
        $orderSurplusAmount = 0;
        $userSurplusMonth = ($user->expired_at - time()) / 2678400;
        foreach ($orders as $k => $item) {
            // 兼容历史余留问题
            if ($item->cycle === 'onetime_price') continue;
            if ($this->orderIsUsed($item)) continue;
            $orderSurplusMonth = $orderSurplusMonth + self::STR_TO_TIME[$item->cycle];
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
        $order->surplus_order_ids = json_encode(array_column($orders->toArray(), 'id'));
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


    private function buyByResetTraffic(User $user)
    {
        $user->u = 0;
        $user->d = 0;
    }

    private function buyByCycle(Order $order, User $user, Plan $plan)
    {
        // change plan process
        if ((int)$order->type === 3) {
            $user->expired_at = time();
        }
        $user->transfer_enable = $plan->transfer_enable * 1073741824;
        // 从一次性转换到循环
        if ($user->expired_at === NULL) $this->buyByResetTraffic($user);
        // 新购
        if ($order->type === 1) $this->buyByResetTraffic($user);
        $user->plan_id = $plan->id;
        $user->group_id = $plan->group_id;
        $user->expired_at = $this->getTime($order->cycle, $user->expired_at);
    }

    private function buyByOneTime(User $user, Plan $plan)
    {
        $this->buyByResetTraffic($user);
        $user->transfer_enable = $plan->transfer_enable * 1073741824;
        $user->plan_id = $plan->id;
        $user->group_id = $plan->group_id;
        $user->expired_at = NULL;
    }

    private function getTime($str, $timestamp)
    {
        if ($timestamp < time()) {
            $timestamp = time();
        }
        switch ($str) {
            case 'month_price':
                return strtotime('+1 month', $timestamp);
            case 'quarter_price':
                return strtotime('+3 month', $timestamp);
            case 'half_year_price':
                return strtotime('+6 month', $timestamp);
            case 'year_price':
                return strtotime('+12 month', $timestamp);
            case 'two_year_price':
                return strtotime('+24 month', $timestamp);
            case 'three_year_price':
                return strtotime('+36 month', $timestamp);
        }
    }
}
