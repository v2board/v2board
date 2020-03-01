<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetTraffic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:traffic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '流量清空';

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
        $user = User::where('expired_at', '!=', NULL);
        $resetTrafficMethod = (int)config('v2board.reset_traffic_method', 0);
        switch ($resetTrafficMethod) {
            // 1 a month
            case 0:
                $user->update([
                    'u' => 0,
                    'd' => 0
                ]);
                break;
            // expire day
            case 1:
                $startAt = strtotime(date('Y-m-d', time()));
                $user->where('expired_at', '>=', $startAt)
                    ->where('expired_at', '<', $startAt + 24 * 3600)
                    ->update([
                        'u' => 0,
                        'd' => 0
                    ]);
        }
    }
}
