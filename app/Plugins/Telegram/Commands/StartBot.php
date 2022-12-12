<?php

namespace App\Plugins\Telegram\Commands;

use App\Models\User;
use App\Plugins\Telegram\Telegram;

class StartBot extends Telegram {
    public $command = '/start';
    public $description = 'Start Bot';

    public function handle($message, $match = []) {
        $telegramService = $this->telegramService;
        $text = sprintf(
            "به ربات فری نت خوش آمدید

/bind - اتصال اکانت تلگرام شما به سایت
/getlatesturl - دریافت آدرس فعال سایت
/traffic - دریافت جزئیات ترافیک
/unbind - قطع دسترسی اکانت تلگرام شما به سایت"
        );
        $telegramService->sendMessage($message->chat_id, $text, 'markdown');
    }
}
