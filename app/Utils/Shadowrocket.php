<?php

namespace App\Utils;


class Shadowrocket
{
    public static function buildShadowsocks($password, $server)
    {
        $name = rawurlencode($server->name);
        $str = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode("{$server->cipher}:{$password}")
        );
        return "ss://{$str}@{$server->host}:{$server->port}#{$name}\r\n";
    }

    public static function buildVmess($uuid, $server)
    {
        $userinfo = base64_encode('auto:' . $uuid . '@' . $server->host . ':' . $server->port);
        $config = [
            'remark' => $server->name
        ];
        if ($server->tls) {
            $tlsSettings = json_decode($server->tlsSettings);
            $config['tls'] = 1;
            if (isset($tlsSettings->serverName)) $config['peer'] = $tlsSettings->serverName;
            if (isset($tlsSettings->allowInsecure)) $config['allowInsecure'] = 1;
        }
        if ($server->network === 'ws') {
            $wsSettings = json_decode($server->networkSettings);
            $config['obfs'] = "websocket";
            if (isset($wsSettings->path)) $config['path'] = $wsSettings->path;
            if (isset($wsSettings->headers->Host)) $config['obfsParam'] = $wsSettings->headers->Host;
        }
        $query = http_build_query($config, null, '&', PHP_QUERY_RFC3986);
        $uri = "vmess://{$userinfo}?{$query}&tfo=1";
        $uri .= "\r\n";
        return $uri;
    }

    public static function buildTrojan($password, $server)
    {
        $name = rawurlencode($server->name);
        $query = http_build_query([
            'allowInsecure' => $server->allow_insecure,
            'peer' => $server->server_name
        ]);
        $uri = "trojan://{$password}@{$server->host}:{$server->port}?{$query}&tfo=1#{$name}";
        $uri .= "\r\n";
        return $uri;
    }
}
