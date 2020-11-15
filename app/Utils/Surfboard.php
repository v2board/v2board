<?php

namespace App\Utils;


class Surfboard
{
    public static function buildShadowsocks($password, $server)
    {
        $config = [
            "{$server['name']}=custom",
            "{$server['host']}",
            "{$server['port']}",
            "{$server['cipher']}",
            "{$password}",
            'https://raw.githubusercontent.com/Hackl0us/proxy-tool-backup/master/SSEncrypt.module',
            'tfo=true',
            'udp-relay=true'
        ];
        $config = array_filter($config);
        $uri = implode(',', $config);
        $uri .= "\r\n";
        return $uri;
    }

    public static function buildVmess($uuid, $server)
    {
        $config = [
            "{$server['name']}=vmess",
            "{$server['host']}",
            "{$server['port']}",
            "username={$uuid}",
            'tfo=true',
            'udp-relay=true'
        ];
        if ($server['network'] === 'tcp') {
            if ($server['tls']) {
                $tlsSettings = json_decode($server['tlsSettings'], true);
                array_push($config, $server['tls'] ? 'tls=true' : 'tls=false');
                if (!empty($tlsSettings['allowInsecure'])) {
                    array_push($config, $tlsSettings['allowInsecure'] ? 'skip-cert-verify=true' : 'skip-cert-verify=false');
                }
            }
        }

        if ($server['network'] === 'ws') {
            array_push($config, 'ws=true');
            if ($server['tls']) {
                $tlsSettings = json_decode($server['tlsSettings'], true);
                array_push($config, $server['tls'] ? 'tls=true' : 'tls=false');
                if (!empty($tlsSettings['allowInsecure'])) {
                    array_push($config, $tlsSettings['allowInsecure'] ? 'skip-cert-verify=true' : 'skip-cert-verify=false');
                }
            }
            if ($server['networkSettings']) {
                $wsSettings = json_decode($server['networkSettings'], true);
                if (isset($wsSettings['path'])) array_push($config, "ws-path={$wsSettings['path']}");
                if (isset($wsSettings['headers']['Host'])) array_push($config, "ws-headers=host:{$wsSettings['headers']['Host']}");
            }
        }

        $uri = implode(',', $config);
        $uri .= "\r\n";
        return $uri;
    }
}
