<?php

namespace App\Services;

use App\Models\ServerLog;
use App\Models\ServerRoute;
use App\Models\ServerShadowsocks;
use App\Models\User;
use App\Models\ServerV2ray;
use App\Models\ServerTrojan;
use App\Utils\CacheKey;
use App\Utils\Helper;
use Illuminate\Support\Facades\Cache;

class ServerService
{

    public function getV2ray(User $user, $all = false):array
    {
        $servers = [];
        $model = ServerV2ray::orderBy('sort', 'ASC');
        if (!$all) {
            $model->where('show', 1);
        }
        $v2ray = $model->get();
        for ($i = 0; $i < count($v2ray); $i++) {
            $v2ray[$i]['type'] = 'v2ray';
            $groupId = $v2ray[$i]['group_id'];
            if (!in_array($user->group_id, $groupId)) continue;
            if (strpos($v2ray[$i]['port'], '-') !== false) {
                $v2ray[$i]['port'] = Helper::randomPort($v2ray[$i]['port']);
            }
            if ($v2ray[$i]['parent_id']) {
                $v2ray[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_V2RAY_LAST_CHECK_AT', $v2ray[$i]['parent_id']));
            } else {
                $v2ray[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_V2RAY_LAST_CHECK_AT', $v2ray[$i]['id']));
            }
            array_push($servers, $v2ray[$i]->toArray());
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
            $groupId = $trojan[$i]['group_id'];
            if (!in_array($user->group_id, $groupId)) continue;
            if (strpos($trojan[$i]['port'], '-') !== false) {
                $trojan[$i]['port'] = Helper::randomPort($trojan[$i]['port']);
            }
            if ($trojan[$i]['parent_id']) {
                $trojan[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_TROJAN_LAST_CHECK_AT', $trojan[$i]['parent_id']));
            } else {
                $trojan[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_TROJAN_LAST_CHECK_AT', $trojan[$i]['id']));
            }
            array_push($servers, $trojan[$i]->toArray());
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
            $groupId = $shadowsocks[$i]['group_id'];
            if (!in_array($user->group_id, $groupId)) continue;
            if (strpos($shadowsocks[$i]['port'], '-') !== false) {
                $shadowsocks[$i]['port'] = Helper::randomPort($shadowsocks[$i]['port']);
            }
            if ($shadowsocks[$i]['parent_id']) {
                $shadowsocks[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_SHADOWSOCKS_LAST_CHECK_AT', $shadowsocks[$i]['parent_id']));
            } else {
                $shadowsocks[$i]['last_check_at'] = Cache::get(CacheKey::get('SERVER_SHADOWSOCKS_LAST_CHECK_AT', $shadowsocks[$i]['id']));
            }
            array_push($servers, $shadowsocks[$i]->toArray());
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
        $servers = array_map(function ($server) {
            $server['port'] = (int)$server['port'];
            return $server;
        }, $servers);
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
                'uuid',
                'speed_limit'
            ])
            ->get();
    }

    public function log(int $userId, int $serverId, int $u, int $d, float $rate, string $method)
    {
        if (($u + $d) < 10240) return true;
        $timestamp = strtotime(date('Y-m-d'));
        $serverLog = ServerLog::where('log_at', '>=', $timestamp)
            ->where('log_at', '<', $timestamp + 3600)
            ->where('server_id', $serverId)
            ->where('user_id', $userId)
            ->where('rate', $rate)
            ->where('method', $method)
            ->first();
        if ($serverLog) {
            try {
                $serverLog->increment('u', $u);
                $serverLog->increment('d', $d);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            $serverLog = new ServerLog();
            $serverLog->user_id = $userId;
            $serverLog->server_id = $serverId;
            $serverLog->u = $u;
            $serverLog->d = $d;
            $serverLog->rate = $rate;
            $serverLog->log_at = $timestamp;
            $serverLog->method = $method;
            return $serverLog->save();
        }
    }

    public function getShadowsocksServers()
    {
        $server = ServerShadowsocks::orderBy('sort', 'ASC')->get();
        for ($i = 0; $i < count($server); $i++) {
            $server[$i]['type'] = 'shadowsocks';
        }
        return $server->toArray();
    }

    public function getV2rayServers()
    {
        $server = ServerV2ray::orderBy('sort', 'ASC')->get();
        for ($i = 0; $i < count($server); $i++) {
            $server[$i]['type'] = 'v2ray';
        }
        return $server->toArray();
    }

    public function getTrojanServers()
    {
        $server = ServerTrojan::orderBy('sort', 'ASC')->get();
        for ($i = 0; $i < count($server); $i++) {
            $server[$i]['type'] = 'trojan';
        }
        return $server->toArray();
    }

    private function mergeData(&$servers)
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

    public function getRoutes(array $routeIds)
    {
        $routes = ServerRoute::select(['id', 'match', 'action', 'action_value'])->whereIn('id', $routeIds)->get();
        // TODO: remove on 1.8.0
        foreach ($routes as $k => $route) {
            $array = json_decode($route->match, true);
            if (is_array($array)) $routes[$k]['match'] = $array;
        }
        // TODO: remove on 1.8.0
        return $routes;
    }

    public function getServer($serverId, $serverType)
    {
        switch ($serverType) {
            case 'v2ray':
                return ServerV2ray::find($serverId);
            case 'shadowsocks':
                return ServerShadowsocks::find($serverId);
            case 'trojan':
                return ServerTrojan::find($serverId);
            default:
                return false;
        }
    }
}
