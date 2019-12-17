<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Models\Server;
use App\Models\ServerLog;
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
        $this->setMonthServerTrafficTotal();
    }

    private function setMonthIncome() {
        Redis::set(
            'month_income',
            Order::where('created_at', '>=', strtotime(date('Y-m-1')))
                ->where('created_at', '<', time())
                ->where('status', '3')
                ->sum('total_amount')
        );
    }

    private function setMonthRegisterTotal() {
        Redis::set(
            'month_register_total',
            User::where('created_at', '>=', strtotime(date('Y-m-1')))
                ->where('created_at', '<', time())
                ->count()
        );
    }

    private function setMonthServerTrafficTotal () {
        $servers = Server::get();
        foreach ($servers as $item) {
            $serverLog = ServerLog::where('created_at', '>=', $item->created_at)
                ->where('created_at', '<', strtotime('+1 month', $item->created_at))
                ->where('node_id', $item->id);
            Redis::set('month_server_traffic_total_u_' . $item->id, $serverLog->sum('u'));
            Redis::set('month_server_traffic_total_d_' . $item->id, $serverLog->sum('d'));
        }
    }
}
