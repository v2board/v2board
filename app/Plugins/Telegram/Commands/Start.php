<?php

namespace App\Plugins\Telegram\Commands;

use App\Models\User;
use App\Plugins\Telegram\Telegram;

class StartBot extends Telegram {
    public $command = '/start';
    public $description = 'Start Bot';

    public function handle($message, $match = []) {
        $telegramService = $this->telegramService;
        $telegramService->sendMessage($message->chat_id, 'Welcome to the v2board bot', 'markdown');
    }
}
