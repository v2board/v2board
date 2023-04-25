<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use App\Models\Plan;
use App\Services\PlanService;
use App\Utils\Helper;
use Illuminate\Console\Command;


class addCoupon extends Command
{
    protected $builder;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customFunction:addCoupon
                            {coupon_id : coupon_id}
                            {random_number : number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时补充优惠券';

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
    public function handle()//php artisan customFunction:addCoupon
    {
        ini_set('memory_limit', -1);
        $this->info("功能：定时补充优惠券\n");
        $coupon_id = $this->argument('coupon_id');
        $base = $this->argument('random_number');
        $number = rand($base - 2, $base + 2);


        if ($number > 0){
            if(Coupon::where('ended_at', '!=', NULL)
                ->where('ended_at', '>', time())
                ->where('id', '=', $coupon_id)
                ->where('limit_use', '=', 0)
                ->update(['limit_use' => $number])){

                $this->info("已为优惠券补充 {$number} 个");
            }
        }
    }
}
