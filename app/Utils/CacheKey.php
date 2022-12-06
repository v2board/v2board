<?php

namespace App\Utils;

class CacheKey
{
    CONST KEYS = [
        'EMAIL_VERIFY_CODE' => 'E-mail verification code',
        'LAST_SEND_EMAIL_VERIFY_TIMESTAMP' => 'Last time to send email verification code',
        'SERVER_V2RAY_ONLINE_USER' => 'Node Online Users',
        'SERVER_V2RAY_LAST_CHECK_AT' => 'Node last check time',
        'SERVER_V2RAY_LAST_PUSH_AT' => 'Node last push time',
        'SERVER_TROJAN_ONLINE_USER' => 'trojan node online users',
        'SERVER_TROJAN_LAST_CHECK_AT' => 'trojan node last check time',
        'SERVER_TROJAN_LAST_PUSH_AT' => 'trojan node last push time',
        'SERVER_SHADOWSOCKS_ONLINE_USER' => 'ss node online users',
        'SERVER_SHADOWSOCKS_LAST_CHECK_AT' => 'ss node last check time',
        'SERVER_SHADOWSOCKS_LAST_PUSH_AT' => 'ss node last push time',
        'TEMP_TOKEN' => 'Temporary token',
        'LAST_SEND_EMAIL_REMIND_TRAFFIC' => 'Lastly send traffic email alerts',
        'SCHEDULE_LAST_CHECK_AT' => 'Schedule task last check time',
        'REGISTER_IP_RATE_LIMIT' => 'Registration frequency limit',
        'LAST_SEND_LOGIN_WITH_MAIL_LINK_TIMESTAMP' => 'Last time the login link was sent'
    ];

    public static function get(string $key, $uniqueValue)
    {
        if (!in_array($key, array_keys(self::KEYS))) {
            abort(500, 'key is not in cache key list');
        }
        return $key . '_' . $uniqueValue;
    }
}
