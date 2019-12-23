<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Plan;

class PlanController extends Controller
{
    public function fetch (Request $request) {
        if (empty($request->input('plan_id'))) {
            abort(500, '参数错误');
        }
        $plan = Plan::find($request->input('plan_id'));
        if (!$plan) {
            abort(500, '该订阅不存在');
        }
        return response([
            'data' => $plan
        ]);
    }
}
