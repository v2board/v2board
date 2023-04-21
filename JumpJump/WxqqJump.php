<?php
// 微信QQ域名防红跳转 把下面一行代码添加至 index.php
// require_once('JumpJump/WxqqJump.php');

// 是否开启跳转
$conf["wxqqjump"]="yes";
// 排除路径 vpay回调地址
$conf["vpayurl"]="api";
$conf["payurl"]="pay";
// 排除路径 后台登陆地址
$conf["adminurl"]="niubie";


if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')||strpos($_SERVER['HTTP_USER_AGENT'], 'Weibo')||strpos($_SERVER['HTTP_USER_AGENT'], 'UCBrowser')||strpos($_SERVER['HTTP_USER_AGENT'], 'UBrowser')||strpos($_SERVER['HTTP_USER_AGENT'], 'baidubrowser')||strpos($_SERVER['HTTP_USER_AGENT'], 'baiduboxapp')||strpos($_SERVER['HTTP_USER_AGENT'], '360SE')||strpos($_SERVER['HTTP_USER_AGENT'], '360browser')||strpos($_SERVER['HTTP_USER_AGENT'], '360se')||strpos($_SERVER['HTTP_USER_AGENT'], '360EE')||strpos($_SERVER['HTTP_USER_AGENT'], 'MQQBrowser')||strpos($_SERVER['HTTP_USER_AGENT'], 'QQBrowser')||strpos($_SERVER['HTTP_USER_AGENT'], 'TencentTraveler')||strpos($_SERVER['HTTP_USER_AGENT'], 'MQQBrowser')||strpos($_SERVER['HTTP_USER_AGENT'], 'MiuiBrowser')||strpos($_SERVER['HTTP_USER_AGENT'], 'Quark')||strpos($_SERVER['HTTP_USER_AGENT'], 'Maxthon')||strpos($_SERVER['HTTP_USER_AGENT'], 'MetaSr')||strpos($_SERVER['HTTP_USER_AGENT'], 'SogouMobileBrowser')||strpos($_SERVER['HTTP_USER_AGENT'], 'NewsArticle')||strpos($_SERVER['HTTP_USER_AGENT'], 'TTWebView')||strpos($_SERVER['HTTP_USER_AGENT'], 'VivoBrowser')||strpos($_SERVER['HTTP_USER_AGENT'], 'HuaweiBrowser')||strpos($_SERVER['HTTP_USER_AGENT'], 'HeyTapBrowser')||strpos($_SERVER['HTTP_USER_AGENT'], 'LBBROWSER')||strpos($_SERVER['HTTP_USER_AGENT'], 'TheWorld')!==false

    && strpos($_SERVER['REQUEST_URI'], strval($conf["vpayurl"]))===false
    && strpos($_SERVER['REQUEST_URI'], strval($conf["adminurl"]))===false
    && strpos($_SERVER['REQUEST_URI'], strval($conf["payurl"]))===false

    && $conf["wxqqjump"]==="yes"){
    $siteurl='https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];


echo '<html>
<head>
    <meta charset="UTF-8">
    <title>使用浏览器打开</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta name="format-detection" content="telephone=no">
    <meta content="false" name="twcClient" id="twcClient">
    <meta name="aplus-touch" content="1">
    <style>
        body,html{width:100%;height:100%}
        *{margin:0;padding:0}
        body{background-color:#fff}
        #browser img{
            width:50px;
        }
        #browser{
            margin: 0px 10px;
            text-align:center;
        }
        #contens{
            font-weight: bold;
            color: #2466f4;
            margin:-285px 0px 10px;
            text-align:center;
            font-size:20px;
            margin-bottom: 125px;
        }
        .top-bar-guidance{font-size:15px;color:#fff;height:70%;line-height:1.8;padding-left:20px;padding-top:20px;background:url(/atu/banner.png) center top/contain no-repeat}
        .top-bar-guidance .icon-safari{width:25px;height:25px;vertical-align:middle;margin:0 .2em}
        .app-download-tip{margin:0 auto;width:290px;text-align:center;font-size:15px;color:#2466f4;background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAcAQMAAACak0ePAAAABlBMVEUAAAAdYfh+GakkAAAAAXRSTlMAQObYZgAAAA5JREFUCNdjwA8acEkAAAy4AIE4hQq/AAAAAElFTkSuQmCC) left center/auto 15px repeat-x}
        .app-download-tip .guidance-desc{background-color:#fff;padding:0 5px}
        .app-download-tip .icon-sgd{width:25px;height:25px;vertical-align:middle;margin:0 .2em}
        .app-download-btn{display:block;width:214px;height:40px;line-height:40px;margin:18px auto 0 auto;text-align:center;font-size:18px;color:#2466f4;border-radius:20px;border:.5px #2466f4 solid;text-decoration:none}
    </style>
</head>
<body>

<div class="top-bar-guidance">
    <p>点击 右上角在 浏览器 打开</p>
    <p>↗↗↗</p>
</div>

<div id="contens">
<p><br/><br/></p>
<p>1.本站不支持 国产浏览器 访问</p>
<p><br/></p>
<p>2.请在 Chrome、Safari、Edge、Firefox 中打开</p>
</div>

<div class="app-download-tip">
    <span class="guidance-desc">'.$siteurl.'</span>
</div>
<p><br/></p>
<div class="app-download-tip">
    <span class="guidance-desc">请点击右上角 在浏览器中打开</span>
</div>

<body>
</html>';
exit;
}
?>
