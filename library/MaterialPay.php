<?php

namespace Library;

use \Curl\Curl;

class MaterialPay
{
    private $appSecret;
    private $gatewayUri;

    public function __construct($appSecret)
    {
        $this->appSecret = $appSecret;
        $this->gatewayUri = 'https://www.materialpay.com/api/payment/';
    }

    public function prepareSign($data)
    {
        ksort($data);
        return http_build_query($data);
    }

    public function sign($data)
    {
        return strtolower(md5(md5($data) . $this->appSecret));
    }

    public function verify($data, $signature)
    {
        $mySign = $this->sign($data);
        return $mySign === $signature;
    }

    public function post($data, $type = 'create')
    {
        if ($type == 'create') {
            $this->gatewayUri .= 'create';
        } else {
            $this->gatewayUri .= 'query';
        }

        $headerArray = array("Content-type:application/json;charset='utf-8'","Accept:application/json");

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->gatewayUri);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$headerArray);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $data = curl_exec($curl);
        curl_close($curl);
        return json_decode($data, true);
    }

    public function create($params)
    {
        $result = $this->post($params);

        if (!isset($result['data']['tradeNo'])) {
            abort(500, '接口请求失败');
        }
        return $result['data']['url'];
    }

}
