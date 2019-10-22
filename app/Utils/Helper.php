<?php

namespace App\Utils;

class Helper
{
    public static function guid ($format = false) {
        if (function_exists('com_create_guid') === true) {
            return md5(trim(com_create_guid(), '{}'));
        }
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        if ($format) {
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
        return md5(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)).'-'.time());
    }

    public static function exchange ($from, $to) {
        $result = file_get_contents('https://api.exchangeratesapi.io/latest?symbols=' . $to . '&base=' . $from);
        $result = json_decode($result, true);
        return $result['rates'][$to];
    }
}
