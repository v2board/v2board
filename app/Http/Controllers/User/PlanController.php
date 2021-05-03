<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;

class PlanController extends Controller
{
    public function fetch(Request $request)
    {
        if ($request->input('id')) {
            $plan = Plan::where('id', $request->input('id'))
                ->first();
            if (!$plan) {
                abort(500, __('user.plan.fetch.plan_not_exist'));
            }
            return response([
                'data' => $plan
            ]);
        }
        $plan = Plan::where('show', 1)
            ->orderBy('sort', 'ASC')
            ->get();
        return response([
            'data' => $plan
        ]);
    }
}
