<?php

namespace App\Http\Controllers\Guest;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TelegramController extends Controller
{
    protected $msg;
    protected $commands = [];

    public function __construct(Request $request)
    {
        if ($request->input('access_token') !== md5(config('v2board.telegram_bot_token'))) {
            abort(401);
        }
    }

    public function webhook(Request $request)
    {
        $this->msg = $this->getMessage($request->input());
        if (!$this->msg) return;
        $this->handle();
    }

    public function handle()
    {
        $msg = $this->msg;
        try {
            foreach (glob(base_path('app//Plugins//Telegram//Commands') . '/*.php') as $file) {
                $command = basename($file, '.php');
                $class = '\\App\\Plugins\\Telegram\\Commands\\' . $command;
                if (!class_exists($class)) continue;
                $instance = new $class();
                if ($msg->message_type === 'message') {
                    if (!isset($instance->command)) continue;
                    if ($msg->command !== $instance->command) continue;
                    $instance->handle($msg);
                    return;
                }
                if ($msg->message_type === 'reply_message') {
                    if (!isset($instance->regex)) continue;
                    if (!preg_match($instance->regex, $msg->reply_text, $match)) continue;
                    $instance->handle($msg, $match);
                    return;
                }
            }
        } catch (\Exception $e) {
            $telegramService = new TelegramService();
            $telegramService->sendMessage($msg->chat_id, $e->getMessage());
        }
    }

    private function getMessage(array $data)
    {
        if (!isset($data['message'])) return false;
        $obj = new \StdClass();
        if (!isset($data['message']['text'])) return false;
        $text = explode(' ', $data['message']['text']);
        $obj->command = $text[0];
        $obj->args = array_slice($text, 1);
        $obj->chat_id = $data['message']['chat']['id'];
        $obj->message_id = $data['message']['message_id'];
        $obj->message_type = !isset($data['message']['reply_to_message']['text']) ? 'message' : 'reply_message';
        $obj->text = $data['message']['text'];
        $obj->is_private = $data['message']['chat']['type'] === 'private';
        if ($obj->message_type === 'reply_message') {
            $obj->reply_text = $data['message']['reply_to_message']['text'];
        }
        return $obj;
    }
}
