<?php

namespace App\Protocols;

use App\Utils\Helper;

class V2rayNG
{
    public $flag = 'v2rayng';
    private $servers;
    private $user;

    public function __construct($user, $servers)
    {
        $this->user = $user;
        $this->servers = $servers;
    }

    public function handle()
    {
        $servers = $this->servers;
        $user = $this->user;
        $uri = '';

        foreach ($servers as $item) {
            if ($item['type'] === 'vmess') {
                $uri .= self::buildVmess($user['uuid'], $item);
            }
            if ($item['type'] === 'vless') {
                $uri .= self::buildVless($user['uuid'], $item);
            }
            if ($item['type'] === 'shadowsocks') {
                $uri .= self::buildShadowsocks($user['uuid'], $item);
            }
            if ($item['type'] === 'trojan') {
                $uri .= self::buildTrojan($user['uuid'], $item);
            }
        }
        return base64_encode($uri);
    }

    public static function buildShadowsocks($password, $server)
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
        $name = rawurlencode($server['name']);
        $str = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode("{$server['cipher']}:{$password}")
        );
        return "ss://{$str}@{$server['host']}:{$server['port']}#{$name}\r\n";
    }

    public static function buildVmess($uuid, $server)
    {
        $config = [
            "v" => "2",
            "ps" => $server['name'],
            "add" => $server['host'],
            "port" => (string)$server['port'],
            "id" => $uuid,
            "aid" => '0',
            "net" => $server['network'],
            "type" => "none",
            "host" => "",
            "path" => "",
            "tls" => $server['tls'] ? "tls" : "",
        ];
        if ($server['tls']) {
            if ($server['tlsSettings']) {
                $tlsSettings = $server['tlsSettings'];
                if (isset($tlsSettings['serverName']) && !empty($tlsSettings['serverName']))
                    $config['sni'] = $tlsSettings['serverName'];
            }
        }
        if ((string)$server['network'] === 'tcp') {
            $tcpSettings = $server['networkSettings'];
            if (isset($tcpSettings['header']['type'])) $config['type'] = $tcpSettings['header']['type'];
            if (isset($tcpSettings['header']['request']['path'][0])) $config['path'] = $tcpSettings['header']['request']['path'][0];
        }
        if ((string)$server['network'] === 'ws') {
            $wsSettings = $server['networkSettings'];
            if (isset($wsSettings['path'])) $config['path'] = $wsSettings['path'];
            if (isset($wsSettings['headers']['Host'])) $config['host'] = $wsSettings['headers']['Host'];
        }
        if ((string)$server['network'] === 'grpc') {
            $grpcSettings = $server['networkSettings'];
            if (isset($grpcSettings['serviceName'])) $config['path'] = $grpcSettings['serviceName'];
        }
        return "vmess://" . base64_encode(json_encode($config)) . "\r\n";
    }

    public static function buildVless($uuid, $server)
    {
        $config = [
            "name" => Helper::encodeURIComponent($server['name']),
            "add" => $server['host'],
            "port" => (string)$server['port'],
            "type" => $server['network'],
            "encryption" => "none",
            "host" => "",
            "path" => "",
            "headerType" => "none",
            "quicSecurity" => "none",
            "serviceName" => "",
            "mode" => "gun",
            "security" => $server['tls'] !=0 ? ($server['tls'] == 2 ? "reality":"tls") : "",
            "flow" => $server['flow'],
            "sni" => "",
            "pbk" => "",
            "sid" =>"",
        ];

        $output = "vless://" . $uuid . "@" . $config['add'] . ":" . $config['port'];
        $output .= "?" . "type={$config['type']}" . "&encryption={$config['encryption']}" . "&security={$config['security']}";

        if ($server['tls']) {
            if ($config['flow'] !="") $output .= "&flow={$config['flow']}";
            if ($server['tls_settings']) {
                $tlsSettings = $server['tls_settings'];
                if (isset($tlsSettings['server_name']) && !empty($tlsSettings['server_name'])) $config['sni'] = $tlsSettings['server_name'];
                $output .= "&sni={$config['sni']}";
                if ($server['tls'] == 2) {
                    $config['pbk'] = $tlsSettings['public_key'];
                    $config['sid'] = $tlsSettings['short_id'];
                    $output .= "&pbk={$config['pbk']}" . "&sid={$config['sid']}";
                }
            }
        }
        if ((string)$server['network'] === 'tcp') {
            $tcpSettings = $server['network_settings'];
            if (isset($tcpSettings['header']['type'])) $config['headerType'] = $tcpSettings['header']['type'];
            $output .= "&headerType={$config['headerType']}";
        }
        if ((string)$server['network'] === 'kcp') {
            $kcpSettings = $server['network_settings'];
            if (isset($kcpSettings['header']['type'])) $config['headerType'] = $kcpSettings['header']['type'];
            if (isset($kcpSettings['seed'])) $config['path'] = Helper::encodeURIComponent($kcpSettings['seed']);
            $output .= "&headerType={$config['headerType']}" . "&seed={$config['path']}";
        }
        if ((string)$server['network'] === 'ws') {
            $wsSettings = $server['network_settings'];
            if (isset($wsSettings['path'])) $config['path'] = Helper::encodeURIComponent($wsSettings['path']);
            if (isset($wsSettings['headers']['Host'])) $config['host'] = Helper::encodeURIComponent($wsSettings['headers']['Host']);
            $output .= "&path={$config['path']}" . "&host={$config['host']}";
        }
        if ((string)$server['network'] === 'h2') {
            $h2Settings = $server['network_settings'];
            if (isset($h2Settings['path'])) $config['path'] = Helper::encodeURIComponent($h2Settings['path']);
            if (isset($h2Settings['host'])) $config['host'] = Helper::encodeURIComponent($h2Settings['host']);
            $output .= "&path={$config['path']}" . "&host={$config['host']}";
        }
        if ((string)$server['network'] === 'quic') {
            $quicSettings = $server['network_settings'];
            if (isset($quicSettings['security'])) $config['quicSecurity'] = $quicSettings['security'];
            if (isset($quicSettings['header']['type'])) $config['headerType'] = $quicSettings['header']['type'];

            $output .= "&quicSecurity={$config['quicSecurity']}" . "&headerType={$config['headerType']}";

            if ((string)$quicSettings['security'] !== 'none' && isset($quicSettings['key'])) $config['path'] = Helper::encodeURIComponent($quicSettings['key']);

            $output .= "&key={$config['path']}";
        }
        if ((string)$server['network'] === 'grpc') {
            $grpcSettings = $server['network_settings'];
            if (isset($grpcSettings['serviceName'])) $config['serviceName'] = Helper::encodeURIComponent($grpcSettings['serviceName']);
            if (isset($grpcSettings['multiMode'])) $config['mode'] = $grpcSettings['multiMode'] ? "multi" : "gun";
            $output .= "&serviceName={$config['serviceName']}" . "&mode={$config['mode']}";
        }
        $output .= "&fp=chrome" . "#" . $config['name'];

        return $output . "\r\n";
    }

    public static function buildTrojan($password, $server)
    {
        $name = rawurlencode($server['name']);
        $query = http_build_query([
            'allowInsecure' => $server['allow_insecure'],
            'peer' => $server['server_name'],
            'sni' => $server['server_name']
        ]);
        $uri = "trojan://{$password}@{$server['host']}:{$server['port']}?{$query}#{$name}";
        $uri .= "\r\n";
        return $uri;
    }
}
