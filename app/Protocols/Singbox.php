<?php
namespace App\Protocols;

use App\Models\ServerHysteria;
use App\Models\User;
use App\Utils\Helper;

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
            if ($item['type'] === 'shadowsocks') {
                $ssConfig = $this->buildShadowsocks($this->user['uuid'], $item);
                $outbounds[] = $ssConfig;
                $selector['outbounds'][] = $item['name'];
                $urltest['outbounds'][] = $item['name'];
            }
            if ($item['type'] === 'trojan') {
                $trojanConfig = $this->buildTrojan($this->user['uuid'], $item);
                $outbounds[] = $trojanConfig;
                $selector['outbounds'][] = $item['name'];
                $urltest['outbounds'][] = $item['name'];
            }
            if ($item['type'] === 'vmess') {
                $vmessConfig = $this->buildVmess($this->user['uuid'], $item);
                $outbounds[] = $vmessConfig;
                $selector['outbounds'][] = $item['name'];
                $urltest['outbounds'][] = $item['name'];
            }
            if ($item['type'] === 'vless') {
                $vlessConfig = $this->buildVless($this->user['uuid'], $item);
                $outbounds[] = $vlessConfig;
                $selector['outbounds'][] = $item['name'];
                $urltest['outbounds'][] = $item['name'];
            }
            if ($item['type'] === 'hysteria') {
                $hysteriaConfig = $this->buildHysteria($this->user['uuid'], $item, $this->user);
                $outbounds[] = $hysteriaConfig;
                $selector['outbounds'][] = $item['name'];
                $urltest['outbounds'][] = $item['name'];
            }
        }

        $outbounds[] = [ "tag" => "direct", "type" => "direct" ];
        $outbounds[] = [ "tag" => "block",  "type" => "block" ];
        $outbounds[] = [ "tag" => "dns-out", "type" => "dns" ];
        $outbounds[] = $urltest;

        return $outbounds;
    }

    protected function buildShadowsocks($password, $server)
    {
        if ($server['cipher'] === '2022-blake3-aes-128-gcm') {
            $serverKey = Helper::getServerKey($server['created_at'], 16);
            $userKey = Helper::uuidToBase64($password, 16);
            $password = "{$serverKey}:{$userKey}";
        }
        if ($server['cipher'] === '2022-blake3-aes-256-gcm') {
            $serverKey = Helper::getServerKey($server['created_at'], 32);
            $userKey = Helper::uuidToBase64($password, 32);
            $password = "{$serverKey}:{$userKey}";
        }
        $array = [];
        $array['tag'] = $server['name'];
        $array['type'] = 'shadowsocks';
        $array['server'] = $server['host'];
        $array['server_port'] = $server['port'];
        $array['method'] = $server['cipher'];
        $array['password'] = $password;

        return $array;
    }


    protected function buildVmess($uuid, $server) 
    {
        $array = [];
        $array['tag'] = $server['name'];
        $array['type'] = 'vmess';
        $array['server'] = $server['host'];
        $array['server_port'] = $server['port'];
        $array['uuid'] = $uuid;
        $array['security'] = 'auto';
        $array['alterId'] = 0;
        $array['transport']= [];

        if ($server['tls']) {
            $tlsConfig = [];
            $tlsConfig['enabled'] = true;
            if ($server['tlsSettings']) {
                $tlsSettings = $server['tlsSettings'] ?? [];
                $tlsConfig['insecure'] = $tlsSettings['allowInsecure'] ? true : false;
                $tlsConfig['server_name'] = $tlsSettings['serverName'] ?? null;
            }
            $array['tls'] = $tlsConfig;
        }
        if ($server['network'] === 'tcp') {
            $tcpSettings = $server['networkSettings'];
            if (isset($tcpSettings['header']['type']) && $tcpSettings['header']['type'] == 'http') $array['transport']['type'] = $tcpSettings['header']['type'];
            if (isset($tcpSettings['header']['request']['path'])) $array['transport']['path'] = $tcpSettings['header']['request']['path'];
        }
        if ($server['network'] === 'ws') {
            $array['transport']['type'] ='ws';
            if ($server['networkSettings']) {
                $wsSettings = $server['networkSettings'];
                if (isset($wsSettings['path']) && !empty($wsSettings['path'])) $array['transport']['path'] = $wsSettings['path'];
                if (isset($wsSettings['headers']['Host']) && !empty($wsSettings['headers']['Host'])) $array['transport']['headers'] = ['Host' => array($wsSettings['headers']['Host'])];
                $array['transport']['max_early_data'] = 2048;
                $array['transport']['early_data_header_name'] = 'Sec-WebSocket-Protocol';
            }
        }
        if ($server['network'] === 'grpc') {
            $array['transport']['type'] ='grpc';
            if ($server['networkSettings']) {
                $grpcSettings = $server['networkSettings'];
                if (isset($grpcSettings['serviceName'])) $array['transport']['service_name'] = $grpcSettings['serviceName'];
            }
        }

        return $array;
    }

    protected function buildVless($password, $server)
    {
        $array = [
            "type" => "vless",
            "tag" => $server['name'],
            "server" => $server['host'],
            "server_port" => $server['port'],
            "uuid" => $password,
            "packet_encoding" => "xudp"
        ];

        $tlsSettings = $server['tls_settings'] ?? [];

        if ($server['tls']) {
            $tlsConfig = [];
            $tlsConfig['enabled'] = true;
            $array['flow'] = !empty($server['flow']) ? $server['flow'] : "";
            $tlsSettings = $server['tls_settings'] ?? [];
            if ($server['tls_settings']) {
                $tlsConfig['insecure'] = isset($tlsSettings['allow_insecure']) && $tlsSettings['allow_insecure'] == 1 ? true : false;
                $tlsConfig['server_name'] = $tlsSettings['server_name'] ?? null;
                if ($server['tls'] == 2) {
                    $tlsConfig['reality'] = [
                        'enabled' => true,
                        'public_key' => $tlsSettings['public_key'],
                        'short_id' => $tlsSettings['short_id']
                    ];
                }
                $fingerprints = ['chrome', 'firefox', 'safari', 'ios', 'edge', 'qq'];
                $tlsConfig['utls'] = [
                    "enabled" => true,
                    "fingerprint" => $fingerprints[array_rand($fingerprints)]
                ];
            }
            $array['tls'] = $tlsConfig;
        }

        if ($server['network'] === 'tcp') {
            $tcpSettings = $server['network_settings'];
            if (isset($tcpSettings['header']['type']) && $tcpSettings['header']['type'] == 'http') $array['transport']['type'] = $tcpSettings['header']['type'];
            if (isset($tcpSettings['header']['request']['path'])) $array['transport']['path'] = $tcpSettings['header']['request']['path'];
        }
        if ($server['network'] === 'ws') {
            $array['transport']['type'] ='ws';
            if ($server['network_settings']) {
                $wsSettings = $server['network_settings'];
                if (isset($wsSettings['path']) && !empty($wsSettings['path'])) $array['transport']['path'] = $wsSettings['path'];
                if (isset($wsSettings['headers']['Host']) && !empty($wsSettings['headers']['Host'])) $array['transport']['headers'] = ['Host' => array($wsSettings['headers']['Host'])];
                $array['transport']['max_early_data'] = 2048;
                $array['transport']['early_data_header_name'] = 'Sec-WebSocket-Protocol';
            }
        }
        if ($server['network'] === 'grpc') {
            $array['transport']['type'] ='grpc';
            if ($server['network_settings']) {
                $grpcSettings = $server['network_settings'];
                if (isset($grpcSettings['serviceName'])) $array['transport']['service_name'] = $grpcSettings['serviceName'];
            }
        }
        if ($server['network'] === 'h2') {
            $array['transport']['type'] = 'http';
            if ($server['network_settings']) {
                $h2Settings = $server['network_settings'];
                if (isset($h2Settings['host'])) $array['transport']['host'] = array($h2Settings['host']);
                if (isset($h2Settings['path'])) $array['transport']['path'] = $h2Settings['path'];
            }
        }

        return $array;
    }

    protected function buildTrojan($password, $server) 
    {
        $array = [];
        $array['tag'] = $server['name'];
        $array['type'] = 'trojan';
        $array['server'] = $server['host'];
        $array['server_port'] = $server['port'];
        $array['password'] = $password;

        $array['tls'] = [
            'enabled' => true,
            'insecure' => $server['allow_insecure'] ? true : false,
            'server_name' => $server['server_name']
        ];

        if(isset($server['network']) && in_array($server['network'], ["grpc", "ws"])){
            $array['transport']['type'] = $server['network'];
            // grpc配置
            if($server['network'] === "grpc" && isset($server['networkSettings']['serviceName'])) {
                $array['transport']['service_name'] = $server['networkSettings']['serviceName'];
            }
            // ws配置
            if($server['network'] === "ws") {
                if(isset($server['networkSettings']['path'])) {
                    $array['transport']['path'] = $server['networkSettings']['path'];
                }
                if(isset($server['networkSettings']['headers']['Host'])){
                    $array['transport']['headers'] = ['Host' => array($server['networkSettings']['headers']['Host'])];
                }
                $array['transport']['max_early_data'] = 2048;
                $array['transport']['early_data_header_name'] = 'Sec-WebSocket-Protocol';
            }
        };

        return $array;
    }

    protected function buildHysteria($password, $server, $user)
    {
        $array = [
            'server' => $server['host'],
            'server_port' => $server['port'],
            'tls' => [
                'enabled' => true,
                'insecure' => $server['insecure'] ? true : false,
                'server_name' => $server['server_name']
            ]
        ];

        if ($server['version'] == 1) {
            $array['auth_str'] = $password;
            $array['tag'] = $server['name'];
            $array['type'] = 'hysteria';
            $array['up_mbps'] = $user->speed_limit ? min($server['down_mbps'], $user->speed_limit) : $server['down_mbps'];
            $array['down_mbps'] = $user->speed_limit ? min($server['up_mbps'], $user->speed_limit) : $server['up_mbps'];
            if (isset($server['obfs']) && isset($server['obfs_password'])) {
                $array['obfs'] = $server['obfs_password'];
            }

            $array['disable_mtu_discovery'] = true;

        } elseif ($server['version'] == 2) {
            $array['password'] = $password;
            $array['tag'] = $server['name'];
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
