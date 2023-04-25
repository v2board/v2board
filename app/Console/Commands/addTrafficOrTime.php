<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class addTrafficOrTime extends Command
{
    protected $builder;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customFunction:compensate
                            {plan_id : plan_id}
                            {type : 1 for time 2 for traffic}
                            {value : GB or day}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '批量添加用户流量或者到期时间 3个参数：plan_id type （1是时间 2是流量） value（GB或天数）';

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
    public function handle()//php artisan customFunction:compensate
    {
        if (!$this->confirm("确定要这么做吗？请确认3个参数是正确的：plan_id type （1是时间 2是流量） value（GB或天数）")) {
            return;
        }
        ini_set('memory_limit', -1);
        $this->info("功能：批量添加用户流量或者到期时间\n");


        $type = $this->argument('type');
        $plan_id = $this->argument('plan_id');
        $value = $this->argument('value');

        if($plan_id != 6 && $plan_id != 7){
            $users = User::where('expired_at', '!=', NULL)
                ->where('expired_at', '>', time())
                ->where('plan_id', '=', $plan_id)
                ->get();
            if($type == 1){
                foreach ($users as $user)
                {
                    $day = $value;
                    $user->expired_at = $user->expired_at + (86400 * $day);
                    $user->save();
                    $this->info("已为用户{$user->email}延长到期时间: {$day} 天 ");
                }
            }

            if($type == 2){
                foreach ($users as $user)
                {
                    $GB = $value;
                    $user->transfer_enable = $user->transfer_enable + (1073741824 * $GB);
                    $user->c_transfer_plan_enable = $user->c_transfer_plan_enable + (1073741824 * $GB);
                    $user->save();
                    $this->info("已为用户{$user->email}增加流量: {$GB} GB ");
                }
            }
        }else{
            $users = User::where('plan_id', '=', $plan_id)->get();
            if($type == 1){
                $this->info("一次性套餐不能延长时间！！！");
            }

            if($type == 2){
                foreach ($users as $user)
                {
                    $GB = $value;
                    $user->transfer_enable = $user->transfer_enable + (1073741824 * $GB);
                    $user->c_transfer_plan_enable = $user->c_transfer_plan_enable + (1073741824 * $GB);
                    $user->save();
                    $this->info("已为用户{$user->email}增加流量: {$GB} GB ");
                }
            }
        }

    }


}
