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
    protected $u;
    protected $d;
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
    public function __construct($u, $d, $server, $protocol, $recordType = 'd')
    {
        $this->onQueue('stat');
        $this->u = $u;
        $this->d = $d;
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

        $data = StatServer::lockForUpdate()
            ->where('record_at', $recordAt)
            ->where('server_id', $this->server['id'])
            ->where('server_type', $this->protocol)
            ->first();
        if ($data) {
            try {
                $data->update([
                    'u' => $data['u'] + $this->u,
                    'd' => $data['d'] + $this->d
                ]);
            } catch (\Exception $e) {
                abort(500, '节点统计数据更新失败');
            }
        } else {
            if (!StatServer::create([
                'server_id' => $this->server['id'],
                'server_type' => $this->protocol,
                'u' => $this->u,
                'd' => $this->d,
                'record_type' => $this->recordType,
                'record_at' => $recordAt
            ])) {
                abort(500, '节点统计数据创建失败');
            }
        }
    }
}
