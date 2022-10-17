<?php

namespace App\Http\Controllers\Server;

use App\Services\ServerService;
use App\Services\UserService;
use App\Utils\CacheKey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServerTrojan;
use App\Models\ServerLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/*
 * Tidal Lab Trojan
 * Github: https://github.com/tokumeikoi/tidalab-trojan
 */
class TrojanTidalabController extends Controller
{
    CONST TROJAN_CONFIG = '{"run_type":"server","local_addr":"0.0.0.0","local_port":443,"remote_addr":"www.taobao.com","remote_port":80,"password":[],"ssl":{"cert":"server.crt","key":"server.key","sni":"domain.com"},"api":{"enabled":true,"api_addr":"127.0.0.1","api_port":10000}}';
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
        $server = ServerTrojan::find($nodeId);
        if (!$server) {
            abort(500, 'fail');
        }
        Cache::put(CacheKey::get('SERVER_TROJAN_LAST_CHECK_AT', $server->id), time(), 3600);
        $serverService = new ServerService();
        $users = $serverService->getAvailableUsers($server->group_id);
        $result = [];
        foreach ($users as $user) {
            $user->trojan_user = [
                "password" => $user->uuid,
            ];
            unset($user['uuid']);
            array_push($result, $user);
        }
        $eTag = sha1(json_encode($result));
        if (strpos($request->header('If-None-Match'), $eTag) !== false ) {
            abort(304);
        }
        return response([
            'msg' => 'ok',
            'data' => $result,
        ])->header('ETag', "\"{$eTag}\"");
    }

    // 后端提交数据
    public function submit(Request $request)
    {
        // Log::info('serverSubmitData:' . $request->input('node_id') . ':' . file_get_contents('php://input'));
        $server = ServerTrojan::find($request->input('node_id'));
        if (!$server) {
            return response([
                'ret' => 0,
                'msg' => 'server is not found'
            ]);
        }
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        Cache::put(CacheKey::get('SERVER_TROJAN_ONLINE_USER', $server->id), count($data), 3600);
        Cache::put(CacheKey::get('SERVER_TROJAN_LAST_PUSH_AT', $server->id), time(), 3600);
        $userService = new UserService();
        foreach ($data as $item) {
            $u = $item['u'];
            $d = $item['d'];
            $userService->trafficFetch($u, $d, $item['user_id'], $server->toArray(), 'trojan');
        }

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
        try {
            $json = $this->getTrojanConfig($nodeId, $localPort);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        die(json_encode($json, JSON_UNESCAPED_UNICODE));
    }

    private function getTrojanConfig(int $nodeId, int $localPort)
    {
        $server = ServerTrojan::find($nodeId);
        if (!$server) {
            abort(500, '节点不存在');
        }

        $json = json_decode(self::TROJAN_CONFIG);
        $json->local_port = $server->server_port;
        $json->ssl->sni = $server->server_name ? $server->server_name : $server->host;
        $json->ssl->cert = "/root/.cert/server.crt";
        $json->ssl->key = "/root/.cert/server.key";
        $json->api->api_port = $localPort;
        return $json;
    }
}
