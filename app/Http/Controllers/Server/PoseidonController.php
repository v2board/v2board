<?php

namespace App\Http\Controllers\Server;

use App\Services\ServerService;
use App\Services\UserService;
use App\Utils\CacheKey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Server;
use App\Models\ServerLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/*
 * V2ray Poseidon
 * Github: https://github.com/ColetteContreras/trojan-poseidon
 */
class PoseidonController extends Controller
{
    public $poseidonVersion;

    public function __construct(Request $request)
    {
        $this->poseidonVersion = $request->input('poseidon_version');
    }

    // 后端获取用户
    public function user(Request $request)
    {
        if ($r = $this->verifyToken($request)) { return $r; }

        $nodeId = $request->input('node_id');
        $server = Server::find($nodeId);
        if (!$server) {
            return $this->error("server could not be found", 404);
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

        return $this->success($result);
    }

    // 后端提交数据
    public function submit(Request $request)
    {
        if ($r = $this->verifyToken($request)) { return $r; }
        $server = Server::find($request->input('node_id'));
        if (!$server) {
            return $this->error("server could not be found", 404);
        }
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        Cache::put(CacheKey::get('SERVER_V2RAY_ONLINE_USER', $server->id), count($data), 3600);
        Cache::put(CacheKey::get('SERVER_V2RAY_LAST_PUSH_AT', $server->id), time(), 3600);
        $userService = new UserService();
        foreach ($data as $item) {
            $u = $item['u'] * $server->rate;
            $d = $item['d'] * $server->rate;
            if (!$userService->trafficFetch($u, $d, $item['user_id'], $server, 'vmess')) {
                return $this->error("user fetch fail", 500);
            }
        }

        return $this->success('');
    }

    // 后端获取配置
    public function config(Request $request)
    {
        if ($r = $this->verifyToken($request)) { return $r; }

        $nodeId = $request->input('node_id');
        $localPort = $request->input('local_port');
        if (empty($nodeId) || empty($localPort)) {
            return $this->error('invalid parameters', 400);
        }

        $serverService = new ServerService();
        try {
            $json = $serverService->getV2RayConfig($nodeId, $localPort);
            $json->poseidon = [
              'license_key' => (string)config('v2board.server_license'),
            ];
            if ($this->poseidonVersion >= 'v1.5.0') {
                // don't need it after v1.5.0
                unset($json->inboundDetour);
                unset($json->stats);
                unset($json->api);
                array_shift($json->routing->rules);
            }

            foreach($json->policy->levels as &$level) {
                $level->handshake = 2;
                $level->uplinkOnly = 2;
                $level->downlinkOnly = 2;
                $level->connIdle = 60;
            }

            return $this->success($json);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    protected function verifyToken(Request $request)
    {
        $token = $request->input('token');
        if (empty($token)) {
            return $this->error("token must be set");
        }
        if ($token !== config('v2board.server_token')) {
            return $this->error("invalid token");
        }
    }

    protected function error($msg, int $status = 400) {
        return response([
            'msg' => $msg,
        ], $status);
    }

    protected function success($data) {
         $req = request();
        // Only for "GET" method
        if (!$req->isMethod('GET') || !$data) {
            return response([
                'msg' => 'ok',
                'data' => $data,
            ]);
        }

        $etag = sha1(json_encode($data));
        if ($etag == $req->header("IF-NONE-MATCH")) {
            return response(null, 304);
        }

        return response([
            'msg' => 'ok',
            'data' => $data,
        ])->header('ETAG', $etag);
    }
}
