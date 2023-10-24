<?php

namespace App\Jobs;

use App\Models\StatServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class StatServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    //protected $u;
    //protected $d;
    protected $data;
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
    public function __construct(array $data,array $server, $protocol, $recordType = 'd')
    {
        $this->onQueue('stat');
        //$this->u = $u;
        //$this->d = $d;
        $this->data = $data;
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
        try {
            DB::beginTransaction();
            $u = 0;
            $d = 0;
            foreach(array_keys($this->data) as $userId){
                $u += $this->data[$userId][0];
                $d += $this->data[$userId][1];
            }
            $serverdata = StatServer::lockForUpdate()
                ->where('record_at', $recordAt)
                ->where('server_id', $this->server['id'])
                ->where('server_type', $this->protocol)
                ->lockForUpdate()->first();
            if ($serverdata) {
                $serverdata->update([
                    'u' => $serverdata['u'] + $u,
                    'd' => $serverdata['d'] + $d
                ]);
            } else {
                StatServer::create([
                    'server_id' => $this->server['id'],
                    'server_type' => $this->protocol,
                    'u' => $u,
                    'd' => $d,
                    'record_type' => $this->recordType,
                    'record_at' => $recordAt
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            abort(500, '节点统计数据失败'. $e->getMessage());
        }
    }
}
