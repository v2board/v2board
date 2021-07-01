<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ResetTraffic extends Command
{
    protected $builder;
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
        $this->builder = User::where('expired_at', '!=', NULL)
            ->where('expired_at', '>', time());
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::beginTransaction();
        $resetTrafficMethod = config('v2board.reset_traffic_method', 0);
        switch ((int)$resetTrafficMethod) {
            // 1 a month
            case 0:
                $this->resetByMonthFirstDay();
                break;
            // expire day
            case 1:
                $this->resetByExpireDay();
                break;
        }
        DB::commit();
    }

    private function resetByMonthFirstDay():void
    {
        $builder = $this->builder;
        if ((string)date('d') === '01') {
            $builder->update([
                'u' => 0,
                'd' => 0
            ]);
        }
    }

    private function resetByExpireDay():void
    {
        $builder = $this->builder;
        $lastDay = date('d', strtotime('last day of +0 months'));
        $users = [];
        foreach ($builder->get() as $item) {
            $expireDay = date('d', $item->expired_at);
            $today = date('d');
            if ($expireDay === $today) {
                array_push($users, $item->id);
            }

            if (($today === $lastDay) && $expireDay >= $lastDay) {
                array_push($users, $item->id);
            }
        }
        User::whereIn('id', $users)->update([
            'u' => 0,
            'd' => 0
        ]);
    }
}
