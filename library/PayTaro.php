<?php

namespace Library;

use \Curl\Curl;

class PayTaro
{
    private $appId;
    private $appSecret;

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function pay($params)
    {
        ksort($params);
        $str = http_build_query($params) . $this->appSecret;
        $params['sign'] = md5($str);
        $curl = new Curl();
        $curl->post('http://api.paytaro.com/v1/gateway/fetch', http_build_query($params));
        var_dump($str, $params, $curl->response);exit;
        if ($curl->error) {
            abort(500, '接口请求失败');
        }
        $result = json_decode($curl->response);
        $curl->close();
        if ($result->code !== 1) {
            abort(500, '接口请求失败');
        }
        return $result->code_url;
    }

    public function verify($params)
    {
        $sign = $params['sign'];
        unset($params['sign']);
        ksort($params);
        reset($params);
        $str = http_build_query($params) . $this->appId;
        if ($sign !== md5($str)) {
            return false;
        }
        return true;
    }
}
