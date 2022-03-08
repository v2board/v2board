<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\ServerService;
use App\Services\UserService;
use App\Utils\CacheKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\ServerV2ray;
use App\Models\ServerLog;
use App\Models\User;

use App\Utils\Helper;
use Illuminate\Support\Facades\DB;

class ServerController extends Controller
{
    public function fetch(Request $request)
    {
        $user = User::find($request->session()->get('id'));
        $servers = [];
        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            $serverService = new ServerService();
            $servers = $serverService->getAvailableServers($user);
        }
        return response([
            'data' => $servers
        ]);
    }
}
