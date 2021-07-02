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
         $this->statOrder();
         $this->statServer();
    }

    private function statOrder()
    {
        $endAt = strtotime(date('Y-m-d'));
        $startAt = strtotime('-1 day', $endAt);
        $builder = Order::where('created_at', '>=', $startAt)
            ->where('created_at', '<', $endAt)
            ->whereNotIn('status', [0, 2]);
        $orderCount = $builder->count();
        $orderAmount = $builder->sum('total_amount');
        $builder = $builder->where('commission_balance', '!=', 0);
        $commissionCount = $builder->count();
        $commissionAmount = $builder->sum('commission_balance');
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

    private function statServer()
    {
        $endAt = strtotime(date('Y-m-d'));
        $startAt = strtotime('-1 day', $endAt);
        $statistics = ServerLog::select([
            'server_id',
            'method as server_type',
            DB::raw("sum(u) as u"),
            DB::raw("sum(d) as d"),
        ])
            ->where('log_at', '>=', $startAt)
            ->where('log_at', '<', $endAt)
            ->groupBy('server_id', 'method')
            ->get()
            ->toArray();
        foreach ($statistics as $statistic) {
            $statistic['record_type'] = 'd';
            $statistic['record_at'] = $startAt;
            StatServerJob::dispatch($statistic);
        }
    }
}
