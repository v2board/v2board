<?php

namespace App\Utils;


class Surge
{
    public static function buildShadowsocks($password, $server)
    {
        $config = [
            "{$server['name']}=ss",
            "{$server['host']}",
            "{$server['port']}",
            "encrypt-method={$server['cipher']}",
            "password={$password}",
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

        if ($server['tls']) {
            array_push($config, 'tls=true');
            if ($server['tlsSettings']) {
                $tlsSettings = json_decode($server['tlsSettings'], true);
                if (isset($tlsSettings['allowInsecure']) && !empty($tlsSettings['allowInsecure']))
                    array_push($config, 'skip-cert-verify=' . ($tlsSettings['allowInsecure'] ? 'true' : 'false'));
                if (isset($tlsSettings['serverName']) && !empty($tlsSettings['serverName']))
                    array_push($config, "sni={$tlsSettings['serverName']}");
            }
        }
        if ($server['network'] === 'ws') {
            array_push($config, 'ws=true');
            if ($server['networkSettings']) {
                $wsSettings = json_decode($server['networkSettings'], true);
                if (isset($wsSettings['path']) && !empty($wsSettings['path']))
                    array_push($config, "ws-path={$wsSettings['path']}");
                if (isset($wsSettings['headers']['Host']) && !empty($wsSettings['headers']['Host']))
                    array_push($config, "ws-headers=Host:{$wsSettings['headers']['Host']}");
            }
        }

        $uri = implode(',', $config);
        $uri .= "\r\n";
        return $uri;
    }

    public static function buildTrojan($password, $server)
    {
        $config = [
            "{$server['name']}=trojan",
            "{$server['host']}",
            "{$server['port']}",
            "password={$password}",
            $server['server_name'] ? "sni={$server['server_name']}" : "",
            'tfo=true',
            'udp-relay=true'
        ];
        if (!empty($server['allow_insecure'])) {
            array_push($config, $server['allow_insecure'] ? 'skip-cert-verify=true' : 'skip-cert-verify=false');
        }
        $config = array_filter($config);
        $uri = implode(',', $config);
        $uri .= "\r\n";
        return $uri;
    }
}
