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
        $curl->post('https://api.paytaro.com/v1/gateway/fetch', http_build_query($params));
        $result = $curl->response;
        if (!$result) {
            abort(500, '网络异常');
        }
        if ($curl->error) {
            $errors = (array)$result->errors;
            abort(500, $errors[array_keys($errors)[0]][0]);
        }
        $curl->close();
        if (!isset($result->data->trade_no)) {
            abort(500, '接口请求失败');
        }
        return $result->data->pay_url;
    }

    public function verify($params)
    {
        $sign = $params['sign'];
        unset($params['sign']);
        ksort($params);
        reset($params);
        $str = http_build_query($params) . $this->appSecret;
        if ($sign !== md5($str)) {
            return false;
        }
        return true;
    }
}
