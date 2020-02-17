<?php

namespace App\Http\Controllers\Server;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Server;
use App\Models\ServerLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DeepbworkController extends Controller
{
    CONST SERVER_CONFIG = '{"api":{"services":["HandlerService","StatsService"],"tag":"api"},"stats":{},"inbound":{"port":443,"protocol":"vmess","settings":{"clients":[]},"sniffing":{"enabled": true,"destOverride": ["http","tls"]},"streamSettings":{"network":"tcp"},"tag":"proxy"},"inboundDetour":[{"listen":"0.0.0.0","port":23333,"protocol":"dokodemo-door","settings":{"address":"0.0.0.0"},"tag":"api"}],"log":{"loglevel":"debug","access":"access.log","error":"error.log"},"outbound":{"protocol":"freedom","settings":{}},"outboundDetour":[{"protocol":"blackhole","settings":{},"tag":"block"}],"routing":{"rules":[{"inboundTag":"api","outboundTag":"api","type":"field"}]},"policy":{"levels":{"0":{"handshake":4,"connIdle":300,"uplinkOnly":5,"downlinkOnly":30,"statsUserUplink":true,"statsUserDownlink":true}}}}';

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
        Cache::put('server_last_check_at_' . $server->id, time());
        $users = User::whereIn('group_id', json_decode($server->group_id))
            ->select([
                'id',
                'email',
                't',
                'u',
                'd',
                'transfer_enable',
                'enable',
                'v2ray_uuid',
                'v2ray_alter_id',
                'v2ray_level'
            ])
            ->get();
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
        return response([
            'msg' => 'ok',
            'data' => $result,
        ]);
    }

    // 后端提交数据
    public function submit(Request $request)
    {
        Log::info('serverSubmitData:' . $request->input('node_id') . ':' . file_get_contents('php://input'));
        $server = Server::find($request->input('node_id'));
        if (!$server) {
            return response([
                'ret' => 1,
                'msg' => 'ok'
            ]);
        }
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $u = $item['u'] * $server->rate;
            $d = $item['d'] * $server->rate;
            $user = User::find($item['user_id']);
            $user->t = time();
            $user->u = $user->u + $u;
            $user->d = $user->d + $d;
            $user->save();

            $serverLog = new ServerLog();
            $serverLog->user_id = $item['user_id'];
            $serverLog->server_id = $request->input('node_id');
            $serverLog->u = $item['u'];
            $serverLog->d = $item['d'];
            $serverLog->rate = $server->rate;
            $serverLog->save();
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
        $server = Server::find($nodeId);
        if (!$server) {
            abort(500, '节点不存在');
        }
        $json = json_decode(self::SERVER_CONFIG);
        $json->inboundDetour[0]->port = (int)$localPort;
        $json->inbound->port = (int)$server->server_port;
        $json->inbound->streamSettings->network = $server->network;
        if ($server->settings) {
            switch ($server->network) {
                case 'tcp':
                    $json->inbound->streamSettings->tcpSettings = json_decode($server->settings);
                    break;
                case 'kcp':
                    $json->inbound->streamSettings->kcpSettings = json_decode($server->settings);
                    break;
                case 'ws':
                    $json->inbound->streamSettings->wsSettings = json_decode($server->settings);
                    break;
                case 'http':
                    $json->inbound->streamSettings->httpSettings = json_decode($server->settings);
                    break;
                case 'domainsocket':
                    $json->inbound->streamSettings->dsSettings = json_decode($server->settings);
                    break;
                case 'quic':
                    $json->inbound->streamSettings->quicSettings = json_decode($server->settings);
                    break;
            }
        }

        if ($server->rules) {
            $rules = json_decode($server->rules);
            // domain
            if (isset($rules->domain)) {
                $domainObj = new \StdClass();
                $domainObj->type = 'field';
                $domainObj->domain = $rules->domain;
                $domainObj->outboundTag = 'block';
                array_push($json->routing->rules, $domainObj);
            }
            // protocol
            if (isset($rules->protocol)) {
                $protocolObj = new \StdClass();
                $protocolObj->type = 'field';
                $protocolObj->protocol = $rules->protocol;
                $protocolObj->outboundTag = 'block';
                array_push($json->routing->rules, $protocolObj);
            }
        }

        if ((int)$server->tls) {
            $json->inbound->streamSettings->security = 'tls';
            $tls = (object)[
                'certificateFile' => '/home/v2ray.crt',
                'keyFile' => '/home/v2ray.key'
            ];
            $json->inbound->streamSettings->tlsSettings = new \StdClass();
            $json->inbound->streamSettings->tlsSettings->certificates[0] = $tls;
        }

        die(json_encode($json, JSON_UNESCAPED_UNICODE));
    }
}
