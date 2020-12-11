<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\ServerService;
use App\Services\UserService;
use App\Utils\Clash;
use Illuminate\Http\Request;
use App\Models\Server;
use Symfony\Component\Yaml\Yaml;

class AppController extends Controller
{
    CONST CLIENT_CONFIG = '{"policy":{"levels":{"0":{"uplinkOnly":0}}},"dns":{"servers":["114.114.114.114","8.8.8.8"]},"outboundDetour":[{"protocol":"freedom","tag":"direct","settings":{}}],"inbound":{"listen":"0.0.0.0","port":31211,"protocol":"socks","settings":{"auth":"noauth","udp":true,"ip":"127.0.0.1"}},"inboundDetour":[{"listen":"0.0.0.0","allocate":{"strategy":"always","refresh":5,"concurrency":3},"port":31210,"protocol":"http","tag":"httpDetour","domainOverride":["http","tls"],"streamSettings":{},"settings":{"timeout":0}}],"routing":{"strategy":"rules","settings":{"domainStrategy":"IPIfNonMatch","rules":[{"type":"field","ip":["geoip:cn"],"outboundTag":"direct"},{"type":"field","ip":["0.0.0.0/8","10.0.0.0/8","100.64.0.0/10","127.0.0.0/8","169.254.0.0/16","172.16.0.0/12","192.0.0.0/24","192.0.2.0/24","192.168.0.0/16","198.18.0.0/15","198.51.100.0/24","203.0.113.0/24","::1/128","fc00::/7","fe80::/10"],"outboundTag":"direct"}]}},"outbound":{"tag":"proxy","sendThrough":"0.0.0.0","mux":{"enabled":false,"concurrency":8},"protocol":"vmess","settings":{"vnext":[{"address":"server","port":443,"users":[{"id":"uuid","alterId":2,"security":"auto","level":0}],"remark":"remark"}]},"streamSettings":{"network":"tcp","tcpSettings":{"header":{"type":"none"}},"security":"none","tlsSettings":{"allowInsecure":true,"allowInsecureCiphers":true},"kcpSettings":{"header":{"type":"none"},"mtu":1350,"congestion":false,"tti":20,"uplinkCapacity":5,"writeBufferSize":1,"readBufferSize":1,"downlinkCapacity":20},"wsSettings":{"path":"","headers":{"Host":"server.cc"}}}}}';
    CONST SOCKS_PORT = 10010;
    CONST HTTP_PORT = 10011;

    public function getConfig(Request $request)
    {
        $servers = [];
        $user = $request->user;
        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            $serverService = new ServerService();
            $servers = $serverService->getAvailableServers($user);
        }
        $config = Yaml::parseFile(base_path() . '/resources/rules/app.clash.yaml');
        $proxy = [];
        $proxies = [];

        foreach ($servers as $item) {
            if ($item['type'] === 'shadowsocks') {
                array_push($proxy, Clash::buildShadowsocks($user['uuid'], $item));
                array_push($proxies, $item['name']);
            }
            if ($item['type'] === 'v2ray') {
                array_push($proxy, Clash::buildVmess($user['uuid'], $item));
                array_push($proxies, $item['name']);
            }
            if ($item['type'] === 'trojan') {
                array_push($proxy, Clash::buildTrojan($user['uuid'], $item));
                array_push($proxies, $item['name']);
            }
        }

        $config['proxies'] = array_merge($config['proxies'] ? $config['proxies'] : [], $proxy);
        foreach ($config['proxy-groups'] as $k => $v) {
            $config['proxy-groups'][$k]['proxies'] = array_merge($config['proxy-groups'][$k]['proxies'], $proxies);
        }
        die(Yaml::dump($config));
    }

    public function getVersion(Request $request)
    {
        if (strpos($request->header('user-agent'), 'tidalab/4.0.0') !== false
            || strpos($request->header('user-agent'), 'tunnelab/4.0.0') !== false
        ) {
            if (strpos($request->header('user-agent'), 'Win64') !== false) {
                return response([
                    'data' => [
                        'version' => config('v2board.windows_version'),
                        'download_url' => config('v2board.windows_download_url')
                    ]
                ]);
            } else {
                return response([
                    'data' => [
                        'version' => config('v2board.macos_version'),
                        'download_url' => config('v2board.macos_download_url')
                    ]
                ]);
            }
            return;
        }
        return response([
            'data' => [
                'windows_version' => config('v2board.windows_version'),
                'windows_download_url' => config('v2board.windows_download_url'),
                'macos_version' => config('v2board.macos_version'),
                'macos_download_url' => config('v2board.macos_download_url'),
                'android_version' => config('v2board.android_version'),
                'android_download_url' => config('v2board.android_download_url')
            ]
        ]);
    }

    public function config(Request $request)
    {
        if (empty($request->input('server_id'))) {
            abort(500, '参数错误');
        }
        $user = $request->user;
        if ($user->expired_at < time() && $user->expired_at !== NULL) {
            abort(500, '订阅计划已过期');
        }
        $server = Server::where('show', 1)
            ->where('id', $request->input('server_id'))
            ->first();
        if (!$server) {
            abort(500, '服务器不存在');
        }
        $json = json_decode(self::CLIENT_CONFIG);
        //socks
        $json->inbound->port = (int)self::SOCKS_PORT;
        //http
        $json->inboundDetour[0]->port = (int)self::HTTP_PORT;
        //other
        $json->outbound->settings->vnext[0]->address = (string)$server->host;
        $json->outbound->settings->vnext[0]->port = (int)$server->port;
        $json->outbound->settings->vnext[0]->users[0]->id = (string)$user->uuid;
        $json->outbound->settings->vnext[0]->users[0]->alterId = (int)$server->alter_id;
        $json->outbound->settings->vnext[0]->remark = (string)$server->name;
        $json->outbound->streamSettings->network = $server->network;
        if ($server->networkSettings) {
            switch ($server->network) {
                case 'tcp':
                    $json->outbound->streamSettings->tcpSettings = json_decode($server->networkSettings);
                    break;
                case 'kcp':
                    $json->outbound->streamSettings->kcpSettings = json_decode($server->networkSettings);
                    break;
                case 'ws':
                    $json->outbound->streamSettings->wsSettings = json_decode($server->networkSettings);
                    break;
                case 'http':
                    $json->outbound->streamSettings->httpSettings = json_decode($server->networkSettings);
                    break;
                case 'domainsocket':
                    $json->outbound->streamSettings->dsSettings = json_decode($server->networkSettings);
                    break;
                case 'quic':
                    $json->outbound->streamSettings->quicSettings = json_decode($server->networkSettings);
                    break;
            }
        }
        if ($request->input('is_global')) {
            $json->routing->settings->rules[0]->outboundTag = 'proxy';
        }
        if ($server->tls) {
            $json->outbound->streamSettings->security = "tls";
        }
        die(json_encode($json, JSON_UNESCAPED_UNICODE));
    }
}
