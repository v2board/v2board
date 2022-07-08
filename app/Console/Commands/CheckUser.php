<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用户检查任务';

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
        $this->resetExpiredUserPlan();
    }

    private function resetExpiredUserPlan($day = 14)
    {
        User::where('expired_at', '<', $day * 86400)
            ->whereNotNull('expired_at')
            ->update([
            'plan_id' => NULL,
            'group_id' => NULL
        ]);
    }
}
