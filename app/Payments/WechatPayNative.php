<?php

namespace App\Payments;

use Omnipay\Omnipay;
use Omnipay\WechatPay\Helper;

class WechatPayNative {
    public function __construct($config)
    {
        $this->config = $config;
        $this->customResult = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }

    public function form()
    {
        return [
            'app_id' => [
                'label' => 'APPID',
                'description' => '绑定微信支付商户的APPID',
                'type' => 'input',
            ],
            'mch_id' => [
                'label' => '商户号',
                'description' => '微信支付商户号',
                'type' => 'input',
            ],
            'api_key' => [
                'label' => 'APIKEY(v1)',
                'description' => '',
                'type' => 'input',
            ]
        ];
    }

    public function pay($order)
    {
        $gateway = Omnipay::create('WechatPay_Native');
        $gateway->setAppId($this->config['app_id']);
        $gateway->setMchId($this->config['mch_id']);
        $gateway->setApiKey($this->config['api_key']);
        $gateway->setNotifyUrl($order['notify_url']);

        $params = [
            'body'              => $order['trade_no'],
            'out_trade_no'      => $order['trade_no'],
            'total_fee'         => $order['total_amount'],
            'spbill_create_ip'  => '0.0.0.0',
            'fee_type'          => 'CNY'
        ];

        $request  = $gateway->purchase($params);
        $response = $request->send();
        $response = $response->getData();
        if ($response['return_code'] !== 'SUCCESS') {
            abort(500, $response['return_msg']);
        }
        return [
            'type' => 0,
            'data' => $response['code_url']
        ];
    }

    public function notify($params)
    {
        $data = Helper::xml2array(file_get_contents('php://input'));
        $gateway = Omnipay::create('WechatPay');
        $gateway->setAppId($this->config['app_id']);
        $gateway->setMchId($this->config['mch_id']);
        $gateway->setApiKey($this->config['api_key']);
        $response = $gateway->completePurchase([
            'request_params' => file_get_contents('php://input')
        ])->send();

        if (!$response->isPaid()) {
            die('FAIL');
        }

        return [
            'trade_no' => $data['out_trade_no'],
            'callback_no' => $data['transaction_id']
        ];
    }
}
