<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\ServerService;
use App\Utils\Clash;
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
        // account not expired and is not banned.
        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            $serverService = new ServerService();
            $servers = $serverService->getAllServers($user);
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Quantumult%20X') !== false) {
                die($this->quantumultX($user, $servers['vmess']));
            }
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Quantumult') !== false) {
                die($this->quantumult($user, $servers['vmess']));
            }
            if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'clash') !== false) {
                die($this->clash($user, $servers['vmess'], $servers['trojan']));
            }
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Surfboard') !== false) {
                die($this->surfboard($user, $servers['vmess']));
            }
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Surge') !== false) {
                die($this->surge($user, $servers['vmess']));
            }
        }
        die($this->origin($user, $servers['vmess']));
    }

    private function quantumultX($user, $vmess)
    {
        $uri = '';
        foreach ($vmess as $item) {
            $uri .= "vmess=" . $item->host . ":" . $item->port . ", method=none, password=" . $user->uuid . ", fast-open=false, udp-relay=false, tag=" . $item->name;
            if ($item->tls) {
                $tlsSettings = json_decode($item->tlsSettings);
                if ($item->network === 'tcp') $uri .= ', obfs=over-tls';
                if (isset($tlsSettings->allowInsecure)) {
                    // Default: tls-verification=true
                    $uri .= ', tls-verification=' . ($tlsSettings->allowInsecure ? "false" : "true");
                }
                if (isset($tlsSettings->serverName)) {
                    $uri .= ', obfs-host=' . $tlsSettings->serverName;
                }
            }
            if ($item->network === 'ws') {
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

    private function quantumult($user, $vmess)
    {
        $uri = '';
        header('subscription-userinfo: upload=' . $user->u . '; download=' . $user->d . ';total=' . $user->transfer_enable);
        foreach ($vmess as $item) {
            $str = '';
            $str .= $item->name . '= vmess, ' . $item->host . ', ' . $item->port . ', chacha20-ietf-poly1305, "' . $user->uuid . '", over-tls=' . ($item->tls ? "true" : "false") . ', certificate=0, group=' . config('v2board.app_name', 'V2Board');
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

    private function origin($user, $vmess)
    {
        $uri = '';
        foreach ($vmess as $item) {
            $uri .= Helper::buildVmessLink($item, $user);
        }
        return base64_encode($uri);
    }

    private function surge($user, $vmess)
    {
        $proxies = '';
        $proxyGroup = '';
        foreach ($vmess as $item) {
            // [Proxy]
            $proxies .= $item->name . ' = vmess, ' . $item->host . ', ' . $item->port . ', username=' . $user->uuid . ', tfo=true';
            if ($item->tls) {
                $tlsSettings = json_decode($item->tlsSettings);
                $proxies .= ', tls=' . ($item->tls ? "true" : "false");
                if (isset($tlsSettings->allowInsecure)) {
                  $proxies .= ', skip-cert-verify=' . ($tlsSettings->allowInsecure ? "true" : "false");
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

        $defaultConfig = base_path() . '/resources/rules/default.surge.conf';
        $customConfig = base_path() . '/resources/rules/custom.surge.conf';
        if (\File::exists($customConfig)) {
            $config = file_get_contents("$customConfig");
        } else {
            $config = file_get_contents("$defaultConfig");
        }

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

        $config = str_replace('$subs_link',$subsURL,$config);
        $config = str_replace('$proxies',$proxies,$config);
        $config = str_replace('$proxy_group',rtrim($proxyGroup, ', '),$config);
        return $config;
    }

    private function surfboard($user, $vmess)
    {
        $proxies = '';
        $proxyGroup = '';
        foreach ($vmess as $item) {
            // [Proxy]
            $proxies .= $item->name . ' = vmess, ' . $item->host . ', ' . $item->port . ', username=' . $user->uuid;
            if ($item->tls) {
                $tlsSettings = json_decode($item->tlsSettings);
                $proxies .= ', tls=' . ($item->tls ? "true" : "false");
                if (isset($tlsSettings->allowInsecure)) {
                  $proxies .= ', skip-cert-verify=' . ($tlsSettings->allowInsecure ? "true" : "false");
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

        $defaultConfig = base_path() . '/resources/rules/default.surfboard.conf';
        $customConfig = base_path() . '/resources/rules/custom.surfboard.conf';
        if (\File::exists($customConfig)) {
            $config = file_get_contents("$customConfig");
        } else {
            $config = file_get_contents("$defaultConfig");
        }

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

        $config = str_replace('$subs_link', $subsURL, $config);
        $config = str_replace('$proxies', $proxies, $config);
        $config = str_replace('$proxy_group', rtrim($proxyGroup, ', '), $config);
        return $config;
    }

    private function clash($user, $vmess = [], $trojan = [])
    {
        $defaultConfig = base_path() . '/resources/rules/default.clash.yaml';
        $customConfig = base_path() . '/resources/rules/custom.clash.yaml';
        if (\File::exists($customConfig)) {
            $config = Yaml::parseFile($customConfig);
        } else {
            $config = Yaml::parseFile($defaultConfig);
        }
        $proxy = [];
        $proxies = [];
        foreach ($vmess as $item) {
            array_push($proxy, Clash::buildVmess($user->uuid, $item));
            array_push($proxies, $item->name);
        }


        foreach ($trojan as $item) {
            array_push($proxy, Clash::buildTrojan($user->uuid, $item));
            array_push($proxies, $item->name);
        }

        $config['Proxy'] = array_merge($config['Proxy'] ? $config['Proxy'] : [], $proxy);
        foreach ($config['Proxy Group'] as $k => $v) {
            $config['Proxy Group'][$k]['proxies'] = array_merge($config['Proxy Group'][$k]['proxies'], $proxies);
        }
        $yaml = Yaml::dump($config);
        $yaml = str_replace('$app_name', config('v2board.app_name', 'V2Board'), $yaml);
        return $yaml;
    }
}
