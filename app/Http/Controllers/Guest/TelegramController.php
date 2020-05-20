<?php

namespace App\Http\Controllers\Guest;

use App\Services\TelegramService;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TelegramController extends Controller
{
    public function __construct(Request $request)
    {
        if ($request->input('access_token') !== md5(config('v2board.telegram_bot_token'))) {
            abort(500, 'authentication failed');
        }
    }

    public function webhook(Request $request)
    {
        $msg = $this->getMessage($request->input());
        if (!$msg) return;
        try {
            switch($msg->command) {
                case '/bind': $this->bind($msg);
                    break;
            }
        } catch (\Exception $e) {
            $telegramService = new TelegramService();
            $telegramService->sendMessage($msg->chat_id, $e->getMessage());
        }
    }

    private function getMessage(array $data)
    {
        if (!$data['message']) return false;
        $obj = new \StdClass();
        $obj->is_private = $data['message']['chat']['type'] === 'private' ? true : false;
        $text = explode(' ', $data['message']['text']);
        $obj->command = $text[0];
        $obj->args = array_slice($text, 1);
        $obj->chat_id = $data['message']['chat']['id'];
        $obj->message_id = $data['message']['message_id'];
        return $obj;
    }

    private function bind(object $msg)
    {
        if (!$msg->is_private) return;
        $subscribeUrl = $msg->args[0];
        $subscribeUrl = parse_url($subscribeUrl);
        $token = parse_str($subscribeUrl['query'])['token'];
        if (!$token) {
            abort(500, '订阅地址无效');
        }
        $user = User::where('token', $token)->first();
        if (!$user) {
            abort(500, '用户不存在');
        }
        $user->telegram_id = $msg->chat_id;
        if (!$user->save()) {
            abort(500, '设置失败');
        }
        $telegramService = new TelegramService();
        $telegramService->sendMessage($msg->chat_id, '绑定成功');
    }
}
