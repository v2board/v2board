<?php

namespace App\Services;

use App\Models\ServerLog;
use App\Models\ServerShadowsocks;
use App\Models\User;
use App\Models\Server;
use App\Models\ServerTrojan;
use App\Utils\CacheKey;
use Illuminate\Support\Facades\Cache;

class ServerService
{

    CONST V2RAY_CONFIG = '{"api":{"services":["HandlerService","StatsService"],"tag":"api"},"dns":{},"stats":{},"inbound":{"port":443,"protocol":"vmess","settings":{"clients":[]},"sniffing":{"enabled":true,"destOverride":["http","tls"]},"streamSettings":{"network":"tcp"},"tag":"proxy"},"inboundDetour":[{"listen":"127.0.0.1","port":23333,"protocol":"dokodemo-door","settings":{"address":"0.0.0.0"},"tag":"api"}],"log":{"loglevel":"debug","access":"access.log","error":"error.log"},"outbound":{"protocol":"freedom","settings":{}},"outboundDetour":[{"protocol":"blackhole","settings":{},"tag":"block"}],"routing":{"rules":[{"inboundTag":"api","outboundTag":"api","type":"field"}]},"policy":{"levels":{"0":{"handshake":4,"connIdle":300,"uplinkOnly":5,"downlinkOnly":30,"statsUserUplink":true,"statsUserDownlink":true}}}}';
    CONST TROJAN_CONFIG = '{"run_type":"server","local_addr":"0.0.0.0","local_port":443,"remote_addr":"www.taobao.com","remote_port":80,"password":[],"ssl":{"cert":"server.crt","key":"server.key","sni":"domain.com"},"api":{"enabled":true,"api_addr":"127.0.0.1","api_port":10000}}';
    public function getV2ray(User $user, $all = false):array
    {
        $servers = [];
        $model = Server::orderBy('sort', 'ASC');
        if (!$all) {
            $model->where('show', 1);
        }
        $v2ray = $model->get();
        for ($i = 0; $i < count($v2ray); $i++) {
            $v2ray[$i]['type'] = 'v2ray';
            $groupId = json_decode($v2ray[$i]['group_id']);
            if (in_array($user->group_id, $groupId)) {
                if ($v2ray[$i]['parent_id']) {
                    $v2ray[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_V2RAY_LAST_CHECK_AT', $v2ray[$i]['parent_id']));
                } else {
                    $v2ray[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_V2RAY_LAST_CHECK_AT', $v2ray[$i]['id']));
                }
                array_push($servers, $v2ray[$i]->toArray());
            }
        }


        return $servers;
    }

    public function getTrojan(User $user, $all = false):array
    {
        $servers = [];
        $model = ServerTrojan::orderBy('sort', 'ASC');
        if (!$all) {
            $model->where('show', 1);
        }
        $trojan = $model->get();
        for ($i = 0; $i < count($trojan); $i++) {
            $trojan[$i]['type'] = 'trojan';
            $groupId = json_decode($trojan[$i]['group_id']);
            if (in_array($user->group_id, $groupId)) {
                if ($trojan[$i]['parent_id']) {
                    $trojan[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_TROJAN_LAST_CHECK_AT', $trojan[$i]['parent_id']));
                } else {
                    $trojan[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_TROJAN_LAST_CHECK_AT', $trojan[$i]['id']));
                }
                array_push($servers, $trojan[$i]->toArray());
            }
        }
        return $servers;
    }

    public function getShadowsocks(User $user, $all = false)
    {
        $servers = [];
        $model = ServerShadowsocks::orderBy('sort', 'ASC');
        if (!$all) {
            $model->where('show', 1);
        }
        $shadowsocks = $model->get();
        for ($i = 0; $i < count($shadowsocks); $i++) {
            $shadowsocks[$i]['type'] = 'shadowsocks';
            $groupId = json_decode($shadowsocks[$i]['group_id']);
            if (in_array($user->group_id, $groupId)) {
                if ($shadowsocks[$i]['parent_id']) {
                    $shadowsocks[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_SHADOWSOCKS_LAST_CHECK_AT', $shadowsocks[$i]['parent_id']));
                } else {
                    $shadowsocks[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_SHADOWSOCKS_LAST_CHECK_AT', $shadowsocks[$i]['id']));
                }
                array_push($servers, $shadowsocks[$i]->toArray());
            }

        }
        return $servers;
    }

    public function getAvailableServers(User $user, $all = false)
    {
        $servers = array_merge(
            $this->getShadowsocks($user, $all),
            $this->getV2ray($user, $all),
            $this->getTrojan($user, $all)
        );
        $tmp = array_column($servers, 'sort');
        array_multisort($tmp, SORT_ASC, $servers);
        return $servers;
    }


    public function getAvailableUsers($groupId)
    {
        return User::whereIn('group_id', $groupId)
            ->whereRaw('u + d < transfer_enable')
            ->where(function ($query) {
                $query->where('expired_at', '>=', time())
                    ->orWhere('expired_at', NULL);
            })
            ->where('banned', 0)
            ->select([
                'id',
                'email',
                't',
                'u',
                'd',
                'transfer_enable',
                'uuid'
            ])
            ->get();
    }

    public function getV2RayConfig(int $nodeId, int $localPort)
    {
        $server = Server::find($nodeId);
        if (!$server) {
            abort(500, '节点不存在');
        }
        $json = json_decode(self::V2RAY_CONFIG);
        $json->log->loglevel = (int)config('v2board.server_log_enable') ? 'debug' : 'none';
        $json->inboundDetour[0]->port = (int)$localPort;
        $json->inbound->port = (int)$server->server_port;
        $json->inbound->streamSettings->network = $server->network;
        $this->setDns($server, $json);
        $this->setNetwork($server, $json);
        $this->setRule($server, $json);
        $this->setTls($server, $json);

        return $json;
    }

    public function getTrojanConfig(int $nodeId, int $localPort)
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

    private function setDns(Server $server, object $json)
    {
        if ($server->dnsSettings) {
            $dns = json_decode($server->dnsSettings);
            if (isset($dns->servers)) {
                array_push($dns->servers, '1.1.1.1');
                array_push($dns->servers, 'localhost');
            }
            $json->dns = $dns;
            $json->outbound->settings->domainStrategy = 'UseIP';
        }
    }

    private function setNetwork(Server $server, object $json)
    {
        if ($server->networkSettings) {
            switch ($server->network) {
                case 'tcp':
                    $json->inbound->streamSettings->tcpSettings = json_decode($server->networkSettings);
                    break;
                case 'kcp':
                    $json->inbound->streamSettings->kcpSettings = json_decode($server->networkSettings);
                    break;
                case 'ws':
                    $json->inbound->streamSettings->wsSettings = json_decode($server->networkSettings);
                    break;
                case 'http':
                    $json->inbound->streamSettings->httpSettings = json_decode($server->networkSettings);
                    break;
                case 'domainsocket':
                    $json->inbound->streamSettings->dsSettings = json_decode($server->networkSettings);
                    break;
                case 'quic':
                    $json->inbound->streamSettings->quicSettings = json_decode($server->networkSettings);
                    break;
                case 'grpc':
                    $json->inbound->streamSettings->grpcSettings = json_decode($server->networkSettings);
                    break;
            }
        }
    }

    private function setRule(Server $server, object $json)
    {
        $domainRules = array_filter(explode(PHP_EOL, config('v2board.server_v2ray_domain')));
        $protocolRules = array_filter(explode(PHP_EOL, config('v2board.server_v2ray_protocol')));
        if ($server->ruleSettings) {
            $ruleSettings = json_decode($server->ruleSettings);
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
            $json->inbound->sniffing->enabled = false;
        }
    }

    private function setTls(Server $server, object $json)
    {
        if ((int)$server->tls) {
            $tlsSettings = json_decode($server->tlsSettings);
            $json->inbound->streamSettings->security = 'tls';
            $tls = (object)[
                'certificateFile' => '/root/.cert/server.crt',
                'keyFile' => '/root/.cert/server.key'
            ];
            $json->inbound->streamSettings->tlsSettings = new \StdClass();
            if (isset($tlsSettings->serverName)) {
                $json->inbound->streamSettings->tlsSettings->serverName = (string)$tlsSettings->serverName;
            }
            if (isset($tlsSettings->allowInsecure)) {
                $json->inbound->streamSettings->tlsSettings->allowInsecure = (int)$tlsSettings->allowInsecure ? true : false;
            }
            $json->inbound->streamSettings->tlsSettings->certificates[0] = $tls;
        }
    }

    public function log(int $userId, int $serverId, int $u, int $d, float $rate, string $method)
    {
        if (($u + $d) <= 10240) return;
        $timestamp = strtotime(date('Y-m-d H:0'));
        $serverLog = ServerLog::where('log_at', '>=', $timestamp)
            ->where('log_at', '<', $timestamp + 3600)
            ->where('server_id', $serverId)
            ->where('user_id', $userId)
            ->where('rate', $rate)
            ->where('method', $method)
            ->lockForUpdate()
            ->first();
        if ($serverLog) {
            $serverLog->u = $serverLog->u + $u;
            $serverLog->d = $serverLog->d + $d;
            $serverLog->save();
        } else {
            $serverLog = new ServerLog();
            $serverLog->user_id = $userId;
            $serverLog->server_id = $serverId;
            $serverLog->u = $u;
            $serverLog->d = $d;
            $serverLog->rate = $rate;
            $serverLog->log_at = $timestamp;
            $serverLog->method = $method;
            $serverLog->save();
        }
    }

    public function getShadowsocksServers()
    {
        $server = ServerShadowsocks::orderBy('sort', 'ASC')->get();
        for ($i = 0; $i < count($server); $i++) {
            $server[$i]['type'] = 'shadowsocks';
            if (!empty($server[$i]['tags'])) {
                $server[$i]['tags'] = json_decode($server[$i]['tags']);
            }
            $server[$i]['group_id'] = json_decode($server[$i]['group_id']);
        }
        return $server->toArray();
    }

    public function getV2rayServers()
    {
        $server = Server::orderBy('sort', 'ASC')->get();
        for ($i = 0; $i < count($server); $i++) {
            $server[$i]['type'] = 'v2ray';
            if (!empty($server[$i]['tags'])) {
                $server[$i]['tags'] = json_decode($server[$i]['tags']);
            }
            if (!empty($server[$i]['dnsSettings'])) {
                $server[$i]['dnsSettings'] = json_decode($server[$i]['dnsSettings']);
            }
            if (!empty($server[$i]['tlsSettings'])) {
                $server[$i]['tlsSettings'] = json_decode($server[$i]['tlsSettings']);
            }
            if (!empty($server[$i]['ruleSettings'])) {
                $server[$i]['ruleSettings'] = json_decode($server[$i]['ruleSettings']);
            }
            $server[$i]['group_id'] = json_decode($server[$i]['group_id']);
        }
        return $server->toArray();
    }

    public function getTrojanServers()
    {
        $server = ServerTrojan::orderBy('sort', 'ASC')->get();
        for ($i = 0; $i < count($server); $i++) {
            $server[$i]['type'] = 'trojan';
            if (!empty($server[$i]['tags'])) {
                $server[$i]['tags'] = json_decode($server[$i]['tags']);
            }
            $server[$i]['group_id'] = json_decode($server[$i]['group_id']);
        }
        return $server->toArray();
    }

    public function mergeData(&$servers)
    {
        foreach ($servers as $k => $v) {
            $serverType = strtoupper($servers[$k]['type']);
            $servers[$k]['online'] = Cache::get(CacheKey::get("SERVER_{$serverType}_ONLINE_USER", $servers[$k]['parent_id'] ? $servers[$k]['parent_id'] : $servers[$k]['id']));
            if ($servers[$k]['parent_id']) {
                $servers[$k]['last_check_at'] = Cache::get(CacheKey::get("SERVER_{$serverType}_LAST_CHECK_AT", $servers[$k]['parent_id']));
                $servers[$k]['last_push_at'] = Cache::get(CacheKey::get("SERVER_{$serverType}_LAST_PUSH_AT", $servers[$k]['parent_id']));
            } else {
                $servers[$k]['last_check_at'] = Cache::get(CacheKey::get("SERVER_{$serverType}_LAST_CHECK_AT", $servers[$k]['id']));
                $servers[$k]['last_push_at'] = Cache::get(CacheKey::get("SERVER_{$serverType}_LAST_PUSH_AT", $servers[$k]['id']));
            }
            if ((time() - 300) >= $servers[$k]['last_check_at']) {
                $servers[$k]['available_status'] = 0;
            } else if ((time() - 300) >= $servers[$k]['last_push_at']) {
                $servers[$k]['available_status'] = 1;
            } else {
                $servers[$k]['available_status'] = 2;
            }
        }
    }

    public function getAllServers()
    {
        $servers = array_merge(
            $this->getShadowsocksServers(),
            $this->getV2rayServers(),
            $this->getTrojanServers()
        );
        $this->mergeData($servers);
        $tmp = array_column($servers, 'sort');
        array_multisort($tmp, SORT_ASC, $servers);
        return $servers;
    }
}
