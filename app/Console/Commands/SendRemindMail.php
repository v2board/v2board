<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Jobs\SendEmail;

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
            if ($user->remind_expire) {
                $this->remindExpire($user);
            }
        }
    }
    
    private function remindExpire ($user) {
        if (($user->expired_at - 86400) < time() && $user->expired_at > time()) {
            $this->dispatch(new SendEmail([
                'email' => $user->email,
                'subject' => '在' . config('v2board.app_name', 'V2board') . '的服务即将到期',
                'template_name' => 'mail.sendRemindExpire',
                'template_value' => [
                    'name' => config('v2board.app_name', 'V2Board')
                ]
            ]));
        }
    }

}
