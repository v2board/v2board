<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use App\Models\ServerVmess;
use App\Services\TelegramService;
use App\Utils\CacheKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ChangePortVMess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'changePort:vmess {ids* : server_ids} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更换指定VMess协议节点的端口';

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

        $max_attempts = 5;
        $month = date('n');
        $date = date('j');
        $telegramService = new TelegramService();
        $telegramService->sendMessageWithAdmin("开始批量换VMess节点的端口");
        $ids = $this->argument('ids');
        foreach ($ids as $id){
            $new_Port = null;
            for ($attempts = 0; $attempts < $max_attempts; $attempts++) {

                $new_Port = rand(20000, 65535);		//端口范围，可以自定义。

                $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                if ($socket !== false && @socket_bind($socket, '127.0.0.1', $new_Port) !== false) {
                    socket_close($socket);
                    break;
                }
            }
            if ($attempts >= $max_attempts) {
                $telegramService->sendMessageWithAdmin("节点ID为 {$id} 的VMess节点换了 {$max_attempts} 次端口，都被占用了！未能换成功");
                throw new Exception("节点ID为 {$id} 的VMess节点换了 {$max_attempts} 次端口，都被占用了！未能换成功");
            }

            if(ServerVmess::where('id', '=', $id)
                ->update(['port' => $new_Port,
                        'server_port' => $new_Port,
                        'name' => "🚀我是防失联节点 {$month}月{$date}号更新"
                ]) ){
                $this->info("已为VMess节点 {$id} 更新端口： {$new_Port}");
                $telegramService->sendMessageWithAdmin("已为VMess节点 {$id} 更新端口： {$new_Port}");
            }
        }
        $telegramService->sendMessageWithAdmin("VMess节点的端口更换完毕");
    }

}
