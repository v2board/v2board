<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PlanSave;
use App\Http\Requests\Admin\PlanUpdate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Order;
use App\Models\User;

class PlanController extends Controller
{
    public function index (Request $request) {
        return response([
            'data' => Plan::get()
        ]);
    }
    
    public function save (PlanSave $request) {
        if ($request->input('id')) {
            $plan = Plan::find($request->input('id'));
            if (!$plan) {
                abort(500, '该订阅不存在');
            }
        } else {
            $plan = new Plan();
        }
        $plan->name = $request->input('name');
        $plan->content = $request->input('content');
        if ($plan->content) {
            $plan->content = str_replace(PHP_EOL, '', $plan->content);
        }
        $plan->transfer_enable = $request->input('transfer_enable');
        $plan->group_id = $request->input('group_id');
        $plan->month_price = $request->input('month_price');
        $plan->quarter_price = $request->input('quarter_price');
        $plan->half_year_price = $request->input('half_year_price');
        $plan->year_price = $request->input('year_price');
        
        return response([
            'data' => $plan->save()
        ]);
    }
    
    public function drop (Request $request) {
        if (Order::where('plan_id', $request->input('id'))->first()) {
            abort(500, '该订阅下存在订单无法删除');
        }
        if (User::where('plan_id', $request->input('id'))->first()) {
            abort(500, '该订阅下存在用户无法删除');
        }
        if ($request->input('id')) {
            $plan = Plan::find($request->input('id'));
            if (!$plan) {
                abort(500, '该订阅ID不存在');
            }
        }
        return response([
            'data' => $plan->delete()
        ]);
    }

    public function update (PlanUpdate $request) {
        $updateData = $request->only([
            'show',
            'renew'
        ]);
        
        $plan = Plan::find($request->input('id'));
        if (!$plan) {
            abort(500, '该订阅不存在');
        }
        if (!$plan->update($updateData)) {
            abort(500, '保存失败');
        }

        return response([
            'data' => true
        ]);
    }
}
