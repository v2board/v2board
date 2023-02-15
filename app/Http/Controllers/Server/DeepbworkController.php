<?php

namespace App\Http\Controllers\Server;

use App\Services\ServerService;
use App\Services\UserService;
use App\Utils\CacheKey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServerVmess;
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
    CONST V2RAY_CONFIG = '{"log":{"loglevel":"debug","access":"access.log","error":"error.log"},"api":{"services":["HandlerService","StatsService"],"tag":"api"},"dns":{},"stats":{},"inbounds":[{"port":443,"protocol":"vmess","settings":{"clients":[]},"sniffing":{"enabled":true,"destOverride":["http","tls"]},"streamSettings":{"network":"tcp"},"tag":"proxy"},{"listen":"127.0.0.1","port":23333,"protocol":"dokodemo-door","settings":{"address":"0.0.0.0"},"tag":"api"}],"outbounds":[{"protocol":"freedom","settings":{}},{"protocol":"blackhole","settings":{},"tag":"block"}],"routing":{"rules":[{"type":"field","inboundTag":"api","outboundTag":"api"}]},"policy":{"levels":{"0":{"handshake":4,"connIdle":300,"uplinkOnly":5,"downlinkOnly":30,"statsUserUplink":true,"statsUserDownlink":true}}}}';
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
        $server = ServerVmess::find($nodeId);
        if (!$server) {
            abort(500, 'fail');
        }
        Cache::put(CacheKey::get('SERVER_VMESS_LAST_CHECK_AT', $server->id), time(), 3600);
        $serverService = new ServerService();
        $users = $serverService->getAvailableUsers($server->group_id);
        $result = [];
        foreach ($users as $user) {
            $user->v2ray_user = [
                "uuid" => $user->uuid,
                "email" => sprintf("%s@v2board.user", $user->uuid),
                "alter_id" => 0,
                "level" => 0,
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
//         Log::info('serverSubmitData:' . $request->input('node_id') . ':' . file_get_contents('php://input'));
        $server = ServerVmess::find($request->input('node_id'));
        if (!$server) {
            return response([
                'ret' => 0,
                'msg' => 'server is not found'
            ]);
        }
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        Cache::put(CacheKey::get('SERVER_VMESS_ONLINE_USER', $server->id), count($data), 3600);
        Cache::put(CacheKey::get('SERVER_VMESS_LAST_PUSH_AT', $server->id), time(), 3600);
        $userService = new UserService();
        foreach ($data as $item) {
            $u = $item['u'];
            $d = $item['d'];
            $userService->trafficFetch($u, $d, $item['user_id'], $server->toArray(), 'vmess');
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
            $json = $this->getV2RayConfig($nodeId, $localPort);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        die(json_encode($json, JSON_UNESCAPED_UNICODE));
    }

    private function getV2RayConfig(int $nodeId, int $localPort)
    {
        $server = ServerVmess::find($nodeId);
        if (!$server) {
            abort(500, '节点不存在');
        }
        $json = json_decode(self::V2RAY_CONFIG);
        $json->log->loglevel = (int)config('v2board.server_log_enable') ? 'debug' : 'none';
        $json->inbounds[1]->port = (int)$localPort;
        $json->inbounds[0]->port = (int)$server->server_port;
        $json->inbounds[0]->streamSettings->network = $server->network;
        $this->setDns($server, $json);
        $this->setNetwork($server, $json);
        $this->setRule($server, $json);
        $this->setTls($server, $json);

        return $json;
    }

    private function setDns(ServerVmess $server, object $json)
    {
        if ($server->dnsSettings) {
            $dns = $server->dnsSettings;
            if (isset($dns->servers)) {
                array_push($dns->servers, '1.1.1.1');
                array_push($dns->servers, 'localhost');
            }
            $json->dns = $dns;
            $json->outbounds[0]->settings->domainStrategy = 'UseIP';
        }
    }

    private function setNetwork(ServerVmess $server, object $json)
    {
        if ($server->networkSettings) {
            switch ($server->network) {
                case 'tcp':
                    $json->inbounds[0]->streamSettings->tcpSettings = $server->networkSettings;
                    break;
                case 'kcp':
                    $json->inbounds[0]->streamSettings->kcpSettings = $server->networkSettings;
                    break;
                case 'ws':
                    $json->inbounds[0]->streamSettings->wsSettings = $server->networkSettings;
                    break;
                case 'http':
                    $json->inbounds[0]->streamSettings->httpSettings = $server->networkSettings;
                    break;
                case 'domainsocket':
                    $json->inbounds[0]->streamSettings->dsSettings = $server->networkSettings;
                    break;
                case 'quic':
                    $json->inbounds[0]->streamSettings->quicSettings = $server->networkSettings;
                    break;
                case 'grpc':
                    $json->inbounds[0]->streamSettings->grpcSettings = $server->networkSettings;
                    break;
            }
        }
    }

    private function setRule(ServerVmess $server, object $json)
    {
        $domainRules = array_filter(explode(PHP_EOL, config('v2board.server_v2ray_domain')));
        $protocolRules = array_filter(explode(PHP_EOL, config('v2board.server_v2ray_protocol')));
        if ($server->ruleSettings) {
            $ruleSettings = $server->ruleSettings;
            // domain
            if (isset($ruleSettings->domain)) {
                $ruleSettings->domain = array_filter($ruleSettings->domain);
                if (!empty($ruleSettings->domain)) {
                    $domainRules = array_merge($domainRules, $ruleSettings->domain);
                }
            }
            // protocol
            if (isset($ruleSettings->protocol)) {
                $ruleSettings->protocol = array_filter($ruleSettings->protocol);
                if (!empty($ruleSettings->protocol)) {
                    $protocolRules = array_merge($protocolRules, $ruleSettings->protocol);
                }
            }
        }
        if (!empty($domainRules)) {
            $domainObj = new \StdClass();
            $domainObj->type = 'field';
            $domainObj->domain = $domainRules;
            $domainObj->outboundTag = 'block';
            array_push($json->routing->rules, $domainObj);
        }
        if (!empty($protocolRules)) {
            $protocolObj = new \StdClass();
            $protocolObj->type = 'field';
            $protocolObj->protocol = $protocolRules;
            $protocolObj->outboundTag = 'block';
            array_push($json->routing->rules, $protocolObj);
        }
        if (empty($domainRules) && empty($protocolRules)) {
            $json->inbounds[0]->sniffing->enabled = false;
        }
    }

    private function setTls(ServerVMess $server, object $json)
    {
        if ((int)$server->tls) {
            $tlsSettings = $server->tlsSettings;
            $json->inbounds[0]->streamSettings->security = 'tls';
            $tls = (object)[
                'certificateFile' => '/root/.cert/server.crt',
                'keyFile' => '/root/.cert/server.key'
            ];
            $json->inbounds[0]->streamSettings->tlsSettings = new \StdClass();
            if (isset($tlsSettings->serverName)) {
                $json->inbounds[0]->streamSettings->tlsSettings->serverName = (string)$tlsSettings->serverName;
            }
            if (isset($tlsSettings->allowInsecure)) {
                $json->inbounds[0]->streamSettings->tlsSettings->allowInsecure = (int)$tlsSettings->allowInsecure ? true : false;
            }
            $json->inbounds[0]->streamSettings->tlsSettings->certificates[0] = $tls;
        }
    }
}
