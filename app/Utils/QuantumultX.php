<?php

namespace App\Utils;


class QuantumultX
{
    public static function buildVmess($uuid, $server)
    {
        $uri = "vmess=" . $server->host . ":" . $server->port . ", method=none, password=" . $uuid . ", fast-open=false, udp-relay=false, tag=" . $server->name;
        if ($server->tls) {
            $tlsSettings = json_decode($server->tlsSettings);
            if ($server->network === 'tcp') $uri .= ', obfs=over-tls';
            if (isset($tlsSettings->allowInsecure)) {
                // Default: tls-verification=true
                $uri .= ', tls-verification=' . ($tlsSettings->allowInsecure ? "false" : "true");
            }
            if (isset($tlsSettings->serverName)) {
                $uri .= ', obfs-host=' . $tlsSettings->serverName;
            }
        }
        if ($server->network === 'ws') {
            $uri .= ', obfs=' . ($server->tls ? 'wss' : 'ws');
            if ($server->networkSettings) {
                $wsSettings = json_decode($server->networkSettings);
                if (isset($wsSettings->path)) $uri .= ', obfs-uri=' . $wsSettings->path;
                if (isset($wsSettings->headers->Host)) $uri .= ', obfs-host=' . $wsSettings->headers->Host;
            }
        }
        $uri .= "\r\n";
        return $uri;
    }

    public static function buildTrojan($password, $server)
    {
        $uri = "trojan={$server->host}:{$server->port}, password={$password}, over-tls=true, tls-verification=true, fast-open=false, udp-relay=false, tag={$server->name}";
        $uri .= "\r\n";
        return $uri;
    }
}
