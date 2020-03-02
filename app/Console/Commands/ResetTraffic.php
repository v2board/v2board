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
                $this->resetByMonthFirstDay($user);
                break;
            // expire day
            case 1:
                $this->resetByExpireDay($user);
                break;
        }
    }

    private function resetByMonthFirstDay(User $user):void
    {
        $user->update([
            'u' => 0,
            'd' => 0
        ]);
    }

    private function resetByExpireDay(User $user):void
    {
        $date = date('Y-m-d', time());
        $startAt = strtotime($date);
        $endAt = $startAt + 24 * 3600;
        $lastDay = (string)date('d', strtotime('last day of +0 months'));
        if ($lastDay === '29') {
            $endAt = $startAt + 72 * 3600;
        }
        if ($lastDay === '30') {
            $endAt = $startAt + 48 * 3600;
        }
        $user->where('expired_at', '>=', $startAt)
            ->where('expired_at', '<', $endAt)
            ->update([
                'u' => 0,
                'd' => 0
            ]);
    }
}
