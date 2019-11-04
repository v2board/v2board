<?php

namespace App\Listeners;

use App\Events\ServerLogSaveEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ServerLogSaveListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ServerLogSaveEvent  $event
     * @return void
     */
    public function handle(ServerLogSaveEvent $event) {
        $redisKey = 'last_submit_server_log_' . $event->serverLog->user_id;
        Redis::set($redisKey, time());
        info($redisKey);
    }
}
