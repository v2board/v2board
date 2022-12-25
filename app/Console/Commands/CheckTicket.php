<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;

class CheckTicket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:ticket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '工单检查任务';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', -1);
        $tickets = Ticket::where('status', 0)
            ->where('updated_at', '<=', time() - 24 * 3600)
            ->where('reply_status', 0)
            ->get();
        foreach ($tickets as $ticket) {
            if ($ticket->user_id === $ticket->last_reply_user_id) continue;
            $ticket->status = 1;
            $ticket->save();
        }
    }
}
