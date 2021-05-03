<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\TicketSave;
use App\Http\Requests\User\TicketWithdraw;
use App\Jobs\SendTelegramJob;
use App\Models\User;
use App\Services\TelegramService;
use App\Utils\Dict;
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
                abort(500, __('user.ticket.fetch.ticket_not_exist'));
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
            abort(500, __('user.ticket.save.exist_other_open_ticket'));
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
            abort(500, __('user.ticket.save.ticket_create_failed'));
        }
        $ticketMessage = TicketMessage::create([
            'user_id' => $request->session()->get('id'),
            'ticket_id' => $ticket->id,
            'message' => $request->input('message')
        ]);
        if (!$ticketMessage) {
            DB::rollback();
            abort(500, __('user.ticket.save.ticket_create_failed'));
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
            abort(500, __('user.ticket.reply.params_wrong'));
        }
        if (empty($request->input('message'))) {
            abort(500, __('user.ticket.reply.message_not_empty'));
        }
        $ticket = Ticket::where('id', $request->input('id'))
            ->where('user_id', $request->session()->get('id'))
            ->first();
        if (!$ticket) {
            abort(500, __('user.ticket.reply.ticket_not_exist'));
        }
        if ($ticket->status) {
            abort(500, __('user.ticket.reply.ticket_close_not_reply'));
        }
        if ($request->session()->get('id') == $this->getLastMessage($ticket->id)->user_id) {
            abort(500, __('user.ticket.reply.wait_reply'));
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
            abort(500, __('user.ticket.reply.ticket_reply_failed'));
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
            abort(500, __('user.ticket.close.params_wrong'));
        }
        $ticket = Ticket::where('id', $request->input('id'))
            ->where('user_id', $request->session()->get('id'))
            ->first();
        if (!$ticket) {
            abort(500, __('user.ticket.close.ticket_not_exist'));
        }
        $ticket->status = 1;
        if (!$ticket->save()) {
            abort(500, __('user.ticket.close.close_failed'));
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
        if ((int)config('v2board.withdraw_close_enable', 0)) {
            abort(500, 'user.ticket.withdraw.not_support_withdraw');
        }
        if (!in_array(
            $request->input('withdraw_method'),
            config(
                'v2board.commission_withdraw_method',
                Dict::WITHDRAW_METHOD_WHITELIST_DEFAULT
            )
        )) {
            abort(500, __('user.ticket.withdraw.not_support_withdraw_method'));
        }
        $user = User::find($request->session()->get('id'));
        $limit = config('v2board.commission_withdraw_limit', 100);
        if ($limit > ($user->commission_balance / 100)) {
            abort(500, __('user.ticket.withdraw.system_require_withdraw_limit', ['limit' => $limit]));
        }
        DB::beginTransaction();
        $subject = __('user.ticket.withdraw.ticket_subject');
        $ticket = Ticket::create([
            'subject' => $subject,
            'level' => 2,
            'user_id' => $request->session()->get('id'),
            'last_reply_user_id' => $request->session()->get('id')
        ]);
        if (!$ticket) {
            DB::rollback();
            abort(500, __('user.ticket.withdraw.ticket_create_failed'));
        }
        $message = __('user.ticket.withdraw.ticket_message', [
            'method' => $request->input('withdraw_method'),
            'account' => $request->input('withdraw_account')
        ]);
        $ticketMessage = TicketMessage::create([
            'user_id' => $request->session()->get('id'),
            'ticket_id' => $ticket->id,
            'message' => $message
        ]);
        if (!$ticketMessage) {
            DB::rollback();
            abort(500, __('user.ticket.withdraw.ticket_create_failed'));
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
