<?php
namespace App\Protocols;

use App\Models\ServerHysteria;
use App\Models\User;

class SingBox
{
    public $flag = 'sing-box';
    private $servers;
    private $user;

    public function __construct($user, $servers, array $options = null)
    {
        $this->user = $user;
        $this->servers = $servers;
    }

    public function handle()
    {
        $appName = config('app_name', 'V2Board');
        $config = $this->loadConfig();
        $outbounds = $this->buildOutbounds();
        $config['outbounds'] = $outbounds;

        return json_encode($config);
        //return response($config, 200);
    }

    protected function loadConfig()
    {
        $defaultConfig = base_path('resources/rules/default.sing-box.json');
        $customConfig = base_path('resources/rules/custom.sing-box.json');
        $jsonData = file_exists($customConfig) ? file_get_contents($customConfig) : file_get_contents($defaultConfig);

        return json_decode($jsonData, true);
    }

    protected function buildOutbounds()
    {
        $outbounds = [];

        $selector = [
            "tag" => "节点选择",
            "type" => "selector",
            "default" => "自动选择",
            "outbounds" => ["自动选择"]
        ];

        $urltest = [
            "tag" => "自动选择",
            "type" => "urltest",
            "outbounds" => []
        ];

        $outbounds[] = &$selector;

        foreach ($this->servers as $item) {
            if ($item['type'] === 'vless') {
                $vlessConfig = $this->buildVless($this->user['uuid'], $item);
                $outbounds[] = $vlessConfig;
                $selector['outbounds'][] = $item['name'];
                $urltest['outbounds'][] = $item['name'];
            } elseif ($item['type'] === 'hysteria') {
                $hysteriaConfig = $this->buildHysteria($this->user['uuid'], $item, $this->user);
                $outbounds[] = $hysteriaConfig;
                $tag = $item['version'] == 2 ? "Hy2" : "Hy";
                $selector['outbounds'][] = "[$tag]{$item['name']}";
                $urltest['outbounds'][] = "[$tag]{$item['name']}";
            }
        }

        $outbounds[] = [ "tag" => "direct", "type" => "direct" ];
        $outbounds[] = [ "tag" => "block",  "type" => "block" ];
        $outbounds[] = [ "tag" => "dns-out", "type" => "dns" ];
        $outbounds[] = $urltest;

        return $outbounds;
    }

    /**
     * Vless订阅
     */

    protected function buildVless($password, $server)
    {
        $tlsSettings = $server['tls_settings'] ?? [];
        $tlsConfig = [];

        if ($server['tls']) {
            $tlsConfig['enabled'] = true;

            switch ($server['tls']) {
                case 1:
                    $tlsConfig['insecure'] = (bool) ($tlsSettings['allowInsecure'] ?? false);
                    $tlsConfig['server_name'] = $tlsSettings['serverName'] ?? null;
                    break;

                case 2:
                    $tlsConfig['insecure'] = (bool) ($tlsSettings['allowInsecure'] ?? false);
                    $tlsConfig['server_name'] = $tlsSettings['server_name'] ?? null;

                    if (
                        isset($tlsSettings['public_key'], $tlsSettings['short_id']) &&
                        !empty($tlsSettings['server_name'])
                    ) {
                        $tlsConfig['reality'] = [
                            'enabled' => true,
                            'public_key' => $tlsSettings['public_key'],
                            'short_id' => $tlsSettings['short_id']
                        ];

                        $fingerprints = ['chrome', 'firefox', 'safari', 'ios', 'edge', 'qq'];
                        $tlsConfig['utls'] = [
                            "enabled" => true,
                            "fingerprint" => $fingerprints[array_rand($fingerprints)]
                        ];
                    }
                    break;
            }
        }

        return [
            "type" => "vless",
            "tag" => $server['name'],
            "server" => $server['host'],
            "server_port" => $server['port'],
            "uuid" => $password,
            "flow" => $server['flow'],
            "packet_encoding" => "xudp",
            "tls" => $tlsConfig
        ];
    }

    protected function buildHysteria($password, $server, $user)
    {
        $array = [
            'server' => $server['host'],
            'server_port' => $server['port'],
            //'up_mbps' => $user->speed_limit ? min($server['up_mbps'], $user->speed_limit) : $server['up_mbps'],
            //'down_mbps' => $user->speed_limit ? min($server['down_mbps'], $user->speed_limit) : $server['down_mbps'],
            'tls' => [
                'enabled' => true,
                'insecure' => $server['insecure'] ? true : false,
                'server_name' => $server['server_name']
            ]
        ];

        if ($server['version'] == 1) {
            $array['auth_str'] = $password;
            $array['tag'] = "[Hy]" . $server['name'];
            $array['type'] = 'hysteria';
            $array['up_mbps'] = $user->speed_limit ? min($server['down_mbps'], $user->speed_limit) : $server['down_mbps'];
            $array['down_mbps'] = $user->speed_limit ? min($server['up_mbps'], $user->speed_limit) : $server['up_mbps'];
            if ($server['is_obfs']) {
                $array['obfs'] = $server['server_key'];
            }

            $array['disable_mtu_discovery'] = true;
            $array['tls']['alpn'] = [ServerHysteria::$alpnMap[$server['alpn']]];
        } elseif ($server['version'] == 2) {
            $array['password'] = $password;
            $array['tag'] = "[Hy2]" . $server['name'];
            $array['type'] = 'hysteria2';
            $array['password'] = $password;

            if (isset($server['obfs'])) {
                $array['obfs']['type'] = $server['obfs'];
                $array['obfs']['password'] = $server['obfs_password'];
            }
        }

        return $array;
    }
}
