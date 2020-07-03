<?php

namespace App\Services;

use App\Models\ServerLog;
use App\Models\User;
use App\Models\Server;
use App\Models\ServerTrojan;
use App\Utils\CacheKey;
use App\Utils\Helper;
use Illuminate\Support\Facades\Cache;

class ServerService
{

    CONST V2RAY_CONFIG = '{"api":{"services":["HandlerService","StatsService"],"tag":"api"},"dns":{},"stats":{},"inbound":{"port":443,"protocol":"vmess","settings":{"clients":[]},"sniffing":{"enabled":true,"destOverride":["http","tls"]},"streamSettings":{"network":"tcp"},"tag":"proxy"},"inboundDetour":[{"listen":"0.0.0.0","port":23333,"protocol":"dokodemo-door","settings":{"address":"0.0.0.0"},"tag":"api"}],"log":{"loglevel":"debug","access":"access.log","error":"error.log"},"outbound":{"protocol":"freedom","settings":{}},"outboundDetour":[{"protocol":"blackhole","settings":{},"tag":"block"}],"routing":{"rules":[{"inboundTag":"api","outboundTag":"api","type":"field"}]},"policy":{"levels":{"0":{"handshake":4,"connIdle":300,"uplinkOnly":5,"downlinkOnly":30,"statsUserUplink":true,"statsUserDownlink":true}}}}';
    CONST TROJAN_CONFIG = '{"run_type":"server","local_addr":"0.0.0.0","local_port":443,"remote_addr":"www.taobao.com","remote_port":80,"password":[],"ssl":{"cert":"server.crt","key":"server.key","sni":"domain.com"},"api":{"enabled":true,"api_addr":"127.0.0.1","api_port":10000}}';
    public function getVmess(User $user, $all = false):array
    {
        $vmess = [];
        $model = Server::orderBy('sort', 'ASC');
        if (!$all) {
            $model->where('show', 1);
        }
        $vmesss = $model->get();
        foreach ($vmesss as $k => $v) {
            $groupId = json_decode($vmesss[$k]['group_id']);
            if (in_array($user->group_id, $groupId)) {
                $vmesss[$k]['link'] = Helper::buildVmessLink($vmesss[$k], $user);
                if ($vmesss[$k]['parent_id']) {
                    $vmesss[$k]['last_check_at'] = Cache::get(CacheKey::get('SERVER_V2RAY_LAST_CHECK_AT', $vmesss[$k]['parent_id']));
                } else {
                    $vmesss[$k]['last_check_at'] = Cache::get(CacheKey::get('SERVER_V2RAY_LAST_CHECK_AT', $vmesss[$k]['id']));
                }
                array_push($vmess, $vmesss[$k]);
            }
        }


        return $vmess;
    }

    public function getTrojan(User $user, $all = false)
    {
        $trojan = [];
        $model = ServerTrojan::orderBy('sort', 'ASC');
        if (!$all) {
            $model->where('show', 1);
        }
        $trojans = $model->get();
        foreach ($trojans as $k => $v) {
            $groupId = json_decode($trojans[$k]['group_id']);
            if (in_array($user->group_id, $groupId)) {
                if ($trojans[$k]['parent_id']) {
                    $trojans[$k]['last_check_at'] = Cache::get(CacheKey::get('SERVER_TROJAN_LAST_CHECK_AT', $trojans[$k]['parent_id']));
                } else {
                    $trojans[$k]['last_check_at'] = Cache::get(CacheKey::get('SERVER_TROJAN_LAST_CHECK_AT', $trojans[$k]['id']));
                }
                array_push($trojan, $trojans[$k]);
            }

        }
        return $trojan;
    }

    public function getAllServers(User $user, $all = false)
    {
        return [
            'vmess' => $this->getVmess($user, $all),
            'trojan' => $this->getTrojan($user, $all)
        ];
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
                'uuid',
                'v2ray_alter_id',
                'v2ray_level'
            ])
            ->get();
    }

    public function getVmessConfig(int $nodeId, int $localPort)
    {
        $server = Server::find($nodeId);
        if (!$server) {
            abort(500, '节点不存在');
        }
        $json = json_decode(self::V2RAY_CONFIG);
        $json->log->loglevel = config('v2board.server_log_level', 'none');
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
            }
        }
    }

    private function setRule(Server $server, object $json)
    {
        if ($server->ruleSettings) {
            $rules = json_decode($server->ruleSettings);
            // domain
            if (isset($rules->domain) && !empty($rules->domain)) {
                $rules->domain = array_filter($rules->domain);
                $domainObj = new \StdClass();
                $domainObj->type = 'field';
                $domainObj->domain = $rules->domain;
                $domainObj->outboundTag = 'block';
                array_push($json->routing->rules, $domainObj);
            }
            // protocol
            if (isset($rules->protocol) && !empty($rules->protocol)) {
                $rules->protocol = array_filter($rules->protocol);
                $protocolObj = new \StdClass();
                $protocolObj->type = 'field';
                $protocolObj->protocol = $rules->protocol;
                $protocolObj->outboundTag = 'block';
                array_push($json->routing->rules, $protocolObj);
            }
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
}
