<?php

/**
 * Telegram@smogate_bot
 */
namespace App\Payments;

use \Curl\Curl;

class Smogate {
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function form()
    {
        return [
            'smogate_source_currency' => [
                'label' => '源货币',
                'description' => '默认CNY',
                'type' => 'input'
            ],
            'smogate_method' => [
                'label' => '支付方式',
                'description' => '支持参数:alipay',
                'type' => 'input',
            ],
            'alert1' => [
                'type' => 'alert',
                'content' => '开户请联系：@smogate'
            ]
        ];
    }

    public function pay($order)
    {
        $params = [
            'out_trade_no' => $order['trade_no'],
            'total_amount' => $order['total_amount'],
            'notify_url' => $order['notify_url'],
            'method' => $this->config['smogate_method']
        ];
        if (isset($this->config['smogate_source_currency'])) {
            $params['source_currency'] = strtolower($this->config['smogate_source_currency']);
        }
        $params['app_id'] = "__APPID__";
        ksort($params);
        $str = http_build_query($params) . "__APPSECRET__";
        $params['sign'] = md5($str);
        $curl = new Curl();
        $curl->setUserAgent("Smogate __APPID__");
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, 0);
        $curl->post("https://__APPID__.vless.org/v1/gateway/pay", http_build_query($params));
        $result = $curl->response;
        if (!$result) {
            abort(500, '网络异常');
        }
        if ($curl->error) {
            if (isset($result->errors)) {
                $errors = (array)$result->errors;
                abort(500, $errors[array_keys($errors)[0]][0]);
            }
            if (isset($result->message)) {
                abort(500, $result->message);
            }
            abort(500, '未知错误');
        }
        $curl->close();
        if (!isset($result->data)) {
            abort(500, '请求失败');
        }
        return [
            'type' => $this->isMobile() ? 1 : 0, // 0:qrcode 1:url
            'data' => $result->data
        ];
    }

    public function notify($params)
    {
        $sign = $params['sign'];
        unset($params['sign']);
        ksort($params);
        reset($params);
        $str = http_build_query($params) . "__APPSECRET__";
        if ($sign !== md5($str)) {
            return false;
        }
        return [
            'trade_no' => $params['out_trade_no'],
            'callback_no' => $params['trade_no']
        ];
    }

    private function isMobile()
    {
        return strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mobile') !== false;
    }
}
