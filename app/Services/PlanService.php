<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;

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
        $count = User::where('plan_id', $this->plan->plan_id)->count();
        return $this->plan->capacity_limit - $count;
    }
}
