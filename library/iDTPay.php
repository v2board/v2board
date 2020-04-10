<?php

namespace Library;

use function GuzzleHttp\Psr7\build_query;

/**
 * https://pay.idt.xyz/
 * 自定义支付
 */
class iDTPay
{
    private $appId;
    private $appSecret;
    private $apiUrl;
    private $inputCharset;

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->apiUrl = 'https://pay.idt.xyz/submit.php';
        $this->inputCharset = 'utf-8';
    }

    /**
     * 生成签名结果
     * @param array $para_sort 已排序要签名的数组
     * @return string 签名结果字符串
     */
    private function buildRequestMysign($para_sort) {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $preStr = $this->createLinkString($para_sort);
        $mySign = $this->md5Sign($preStr, $this->appSecret);
        return $mySign;
    }

    /**
     * 生成要请求给支付宝的参数数组
     * @param array $para_temp 请求前的参数数组
     * @return array 要请求的参数数组
     */
    private function buildRequestPara($params) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($params);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);
        $mySign = $this->buildRequestMysign($para_sort);

        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mySign;
        $para_sort['sign_type'] = 'MD5';

        return $para_sort;
    }

    /**
     * 生成要请求给支付宝的参数数组
     * @param array $para_temp 请求前的参数数组
     * @return string 要请求的参数数组字符串
     */
    public function buildRequestParaToString($params) {
        //待请求参数数组
        $para = $this->buildRequestPara($params);

        //把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
        $request_data = $this->createLinkStringUrlencode($para);

        return $request_data;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param array $para_temp 请求参数数组
     * @param string $method 提交方式。两个值可选：post、get
     * @param string $button_name 确认按钮显示文字
     * @return string 提交表单HTML文本
     */
    function buildRequestForm($para_temp, $method='POST', $button_name='正在跳转') {
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);
//        dump($para);
//        exit;
        $sHtml = "<style type='text/css'>
    body {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        color: #3498db;
        background-color: #f7f7f7;
        -webkit-font-smoothing: antialiased;
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    }
    .load-wrapp p {padding: 0 0 20px;}
    .load-wrapp:last-child {margin-right: 0;}
    .letter-holder {padding: 16px;}
    .letter {
        float: left;
        font-size: 2em;
        color: #3498db;
    }
    .load .letter {
        animation-name: loading;
        animation-duration: 1.6s;
        animation-iteration-count: infinite;
        animation-direction: linear;
    }
    .l-1 {animation-delay: .48s;}
    .l-2 {animation-delay: .6s;}
    .l-3 {animation-delay: .72s;}
    .l-4 {animation-delay: .84s;}
    .l-5 {animation-delay: .96s;}
    .l-6 {animation-delay: 1.08s;}
    .l-7 {animation-delay: 1.2s;}
    .l-8 {animation-delay: 1.32s;}
    .l-9 {animation-delay: 1.44s;}
    .l-10 {animation-delay: 1.56s;}
    @keyframes loading {
        0% {opacity: 0;}
        100% {opacity: 1;}
    }

</style>
<div class=\"load-wrapp\">
    <div class=\"load\">
        <div class=\"letter-holder\">
            <div class=\"l-1 letter\">·</div>
            <div class=\"l-2 letter\">·</div>
            <div class=\"l-3 letter\">·</div>
            <div class=\"l-4 letter\">正</div>
            <div class=\"l-5 letter\">在</div>
            <div class=\"l-6 letter\">跳</div>
            <div class=\"l-7 letter\">转</div>
            <div class=\"l-8 letter\">·</div>
            <div class=\"l-9 letter\">·</div>
            <div class=\"l-10 letter\">·</div>
        </div>
    </div>
</div>";
        $sHtml = $sHtml."<form id='idtpaysubmit' name='idtpaysubmit' action='".$this->apiUrl."?_input_charset=".trim(strtolower($this->inputCharset))."' method='".$method."'>";

        foreach ($para as $key=>$val){
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' hidden></form>";

        $sHtml = $sHtml."<script>document.forms['idtpaysubmit'].submit();</script>";
        return $sHtml;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param array $para 需要拼接的数组
     * @return string 拼接完成以后的字符串
     */
    private function createLinkStringUrlencode($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".urlencode($val)."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);

        //如果存在转义字符，那么去掉转义
//        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }

    /**
     * 对数组排序
     * @param array $para 排序前的数组
     * @return array 排序后的数组
     */
    private function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 除去数组中的空值和签名参数
     * @param array $para 签名参数组
     * @return array 去掉空值与签名参数后的新签名参数组
     */
    private function paraFilter($para) {
        $para_filter = array();
        foreach ($para as $key=>$val){
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
            else	$para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param array $para 需要拼接的数组
     * @return string 拼接完成以后的字符串
     */
    private function createLinkString($para) {
        $arg  = "";
        foreach ($para as $key=>$val){
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,strlen($arg)-1);

        //如果存在转义字符，那么去掉转义
//        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }

    /**
     * 签名字符串
     * @param string $preStr 需要签名的字符串
     * @param string $key 私钥
     * @return string 签名结果
     */
    private function md5Sign($preStr, $key) {
        $preStr = $preStr . $key;
        return md5($preStr);
    }

    /**
     * 验证签名
     * @param string $preStr 需要签名的字符串
     * @param string $sign 签名结果
     * @param string $key 私钥
     * @return bool 签名结果
     */
    private function md5Verify($preStr, $sign, $key) {
        $preStr = $preStr . $key;
        $mySign = md5($preStr);
        if($mySign == $sign) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @param array $params 回调参数
     * @return bool 验证结果
     */
    public function verifyNotify($params){
        if(empty($params)) {//判断POST来的数组是否为空
            return false;
        }
        else {
            //生成签名结果
            $isSign = $this->getSignVerify($params, $params["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            //if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}

            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i",$responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取返回时的签名验证结果
     * @param array $para_temp 通知返回来的参数数组
     * @param string $sign 返回的签名结果
     * @return bool 签名验证结果
     */
    private function getSignVerify($para_temp, $sign) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $preStr = $this->createLinkstring($para_sort);

        $isSign = $this->md5Verify($preStr, $sign, $this->appSecret);

        return $isSign;
    }

}
