<?php

namespace App\Utils;

class CacheKey
{
    CONST KEYS = [
        'EMAIL_VERIFY_CODE' => '邮箱验证码',
        'LAST_SEND_EMAIL_VERIFY_TIMESTAMP' => '最后一次发送邮箱验证码时间',
        'SERVER_V2RAY_ONLINE_USER' => '节点在线用户',
        'SERVER_V2RAY_LAST_CHECK_AT' => '节点最后检查时间',
        'SERVER_V2RAY_LAST_PUSH_AT' => '节点最后推送时间',
        'SERVER_TROJAN_ONLINE_USER' => 'trojan节点在线用户',
        'SERVER_TROJAN_LAST_CHECK_AT' => 'trojan节点最后检查时间',
        'SERVER_TROJAN_LAST_PUSH_AT' => 'trojan节点最后推送时间',
        'SERVER_SHADOWSOCKS_ONLINE_USER' => 'ss节点在线用户',
        'SERVER_SHADOWSOCKS_LAST_CHECK_AT' => 'ss节点最后检查时间',
        'SERVER_SHADOWSOCKS_LAST_PUSH_AT' => 'ss节点最后推送时间',
        'TEMP_TOKEN' => '临时令牌',
        'LAST_SEND_EMAIL_REMIND_TRAFFIC'
    ];

    public static function get(string $key, $uniqueValue)
    {
        if (!in_array($key, array_keys(self::KEYS))) {
            abort(500, 'key is not in cache key list');
        }
        return $key . '_' . $uniqueValue;
    }
}
