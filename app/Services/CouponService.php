<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CouponService
{
    public $order;

    public function __construct($code)
    {
        $this->coupon = Coupon::where('code', $code)->first();
        if (!$this->coupon) {
            abort(500, '优惠券无效');
        }
        if ($this->coupon->limit_use <= 0 && $this->coupon->limit_use !== NULL) {
            abort(500, '优惠券已无可用次数');
        }
        if (time() < $this->coupon->started_at) {
            abort(500, '优惠券还未到可用时间');
        }
        if (time() > $this->coupon->ended_at) {
            abort(500, '优惠券已过期');
        }
    }

    public function use(Order $order)
    {
        switch ($this->coupon->type) {
            case 1:
                $order->discount_amount = $this->coupon->value;
                break;
            case 2:
                $order->discount_amount = $order->total_amount * ($this->coupon->value / 100);
                break;
        }
        if ($this->coupon->limit_use !== NULL) {
            $this->coupon->limit_use = $this->coupon->limit_use - 1;
            if (!$this->coupon->save()) {
                return false;
            }
        }
        if ($this->coupon->limit_plan_ids) {
            $limitPlanIds = json_decode($this->coupon->limit_plan_ids);
            if (!in_array($order->plan_id, $limitPlanIds)) {
                return false;
            }
        }
        return true;
    }

    public function getId()
    {
        return $this->coupon->id;
    }
}
