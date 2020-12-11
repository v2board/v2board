<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\ServerService;
use App\Utils\Clash;
use App\Utils\QuantumultX;
use App\Utils\Shadowrocket;
use App\Utils\Surge;
use App\Utils\Surfboard;
use App\Utils\URLSchemes;
use Illuminate\Http\Request;
use App\Models\Server;
use App\Utils\Helper;
use Symfony\Component\Yaml\Yaml;
use App\Services\UserService;

class ClientController extends Controller
{
    public function subscribe(Request $request)
    {
        $flag = $request->input('flag')
            ?? (isset($_SERVER['HTTP_USER_AGENT'])
                ? $_SERVER['HTTP_USER_AGENT']
                : '');
        $flag = strtolower($flag);
        $user = $request->user;
        // account not expired and is not banned.
        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            $serverService = new ServerService();
            $servers = $serverService->getAvailableServers($user);
            if ($flag) {
                if (strpos($flag, 'quantumult%20x') !== false) {
                    die($this->quantumultX($user, $servers));
                }
                if (strpos($flag, 'quantumult') !== false) {
                    die($this->quantumult($user, $servers));
                }
                if (strpos($flag, 'clash') !== false) {
                    die($this->clash($user, $servers));
                }
                if (strpos($flag, 'surfboard') !== false) {
                    die($this->surfboard($user, $servers));
                }
                if (strpos($flag, 'surge') !== false) {
                    die($this->surge($user, $servers));
                }
                if (strpos($flag, 'shadowrocket') !== false) {
                    die($this->shadowrocket($user, $servers));
                }
                if (strpos($flag, 'shadowsocks') !== false) {
                    die($this->shaodowsocksSIP008($user, $servers));
                }
            }
            die($this->origin($user, $servers));
        }
    }
    // TODO: Ready to stop support
    private function quantumult($user, $servers = [])
    {
        $uri = '';
        header('subscription-userinfo: upload=' . $user->u . '; download=' . $user->d . ';total=' . $user->transfer_enable);
        foreach ($servers as $item) {
            if ($item['type'] === 'v2ray') {
                $str = '';
                $str .= $item['name'] . '= vmess, ' . $item['host'] . ', ' . $item['port'] . ', chacha20-ietf-poly1305, "' . $user['uuid'] . '", over-tls=' . ($item['tls'] ? "true" : "false") . ', certificate=0, group=' . config('v2board.app_name', 'V2Board');
                if ($item['network'] === 'ws') {
                    $str .= ', obfs=ws';
                    if ($item['networkSettings']) {
                        $wsSettings = json_decode($item['networkSettings'], true);
                        if (isset($wsSettings['path'])) $str .= ', obfs-path="' . $wsSettings['path'] . '"';
                        if (isset($wsSettings['headers']['Host'])) $str .= ', obfs-header="Host:' . $wsSettings['headers']['Host'] . '"';
                    }
                }
                $uri .= "vmess://" . base64_encode($str) . "\r\n";
            }
        }
        return base64_encode($uri);
    }

    private function shadowrocket($user, $servers = [])
    {
        $uri = '';
        //display remaining traffic and expire date
        $upload = round($user->u / (1024*1024*1024), 2);
        $download = round($user->d / (1024*1024*1024), 2);
        $totalTraffic = round($user->transfer_enable / (1024*1024*1024), 2);
        $expiredDate = date('Y-m-d', $user->expired_at);
        $uri .= "STATUS=🚀↑:{$upload}GB,↓:{$download}GB,TOT:{$totalTraffic}GB💡Expires:{$expiredDate}\r\n";
        foreach ($servers as $item) {
            if ($item['type'] === 'shadowsocks') {
                $uri .= Shadowrocket::buildShadowsocks($user['uuid'], $item);
            }
            if ($item['type'] === 'v2ray') {
                $uri .= Shadowrocket::buildVmess($user['uuid'], $item);
            }
            if ($item['type'] === 'trojan') {
                $uri .= Shadowrocket::buildTrojan($user['uuid'], $item);
            }
        }
        return base64_encode($uri);
    }

    private function quantumultX($user, $servers = [])
    {
        $uri = '';
        header("subscription-userinfo: upload={$user->u}; download={$user->d}; total={$user->transfer_enable}; expire={$user->expired_at}");
        foreach ($servers as $item) {
            if ($item['type'] === 'shadowsocks') {
                $uri .= QuantumultX::buildShadowsocks($user['uuid'], $item);
            }
            if ($item['type'] === 'v2ray') {
                $uri .= QuantumultX::buildVmess($user['uuid'], $item);
            }
            if ($item['type'] === 'trojan') {
                $uri .= QuantumultX::buildTrojan($user['uuid'], $item);
            }
        }
        return base64_encode($uri);
    }

    private function origin($user, $servers = [])
    {
        $uri = '';
        foreach ($servers as $item) {
            if ($item['type'] === 'shadowsocks') {
                $uri .= URLSchemes::buildShadowsocks($item, $user);
            }
            if ($item['type'] === 'v2ray') {
                $uri .= URLSchemes::buildVmess($item, $user);
            }
            if ($item['type'] === 'trojan') {
                $uri .= URLSchemes::buildTrojan($item, $user);
            }
        }
        return base64_encode($uri);
    }

    private function shaodowsocksSIP008($user, $servers = [])
    {
        $configs = [];
        $subs = [];
        $subs['servers'] = [];

        foreach ($servers as $item) {
            if ($item['type'] === 'shadowsocks') {
                array_push($configs, URLSchemes::buildShadowsocksSIP008($item, $user));
            }
        }

        $subs['version'] = 1;
        $subs['remark'] = config('v2board.app_name', 'V2Board');
        $subs['servers'] = array_merge($subs['servers'] ? $subs['servers'] : [], $configs);

        return json_encode($subs, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    }

    private function surge($user, $servers = [])
    {
        $proxies = '';
        $proxyGroup = '';

        foreach ($servers as $item) {
            if ($item['type'] === 'shadowsocks') {
                // [Proxy]
                $proxies .= Surge::buildShadowsocks($user['uuid'], $item);
                // [Proxy Group]
                $proxyGroup .= $item['name'] . ', ';
            }
            if ($item['type'] === 'v2ray') {
                // [Proxy]
                $proxies .= Surge::buildVmess($user['uuid'], $item);
                // [Proxy Group]
                $proxyGroup .= $item['name'] . ', ';
            }
            if ($item['type'] === 'trojan') {
                // [Proxy]
                $proxies .= Surge::buildTrojan($user['uuid'], $item);
                // [Proxy Group]
                $proxyGroup .= $item['name'] . ', ';
            }
        }

        $defaultConfig = base_path() . '/resources/rules/default.surge.conf';
        $customConfig = base_path() . '/resources/rules/custom.surge.conf';
        if (\File::exists($customConfig)) {
            $config = file_get_contents("$customConfig");
        } else {
            $config = file_get_contents("$defaultConfig");
        }

        // Subscription link
        $subsURL = config('v2board.subscribe_url', config('v2board.app_url', env('APP_URL'))) . '/api/v1/client/subscribe?token=' . $user['token'];

        $config = str_replace('$subs_link', $subsURL, $config);
        $config = str_replace('$proxies', $proxies, $config);
        $config = str_replace('$proxy_group', rtrim($proxyGroup, ', '), $config);
        return $config;
    }

    private function surfboard($user, $servers = [])
    {
        $proxies = '';
        $proxyGroup = '';

        foreach ($servers as $item) {
            if ($item['type'] === 'shadowsocks') {
                // [Proxy]
                $proxies .= Surfboard::buildShadowsocks($user['uuid'], $item);
                // [Proxy Group]
                $proxyGroup .= $item['name'] . ', ';
            }
            if ($item['type'] === 'v2ray') {
                // [Proxy]
                $proxies .= Surfboard::buildVmess($user['uuid'], $item);
                // [Proxy Group]
                $proxyGroup .= $item['name'] . ', ';
            }
        }

        $defaultConfig = base_path() . '/resources/rules/default.surfboard.conf';
        $customConfig = base_path() . '/resources/rules/custom.surfboard.conf';
        if (\File::exists($customConfig)) {
            $config = file_get_contents("$customConfig");
        } else {
            $config = file_get_contents("$defaultConfig");
        }

        // Subscription link
        $subsURL = config('v2board.subscribe_url', config('v2board.app_url', env('APP_URL'))) . '/api/v1/client/subscribe?token=' . $user['token'];

        $config = str_replace('$subs_link', $subsURL, $config);
        $config = str_replace('$proxies', $proxies, $config);
        $config = str_replace('$proxy_group', rtrim($proxyGroup, ', '), $config);
        return $config;
    }

    private function clash($user, $servers = [])
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
            if (!is_array($config['proxy-groups'][$k]['proxies'])) continue;
            $config['proxy-groups'][$k]['proxies'] = array_merge($config['proxy-groups'][$k]['proxies'], $proxies);
        }
        $yaml = Yaml::dump($config);
        $yaml = str_replace('$app_name', config('v2board.app_name', 'V2Board'), $yaml);
        return $yaml;
    }
}
