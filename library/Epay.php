<?php

namespace Library;

class Epay
{
    private $pid;
    private $key;
    private $url;

    public function __construct($url, $pid, $key)
    {
        $this->pid = $pid;
        $this->key = $key;
        $this->url = $url;
    }

    public function pay($params)
    {
        $params['pid'] = $this->pid;
        ksort($params);
        reset($params);
        $str = stripslashes(urldecode(http_build_query($params))) . $this->key;
        $params['sign'] = md5($str);
        $params['sign_type'] = 'MD5';
        return $this->url . '/submit.php?' . http_build_query($params);
    }

    public function verify($params)
    {
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['sign_type']);
        ksort($params);
        reset($params);
        $str = stripslashes(urldecode(http_build_query($params))) . $this->key;
        if ($sign !== md5($str)) {
            return false;
        }
        return true;
    }
}
