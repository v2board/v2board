<?php

namespace App\Http\Controllers\Guest;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class TelegramController extends Controller
{
    protected $msg;

    public function __construct(Request $request)
    {
        if ($request->input('access_token') !== md5(config('v2board.telegram_bot_token'))) {
            abort(500, 'authentication failed');
        }
    }

    public function webhook(Request $request)
    {
        $this->msg = $this->getMessage($request->input());
        if (!$this->msg) return;
        try {
            switch($this->msg->command) {
                case '/bind': $this->bind();
                    break;
                default: $this->help();
            }
        } catch (\Exception $e) {
            $telegramService = new TelegramService();
            $telegramService->sendMessage($this->msg->chat_id, $e->getMessage());
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

    private function bind()
    {
        $msg = $this->msg;
        if (!$msg->is_private) return;
        if (!isset($msg->args[0])) {
            abort(500, '参数有误，请携带订阅地址发送');
        }
        $subscribeUrl = $msg->args[0];
        $subscribeUrl = parse_url($subscribeUrl);
        parse_str($subscribeUrl['query'], $query);
        $token = $query['token'];
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

    private function help()
    {
        $msg = $this->msg;
        if (!$msg->is_private) return;
        $telegramService = new TelegramService();
        $commands = [
            '/bind 订阅地址 - 绑定你的' . config('v2board.app_name', 'V2Board') . '账号'
        ];
        $text = implode(PHP_EOL, $commands);
        $telegramService->sendMessage($msg->chat_id, "你可以使用以下命令进行操作：\n\n$text", 'markdown');
    }
}
