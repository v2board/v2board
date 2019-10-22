<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Server;
use App\Utils\Helper;
use App\Models\Order;

class UserController extends Controller
{
    public function logout (Request $request) {
        return response([
            'data' => $request->session()->flush()
        ]);
    }

    public function index (Request $request) {
    }
    
    public function save (Request $request) {
    }
    
    public function info (Request $request) {
        $user = User::where('id', $request->session()->get('id'))
            ->select([
                'email',
                'last_login_at',
                'created_at',
                'enable',
                'is_admin'
            ])
            ->first();
        $user['avatar_url'] = 'https://cdn.v2ex.com/gravatar/' . md5($user->email) . '?s=64&d=identicon';
        return response([
            'data' => $user
        ]);
    }
    
    public function dashboard (Request $request) {
        $user = User::find($request->session()->get('id'));
        if ($user->plan_id) {
            $user['plan'] = Plan::find($user->plan_id);
        }
        $user['subscribe_url'] = config('v2panel.app_url', env('APP_URL')) . '/api/v1/client/subscribe?token=' . $user['token'];
        $stat = [
            Order::where('status', 0)
                ->where('user_id', $request->session()->get('id'))
                ->count(),
            0,
            User::where('invite_user_id', $request->session()->get('id'))
                ->count()
        ];
        return response([
            'data' => [
                'user' => $user,
                'stat' => $stat
            ]
        ]);
    }
    
    public function subscribe (Request $request) {
        $user = User::find($request->session()->get('id'));
        $server = [];
        if ($user->plan_id) {
            $user['plan'] = Plan::find($user->plan_id);
            if (!$user['plan']) {
                abort(500, '订阅计划不存在');
            }
            if ($user->expired_at > time()) {
                $servers = Server::all();
                foreach ($servers as $item) {
                    $groupId = json_decode($item['group_id']);
                    if (in_array($user->group_id, $groupId)) {
                        array_push($server, $item);
                    }
                }
            }
        }
        $user['subscribe_url'] = config('v2panel.app_url', env('APP_URL')) . '/api/v1/client/subscribe?token=' . $user['token'];
        return response([
            'data' => [
                'user' => $user,
                'server' => $server
            ]
        ]);
    }
    
    public function resetUUID (Request $request) {
        $user = User::find($request->session()->get('id'));
        $user->v2ray_uuid = Helper::guid(true);
        if (!$user->save()) {
            abort(500, '重置失败');
        }
        return response([
            'data' => true
        ]);
    }
}
