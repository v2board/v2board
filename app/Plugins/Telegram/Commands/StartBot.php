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
            "Welcome to the bot"
        );
        $telegramService->sendMessage($message->chat_id, $text, 'markdown');
    }
}