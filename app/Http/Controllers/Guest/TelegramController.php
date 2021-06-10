<?php

namespace App\Http\Controllers\Guest;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Utils\Helper;
use App\Services\TicketService;

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
            switch($this->msg->message_type) {
                case 'send':
                    $this->fromSend();
                    break;
                case 'reply':
                    $this->fromReply();
                    break;
            }
        } catch (\Exception $e) {
            $telegramService = new TelegramService();
            $telegramService->sendMessage($this->msg->chat_id, $e->getMessage());
        }
    }

    private function fromSend()
    {
        switch($this->msg->command) {
            case '/bind': $this->bind();
                break;
            case '/traffic': $this->traffic();
                break;
            case '/getlatesturl': $this->getLatestUrl();
                break;
            case '/unbind': $this->unbind();
                break;
            default: $this->help();
        }
    }

    private function fromReply()
    {
        // ticket
        if (preg_match("/[#](.*)/", $this->msg->reply_text, $match)) {
            $this->replayTicket($match[1]);
        }
    }

    private function getMessage(array $data)
    {
        if (!isset($data['message'])) return false;
        $obj = new \StdClass();
        $obj->is_private = $data['message']['chat']['type'] === 'private' ? true : false;
        if (!isset($data['message']['text'])) return false;
        $text = explode(' ', $data['message']['text']);
        $obj->command = $text[0];
        $obj->args = array_slice($text, 1);
        $obj->chat_id = $data['message']['chat']['id'];
        $obj->message_id = $data['message']['message_id'];
        $obj->message_type = !isset($data['message']['reply_to_message']['text']) ? 'send' : 'reply';
        $obj->text = $data['message']['text'];
        if ($obj->message_type === 'reply') {
            $obj->reply_text = $data['message']['reply_to_message']['text'];
        }
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
        if ($user->telegram_id) {
            abort(500, '该账号已经绑定了Telegram账号');
        }
        $user->telegram_id = $msg->chat_id;
        if (!$user->save()) {
            abort(500, '设置失败');
        }
        $telegramService = new TelegramService();
        $telegramService->sendMessage($msg->chat_id, '绑定成功');
    }

    private function unbind()
    {
        $msg = $this->msg;
        if (!$msg->is_private) return;
        $user = User::where('telegram_id', $msg->chat_id)->first();
        $telegramService = new TelegramService();
        if (!$user) {
            $this->help();
            $telegramService->sendMessage($msg->chat_id, '没有查询到您的用户信息，请先绑定账号', 'markdown');
            return;
        }
        $user->telegram_id = NULL;
        if (!$user->save()) {
            abort(500, '解绑失败');
        }
        $telegramService->sendMessage($msg->chat_id, '解绑成功', 'markdown');
    }

    private function help()
    {
        $msg = $this->msg;
        if (!$msg->is_private) return;
        $telegramService = new TelegramService();
        $commands = [
            '/bind 订阅地址 - 绑定你的' . config('v2board.app_name', 'V2Board') . '账号',
            '/traffic - 查询流量信息',
            '/getlatesturl - 获取最新的' . config('v2board.app_name', 'V2Board') . '网址',
            '/unbind - 解除绑定'
        ];
        $text = implode(PHP_EOL, $commands);
        $telegramService->sendMessage($msg->chat_id, "你可以使用以下命令进行操作：\n\n$text", 'markdown');
    }

    private function traffic()
    {
        $msg = $this->msg;
        if (!$msg->is_private) return;
        $user = User::where('telegram_id', $msg->chat_id)->first();
        $telegramService = new TelegramService();
        if (!$user) {
            $this->help();
            $telegramService->sendMessage($msg->chat_id, '没有查询到您的用户信息，请先绑定账号', 'markdown');
            return;
        }
        $transferEnable = Helper::trafficConvert($user->transfer_enable);
        $up = Helper::trafficConvert($user->u);
        $down = Helper::trafficConvert($user->d);
        $remaining = Helper::trafficConvert($user->transfer_enable - ($user->u + $user->d));
        $text = "🚥流量查询\n———————————————\n计划流量：`{$transferEnable}`\n已用上行：`{$up}`\n已用下行：`{$down}`\n剩余流量：`{$remaining}`";
        $telegramService->sendMessage($msg->chat_id, $text, 'markdown');
    }

    private function getLatestUrl()
    {
        $msg = $this->msg;
        $user = User::where('telegram_id', $msg->chat_id)->first();
        $telegramService = new TelegramService();
        $text = sprintf(
            "%s的最新网址是：%s",
            config('v2board.app_name', 'V2Board'),
            config('v2board.app_url')
        );
        $telegramService->sendMessage($msg->chat_id, $text, 'markdown');
    }

    private function replayTicket($ticketId)
    {
        $msg = $this->msg;
        if (!$msg->is_private) return;
        $user = User::where('telegram_id', $msg->chat_id)->first();
        if (!$user) {
            abort(500, '用户不存在');
        }
        $ticketService = new TicketService();
        if ($user->is_admin || $user->is_staff) {
            $ticketService->replyByAdmin(
                $ticketId,
                $msg->text,
                $user->id
            );
        }
        $telegramService = new TelegramService();
        $telegramService->sendMessage($msg->chat_id, "#`{$ticketId}` 的工单已回复成功", 'markdown');
        $telegramService->sendMessageWithAdmin("#`{$ticketId}` 的工单已由 {$user->email} 进行回复", true);
    }


}
