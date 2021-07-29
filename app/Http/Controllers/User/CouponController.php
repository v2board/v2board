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
            abort(500, __('Coupon cannot be empty'));
        }
        $coupon = Coupon::where('code', $request->input('code'))->first();
        if (!$coupon) {
            abort(500, __('Invalid coupon'));
        }
        if ($coupon->limit_use <= 0 && $coupon->limit_use !== NULL) {
            abort(500, __('This coupon is no longer available'));
        }
        if (time() < $coupon->started_at) {
            abort(500, __('This coupon has not yet started'));
        }
        if (time() > $coupon->ended_at) {
            abort(500, __('This coupon has expired'));
        }
        if ($coupon->limit_plan_ids) {
            $limitPlanIds = json_decode($coupon->limit_plan_ids);
            if (!in_array($request->input('plan_id'), $limitPlanIds)) {
                abort(500, __('The coupon code cannot be used for this subscription'));
            }
        }
        return response([
            'data' => $coupon
        ]);
    }
}
