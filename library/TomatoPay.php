<?php
namespace Library;

class TomatoPay {
    private $mchid;
    private $account;
    private $key;

    public function __construct($mchid, $account, $key) {
        $this->mchid = $mchid;
        $this->account = $account;
        $this->key = $key;
    }
    
    public function alipay ($cny, $trade) {
        $params = [
            'mchid' => $this->mchid,
            'account' => $this->account,
            'cny' => $cny,
            'type' => '1',
            'trade' => $trade
        ];
        $params['signs'] = $this->sign($params);
        return $this->buildHtml('https://b.fanqieui.com/gateways/alipay.php', $params);
    }

    public function wxpay ($cny, $trade) {
        $params = [
            'mchid' => $this->mchid,
            'account' => $this->account,
            'cny' => $cny,
            'type' => '1',
            'trade' => $trade
        ];
        $params['signs'] = $this->sign($params);
        return $this->buildHtml('https://b.fanqieui.com/gateways/wxpay.php', $params);
    }

    public function sign ($params) {
    	$o = '';
        foreach ($params as $k=>$v){
        	$o.= "$k=".($v)."&";
        }
        return md5(substr($o,0,-1).$this->key);
    }

    public function buildHtml($url, $params, $method = 'post', $target = '_self'){
    	// return var_dump($params);
		$html = "<form id='submit' name='submit' action='".$url."' method='$method' target='$target'>";
		foreach ($params as $key => $value) {
			$html .= "<input type='hidden' name='$key' value='$value'/>";
		}
		$html .= "</form><script>document.forms['submit'].submit();</script>";
		return $html;
    }
}