<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    public function fetch(Request $request)
    {
        $user = User::find($request->session()->get('id'));
        if ($request->input('id')) {
            $plan = Plan::where('id', $request->input('id'))->first();
            if (!$plan) {
                abort(500, __('Subscription plan does not exist'));
            }
            if ((!$plan->show && !$plan->renew) || (!$plan->show && $user->plan_id !== $plan->id)) {
                abort(500, __('Subscription plan does not exist'));
            }
            return response([
                'data' => $plan
            ]);
        } else {
            $counts = User::select(
                DB::raw("plan_id"),
                DB::raw("count(*) as count")
            )
                ->where('plan_id', '!=', NULL)
                ->where(function ($query) {
                    $query->where('expired_at', '>=', time())
                        ->orWhere('expired_at', NULL);
                })
                ->groupBy("plan_id")
                ->get()
                ->keyBy('plan_id');
        }
        $plans = Plan::where('show', 1)
            ->orderBy('sort', 'ASC')
            ->get();
        if (isset($counts)) {
            foreach ($plans as $k => $v) {
                if (isset($counts[$plans[$k]->id])) {
                    $plans[$k]->capacity_limit = $plans[$k]->capacity_limit - $counts[$plans[$k]->id]->count;
                }
            }
        }
        return response([
            'data' => $plans
        ]);
    }
}
