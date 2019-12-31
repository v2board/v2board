<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CouponSave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Utils\Helper;

class CouponController extends Controller
{
    public function fetch (Request $request) {
        return response([
            'data' => Coupon::all()
        ]);
    }

    public function save (Request $request) {
        $params = $request->only([
            'name',
            'type',
            'value',
            'expired_at',
            'limit_use'
        ]);

        $params['code'] = Helper::guid();
        if (!Coupon::create($params)) {
            abort(500, '创建失败');
        }

        return response([
            'data' => true
        ]);
    }
}