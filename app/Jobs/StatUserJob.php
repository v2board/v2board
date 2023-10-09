<?php

namespace App\Jobs;

use App\Models\StatServer;
use App\Models\StatUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StatUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $u;
    protected $d;
    protected $userId;
    protected $server;
    protected $protocol;
    protected $recordType;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($u, $d, $userId, array $server, $protocol, $recordType = 'd')
    {
        $this->onQueue('stat');
        $this->u = $u;
        $this->d = $d;
        $this->userId = $userId;
        $this->server = $server;
        $this->protocol = $protocol;
        $this->recordType = $recordType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $recordAt = strtotime(date('Y-m-d'));
        if ($this->recordType === 'm') {
            //
        }

        $data = StatUser::where('record_at', $recordAt)
            ->where('server_rate', $this->server['rate'])
            ->where('user_id', $this->userId)
            ->first();
        if ($data) {
            try {
                $data->update([
                    'u' => $data['u'] + ($this->u * $this->server['rate']),
                    'd' => $data['d'] + ($this->d * $this->server['rate'])
                ]);
            } catch (\Exception $e) {
                abort(500, '用户统计数据更新失败');
            }
        } else {
            if (!StatUser::create([
                'user_id' => $this->userId,
                'server_rate' => $this->server['rate'],
                'u' => $this->u,
                'd' => $this->d,
                'record_type' => $this->recordType,
                'record_at' => $recordAt
            ])) {
                abort(500, '用户统计数据创建失败');
            }
        }
    }
}
