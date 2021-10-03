<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function check(Request $request)
    {
        if (empty($request->input('code'))) {
            abort(500, __('Coupon cannot be empty'));
        }
        $couponService = new CouponService($request->input('code'));
        $couponService->setPlanId($request->input('plan_id'));
        $couponService->setUserId($request->session()->get('id'));
        $couponService->check();
        return response([
            'data' => $couponService->getCoupon()
        ]);
    }
}
