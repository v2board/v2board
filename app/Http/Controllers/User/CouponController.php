<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function check(Request $request)
    {
        if (empty($request->input('code'))) {
            abort(500, __('user.coupon.check.coupon_not_empty'));
        }
        $coupon = Coupon::where('code', $request->input('code'))->first();
        if (!$coupon) {
            abort(500, __('user.coupon.check.coupon_invalid'));
        }
        if ($coupon->limit_use <= 0 && $coupon->limit_use !== NULL) {
            abort(500, __('user.coupon.check.coupon_not_available_by_number'));
        }
        if (time() < $coupon->started_at) {
            abort(500, __('user.coupon.check.coupon_not_available_by_time'));
        }
        if (time() > $coupon->ended_at) {
            abort(500, __('user.coupon.check.coupon_expired'));
        }
        if ($coupon->limit_plan_ids) {
            $limitPlanIds = json_decode($coupon->limit_plan_ids);
            if (!in_array($request->input('plan_id'), $limitPlanIds)) {
                abort(500, __('user.coupon.check.coupon_limit_plan'));
            }
        }
        return response([
            'data' => $coupon
        ]);
    }
}
