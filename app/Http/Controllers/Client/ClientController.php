<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Protocols\V2rayN;
use App\Http\Controllers\Controller;
use App\Services\ServerService;
use Illuminate\Http\Request;
use App\Services\UserService;

class ClientController extends Controller
{
    public function subscribe(Request $request)
    {
        $flag = $request->input('flag')
            ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');
        $flag = strtolower($flag);
        $user = $request->user;
        // account not expired and is not banned.
        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            $serverService = new ServerService();
            $servers = $serverService->getAvailableServers($user);
            $this->setSubscribeInfoToServers($servers, $user);
            if ($flag) {
                foreach (glob(app_path('Http//Controllers//Client//Protocols') . '/*.php') as $file) {
                    $file = 'App\\Http\\Controllers\\Client\\Protocols\\' . basename($file, '.php');
                    $class = new $file($user, $servers);
                    if (strpos($flag, $class->flag) !== false) {
                        die($class->handle());
                    }
                }
            }
            // todo 1.5.3 remove
            $class = new V2rayN($user, $servers);
            die($class->handle());
            die('该客户端暂不支持进行订阅');
        }
    }

    private function setSubscribeInfoToServers(&$servers, $user)
    {
        if (!isset($servers[0])) return;
        if (!(int)config('v2board.show_info_to_server_enable', 0)) return;
        $useTraffic = round($user['u'] / (1024*1024*1024), 2) + round($user['d'] / (1024*1024*1024), 2);
        $totalTraffic = round($user['transfer_enable'] / (1024*1024*1024), 2);
        $remainingTraffic = $totalTraffic - $useTraffic;
        $expiredDate = $user['expired_at'] ? date('Y-m-d', $user['expired_at']) : '长期有效';
        $userService = new UserService();
        $resetDay = $userService->getResetDay($user);

        array_unshift($servers, array_merge($servers[3], [
            'name' => "💡到期前及时续费，防止失联",
        ]));

        array_unshift($servers, array_merge($servers[3], [
            'name' => "💡用全局模式可加速打开官网",
        ]));

        $planId= $user['plan_id'];
        if($planId==1){
            $expireHour = $user['expired_at'] ? round(($user['expired_at']-time())/60) : '长期有效';
            array_unshift($servers, array_merge($servers[3], [
                'name' => "💡流量{$useTraffic}|{$totalTraffic}G $expireHour 分钟后过期",
            ]));
        }else{
            array_unshift($servers, array_merge($servers[3], [
                'name' => "💡流量{$useTraffic}|{$totalTraffic}G 到期{$expiredDate} {$resetDay}天后重置",
            ]));
        }
    }
}
