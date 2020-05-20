<?php

namespace App\Http\Controllers\Guest;

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
        switch($msg->command) {
            case '/bind': $this->bind($msg);
            break;
        }
    }

    private function getMessage(array $data)
    {
        if (!$data['message']) return false;
        $obj = new \StdClass();
        $obj->is_private = $data['message']['chat']['type'] === 'private' ? true : false;
        $text = explode(' ', $data['message']['text']);
        $obj->command = $text[0] || '';
        $obj->args = array_slice($text, 1) || [];
        $obj->chat_id = $data['message']['chat']['id'] || '';
        return $obj;
    }

    private function bind(object $msg)
    {
        if (!$msg->is_private) return;
        $userService = new UserService();
        $subscribeUrl = $msg->args[0];
        $subscribeUrl = parse_url($subscribeUrl);
        info($subscribeUrl);
    }
}
