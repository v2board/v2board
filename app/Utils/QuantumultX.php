<?php

namespace App\Utils;


class QuantumultX
{
    public static function buildVmess($uuid, $server)
    {
        $config = [
            "vmess={$server->host}:{$server->port}",
            "method=chacha20-poly1305",
            "password={$uuid}",
            "tag={$server->name}"
        ];
        if ($server->network === 'tcp') {
            if ($server->tls) {
                $tlsSettings = json_decode($server->tlsSettings);
                array_push($config, 'obfs=over-tls');
                if (isset($tlsSettings->allowInsecure)) {
                    // Tips: allowInsecure=false = tls-verification=true
                    array_push($config, $tlsSettings->allowInsecure ? 'tls-verification=false' : 'tls-verification=true');
                }
                if (isset($tlsSettings->serverName)) {
                    array_push($config, "obfs-host={$tlsSettings->serverName}");
                }
            }
        }

        if ($server->network === 'ws') {
            if ($server->tls) {
                $tlsSettings = json_decode($server->tlsSettings);
                array_push($config, 'obfs=wss');
                if (isset($tlsSettings->allowInsecure)) {
                    array_push($config, $tlsSettings->allowInsecure ? 'tls-verification=false' : 'tls-verification=true');
                }
            } else {
                array_push($config, 'obfs=ws');
            }
            if ($server->networkSettings) {
                $wsSettings = json_decode($server->networkSettings);
                if (isset($wsSettings->path)) array_push($config, "obfs-uri={$wsSettings->path}");
                if (isset($wsSettings->headers->Host)) array_push($config, "obfs-host={$wsSettings->headers->Host}");
            }
        }

        $uri = implode(',', $config);
        $uri .= "\r\n";
        return $uri;
    }

    public static function buildTrojan($password, $server)
    {
        $config = [
            "trojan={$server->host}:{$server->port}",
            "password={$password}",
            "over-tls=true",
            $server->server_name ? "tls-host={$server->server_name}" : "",
            // Tips: allowInsecure=false = tls-verification=true
            $server->allow_insecure ? 'tls-verification=false' : 'tls-verification=true',
            "fast-open=false",
            "udp-relay=false",
            "tag={$server->name}"
        ];
        $config = array_filter($config);
        $uri = implode(',', $config);
        $uri .= "\r\n";
        return $uri;
    }
}
