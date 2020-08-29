<?php
namespace App\Services;


use App\Jobs\SendEmailJob;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TicketService {
    public function replyByAdmin($ticketId, $message, $userId):void
    {
        $ticket = Ticket::where('id', $ticketId)
            ->first();
        if (!$ticket) {
            abort(500, '工单不存在');
        }
        if ($ticket->status) {
            abort(500, '工单已关闭，无法回复');
        }
        DB::beginTransaction();
        $ticketMessage = TicketMessage::create([
            'user_id' => $userId,
            'ticket_id' => $ticket->id,
            'message' => $message
        ]);
        $ticket->last_reply_user_id = $userId;
        if (!$ticketMessage || !$ticket->save()) {
            DB::rollback();
            abort(500, '工单回复失败');
        }
        DB::commit();
        $this->sendEmailNotify($ticket, $ticketMessage);
    }

    // 半小时内不再重复通知
    private function sendEmailNotify(Ticket $ticket, TicketMessage $ticketMessage)
    {
        $user = User::find($ticket->user_id);
        $cacheKey = 'ticket_sendEmailNotify_' . $ticket->user_id;
        if (!Cache::get($cacheKey)) {
            Cache::put($cacheKey, 1, 1800);
            SendEmailJob::dispatch([
                'email' => $user->email,
                'subject' => '您在' . config('v2board.app_name', 'V2Board') . '的工单得到了回复',
                'template_name' => 'notify',
                'template_value' => [
                    'name' => config('v2board.app_name', 'V2Board'),
                    'url' => config('v2board.app_url'),
                    'content' => "主题：{$ticket->subject}\r\n回复内容：{$ticketMessage->message}"
                ]
            ]);
        }
    }
}
