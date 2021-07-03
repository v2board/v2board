<?php

namespace App\Http\Controllers\Client\Protocols;


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
            if ($item['type'] === 'v2ray') {
                $uri .= self::buildVmess($user['uuid'], $item);
            }
        }
        return base64_encode($uri);
    }

    public static function buildVmess($uuid, $server)
    {
        $config = [
            "v" => "2",
            "ps" => $server['name'],
            "add" => $server['host'],
            "port" => (string)$server['port'],
            "id" => $uuid,
            "aid" => (string)$server['alter_id'],
            "net" => $server['network'],
            "type" => "none",
            "host" => "",
            "path" => "",
            "tls" => $server['tls'] ? "tls" : "",
            "sni" => $server['tls'] ? isset(json_decode($server['tlsSettings'], true)['serverName']) : ""
        ];
        if ((string)$server['network'] === 'ws') {
            $wsSettings = json_decode($server['networkSettings'], true);
            if (isset($wsSettings['path'])) $config['path'] = $wsSettings['path'];
            if (isset($wsSettings['headers']['Host'])) $config['host'] = $wsSettings['headers']['Host'];
        }
        if ((string)$server['network'] === 'grpc') {
            $grpcSettings = json_decode($server['networkSettings'], true);
            if (isset($grpcSettings['path'])) $config['path'] = $grpcSettings['serviceName'];
        }
        return "vmess://" . base64_encode(json_encode($config)) . "\r\n";
    }

}
