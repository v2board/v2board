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

    public function incrementInventory(): bool
    {
        if ($this->plan->inventory_limit !== NULL) return true;
        return $this->plan->increment('inventory_limit');
    }

    public function decrementInventory(): bool
    {
        if ($this->plan->inventory_limit !== NULL) return true;
        return $this->plan->decrement('inventory_limit');
    }
}
