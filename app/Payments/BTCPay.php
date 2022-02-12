<?php

namespace App\Payments;


class BTCPay {
    public function __construct($config) {
        $this->config = $config;
    }

    public function form()
    {
        return [
            'btcpay_url' => [
                'label' => '接口地址',
                'description' => '需使用包含storeId的地址',
                'type' => 'input',
            ],
            'btcpay_storeId' => [
                'label' => 'storeId',
                'description' => '',
                'type' => 'input',
            ],
            'btcpay_api_key' => [
                'label' => 'API KEY',
                'description' => '个人设置中的API KEY(非商店设置中的)',
                'type' => 'input',
            ],
            'btcpay_webhook_key' => [
                'label' => 'WEBHOOK KEY',
                'description' => '',
                'type' => 'input',
            ],
        ];
    }

    public function pay($order) {

        $params = [
            'storeId' => $this->config['btcpay_storeId'],
            'orderId' => $order['trade_no'],
            'checkoutDesc' => 'MATRIX REVOLUTIONS 订阅套餐' . '订单号 ' . $order['trade_no'],
            'jsonResponse' => true,
            'amount' => sprintf('%.2f', $order['total_amount'] / 100),
            'currency' => 'CNY',
            'metadata' => [
                'checkoutDesc' => 'MATRIX REVOLUTIONS 订阅套餐' . '<br>订单号 ' . $order['trade_no'],
                'orderId' => $order['trade_no']
            ]
        ];

        $params_string = @json_encode($params);

        $ret_raw = self::_curlPost($this->config['btcpay_url'], $params_string);

        $ret = @json_decode($ret_raw, true);
        // echo $ret_raw;
        
        if(empty($ret['checkoutLink'])) {
            abort(500, "error!");
        }
        return [
            'type' => 1, // Redirect to url
            'data' => $ret['checkoutLink'],
        ];
    }

    public function notify($params) {
        $payload = trim(file_get_contents('php://input'));

        $headers = getallheaders();

        //IS Btcpay-Sig
        //NOT BTCPay-Sig
        //API doc is WRONG!
        $headerName = 'Btcpay-Sig';
        $signraturHeader = isset($headers[$headerName]) ? $headers[$headerName] : '';
        $json_param = json_decode($payload, true);
        
        //计算并拼接sig
        $computedSignature = "sha256=" . \hash_hmac('sha256', $payload, $this->config['btcpay_webhook_key']);

        if (!self::hashEqual($signraturHeader, $computedSignature)) {
            echo json_encode(['status' => 400]);
            return false;
        }

        // $out_trade_no = $json_param['outTradeNo'];
        $pay_trade_no=$json_param['invoiceId'];
        return [
            // 'trade_no' => $out_trade_no,
            'callback_no' => $pay_trade_no
        ];
    }


    private function _curlPost($url,$params=false){
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); //设置超时
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array('Authorization:' .'token '.$this->config['btcpay_api_key'], 'Content-Type: application/json')
        );
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


    /**
     * @param string $str1
     * @param string $str2
     * @return bool
     */
    private function hashEqual($str1, $str2)
    {   

        if (function_exists('hash_equals')) {
            return \hash_equals($str1, $str2);
        }

        if (strlen($str1) != strlen($str2)) {
            return false;
        } else {
            $res = $str1 ^ $str2;
            $ret = 0;

            for ($i = strlen($res) - 1; $i >= 0; $i--) {
                $ret |= ord($res[$i]);
            }
            return !$ret;
        }
    }
    
}

