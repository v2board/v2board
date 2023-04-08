<?php

namespace App\Http\Controllers\Client\Protocols;

use App\Utils\Helper;

class Shadowsocks
{
    public $flag = 'shadowsocks';
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

        $configs = [];
        $subs = [];
        $subs['servers'] = [];
        $subs['bytes_used'] = '';
        $subs['bytes_remaining'] = '';

        $bytesUsed = $user['u'] + $user['d'];
        $bytesRemaining = $user['transfer_enable'] - $bytesUsed;

        foreach ($servers as $item) {
            if ($item['type'] === 'shadowsocks'
                && in_array($item['cipher'], ['aes-128-gcm', 'aes-256-gcm', 'aes-192-gcm', 'chacha20-ietf-poly1305', '2022-blake3-aes-128-gcm', '2022-blake3-aes-256-gcm'])
            ) {
                array_push($configs, self::SIP008($item, $user));
            }
        }

        $subs['version'] = 1;
        $subs['bytes_used'] = $bytesUsed;
        $subs['bytes_remaining'] = $bytesRemaining;
        $subs['servers'] = array_merge($subs['servers'] ? $subs['servers'] : [], $configs);

        return json_encode($subs, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    }

    public static function SIP008($server, $user)
    {
        $password = $user['uuid'];
        if ($server['cipher'] === '2022-blake3-aes-128-gcm') {
            $serverKey = Helper::getShadowsocksServerKey($server['created_at'], 16);
            $userKey = Helper::uuidToBase64($password, 16);
            $password = "{$serverKey}:{$userKey}";
        }
        if ($server['cipher'] === '2022-blake3-aes-256-gcm') {
            $serverKey = Helper::getShadowsocksServerKey($server['created_at'], 32);
            $userKey = Helper::uuidToBase64($password, 32);
            $password = "{$serverKey}:{$userKey}";
        }
        $config = [
            "id" => $server['id'],
            "remarks" => $server['name'],
            "server" => $server['host'],
            "server_port" => $server['port'],
            "password" => $password,
            "method" => $server['cipher']
        ];
        return $config;
    }
}
