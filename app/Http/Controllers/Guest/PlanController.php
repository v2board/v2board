<?php

namespace App\Http\Controllers\Guest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Plan;

class PlanController extends Controller
{
    public function fetch(Request $request)
    {
        $plan = Plan::where('show', 1)->get();
        return response([
            'data' => $plan
        ]);
    }
}
