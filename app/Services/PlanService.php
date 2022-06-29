<?php

namespace App\Services;

use App\Models\Plan;

class PlanService
{
    public $plan;

    public function __construct(int $planId)
    {
        $this->plan = Plan::lockForUpdate()->find($planId);
    }

    public function incrementInventory()
    {
        if ($this->plan->inventory_limit !== NULL) {
            return $this->plan->increment('inventory_limit');
        }
    }

    public function decrementInventory()
    {
        if ($this->plan->inventory_limit !== NULL) {
            return $this->plan->decrement('inventory_limit');
        }
    }
}
