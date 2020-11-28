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
            abort(500, '优惠券码不能为空');
        }
        $coupon = Coupon::where('code', $request->input('code'))->first();
        if (!$coupon) {
            abort(500, '优惠券无效');
        }
        if ($coupon->limit_use <= 0 && $coupon->limit_use !== NULL) {
            abort(500, '优惠券已无可用次数');
        }
        if (time() < $coupon->started_at) {
            abort(500, '优惠券还未到可用时间');
        }
        if (time() > $coupon->ended_at) {
            abort(500, '优惠券已过期');
        }
        if ($coupon->limit_plan_ids) {
            $limitPlanIds = json_decode($coupon->limit_plan_ids);
            if (!in_array($request->input('plan_id'), $limitPlanIds)) {
                abort(500, '这个计划无法使用该优惠码');
            }
        }
        return response([
            'data' => $coupon
        ]);
    }
}
