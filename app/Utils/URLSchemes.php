<?php
namespace App\Utils;

use App\Models\Server;
use App\Models\User;

class URLSchemes
{
    public static function buildShadowsocks($server, User $user)
    {
        $name = rawurlencode($server['name']);
        $str = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode("{$server['cipher']}:{$user['uuid']}")
        );
        return "ss://{$str}@{$server['host']}:{$server['port']}#{$name}\r\n";
    }

    public static function buildShadowsocksSIP008($server, User $user)
    {
        $config = [
            "id" => $server['id'],
            "remark" => $server['name'],
            "server" => $server['host'],
            "server_port" => $server['port'],
            "password" => $user['uuid'],
            "method" => $server['cipher']
        ];
        return $config;
    }

    public static function buildVmess($server, User $user)
    {
        $config = [
            "v" => "2",
            "ps" => $server['name'],
            "add" => $server['host'],
            "port" => $server['port'],
            "id" => $user['uuid'],
            "aid" => $server['alter_id'],
            "net" => $server['network'],
            "type" => "none",
            "host" => "",
            "path" => "",
            "tls" => $server['tls'] ? "tls" : ""
        ];
        if ((string)$server['network'] === 'ws') {
            $wsSettings = json_decode($server['networkSettings'], true);
            if (isset($wsSettings['path'])) $config['path'] = $wsSettings['path'];
            if (isset($wsSettings['headers']['Host'])) $config['host'] = $wsSettings['headers']['Host'];
        }
        return "vmess://" . base64_encode(json_encode($config)) . "\r\n";
    }

    public static function buildTrojan($server, User $user)
    {
        $name = rawurlencode($server['name']);
        $query = http_build_query([
            'allowInsecure' => $server['allow_insecure'],
            'peer' => $server['server_name'],
            'sni' => $server['server_name']
        ]);
        $uri = "trojan://{$user['uuid']}@{$server['host']}:{$server['port']}?{$query}#{$name}";
        $uri .= "\r\n";
        return $uri;
    }
}
