<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Plan;

class PlanController extends Controller
{
    public function fetch (Request $request) {
        if ($request->input('id')) {
            $plan = Plan::where('id', $request->input('id'))
                ->where('show', 1)
                ->first();
            if (!$plan) {
                abort(500, '该订阅不存在');
            }
            return response([
                'data' => $plan
            ]);
        }
        $plan = Plan::where('show', 1)
            ->get();
        return response([
            'data' => $plan
        ]);
    }
}
