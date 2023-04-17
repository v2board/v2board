<?php

namespace App\Console\Commands;

use App\Models\StatUser;
use App\Services\StatisticalService;
use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\StatOrder;
use App\Models\CommissionLog;
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
        $this->statUser();
    }

    private function statUser()
    {
        $createdAt = time();
        $recordAt = strtotime('-1 day', strtotime(date('Y-m-d')));
        $statService = new StatisticalService($recordAt);
        $stats = $statService->getStatUser();
        DB::beginTransaction();
        foreach ($stats as $stat) {
            if (!StatUser::insert([
                'user_id' => $stat['user_id'],
                'u' => $stat['u'],
                'd' => $stat['d'],
                'server_rate' => $stat['server_rate'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'record_type' => 'd',
                'record_at' => $recordAt
            ])) {
                DB::rollback();
                throw new \Exception('stat user fail');
            }
        }
        DB::commit();
        $statService->clearStatUser();
    }

    private function statOrder()
    {
        $endAt = strtotime(date('Y-m-d'));
        $startAt = strtotime('-1 day', $endAt);
        $orderBuilder = Order::where('paid_at', '>=', $startAt)
            ->where('paid_at', '<', $endAt)
            ->whereNotIn('status', [0, 2]);
        $orderCount = $orderBuilder->count();
        $orderAmount = $orderBuilder->sum('total_amount');
        $commissionLogBuilder = CommissionLog::where('created_at', '>=', $startAt)
            ->where('created_at', '<', $endAt);
        $commissionCount = $commissionLogBuilder->count();
        $commissionAmount = $commissionLogBuilder->sum('get_amount');
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
