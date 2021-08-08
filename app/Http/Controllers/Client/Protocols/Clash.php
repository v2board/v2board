<?php

namespace App\Http\Controllers\Client\Protocols;

use Symfony\Component\Yaml\Yaml;

class Clash
{
    public $flag = 'clash';
    private $servers;
    private $user;

    public function __construct($user, $servers)
    {
        $this->user = $user;
        $this->servers = $servers;
    }

    public function handle()
    {
        $user = $this->user;
        if (empty($_REQUEST['getsubscribe'])) {
            $app_name = config('v2board.app_name', 'V2Board');
            header("subscription-userinfo: upload={$user['u']}; download={$user['d']}; total={$user['transfer_enable']}; expire={$user['expired_at']}");
            header("profile-update-interval: 1");
            header("content-disposition: filename={$app_name}");
            $defaultConfig = base_path() . '/resources/rules/default.clash.yaml';
            $customConfig = base_path() . '/resources/rules/custom.clash.yaml';
            if (\File::exists($customConfig)) {
                $config = Yaml::parseFile($customConfig);
            } else {
                $config = Yaml::parseFile($defaultConfig);
            }
            $args = array(
                'token' => $user['token'],
                'flag' => 'clash',
                'getsubscribe' => 'true'
            );
            $proxy = array(
                $app_name => array(
                    'type' => 'http',
                    'url' => config('v2board.subscribe_url') . '/api/v1/client/subscribe?' . http_build_query($args),
                    'interval' => 3600,
                    'path' => './Proxy/' . $app_name . '.yaml',
                    'health-check' => array(
                        'enable' => true,
                        'interval' => 900,
                        'url' => 'http://www.gstatic.com/generate_204'
                    )
                ) 
            );
            $config['proxy-providers'] = array_merge($config['proxy-providers'] ? $config['proxy-providers'] : [], $proxy);
            foreach ($config['proxy-groups'] as $k => $v) {
                if ( isset($config['proxy-groups'][$k]['use']) ) {
                    if ( !is_array($config['proxy-groups'][$k]['use']) ) continue;
                    $config['proxy-groups'][$k]['use'] = [$app_name];
                }
            }
            $yaml = Yaml::dump($config);
            $yaml = str_replace('$app_name', $app_name, $yaml);
        } else {
            $servers = $this->servers;
            $proxy = [];
            $proxies = [];

            foreach ($servers as $item) {
                if ($item['type'] === 'shadowsocks') {
                    array_push($proxy, self::buildShadowsocks($user['uuid'], $item));
                    array_push($proxies, $item['name']);
                }
                if ($item['type'] === 'v2ray') {
                    array_push($proxy, self::buildVmess($user['uuid'], $item));
                    array_push($proxies, $item['name']);
                }
                if ($item['type'] === 'trojan') {
                    array_push($proxy, self::buildTrojan($user['uuid'], $item));
                    array_push($proxies, $item['name']);
                }
            }

            $config['proxies'] = array_merge($proxy);
            $yaml = Yaml::dump($config);
        }
        return $yaml;
    }

    public static function buildShadowsocks($uuid, $server)
    {
        $array = [];
        $array['name'] = $server['name'];
        $array['type'] = 'ss';
        $array['server'] = $server['host'];
        $array['port'] = $server['port'];
        $array['cipher'] = $server['cipher'];
        $array['password'] = $uuid;
        $array['udp'] = true;
        return $array;
    }

    public static function buildVmess($uuid, $server)
    {
        $array = [];
        $array['name'] = $server['name'];
        $array['type'] = 'vmess';
        $array['server'] = $server['host'];
        $array['port'] = $server['port'];
        $array['uuid'] = $uuid;
        $array['alterId'] = $server['alter_id'];
        $array['cipher'] = 'auto';
        $array['udp'] = true;

        if ($server['tls']) {
            $array['tls'] = true;
            if ($server['tlsSettings']) {
                $tlsSettings = $server['tlsSettings'];
                if (isset($tlsSettings['allowInsecure']) && !empty($tlsSettings['allowInsecure']))
                    $array['skip-cert-verify'] = ($tlsSettings['allowInsecure'] ? true : false);
                if (isset($tlsSettings['serverName']) && !empty($tlsSettings['serverName']))
                    $array['servername'] = $tlsSettings['serverName'];
            }
        }
        if ($server['network'] === 'ws') {
            $array['network'] = 'ws';
            if ($server['networkSettings']) {
                $wsSettings = $server['networkSettings'];
                if (isset($wsSettings['path']) && !empty($wsSettings['path']))
                    $array['ws-path'] = $wsSettings['path'];
                if (isset($wsSettings['headers']['Host']) && !empty($wsSettings['headers']['Host']))
                    $array['ws-headers'] = ['Host' => $wsSettings['headers']['Host']];
            }
        }
        if ($server['network'] === 'grpc') {
            $array['network'] = 'grpc';
            if ($server['networkSettings']) {
                $grpcObject = $server['networkSettings'];
                $array['grpc-opts'] = [];
                $array['grpc-opts']['grpc-service-name'] = $grpcObject['serviceName'];
            }
        }

        return $array;
    }

    public static function buildTrojan($password, $server)
    {
        $array = [];
        $array['name'] = $server['name'];
        $array['type'] = 'trojan';
        $array['server'] = $server['host'];
        $array['port'] = $server['port'];
        $array['password'] = $password;
        $array['udp'] = true;
        if (!empty($server['server_name'])) $array['sni'] = $server['server_name'];
        if (!empty($server['allow_insecure'])) $array['skip-cert-verify'] = ($server['allow_insecure'] ? true : false);
        return $array;
    }
}
