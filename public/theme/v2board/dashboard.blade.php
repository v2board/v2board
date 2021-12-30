<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="/theme/{{$theme}}/assets/components.chunk.css?v={{$verison}}">
    <link rel="stylesheet" href="/theme/{{$theme}}/assets/umi.css?v={{$verison}}">
    <link rel="stylesheet" href="/theme/{{$theme}}/assets/custom.css?v={{$verison}}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no">
    @php ($colors = [
        'darkblue' => '#3b5998',
        'black' => '#343a40',
        'default' => '#0665d0',
        'green' => '#319795'
    ])
    <meta name="theme-color" content="{{$colors[$theme_color]}}">

    <title>{{$title}}</title>
    <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,400i,600,700"> -->
    <script>window.routerBase = "/";</script>
    <script>
        window.settings = {
            title: '{{$title}}',
            theme: {
                sidebar: '{{$theme_sidebar}}',
                header: '{{$theme_header}}',
                color: '{{$theme_color}}',
            },
            verison: '{{$verison}}',
            background_url: '{{$backgroun_url}}',
            description: '{{$description}}',
            crisp_id: '{{$crisp_id}}'
        }
    </script>
    <script src="/theme/{{$theme}}/assets/i18n.js"></script>
    <script src="/theme/{{$theme}}/assets/i18n/zh-CN.js"></script>
    <script src="/theme/{{$theme}}/assets/i18n/en-US.js"></script>
    <script src="/theme/{{$theme}}/assets/i18n/ja-JP.js"></script>
    <script src="/theme/{{$theme}}/assets/i18n/vi-VN.js"></script>
</head>

<body>
<div id="root"></div>
<script src="/theme/{{$theme}}/assets/vendors.async.js?v={{$verison}}"></script>
<script src="/theme/{{$theme}}/assets/components.async.js?v={{$verison}}"></script>
<script src="/theme/{{$theme}}/assets/umi.js?v={{$verison}}"></script>
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
