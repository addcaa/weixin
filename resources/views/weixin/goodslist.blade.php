<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script>
        wx.config({
        debug: true,
        appId: "{{$wxconfig['appId']}}",
        timestamp: "{{$wxconfig['timestamp']}}",
        nonceStr: "{{$wxconfig['nonceStr']}}",
        signature: "{{$wxconfig['signature']}}",
        jsApiList: [
        'onMenuShareTimeline',
        ]
        });
        wx.ready(function () {
            wx.onMenuShareTimeline({
            title: '电视', // 分享标题
            link: document.URL, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl:'http://mmbiz.qpic.cn/mmbiz_jpg/Hiak941wazMV8NXcT7cfIL1NMBf26bia8GOib2v1vO2qwQZgvR1vj9NibdFS7RBseaPPDRYpsqhJzTHBgpft2INGfA/0?wx_fmt=jpeg', // 分享图标
                success: function () {
                // 用户点击了分享后执行的回调函数
                },
        });
        });
    </script>

</body>
</html>
