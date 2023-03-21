<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;

class Start extends Telegram {
    public $command = '/start';
    public $description = '欢迎词及使用帮助';

    public function handle($message, $match = []) {
        $telegramService = $this->telegramService;
        $appName = config('v2board.app_name', 'V2Board');
        $text = "👏 欢迎使用`{$appName}`:
/bind - 绑定账号，发送：/bind 您的订阅地址
/getlatesturl - 获取最新网址
/traffic - 查询流量信息
/unbind - 解绑您的账号            
祝你玩的开心😄";
        $telegramService->sendMessage($message->chat_id, $text, 'markdown');
    }
}
