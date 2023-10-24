<?php

namespace App\Jobs;

use App\Models\StatServer;
use App\Models\StatUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class StatUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    //protected $u;
    //protected $d;
    //protected $userId;
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
    public function __construct(array $data, array $server, $protocol, $recordType = 'd')
    {
        $this->onQueue('stat');
        //$this->u = $u;
        //$this->d = $d;
        //$this->userId = $userId;
        $this->data =$data;
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
            foreach(array_keys($this->data) as $userId){
                $userdata = StatUser::where('record_at', $recordAt)
                    ->where('server_rate', $this->server['rate'])
                    ->where('user_id', $userId)
                    ->lockForUpdate()->first();
                if ($userdata) {
                    $userdata->update([
                        'u' => $userdata['u'] + $this->data[$userId][0],
                        'd' => $userdata['d'] + $this->data[$userId][1]
                    ]);
                } else {
                    $insertData[] = [
                        'user_id' => $userId,
                        'server_rate' => $this->server['rate'],
                        'u' => $this->data[$userId][0],
                        'd' => $this->data[$userId][1],
                        'record_type' => $this->recordType,
                        'record_at' => $recordAt
                    ];
                }
            }
            if (!empty($insertData)) {
                StatUser::upsert($insertData, ['user_id', 'server_rate', 'record_at']);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            abort(500, '用户统计数据失败'. $e->getMessage());
        }
    }
}
