<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\User;

class MailService
{
    public function remindTraffic (User $user)
    {
        if (!$user->remind_traffic) return;
        if (!$this->remindTrafficIsWarnValue(($user->u + $user->d), $user->transfer_enable)) {
            return;
        }
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

    private function remindTrafficIsWarnValue($ud, $transfer_enable)
    {
        if ($ud <= 0) return false;
        if (($ud / $transfer_enable * 100) < 80) return false;
        return true;
    }
}
