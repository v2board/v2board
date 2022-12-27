<?php

namespace App\Plugins\Telegram\Commands;

use App\Models\User;
use App\Plugins\Telegram\Telegram;
use App\Services\TicketService;

class ReplyTicket extends Telegram {
    public $regex = '/[#](.*)/';
    public $description = 'Quick Ticket Response';

    public function handle($message, $match = []) {
        if (!$message->is_private) return;
        $this->replayTicket($message, $match[1]);
    }


    private function replayTicket($msg, $ticketId)
    {
        $user = User::where('telegram_id', $msg->chat_id)->first();
        if (!$user) {
            abort(500, 'The user does not exist');
        }
        if (!$msg->text) return;
        if (!($user->is_admin || $user->is_staff)) return;
        $ticketService = new TicketService();
        $ticketService->replyByAdmin(
            $ticketId,
            $msg->text,
            $user->id
        );
        $telegramService = $this->telegramService;
        $telegramService->sendMessage($msg->chat_id, "#`{$ticketId}` The ticket has been answered successfully", 'markdown');
        $telegramService->sendMessageWithAdmin("#`{$ticketId}` The ticket has been issued by {$user->email} Reply", true);
    }
}
