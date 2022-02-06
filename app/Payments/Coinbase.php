<?php

namespace App\Payments;


class Coinbase {
    public function __construct($config) {
        $this->config = $config;
    }

    public function form()
    {
        return [
            'coinbase_url' => [
                'label' => '接口地址',
                'description' => '',
                'type' => 'input',
            ],
            'coinbase_api_key' => [
                'label' => 'API KEY',
                'description' => '',
                'type' => 'input',
            ],
            'coinbase_webhook_key' => [
                'label' => 'WEBHOOK KEY',
                'description' => '',
                'type' => 'input',
            ],
        ];
    }

    public function pay($order) {

        $params = [
            'name' => $order['trade_no'],
            'description' => 'TODO DESC',
            'pricing_type' => 'fixed_price',
            'local_price' => [
                'amount' => sprintf('%.2f', $order['total_amount'] / 100),
                'currency' => 'CNY'
            ],
            'metadata' => [
                "outTradeNo" => $order['trade_no'],
            ],
        ];

        $params_string = http_build_query($params);
        
        //echo $params['totalAmount'];
        // 网关连接
        $ret_raw = self::_curlPost($this->config['coinbase_url'], $params_string);

        $ret = @json_decode($ret_raw, true);

        // echo $ret_raw;
        
        if(empty($ret['data']['hosted_url'])) {
            abort(500, "error!");
        }
        return [
            'type' => 1, // Redirect to url
            'data' => $ret['data']['hosted_url'],
        ];
    }

    public function notify($params) {
        $payload = trim(file_get_contents('php://input'));

        $json_param = json_decode($payload, true); //convert JSON into array

        //TODO 进行验证 需要验证
        //获取已有的sign
        $headerName = 'X-Cc-Webhook-Signature';
        $headers = getallheaders();
        $signraturHeader = isset($headers[$headerName]) ? $headers[$headerName] : null;

        //计算sign
        $computedSignature = \hash_hmac('sha256', $payload, $this->config['coinbase_webhook_key']);

        //比较 不通过则返回失败
        if (!self::hashEqual($signraturHeader, $computedSignature)) {
            echo json_encode(['status' => 400]);
            return false;
        }

        //通过校验， 获取交易单号然后返回
        http_response_code(200);
        echo sprintf('Successully pay.');
        $out_trade_no = $json_param['event']['metadata']['outTradeNo'];
        $pay_trade_no=$json_param['event']['id'];
        return [
            'trade_no' => $out_trade_no,
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
            $ch, CURLOPT_HTTPHEADER, array('X-CC-Api-Key:' .$this->config['coinbase_api_key'], 'X-CC-Version: 2018-03-22')
        );
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function hashEqual($str1, $str2)
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
