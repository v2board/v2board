<?php

namespace App\Jobs;

use App\Models\StatServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StatServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $statistic;

    public $tries = 3;
    public $timeout = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $statistic)
    {
        $this->onQueue('stat_server');
        $this->statistic = $statistic;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $statistic = $this->statistic;
        $data = StatServer::where('record_at', $statistic['record_at'])
            ->where('server_id', $statistic['server_id'])
            ->first();
        if ($data) {
            try {
                $data->update($statistic);
            } catch (\Exception $e) {
                abort(500, '节点统计数据更新失败');
            }
        } else {
            if (!StatServer::create($statistic)) {
                abort(500, '节点统计数据创建失败');
            }
        }
    }
}
