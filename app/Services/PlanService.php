<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlanService
{
    public $plan;

    public function __construct(int $planId)
    {
        $this->plan = Plan::lockForUpdate()->find($planId);
    }

    public function haveCapacity(): bool
    {
        if ($this->plan->capacity_limit === NULL) return true;
        $count = self::countActiveUsers();
        $count = $count[$this->plan->id]['count'] ?? 0;
        return ($this->plan->capacity_limit - $count) > 0;
    }

    public static function countActiveUsers()
    {
        return User::select(
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
}
