<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrafficFetchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $u;
    protected $d;
    protected $userId;
    protected $server;
    protected $protocol;

    public $tries = 3;
    public $timeout = 10;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($u, $d, $userId, array $server, $protocol)
    {
        $this->onQueue('traffic_fetch');
        $this->u = $u;
        $this->d = $d;
        $this->userId = $userId;
        $this->server = $server;
        $this->protocol = $protocol;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::lockForUpdate()->find($this->userId);
        if (!$user) return;

        $user->t = time();
        $user->u = $user->u + ($this->u * $this->server['rate']);
        $user->d = $user->d + ($this->d * $this->server['rate']);
        if (!$user->save()) {
            info("流量更新失败\n未记录用户ID:{$this->userId}\n未记录上行:{$user->u}\n未记录下行:{$user->d}");
        }
    }
}
