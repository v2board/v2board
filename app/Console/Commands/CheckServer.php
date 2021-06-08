<?php

namespace App\Console\Commands;

use App\Services\ServerService;
use App\Services\TelegramService;
use App\Utils\CacheKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '节点检查任务';

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
        $this->checkOffline();
    }

    private function checkOffline()
    {
        $serverService = new ServerService();
        $servers = $serverService->getAllServers();
        foreach ($servers as $server) {
            if ($server['parent_id']) continue;
            if ($server['last_check_at'] && (time() - $server['last_check_at']) > 1800) {
                $telegramService = new TelegramService();
                $message = sprintf(
                    "节点掉线通知\r\n----\r\n节点名称：%s\r\n节点地址：%s\r\n",
                    $server['name'],
                    $server['host']
                );
                $telegramService->sendMessageWithAdmin($message);
                Cache::forget(CacheKey::get(sprintf("SERVER_%s_LAST_CHECK_AT", strtoupper($server['type'])), $server->id));
            }
        }
    }
}
