<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\ServerLog;
use App\Models\User;

use App\Utils\Helper;

class ServerController extends Controller {
    public function getServers (Request $request) {
        $user = User::find($request->session()->get('id'));
        $server = [];
        if ($user->expired_at > time()) {
            $servers = Server::where('show', 1)
                ->orderBy('name')
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
            $server[$i]['last_check_at'] = Redis::get('server_last_check_at_' . $server[$i]['id']);
        }
        return response([
            'data' => $server
        ]);
    }

    public function getTrafficLog (Request $request) {
    	$type = $request->input('type') ? $request->input('type') : 0;
        $current = $request->input('current') ? $request->input('current') : 1;
        $pageSize = $request->input('pageSize') >= 10 ? $request->input('pageSize') : 10;
        $serverLogModel = ServerLog::where('user_id', $request->session()->get('id'))
            ->orderBy('created_at', 'DESC');
    	switch ($type) {
    		case 0: $serverLogModel->where('created_at', '>=', strtotime(date('Y-m-d')));
    			break;
    		case 1: $serverLogModel->where('created_at', '>=', strtotime(date('Y-m-d')) - 604800);
    			break;
    		case 2: $serverLogModel->where('created_at', '>=', strtotime(date('Y-m-1')));
    	}
    	$sum = [
    		'u' => $serverLogModel->sum('u'),
    		'd' => $serverLogModel->sum('d')
    	];
        $total = $serverLogModel->count();
        $res = $serverLogModel->forPage($current, $pageSize)
            ->get();
        return response([
            'data' => $res,
            'total' => $total,
            'sum' => $sum
        ]);
    }
}