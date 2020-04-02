<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Server;
use App\Utils\Helper;
use Symfony\Component\Yaml\Yaml;
use App\Services\UserService;

class ClientController extends Controller
{
    public function subscribe(Request $request)
    {
        $user = $request->user;
        $server = [];
        // account not expired and is not banned.
        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            $servers = Server::where('show', 1)
                ->orderBy('name')
                ->get();
            foreach ($servers as $item) {
                $groupId = json_decode($item['group_id']);
                if (in_array($user->group_id, $groupId)) {
                    array_push($server, $item);
                }
            }
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Quantumult%20X') !== false) {
                die($this->quantumultX($user, $server));
            }
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Quantumult') !== false) {
                die($this->quantumult($user, $server));
            }
            if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'clash') !== false) {
                die($this->clash($user, $server));
            }
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Surfboard') !== false) {
                die($this->surge($user, $server));
            }
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Surge') !== false) {
                die($this->surge($user, $server));
            }
        }
        die($this->origin($user, $server));
    }

    private function quantumultX($user, $server)
    {
        $uri = '';
        foreach ($server as $item) {
            $uri .= "vmess=" . $item->host . ":" . $item->port . ", method=none, password=" . $user->v2ray_uuid . ", fast-open=false, udp-relay=false, tag=" . $item->name;
            if ($item->network == 'ws') {
                $uri .= ', obfs=' . ($item->tls ? 'wss' : 'ws');
                if ($item->networkSettings) {
                    $wsSettings = json_decode($item->networkSettings);
                    if (isset($wsSettings->path)) $uri .= ', obfs-uri=' . $wsSettings->path;
                    if (isset($wsSettings->headers->Host)) $uri .= ', obfs-host=' . $wsSettings->headers->Host;
                }
            }
            $uri .= "\r\n";
        }
        return base64_encode($uri);
    }

    private function quantumult($user, $server)
    {
        $uri = '';
        header('subscription-userinfo: upload=' . $user->u . '; download=' . $user->d . ';total=' . $user->transfer_enable);
        foreach ($server as $item) {
            $str = '';
            $str .= $item->name . '= vmess, ' . $item->host . ', ' . $item->port . ', chacha20-ietf-poly1305, "' . $user->v2ray_uuid . '", over-tls=' . ($item->tls ? "true" : "false") . ', certificate=0, group=' . config('v2board.app_name', 'V2Board');
            if ($item->network === 'ws') {
                $str .= ', obfs=ws';
                if ($item->networkSettings) {
                    $wsSettings = json_decode($item->networkSettings);
                    if (isset($wsSettings->path)) $str .= ', obfs-path="' . $wsSettings->path . '"';
                    if (isset($wsSettings->headers->Host)) $str .= ', obfs-header="Host:' . $wsSettings->headers->Host . '"';
                }
            }
            $uri .= "vmess://" . base64_encode($str) . "\r\n";
        }
        return base64_encode($uri);
    }

    private function origin($user, $server)
    {
        $uri = '';
        foreach ($server as $item) {
            $uri .= Helper::buildVmessLink($item, $user);
        }
        return base64_encode($uri);
    }

    private function surge($user, $server)
    {
        $proxies = '';
        $proxyGroup = '';
        foreach ($server as $item) {
            // [Proxy]
            $proxies .= $item->name . ' = vmess, ' . $item->host . ', ' . $item->port . ', username=' . $user->v2ray_uuid . ', tls=' . ($item->tls ? "true" : "false");
            if ($item->network == 'ws') {
                $proxies .= ', ws=true';
                if ($item->networkSettings) {
                    $wsSettings = json_decode($item->networkSettings);
                    if (isset($wsSettings->path)) $proxies .= ', ws-path=' . $wsSettings->path;
                    if (isset($wsSettings->headers->Host)) $proxies .= ', ws-headers=' . $wsSettings->headers->Host;
                }
            }
            $proxies .= "\r\n";
            // [Proxy Group]
            $proxyGroup .= $item->name . ', ';
        }

        try {
            $rules = '';
            foreach (glob(base_path() . '/resources/rules/' . '*.surge.conf') as $file) {
                $rules = file_get_contents("$file");
            }
        } catch (\Exception $e) {}

        // Subscription link
        $subsURL = 'http';
        if ($_SERVER['HTTPS'] == 'on') {
            $subsURL .= 's';
        }
        $subsURL .= '://';
        if ($_SERVER['SERVER_PORT'] != ('80' || '443')) {
            $subsURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else {
            $subsURL .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        }

        $rules = str_replace('{subs_link}',$subsURL,$rules);
        $rules = str_replace('{proxies}',$proxies,$rules);
        $rules = str_replace('{proxy_group}',rtrim($proxyGroup, ', '),$rules);
        return $rules;
    }

    private function clash($user, $server)
    {
        $proxy = [];
        $proxyGroup = [];
        $proxies = [];
        $rules = [];
        foreach ($server as $item) {
            $array = [];
            $array['name'] = $item->name;
            $array['type'] = 'vmess';
            $array['server'] = $item->host;
            $array['port'] = $item->port;
            $array['uuid'] = $user->v2ray_uuid;
            $array['alterId'] = $user->v2ray_alter_id;
            $array['cipher'] = 'auto';
            if ($item->tls) {
                $array['tls'] = true;
                $array['skip-cert-verify'] = true;
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

        array_push($proxyGroup, [
            'name' => 'auto',
            'type' => 'url-test',
            'proxies' => $proxies,
            'url' => 'https://www.bing.com',
            'interval' => 300
        ]);
        array_push($proxyGroup, [
            'name' => 'fallback-auto',
            'type' => 'fallback',
            'proxies' => $proxies,
            'url' => 'https://www.bing.com',
            'interval' => 300
        ]);
        array_push($proxyGroup, [
            'name' => 'select',
            'type' => 'select',
            'proxies' => array_merge($proxies, [
                'auto',
                'fallback-auto'
            ])
        ]);

        try {
            $rules = [];
            foreach (glob(base_path() . '/resources/rules/' . '*.clash.yaml') as $file) {
                $rules = array_merge($rules, Yaml::parseFile($file)['Rule']);
            }
        } catch (\Exception $e) {}

        $config = [
            'port' => 7890,
            'socks-port' => 7891,
            'allow-lan' => false,
            'mode' => 'Rule',
            'log-level' => 'info',
            'external-controller' => '0.0.0.0:9090',
            'secret' => '',
            'Proxy' => $proxy,
            'Proxy Group' => $proxyGroup,
            'Rule' => $rules
        ];

        return Yaml::dump($config);
    }
}
