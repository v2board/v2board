<?php

namespace App\Plugins\Telegram\Commands;

use App\Models\User;
use App\Plugins\Telegram\Telegram;
use App\Utils\Helper;

class Traffic extends Telegram {
    public $command = '/traffic';
    public $description = 'Check traffic information';

    public function handle($message, $match = []) {
        $telegramService = $this->telegramService;
        if (!$message->is_private) return;
        $user = User::where('telegram_id', $message->chat_id)->first();
        if (!$user) {
            $telegramService->sendMessage($message->chat_id, 'اطلاعات کاربری شما در دسترس نیست، لطفا ابتدا حساب خود را متصل کنید', 'markdown');
            return;
        }
        $transferEnable = Helper::trafficConvert($user->transfer_enable);
        $up = Helper::trafficConvert($user->u);
        $down = Helper::trafficConvert($user->d);
        $remaining = Helper::trafficConvert($user->transfer_enable - ($user->u + $user->d));
        $text = "🚥جزئیات مصرف\n———————————————\nترافیک کل：`{$transferEnable}`\nآپلود：`{$up}`\nدانلود：`{$down}`\nحجم باقیمانده：`{$remaining}`";
        $telegramService->sendMessage($message->chat_id, $text, 'markdown');
    }
}
