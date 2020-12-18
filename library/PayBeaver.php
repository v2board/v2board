<?php

/**
 * Created by PayBeaver <merchant.paybeaver.com>
 * Version: 2020-12-06
 */

namespace Library;

use \Curl\Curl;

class PayBeaver
{
    private $appId;
    private $appSecret;
    private $url = 'https://api.paybeaver.com/api/v1/developer';

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function createOrder($params)
    {
        $params['sign'] = $this->sign($params);
        return $this->request('/orders', $params);
    }

    public function verify($params)
    {
        return hash_equals($params['sign'], $this->sign($params));
    }

    private function sign($params)
    {
        if (isset($params['sign'])) unset($params['sign']);
        ksort($params);
        reset($params);
        return strtolower(md5(http_build_query($params) . $this->appSecret));
    }

    private function request($path, $data) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "{$this->url}{$path}");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($curl);
        curl_close($curl);
        return json_decode($data, true);
    }
}
