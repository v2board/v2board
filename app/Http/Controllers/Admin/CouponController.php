<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CouponSave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Utils\Helper;

class CouponController extends Controller
{
    public function fetch(Request $request)
    {
        $coupons = Coupon::all();
        foreach ($coupons as $k => $v) {
            if ($coupons[$k]['limit_plan_ids']) $coupons[$k]['limit_plan_ids'] = json_decode($coupons[$k]['limit_plan_ids']);
        }
        return response([
            'data' => $coupons
        ]);
    }

    public function save(CouponSave $request)
    {
        $params = $request->only(array_keys(CouponSave::RULES));
        if (isset($params['limit_plan_ids'])) {
            $params['limit_plan_ids'] = json_encode($params['limit_plan_ids']);
        }
        if (!$request->input('id')) {
            $params['code'] = Helper::randomChar(8);
            if (!Coupon::create($params)) {
                abort(500, '创建失败');
            }
        } else {
            try {
                Coupon::find($request->input('id'))->update($params);
            } catch (\Exception $e) {
                abort(500, '保存失败');
            }
        }

        return response([
            'data' => true
        ]);
    }

    public function drop(Request $request)
    {
        if (empty($request->input('id'))) {
            abort(500, '参数有误');
        }
        $coupon = Coupon::find($request->input('id'));
        if (!$coupon) {
            abort(500, '优惠券不存在');
        }
        if (!$coupon->delete()) {
            abort(500, '删除失败');
        }

        return response([
            'data' => true
        ]);
    }
}
