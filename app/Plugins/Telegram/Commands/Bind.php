<?php

namespace App\Plugins\Telegram\Commands;

use App\Models\User;
use App\Plugins\Telegram\Telegram;

class Bind extends Telegram {
    public $command = '/bind';
    public $description = 'Bind your Telegram account to the website';

    public function handle($message, $match = []) {
        if (!$message->is_private) return;
        if (!isset($message->args[0])) {
            abort(500, 'در پارامترها خطایی وجود دارد، لطفاً آدرس اشتراک خود را  همراه دستور ارسال کنید');
        }
        $subscribeUrl = $message->args[0];
        $subscribeUrl = parse_url($subscribeUrl);
        parse_str($subscribeUrl['query'], $query);
        $token = $query['token'];
        if (!$token) {
            abort(500, 'توکن نامعتبر است');
        }
        $user = User::where('token', $token)->first();
        if (!$user) {
            abort(500, 'کاربر وجود ندارد');
        }
        if ($user->telegram_id) {
            abort(500, 'این حساب در حال حاضر به یک حساب تلگرام متصل است');
        }
        $user->telegram_id = $message->chat_id;
        if (!$user->save()) {
            abort(500, 'راه اندازی ناموفق بود');
        }
        $telegramService = $this->telegramService;
        $telegramService->sendMessage($message->chat_id, 'اتصال با موفقیت انجام شد');
    }
}
