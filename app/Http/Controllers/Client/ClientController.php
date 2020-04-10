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
            // TODO 二次开发
            $ua_list=array("Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.3; Trident/7.0; .NET4.0E; .NET4.0C)",
                "Mozilla/5.0 (compatible; WOW64; MSIE 10.0; Windows NT 6.2)",
                "Mozilla/5.0 (iPad; CPU OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3",
                "Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3",
                "Mozilla/5.0 (Linux; Android 4.4; Nexus 5 Build/BuildID) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36",
                "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36",
                "Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko",
                "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0",
                "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:40.0) Gecko/20100101 Firefox/44.0",
                "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
                "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.11 (KHTML, like Gecko) Ubuntu/11.10 Chromium/27.0.1453.93 Chrome/27.0.1453.93 Safari/537.36",
                "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:35.0) Gecko/20100101 Firefox/35.0");
            foreach ($ua_list as $ua){
                if(strcasecmp($_SERVER['HTTP_USER_AGENT'],"$ua")==0){
                    die($this->shadowrocket($user, $server));
                    break;
                }
            }
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


    private function shadowrocket($user, $server)
    {
        $uri = '';
        foreach ($server as $item) {
            $uri .= "vmess://" . base64_encode( "auto:" . $user->v2ray_uuid . "@" . $item->host . ":" . $item->port) . "?remarks=" . $item->name . "&tfo=1";
            if ($item->network == 'ws') {
                $uri .= '&obfs=websocket';
                $uri .= '&tls=' . ($item->tls ? '1' : '0');
                if ($item->networkSettings) {
                    $wsSettings = json_decode($item->networkSettings);
                    if (isset($wsSettings->path)) $uri .= '&path=' . $wsSettings->path;
                    if (isset($wsSettings->headers->Host)) $uri .= '&obfsParam=' . $wsSettings->headers->Host;
                }
                if ($item->tlsSettings) {
                    $tlsSettings = json_decode($item->tlsSettings);
                    if (isset($tlsSettings->allowInsecure)) $uri .= '&allowInsecure=' . $tlsSettings->allowInsecure;
                }
            }
            $uri .= "\r\n";
        }
        return base64_encode($uri);
    }

    private function quantumultX($user, $server)
    {
        $uri = '';
        foreach ($server as $item) {
            $uri .= "vmess=" . $item->host . ":" . $item->port . ", method=none, password=" . $user->v2ray_uuid . ", fast-open=true, udp-relay=false, tag=" . $item->name;
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
            $proxies .= $item->name . ' = vmess, ' . $item->host . ', ' . $item->port . ', username=' . $user->v2ray_uuid;
            if ($item->tls) {
                $tlsSettings = json_decode($item->tlsSettings);
                $proxies .= ', tls=' . ($item->tls ? "true" : "false");
                if (isset($tlsSettings->allowInsecure)) {
                  $proxies .= ', skip-cert-verify=true';
                }
            }
            if ($item->network == 'ws') {
                $proxies .= ', ws=true';
                if ($item->networkSettings) {
                    $wsSettings = json_decode($item->networkSettings);
                    if (isset($wsSettings->path)) $proxies .= ', ws-path=' . $wsSettings->path;
                    if (isset($wsSettings->headers->Host)) $proxies .= ', ws-headers=host:' . $wsSettings->headers->Host;
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
        if (isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == 'on') {
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
