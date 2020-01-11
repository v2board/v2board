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
        return response([
            'data' => Coupon::all()
        ]);
    }

    public function save(CouponSave $request)
    {
        $params = $request->only([
            'name',
            'type',
            'value',
            'started_at',
            'ended_at',
            'limit_use'
        ]);

        if (!$request->input('id')) {
            $params['code'] = Helper::randomChar(8);
            if (!Coupon::create($params)) {
                abort(500, '创建失败');
            }
        } else {
            if (!Coupon::find($request->input('id'))->update($params)) {
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
