<?php

namespace App\Http\Controllers\Staff;

use App\Http\Requests\Admin\PlanSave;
use App\Http\Requests\Admin\PlanSort;
use App\Http\Requests\Admin\PlanUpdate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    public function fetch(Request $request)
    {
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
            ->get();
        $plans = Plan::orderBy('sort', 'ASC')->get();
        foreach ($plans as $k => $v) {
            $plans[$k]->count = 0;
            foreach ($counts as $kk => $vv) {
                if ($plans[$k]->id === $counts[$kk]->plan_id) $plans[$k]->count = $counts[$kk]->count;
            }
        }
        return response([
            'data' => $plans
        ]);
    }
}
