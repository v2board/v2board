<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\TicketSave;
use App\Http\Requests\User\TicketWithdraw;
use App\Jobs\SendTelegramJob;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function fetch(Request $request)
    {
        if ($request->input('id')) {
            $ticket = Ticket::where('id', $request->input('id'))
                ->where('user_id', $request->session()->get('id'))
                ->first();
            if (!$ticket) {
                abort(500, '工单不存在');
            }
            $ticket['message'] = TicketMessage::where('ticket_id', $ticket->id)->get();
            for ($i = 0; $i < count($ticket['message']); $i++) {
                if ($ticket['message'][$i]['user_id'] == $ticket->user_id) {
                    $ticket['message'][$i]['is_me'] = true;
                } else {
                    $ticket['message'][$i]['is_me'] = false;
                }
            }
            return response([
                'data' => $ticket
            ]);
        }
        $ticket = Ticket::where('user_id', $request->session()->get('id'))
            ->orderBy('created_at', 'DESC')
            ->get();
        for ($i = 0; $i < count($ticket); $i++) {
            if ($ticket[$i]['last_reply_user_id'] == $request->session()->get('id')) {
                $ticket[$i]['reply_status'] = 0;
            } else {
                $ticket[$i]['reply_status'] = 1;
            }
        }
        return response([
            'data' => $ticket
        ]);
    }

    public function save(TicketSave $request)
    {
        DB::beginTransaction();
        if ((int)Ticket::where('status', 0)->where('user_id', $request->session()->get('id'))->count()) {
            abort(500, '存在其他工单尚未处理');
        }
        $ticket = Ticket::create(array_merge($request->only([
            'subject',
            'level'
        ]), [
            'user_id' => $request->session()->get('id'),
            'last_reply_user_id' => $request->session()->get('id')
        ]));
        if (!$ticket) {
            DB::rollback();
            abort(500, '工单创建失败');
        }
        $ticketMessage = TicketMessage::create([
            'user_id' => $request->session()->get('id'),
            'ticket_id' => $ticket->id,
            'message' => $request->input('message')
        ]);
        if (!$ticketMessage) {
            DB::rollback();
            abort(500, '工单创建失败');
        }
        DB::commit();
        $this->sendNotify($ticket, $ticketMessage);
        return response([
            'data' => true
        ]);
    }

    public function reply(Request $request)
    {
        if (empty($request->input('id'))) {
            abort(500, '参数错误');
        }
        if (empty($request->input('message'))) {
            abort(500, '消息不能为空');
        }
        $ticket = Ticket::where('id', $request->input('id'))
            ->where('user_id', $request->session()->get('id'))
            ->first();
        if (!$ticket) {
            abort(500, '工单不存在');
        }
        if ($ticket->status) {
            abort(500, '工单已关闭，无法回复');
        }
        if ($request->session()->get('id') == $this->getLastMessage($ticket->id)->user_id) {
            abort(500, '请等待技术支持回复');
        }
        DB::beginTransaction();
        $ticketMessage = TicketMessage::create([
            'user_id' => $request->session()->get('id'),
            'ticket_id' => $ticket->id,
            'message' => $request->input('message')
        ]);
        $ticket->last_reply_user_id = $request->session()->get('id');
        if (!$ticketMessage || !$ticket->save()) {
            DB::rollback();
            abort(500, '工单回复失败');
        }
        DB::commit();
        $this->sendNotify($ticket, $ticketMessage);
        return response([
            'data' => true
        ]);
    }


    public function close(Request $request)
    {
        if (empty($request->input('id'))) {
            abort(500, '参数错误');
        }
        $ticket = Ticket::where('id', $request->input('id'))
            ->where('user_id', $request->session()->get('id'))
            ->first();
        if (!$ticket) {
            abort(500, '工单不存在');
        }
        $ticket->status = 1;
        if (!$ticket->save()) {
            abort(500, '关闭失败');
        }
        return response([
            'data' => true
        ]);
    }

    private function getLastMessage($ticketId)
    {
        return TicketMessage::where('ticket_id', $ticketId)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function withdraw(TicketWithdraw $request)
    {
        $user = User::find($request->session()->get('id'));
        $limit = config('v2board.commission_withdraw_limit', 100);
        if ($limit > ($user->commission_balance / 100)) {
            abort(500, "当前系统要求的提现门槛佣金需为{$limit}CNY");
        }
        DB::beginTransaction();
        $subject = '[提现申请]本工单由系统发出';
        $ticket = Ticket::create([
            'subject' => $subject,
            'level' => 2,
            'user_id' => $request->session()->get('id'),
            'last_reply_user_id' => $request->session()->get('id')
        ]);
        if (!$ticket) {
            DB::rollback();
            abort(500, '工单创建失败');
        }
        $methodText = [
            'alipay' => '支付宝',
            'paypal' => '贝宝(Paypal)',
            'usdt' => 'USDT',
            'btc' => '比特币'
        ];
        $message = "提现方式：{$methodText[$request->input('withdraw_method')]}\r\n提现账号：{$request->input('withdraw_account')}\r\n";
        $ticketMessage = TicketMessage::create([
            'user_id' => $request->session()->get('id'),
            'ticket_id' => $ticket->id,
            'message' => $message
        ]);
        if (!$ticketMessage) {
            DB::rollback();
            abort(500, '工单创建失败');
        }
        DB::commit();
        $this->sendNotify($ticket, $ticketMessage);
        return response([
            'data' => true
        ]);
    }

    private function sendNotify(Ticket $ticket, TicketMessage $ticketMessage)
    {
        $telegramService = new TelegramService();
        $telegramService->sendMessageWithAdmin("📮工单提醒 #{$ticket->id}\n———————————————\n主题：\n`{$ticket->subject}`\n内容：\n`{$ticketMessage->message}`", true);
    }
}
