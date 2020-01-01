<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function check (Request $request) {
        if (empty($request->input('code'))) {
            abort(500, '参数错误');
        }
        $coupon = Coupon::where('code', $request->input('code'))->first();
        if (!$coupon) {
            abort(500, '优惠券无效');
        }
        if (time() < $coupon->started_at) {
            abort(500, '优惠券还未到可用时间');
        }
        if (time() > $coupon->ended_at) {
            abort(500, '优惠券已过期');
        }
        return response([
            'data' => $coupon
        ]);
    }
}
