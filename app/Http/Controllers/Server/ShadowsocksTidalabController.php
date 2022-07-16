<?php

namespace App\Http\Controllers\Server;

use App\Models\ServerShadowsocks;
use App\Services\ServerService;
use App\Services\UserService;
use App\Utils\CacheKey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

/*
 * Tidal Lab Shadowsocks
 * Github: https://github.com/tokumeikoi/tidalab-ss
 */
class ShadowsocksTidalabController extends Controller
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
        ini_set('memory_limit', -1);
        $nodeId = $request->input('node_id');
        $server = ServerShadowsocks::find($nodeId);
        if (!$server) {
            abort(500, 'fail');
        }
        Cache::put(CacheKey::get('SERVER_SHADOWSOCKS_LAST_CHECK_AT', $server->id), time(), 3600);
        $serverService = new ServerService();
        $users = $serverService->getAvailableUsers($server->group_id);
        $result = [];
        foreach ($users as $user) {
            array_push($result, [
                'id' => $user->id,
                'port' => $server->server_port,
                'cipher' => $server->cipher,
                'secret' => $user->uuid
            ]);
        }
        $eTag = sha1(json_encode($result));
        if (strpos($request->header('If-None-Match'), $eTag) !== false ) {
            abort(304);
        }
        return response([
            'data' => $result
        ])->header('ETag', "\"{$eTag}\"");
    }

    // 后端提交数据
    public function submit(Request $request)
    {
//         Log::info('serverSubmitData:' . $request->input('node_id') . ':' . file_get_contents('php://input'));
        $server = ServerShadowsocks::find($request->input('node_id'));
        if (!$server) {
            return response([
                'ret' => 0,
                'msg' => 'server is not found'
            ]);
        }
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        Cache::put(CacheKey::get('SERVER_SHADOWSOCKS_ONLINE_USER', $server->id), count($data), 3600);
        Cache::put(CacheKey::get('SERVER_SHADOWSOCKS_LAST_PUSH_AT', $server->id), time(), 3600);
        $userService = new UserService();
        foreach ($data as $item) {
            $u = $item['u'];
            $d = $item['d'];
            $userService->trafficFetch($u, $d, $item['user_id'], $server->toArray(), 'shadowsocks');
        }

        return response([
            'ret' => 1,
            'msg' => 'ok'
        ]);
    }
}
