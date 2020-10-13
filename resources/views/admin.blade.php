<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="/assets/admin/components.chunk.css?v={{$verison}}">
    <link rel="stylesheet" href="/assets/admin/umi.css?v={{$verison}}">
    <link rel="stylesheet" href="/assets/admin/custom.css?v={{$verison}}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no">
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
            background_url: '{{$backgroun_url}}'
        }
    </script>
</head>

<body>
<div id="root"></div>
<script src="/assets/admin/vendors.async.js?v={{$verison}}"></script>
<script src="/assets/admin/components.async.js?v={{$verison}}"></script>
<script src="/assets/admin/umi.js?v={{$verison}}"></script>
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
