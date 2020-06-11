<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Models\Server;
use Symfony\Component\Yaml\Yaml;

class AppController extends Controller
{
    public function data(Request $request)
    {
        $server = [];
        $user = $request->user;
        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            $servers = Server::where('show', 1)
                ->orderBy('sort', 'ASC')
                ->get();
            foreach ($servers as $item) {
                $groupId = json_decode($item['group_id']);
                if (in_array($user->group_id, $groupId)) {
                    array_push($server, $item);
                }
            }
        }
        $config = Yaml::parseFile(base_path() . '/resources/rules/app.clash.yaml');
        $proxy = [];
        $proxies = [];
        foreach ($server as $item) {
            $array = [];
            $array['name'] = $item->name;
            $array['type'] = 'vmess';
            $array['server'] = $item->host;
            $array['port'] = $item->port;
            $array['uuid'] = $user->uuid;
            $array['alterId'] = $user->v2ray_alter_id;
            $array['cipher'] = 'auto';
            if ($item->tls) {
                $tlsSettings = json_decode($item->tlsSettings);
                $array['tls'] = true;
                if (isset($tlsSettings->allowInsecure)) $array['skip-cert-verify'] = ($tlsSettings->allowInsecure ? true : false );
            }
            if ($item->network == 'ws') {
                $array['network'] = $item->network;
                if ($item->networkSettings) {
                    $wsSettings = json_decode($item->networkSettings);
                    if (isset($wsSettings->path)) $array['ws-path'] = $wsSettings->path;
                    if (isset($wsSettings->headers->Host)) $array['ws-headers'] = [
                        'Host' => $wsSettings->headers->Host
                    ];
                }
            }
            array_push($proxy, $array);
            array_push($proxies, $item->name);
        }

        $config['Proxy'] = array_merge($config['Proxy'] ? $config['Proxy'] : [], $proxy);
        foreach ($config['Proxy Group'] as $k => $v) {
            $config['Proxy Group'][$k]['proxies'] = array_merge($config['Proxy Group'][$k]['proxies'], $proxies);
        }
        die(Yaml::dump($config));
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
        $json->outbound->settings->vnext[0]->users[0]->alterId = (int)$user->v2ray_alter_id;
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
