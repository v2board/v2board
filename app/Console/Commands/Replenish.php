<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Services\PlanService;
use App\Utils\Helper;
use Illuminate\Console\Command;


class Replenish extends Command
{
    protected $builder;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customFunction:replenish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '0点的时候补货一次性流量套餐';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()//php artisan customFunction:replenish
    {
        ini_set('memory_limit', -1);

        $counts = PlanService::countActiveUsers();
        $plans = Plan::all();
        foreach ($plans as $k => $v) {
            $plans[$k]->count = 0;
            foreach ($counts as $kk => $vv) {
                if ($plans[$k]->id === $counts[$kk]->plan_id) $plans[$k]->count = $counts[$kk]->count;
            }
            if($plans[$k]->onetime_price > 0 && $plans[$k]->capacity_limit == $plans[$k]->count){
                $plans[$k]->capacity_limit = $plans[$k]->capacity_limit + 1;
                unset($plans[$k]->count);
                $plans[$k]->save();
                $this->info("{$plans[$k]->name} 套餐已补货：1个");
            }
        }

        $this->info("执行完毕");
    }
}
