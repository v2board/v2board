<?php

namespace App\Http\Controllers\V1\Guest;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

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
