<?php
namespace Library;

use Illuminate\Support\Facades\Http;

class AlipayF2F {
    private $appId;
    private $privateKey;
    private $alipayPublicKey;
    private $signType = 'RSA2';
    public $bizContent;
    public $method;
    public $notifyUrl;
    public $response;

    public function __construct()
    {
    }

    public function verify($data): bool
    {
        if (is_string($data)) {
            parse_str($data, $data);
        }
        $sign = $data['sign'];
        unset($data['sign']);
        unset($data['sign_type']);
        ksort($data);
        $data = $this->buildQuery($data);
        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->alipayPublicKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        if ("RSA2" == $this->signType) {
            $result = (openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1);
        } else {
            $result = (openssl_verify($data, base64_decode($sign), $res) === 1);
        }
        openssl_free_key(openssl_get_publickey($res));
        return $result;
    }

    public function setBizContent($bizContent = [])
    {
        $this->bizContent = json_encode($bizContent);
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
    }

    public function setAlipayPublicKey($alipayPublicKey)
    {
        $this->alipayPublicKey = $alipayPublicKey;
    }

    public function setNotifyUrl($url)
    {
        $this->notifyUrl = $url;
    }

    public function send()
    {
        $response = Http::get('https://openapi.alipay.com/gateway.do', $this->buildParam())->json();
        $resKey = str_replace('.', '_', $this->method) . '_response';
        if (!isset($response[$resKey])) throw new \Exception('Request from paypal failed');
        $response = $response[$resKey];
        if ($response['msg'] !== 'Success') throw new \Exception($response['sub_msg']);
        $this->response = $response;
    }

    public function getQrCodeUrl()
    {
        $response = $this->response;
        if (!isset($response['qr_code'])) throw new \Exception('Failed to get payment QR code');
        return $response['qr_code'];
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function buildParam(): array
    {
        $params = [
            'app_id' => $this->appId,
            'method' => $this->method,
            'charset' => 'UTF-8',
            'sign_type' => $this->signType,
            'timestamp' => date('Y-m-d H:m:s'),
            'biz_content' => $this->bizContent,
            'version' => '1.0',
            '_input_charset' => 'UTF-8'
        ];
        if ($this->notifyUrl) $params['notify_url'] = $this->notifyUrl;
        ksort($params);
        $params['sign'] = $this->buildSign($this->buildQuery($params));
        return $params;
    }

    public function buildQuery($query)
    {
        if (!$query) {
            throw new \Exception('Parameter construction error');
        }
        //To sort the parameters
        ksort($query);

        //Reassembly parameters
        $params = array();
        foreach ($query as $key => $value) {
            $params[] = $key . '=' . $value;
        }
        $data = implode('&', $params);
        return $data;
    }

    private function buildSign(string $signData): string
    {
        $privateKey = $this->privateKey;
        $p_key = array();
        //If the private key is 1 line
        if (!stripos($privateKey, "\n")) {
            $i = 0;
            while ($key_str = substr($privateKey, $i * 64, 64)) {
                $p_key[] = $key_str;
                $i++;
            }
        }
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . implode("\n", $p_key);
        $privateKey = $privateKey . "\n-----END RSA PRIVATE KEY-----";

        //Private Key
        $privateId = openssl_pkey_get_private($privateKey, '');

        // Signature
        $signature = '';

        if ("RSA2" == $this->signType) {

            openssl_sign($signData, $signature, $privateId, OPENSSL_ALGO_SHA256);
        } else {

            openssl_sign($signData, $signature, $privateId, OPENSSL_ALGO_SHA1);
        }

        openssl_free_key($privateId);

        //The encrypted content usually contains special characters, which need to be encoded under
        $signature = base64_encode($signature);
        return $signature;
    }
}
