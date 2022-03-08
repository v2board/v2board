<?php

namespace App\Console\Commands;

use App\Jobs\StatServerJob;
use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\StatOrder;
use App\Models\ServerLog;
use Illuminate\Support\Facades\DB;

class V2boardStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2board:statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计任务';

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
        $this->statOrder();
    }

    private function statOrder()
    {
        $endAt = strtotime(date('Y-m-d'));
        $startAt = strtotime('-1 day', $endAt);
        $builder = Order::where('paid_at', '>=', $startAt)
            ->where('paid_at', '<', $endAt)
            ->whereNotIn('status', [0, 2]);
        $orderCount = $builder->count();
        $orderAmount = $builder->sum('total_amount');
        $builder = $builder->whereNotNull('actual_commission_balance');
        $commissionCount = $builder->count();
        $commissionAmount = $builder->sum('actual_commission_balance');
        $data = [
            'order_count' => $orderCount,
            'order_amount' => $orderAmount,
            'commission_count' => $commissionCount,
            'commission_amount' => $commissionAmount,
            'record_type' => 'd',
            'record_at' => $startAt
        ];
        $statistic = StatOrder::where('record_at', $startAt)
            ->where('record_type', 'd')
            ->first();
        if ($statistic) {
            $statistic->update($data);
            return;
        }
        StatOrder::create($data);
    }
}
