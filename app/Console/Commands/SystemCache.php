<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Utils\Helper;
use Illuminate\Support\Facades\Redis;

class SystemCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '系统缓存任务';

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
    public function handle()
    {
        $this->setMonthIncome();
        $this->setMonthRegisterTotal();
    }

    private function setMonthIncome() {
        Redis::set(
            'getMonthIncome',
            Order::where('created_at', '>', strtotime(date('Y-m-1')))
                ->where('created_at', '>', time())
                ->where('status', '3')
                ->where('callback_no', '!=', NULL)
                ->sum('total_amount')
        );
    }

    private function setMonthRegisterTotal() {
        Redis::set(
            'getMonthRegisterTotal',
            User::where('created_at', '>', strtotime(date('Y-m-1')))
                ->where('created_at', '>', time())
                ->count()
        );
    }
}
