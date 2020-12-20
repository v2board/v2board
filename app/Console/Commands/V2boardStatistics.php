<?php

namespace App\Console\Commands;

use App\Jobs\StatServerJob;
use Illuminate\Console\Command;
use App\Models\ServerLog;
use Illuminate\Support\Facades\DB;

class V2BoardStatistics extends Command
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
        $this->statServer();
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
            $statistic['record_type'] = 'm';
            $statistic['record_at'] = $startAt;
            StatServerJob::dispatch($statistic);
        }
    }
}
