<?php

namespace App\Http\Controllers\Server;

use App\Services\ServerService;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Server;
use App\Models\ServerLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PoseidonController extends Controller
{
    CONST SERVER_CONFIG = '{"api":{"services":["HandlerService","StatsService"],"tag":"api"},"stats":{},"inbound":{"port":443,"protocol":"vmess","settings":{"clients":[]},"sniffing":{"enabled": true,"destOverride": ["http","tls"]},"streamSettings":{"network":"tcp"},"tag":"proxy"},"inboundDetour":[{"listen":"0.0.0.0","port":23333,"protocol":"dokodemo-door","settings":{"address":"0.0.0.0"},"tag":"api"}],"log":{"loglevel":"debug","access":"access.log","error":"error.log"},"outbound":{"protocol":"freedom","settings":{}},"outboundDetour":[{"protocol":"blackhole","settings":{},"tag":"block"}],"routing":{"rules":[{"inboundTag":"api","outboundTag":"api","type":"field"}]},"policy":{"levels":{"0":{"handshake":4,"connIdle":300,"uplinkOnly":5,"downlinkOnly":30,"statsUserUplink":true,"statsUserDownlink":true}}}}';

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
        Cache::put('server_last_check_at_' . $server->id, time());
        $serverService = new ServerService();
        $users = $serverService->getAvailableUsers(json_decode($server->group_id));
        $result = [];
        foreach ($users as $user) {
            $user->v2ray_user = [
                "uuid" => $user->v2ray_uuid,
                "email" => sprintf("%s@v2board.user", $user->v2ray_uuid),
                "alter_id" => $user->v2ray_alter_id,
                "level" => $user->v2ray_level,
            ];
            unset($user['v2ray_uuid']);
            unset($user['v2ray_alter_id']);
            unset($user['v2ray_level']);
            array_push($result, $user);
        }

        return $this->success($result);
    }

    // 后端提交数据
    public function submit(Request $request)
    {
        if ($r = $this->verifyToken($request)) { return $r; }

        // Log::info('serverSubmitData:' . $request->input('node_id') . ':' . file_get_contents('php://input'));
        $server = Server::find($request->input('node_id'));
        if (!$server) {
            return $this->error("server could not be found", 404);
        }
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        $serverService = new ServerService();
        $userService = new UserService();
        foreach ($data as $item) {
            $u = $item['u'] * $server->rate;
            $d = $item['d'] * $server->rate;
            if (!$userService->trafficFetch($u, $d, $item['user_id'])) {
                return $this->error("user fetch fail", 500);
            }

            $serverService->log(
                $item['user_id'],
                $request->input('node_id'),
                $item['u'],
                $item['d'],
                $server->rate
            );
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
            $json = $serverService->getConfig($nodeId, $localPort);
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
        return response([
            'msg' => 'ok',
            'data' => $data,
        ]);
    }
}
