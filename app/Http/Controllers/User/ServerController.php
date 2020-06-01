<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Server;
use App\Models\ServerLog;
use App\Models\User;

use App\Utils\Helper;

class ServerController extends Controller
{
    public function fetch(Request $request)
    {
        $user = User::find($request->session()->get('id'));
        $server = [];
        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            $servers = Server::where('show', 1)
                ->orderBy('sort', 'ASC')
                ->get();
            foreach ($servers as $item) {
                $groupId = json_decode($item['group_id']);
                if (in_array($user->group_id, $groupId)) {
                    array_push($server, $item);
                }
            }
        }
        for ($i = 0; $i < count($server); $i++) {
            $server[$i]['link'] = Helper::buildVmessLink($server[$i], $user);
            if ($server[$i]['parent_id']) {
                $server[$i]['last_check_at'] = Cache::get('server_last_check_at_' . $server[$i]['parent_id']);
            } else {
                $server[$i]['last_check_at'] = Cache::get('server_last_check_at_' . $server[$i]['id']);
            }
        }
        return response([
            'data' => $server
        ]);
    }

    public function logFetch(Request $request)
    {
        $type = $request->input('type') ? $request->input('type') : 0;
        $current = $request->input('current') ? $request->input('current') : 1;
        $pageSize = $request->input('pageSize') >= 10 ? $request->input('pageSize') : 10;
        $serverLogModel = ServerLog::where('user_id', $request->session()->get('id'))
            ->orderBy('created_at', 'DESC');
        switch ($type) {
            case 0:
                $serverLogModel->where('created_at', '>=', strtotime(date('Y-m-d')));
                break;
            case 1:
                $serverLogModel->where('created_at', '>=', strtotime(date('Y-m-d')) - 604800);
                break;
            case 2:
                $serverLogModel->where('created_at', '>=', strtotime(date('Y-m-1')));
        }
        $total = $serverLogModel->count();
        $res = $serverLogModel->forPage($current, $pageSize)
            ->get();
        return response([
            'data' => $res,
            'total' => $total
        ]);
    }
}
