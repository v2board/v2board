<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckCommission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:commission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '返佣服务';

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
        $this->autoCheck();
        $this->autoPayCommission();
    }

    public function autoCheck()
    {
        if ((int)config('v2board.commission_auto_check_enable', 1)) {
            Order::where('commission_status', 0)
                ->where('invite_user_id', '!=', NULL)
                ->whereNotIn('status', [0, 2])
                ->where('updated_at', '<=', strtotime('-3 day', time()))
                ->update([
                    'commission_status' => 1
                ]);
        }
    }

    public function autoPayCommission()
    {
        $order = Order::where('commission_status', 1)
            ->where('invite_user_id', '!=', NULL)
            ->get();
        foreach ($order as $item) {
            $inviter = User::find($item->invite_user_id);
            if (!$inviter) continue;
            if ((int)config('v2board.withdraw_close_enable', 0)) {
                $inviter->balance = $inviter->balance + $item->commission_balance;
            } else {
                $inviter->commission_balance = $inviter->commission_balance + $item->commission_balance;
            }
            DB::beginTransaction();
            if ($inviter->save()) {
                $item->commission_status = 2;
                if (!$item->save()) {
                    DB::rollBack();
                    continue;
                }
                DB::commit();
            }
        }
    }

}
