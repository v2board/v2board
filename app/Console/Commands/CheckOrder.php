<?php

namespace App\Console\Commands;

use App\Jobs\OrderHandleJob;
use App\Services\OrderService;
use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class CheckOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '订单检查任务';

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
        ini_set('memory_limit', -1);
        $orders = Order::whereIn('status', [0, 1])
            ->orderBy('created_at', 'ASC')
            ->get();
        foreach ($orders as $order) {
            OrderHandleJob::dispatch($order->trade_no);
        }
    }
}
