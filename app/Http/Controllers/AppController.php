<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Server;
use App\Utils\Helper;

class AppController extends Controller
{
    public function data (Request $request) {
        $user = $request->user;
        $nodes = [];
        if ($user->plan_id) {
            $user['plan'] = Plan::find($user->plan_id);
            if (!$user['plan']) {
                abort(500, '订阅计划不存在');
            }
            if ($user->expired_at > time()) {
                $servers = Server::where('show', 1)->get();
                foreach ($servers as $item) {
                    $groupId = json_decode($item['group_id']);
                    if (in_array($user->group_id, $groupId)) {
                        array_push($nodes, $item);
                    }
                }
            }
        }
        return response([
            'nodes' => $nodes,
            'u' => $user->u,
            'd' => $user->d,
            'transfer_enable' => $user->transfer_enable,
            'expired_time' => $user->expired_at
        ]);
    }
}
