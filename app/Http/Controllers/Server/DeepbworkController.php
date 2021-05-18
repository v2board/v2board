<?php

namespace App\Http\Controllers\Server;

use App\Services\ServerService;
use App\Services\UserService;
use App\Utils\CacheKey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Server;
use App\Models\ServerLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/*
 * V2ray Aurora
 * Github: https://github.com/tokumeikoi/aurora
 */
class DeepbworkController extends Controller
{
    public function __construct(Request $request)
    {
        $token = $request->input('token');
        if (empty($token)) {
            abort(500, 'token is null');
        }
        if ($token !== config('v2board.server_token')) {
            abort(500, 'token is error');
        }
    }

    // 后端获取用户
    public function user(Request $request)
    {
        $nodeId = $request->input('node_id');
        $server = Server::find($nodeId);
        if (!$server) {
            abort(500, 'fail');
        }
        Cache::put(CacheKey::get('SERVER_V2RAY_LAST_CHECK_AT', $server->id), time(), 3600);
        $serverService = new ServerService();
        $users = $serverService->getAvailableUsers(json_decode($server->group_id));
        $result = [];
        foreach ($users as $user) {
            $user->v2ray_user = [
                "uuid" => $user->uuid,
                "email" => sprintf("%s@v2board.user", $user->uuid),
                "alter_id" => $server->alter_id,
                "level" => 0,
            ];
            unset($user['uuid']);
            unset($user['email']);
            array_push($result, $user);
        }
        return response([
            'msg' => 'ok',
            'data' => $result,
        ]);
    }

    // 后端提交数据
    public function submit(Request $request)
    {
//         Log::info('serverSubmitData:' . $request->input('node_id') . ':' . file_get_contents('php://input'));
        $server = Server::find($request->input('node_id'));
        if (!$server) {
            return response([
                'ret' => 0,
                'msg' => 'server is not found'
            ]);
        }
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        Cache::put(CacheKey::get('SERVER_V2RAY_ONLINE_USER', $server->id), count($data), 3600);
        Cache::put(CacheKey::get('SERVER_V2RAY_LAST_PUSH_AT', $server->id), time(), 3600);
        $userService = new UserService();
        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                $u = $item['u'] * $server->rate;
                $d = $item['d'] * $server->rate;
                if (!$userService->trafficFetch($u, $d, $item['user_id'], $server, 'vmess')) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'ret' => 0,
                'msg' => 'user fetch fail'
            ]);
        }
        DB::commit();

        return response([
            'ret' => 1,
            'msg' => 'ok'
        ]);
    }

    // 后端获取配置
    public function config(Request $request)
    {
        $nodeId = $request->input('node_id');
        $localPort = $request->input('local_port');
        if (empty($nodeId) || empty($localPort)) {
            abort(500, '参数错误');
        }
        $serverService = new ServerService();
        try {
            $json = $serverService->getV2RayConfig($nodeId, $localPort);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        die(json_encode($json, JSON_UNESCAPED_UNICODE));
    }
}
