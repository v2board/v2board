<?php

namespace App\Utils;


class Clash
{
    public static function buildVmess($uuid, $server)
    {
        $array = [];
        $array['name'] = $server->name;
        $array['type'] = 'vmess';
        $array['server'] = $server->host;
        $array['port'] = $server->port;
        $array['uuid'] = $uuid;
        $array['alterId'] = 2;
        $array['cipher'] = 'auto';
        if ($server->tls) {
            $tlsSettings = json_decode($server->tlsSettings);
            $array['tls'] = true;
            if (isset($tlsSettings->allowInsecure)) $array['skip-cert-verify'] = ($tlsSettings->allowInsecure ? true : false );
        }
        if ($server->network == 'ws') {
            $array['network'] = $server->network;
            if ($server->networkSettings) {
                $wsSettings = json_decode($server->networkSettings);
                if (isset($wsSettings->path)) $array['ws-path'] = $wsSettings->path;
                if (isset($wsSettings->headers->Host)) $array['ws-headers'] = [
                    'Host' => $wsSettings->headers->Host
                ];
            }
        }
        return $array;
    }

    public static function buildTrojan($password, $server)
    {
        $array = [];
        $array['name'] = $server->name;
        $array['type'] = 'trojan';
        $array['server'] = $server->host;
        $array['port'] = $server->port;
        $array['password'] = $password;
        $array['sni'] = $server->server_name;
        if ($server->allow_insecure) {
            $array['skip-cert-verify'] = true;
        } else {
            $array['skip-cert-verify'] = false;
        }
        return $array;
    }
}
