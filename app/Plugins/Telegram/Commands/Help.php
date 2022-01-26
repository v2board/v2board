<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;

class Help extends Telegram {
    public $command = '/help';
    public $description = '获取帮助';

    public function handle($message, $match = []) {
        if (!$message->is_private) return;
        $commands = [
            '/bind 订阅地址 - 绑定你的' . config('v2board.app_name', 'V2Board') . '账号',
            '/traffic - 查询流量信息',
            '/getlatesturl - 获取最新的' . config('v2board.app_name', 'V2Board') . '网址',
            '/unbind - 解除绑定'
        ];
        $text = implode(PHP_EOL, $commands);
        $this->telegramService->sendMessage($message->chat_id, "你可以使用以下命令进行操作：\n\n$text", 'markdown');
    }
}
