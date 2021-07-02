<?php
namespace App\Utils;

use App\Models\User;

class Shadowsocks
{
    public static function SIP008($server, User $user)
    {
        $config = [
            "id" => $server['id'],
            "remarks" => $server['name'],
            "server" => $server['host'],
            "server_port" => $server['port'],
            "password" => $user['uuid'],
            "method" => $server['cipher']
        ];
        return $config;
    }
}
