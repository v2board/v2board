<?php

namespace App\Http\Controllers\Client\Protocols;

class AnXray
{
    public $flag = 'anxray';
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
            if ($item['type'] === 'v2ray') {
                $uri .= self::buildVmess($user['uuid'], $item);
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

    public static function buildShadowsocks($uuid, $server)
    {
        $name = rawurlencode($server['name']);
        $str = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode("{$server['cipher']}:{$uuid}")
        );
        return "ss://{$str}@{$server['host']}:{$server['port']}#{$name}\r\n";
    }

    public static function buildShadowsocksSIP008($uuid, $server)
    {
        $config = [
            "id" => $server['id'],
            "remarks" => $server['name'],
            "server" => $server['host'],
            "server_port" => $server['port'],
            "password" => $uuid,
            "method" => $server['cipher']
        ];
        return $config;
    }

    public static function buildVmess($uuid, $server)
    {
        $config = [
            "encryption" => "none",
            "type" => urlencode($server['network']),
            "security" => $server['tls'] ? "tls" : "",
            "sni" => $server['tls'] ? urlencode(json_decode($server['tlsSettings'], true)['serverName']) : ""
        ];
        if ((string)$server['network'] === 'ws') {
            $wsSettings = json_decode($server['networkSettings'], true);
            if (isset($wsSettings['path'])) $config['path'] = urlencode($wsSettings['path']);
            if (isset($wsSettings['headers']['Host'])) $config['host'] = urlencode($wsSettings['headers']['Host']);
        }
        if ((string)$server['network'] === 'grpc') {
            $grpcSettings = json_decode($server['networkSettings'], true);
            if (isset($grpcSettings['serviceName'])) $config['serviceName'] = urlencode($grpcSettings['serviceName']);
        }
        return "vmess://" . $uuid . "@" . $server['host'] . ":" . $server['port'] . "?" . http_build_query($config) . "#" . urlencode($server['name']) . "\r\n";
    }

    public static function buildTrojan($uuid, $server)
    {
        $name = rawurlencode($server['name']);
        $query = http_build_query([
            'allowInsecure' => $server['allow_insecure'],
            'peer' => $server['server_name'],
            'sni' => $server['server_name']
        ]);
        $uri = "trojan://{$uuid}@{$server['host']}:{$server['port']}?{$query}#{$name}";
        $uri .= "\r\n";
        return $uri;
    }
}
