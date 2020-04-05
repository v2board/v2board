<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\MailLog;
use App\Jobs\SendEmailJob;

class SendRemindMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:remindMail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送提醒邮件';

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
        $users = User::all();
        foreach ($users as $user) {
            if ($user->remind_expire) $this->remindExpire($user);
            if ($user->remind_traffic) $this->remindTraffic($user);
        }
    }

    private function remindExpire($user)
    {
        if ($user->expired_at !== NULL && ($user->expired_at - 86400) < time() && $user->expired_at > time()) {
            SendEmailJob::dispatch([
                'email' => $user->email,
                'subject' => '在' . config('v2board.app_name', 'V2board') . '的服务即将到期',
                'template_name' => 'remindExpire',
                'template_value' => [
                    'name' => config('v2board.app_name', 'V2Board'),
                    'url' => config('v2board.app_url')
                ]
            ]);
        }
    }

    private function remindTraffic($user)
    {
        if ($this->remindTrafficIsWarnValue(($user->u + $user->d), $user->transfer_enable)) {
            $sendCount = MailLog::where('created_at', '>=', strtotime(date('Y-m-1')))
                ->where('template_name', 'like', '%remindTraffic%')
                ->count();
            if ($sendCount > 0) return;
            SendEmailJob::dispatch([
                'email' => $user->email,
                'subject' => '在' . config('v2board.app_name', 'V2board') . '的流量使用已达到80%',
                'template_name' => 'remindTraffic',
                'template_value' => [
                    'name' => config('v2board.app_name', 'V2Board'),
                    'url' => config('v2board.app_url')
                ]
            ]);
        }
    }

    private function remindTrafficIsWarnValue($ud, $transfer_enable)
    {
        if ($ud <= 0) return false;
        if (($ud / $transfer_enable * 100) < 80) return false;
        return true;
    }

}
