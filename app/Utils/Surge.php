<?php

namespace App\Utils;


class Surge
{
    public static function buildVmess($uuid, $server)
    {
        $proxies = $server->name . ' = vmess, ' . $server->host . ', ' . $server->port . ', username=' . $uuid . ', tfo=true';
        if ($server->tls) {
            $tlsSettings = json_decode($server->tlsSettings);
            $proxies .= ', tls=' . ($server->tls ? "true" : "false");
            if (isset($tlsSettings->allowInsecure)) {
                $proxies .= ', skip-cert-verify=' . ($tlsSettings->allowInsecure ? "true" : "false");
            }
        }
        if ($server->network == 'ws') {
            $proxies .= ', ws=true';
            if ($server->networkSettings) {
                $wsSettings = json_decode($server->networkSettings);
                if (isset($wsSettings->path)) $proxies .= ', ws-path=' . $wsSettings->path;
                if (isset($wsSettings->headers->Host)) $proxies .= ', ws-headers=host:' . $wsSettings->headers->Host;
            }
        }
        $proxies .= "\r\n";
        return $proxies;
    }

    public static function buildTrojan($password, $server)
    {
        $config = [
            "{$server->name}=trojan",
            "{$server->host}",
            "{$server->port}",
            "password={$password}",
            $server->allow_insecure ? 'skip-cert-verify=true' : 'skip-cert-verify=false',
            $server->server_name ? "sni={$server->server_name}" : "",
            "tfo=true"
        ];
        $config = array_filter($config);
        $uri = implode($config, ',');
        $uri .= "\r\n";
        return $uri;
    }
}
