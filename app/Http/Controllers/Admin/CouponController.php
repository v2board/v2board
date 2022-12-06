<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CouponSave;
use App\Http\Requests\Admin\CouponGenerate;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Utils\Helper;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    public function fetch(Request $request)
    {
        $current = $request->input('current') ? $request->input('current') : 1;
        $pageSize = $request->input('pageSize') >= 10 ? $request->input('pageSize') : 10;
        $sortType = in_array($request->input('sort_type'), ['ASC', 'DESC']) ? $request->input('sort_type') : 'DESC';
        $sort = $request->input('sort') ? $request->input('sort') : 'id';
        $builder = Coupon::orderBy($sort, $sortType);
        $total = $builder->count();
        $coupons = $builder->forPage($current, $pageSize)
            ->get();
        return response([
            'data' => $coupons,
            'total' => $total
        ]);
    }

    public function show(Request $request)
    {
        if (empty($request->input('id'))) {
            abort(500, 'Wrong parameters');
        }
        $coupon = Coupon::find($request->input('id'));
        if (!$coupon) {
            abort(500, 'Coupon does not exist');
        }
        $coupon->show = $coupon->show ? 0 : 1;
        if (!$coupon->save()) {
            abort(500, 'Failed to show');
        }

        return response([
            'data' => true
        ]);
    }

    public function generate(CouponGenerate $request)
    {
        if ($request->input('generate_count')) {
            $this->multiGenerate($request);
            return;
        }

        $params = $request->validated();
        if (!$request->input('id')) {
            if (!isset($params['code'])) {
                $params['code'] = Helper::randomChar(8);
            }
            if (!Coupon::create($params)) {
                abort(500, 'Failed to create');
            }
        } else {
            try {
                Coupon::find($request->input('id'))->update($params);
            } catch (\Exception $e) {
                abort(500, 'Failed to update');
            }
        }

        return response([
            'data' => true
        ]);
    }

    private function multiGenerate(CouponGenerate $request)
    {
        $coupons = [];
        $coupon = $request->validated();
        $coupon['created_at'] = $coupon['updated_at'] = time();
        unset($coupon['generate_count']);
        for ($i = 0;$i < $request->input('generate_count');$i++) {
            $coupon['code'] = Helper::randomChar(8);
            array_push($coupons, $coupon);
        }
        DB::beginTransaction();
        if (!Coupon::insert(array_map(function ($item) use ($coupon) {
            // format data
            if (isset($item['limit_plan_ids']) && is_array($item['limit_plan_ids'])) {
                $item['limit_plan_ids'] = json_encode($coupon['limit_plan_ids']);
            }
            if (isset($item['limit_period']) && is_array($item['limit_period'])) {
                $item['limit_period'] = json_encode($coupon['limit_period']);
            }
            return $item;
        }, $coupons))) {
            DB::rollBack();
            abort(500, 'Failed to generate');
        }
        DB::commit();
        $data = "Name, type, amount or percentage, start time, end time, available times, available for subscription, coupon code, generation time\r\n";
        foreach($coupons as $coupon) {
            $type = ['', 'Amount', 'Proportion'][$coupon['type']];
            $value = ['', ($coupon['value'] / 100),$coupon['value']][$coupon['type']];
            $startTime = date('Y-m-d H:i:s', $coupon['started_at']);
            $endTime = date('Y-m-d H:i:s', $coupon['ended_at']);
            $limitUse = $coupon['limit_use'] ?? 'Unrestricted';
            $createTime = date('Y-m-d H:i:s', $coupon['created_at']);
            $limitPlanIds = isset($coupon['limit_plan_ids']) ? implode("/", $coupon['limit_plan_ids']) : 'Unrestricted';
            $data .= "{$coupon['name']},{$type},{$value},{$startTime},{$endTime},{$limitUse},{$limitPlanIds},{$coupon['code']},{$createTime}\r\n";
        }
        echo $data;
    }

    public function drop(Request $request)
    {
        if (empty($request->input('id'))) {
            abort(500, 'Wrong parameters');
        }
        $coupon = Coupon::find($request->input('id'));
        if (!$coupon) {
            abort(500, 'Coupon does not exist');
        }
        if (!$coupon->delete()) {
            abort(500, 'Failed to delete');
        }

        return response([
            'data' => true
        ]);
    }
}
