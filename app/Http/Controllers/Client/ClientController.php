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
            ?? (isset($_SERVER['HTTP_USER_AGENT'])
                ? $_SERVER['HTTP_USER_AGENT']
                : '');
        $flag = strtolower($flag);
        $user = $request->user;
        // account not expired and is not banned.
        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            $serverService = new ServerService();
            $servers = $serverService->getAvailableServers($user);
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
}
