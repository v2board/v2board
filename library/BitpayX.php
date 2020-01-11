<?php

namespace Library;

class BitpayX
{
    private $bitpayxAppSecret;
    private $bitpayxGatewayUri;

    /**
     * 签名初始化
     * @param merKey    签名密钥
     */
    public function __construct($bitpayxAppSecret)
    {
        $this->bitpayxAppSecret = $bitpayxAppSecret;
        $this->bitpayxGatewayUri = 'https://api.mugglepay.com/v1/';
    }

    public function prepareSignId($tradeno)
    {
        $data_sign = array();
        $data_sign['merchant_order_id'] = $tradeno;
        $data_sign['secret'] = $this->bitpayxAppSecret;
        $data_sign['type'] = 'FIAT';
        ksort($data_sign);
        return http_build_query($data_sign);
    }

    public function sign($data)
    {
        return strtolower(md5(md5($data) . $this->bitpayxAppSecret));
    }

    public function verify($data, $signature)
    {
        $mySign = $this->sign($data);
        return $mySign === $signature;
    }

    public function mprequest($data)
    {
        $headers = array('content-type: application/json', 'token: ' . $this->bitpayxAppSecret);
        $curl = curl_init();
        $url = $this->bitpayxGatewayUri . 'orders';
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        $data_string = json_encode($data);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($curl);
        curl_close($curl);
        return json_decode($data, true);
    }

    public function mpcheckout($orderId, $data)
    {
        $headers = array('content-type: application/json', 'token: ' . $this->bitpayxAppSecret);
        $curl = curl_init();
        $url = $this->bitpayxGatewayUri . 'orders/' . $orderId . '/checkout';
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        $data_string = json_encode($data);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($curl);
        curl_close($curl);
        return json_decode($data, true);
    }

    public function refund($merchantTradeNo)
    {
        // TODO
        return true;
    }

    public function buildHtml($params, $method = 'post', $target = '_self')
    {
        // var_dump($params);exit;
        $html = "<form id='submit' name='submit' action='" . $this->gatewayUri . "' method='$method' target='$target'>";
        foreach ($params as $key => $value) {
            $html .= "<input type='hidden' name='$key' value='$value'/>";
        }
        $html .= "</form><script>document.forms['submit'].submit();</script>";
        return $html;
    }
}
