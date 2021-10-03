<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Utils\CacheKey;
use Illuminate\Support\Facades\Cache;

class MailService
{
    public function remindTraffic (User $user)
    {
        if (!$user->remind_traffic) return;
        if (!$this->remindTrafficIsWarnValue($user->u, $user->d, $user->transfer_enable)) return;
        $flag = CacheKey::get('LAST_SEND_EMAIL_REMIND_TRAFFIC', $user->id);
        if (Cache::get($flag)) return;
        if (!Cache::put($flag, 1, 24 * 3600)) return;
        SendEmailJob::dispatch([
            'email' => $user->email,
            'subject' => __('The traffic usage in :app_name has reached 80%', [
                'app_name' => config('v2board.app_name', 'V2board')
            ]),
            'template_name' => 'remindTraffic',
            'template_value' => [
                'name' => config('v2board.app_name', 'V2Board'),
                'url' => config('v2board.app_url')
            ]
        ]);
    }

    public function remindExpire(User $user)
    {
        if (!($user->expired_at !== NULL && ($user->expired_at - 86400) < time() && $user->expired_at > time())) return;
        SendEmailJob::dispatch([
            'email' => $user->email,
            'subject' => __('The service in :app_name is about to expire', [
               'app_name' =>  config('v2board.app_name', 'V2board')
            ]),
            'template_name' => 'remindExpire',
            'template_value' => [
                'name' => config('v2board.app_name', 'V2Board'),
                'url' => config('v2board.app_url')
            ]
        ]);
    }

    private function remindTrafficIsWarnValue($u, $d, $transfer_enable)
    {
        $ud = $u + $d;
        if (!$ud) return false;
        if (!$transfer_enable) return false;
        $percentage = ($ud / $transfer_enable) * 100;
        if ($percentage < 80) return false;
        if ($percentage >= 100) return false;
        return true;
    }
}
