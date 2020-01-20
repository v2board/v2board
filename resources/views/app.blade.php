<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./antd.chunk.css?v={{$verison}}">
    <link rel="stylesheet" href="./umi.css?v={{$verison}}">
    <link rel="stylesheet" href="./custom.css?v={{$verison}}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no">
    <title>{{$title}}</title>
    <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,400i,600,700"> -->
    <script>window.routerBase = "/";</script>
    <script>
        window.v2board = {
            title: '{{$title}}',
            theme: '{{$theme}}',
            verison: '{{$verison}}',
            background_url: '{{$backgroun_url}}'
        }
    </script>
</head>

<body>
<div id="root"></div>
<script src="./vendors.async.js?v={{$verison}}"></script>
<script src="./antd.async.js?v={{$verison}}"></script>
<script src="./umi.js?v={{$verison}}"></script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-P1E9Z5LRRK"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());

    gtag('config', 'G-P1E9Z5LRRK');
</script>
</body>

</html>
