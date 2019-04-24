<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <table>
        <tr>
            <td>ID</td>
            <td>商品名</td>
            <td>商品图片</td>
            <td>价格</td>
        </tr>
        @foreach($goods_info as $v)
        <tr>
            <td>{{$v->goods_id}}</td>
            <td>{{$v->goods_name}}</td>
            <td> <img src="{{$v->goods_img}}" alt="暂无图片" width="40"></td>
            <td>{{$v->goods_price}}</td>
        </tr>
        @endforeach
    </table>
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
        'onMenuShareAppMessage',
        'updateTimelineShareData'
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
            // // 自定义“分享给朋友”及“分享到QQ”按钮的分享内容
            // wx.updateAppMessageShareData({
            //     title: '电视', // 分享标题
            //     desc: '大屏电视', // 分享描述
            //     link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            //     imgUrl: '', // 分享图标
            //     success: function () {
            //     // 设置成功
            //     }
            // })
            wx.onMenuShareAppMessage({
                title: '电视', // 分享标题
                desc: '大屏电视', // 分享描述
                link: document.URL, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: 'http://mmbiz.qpic.cn/mmbiz_jpg/Hiak941wazMV8NXcT7cfIL1NMBf26bia8GOib2v1vO2qwQZgvR1vj9NibdFS7RBseaPPDRYpsqhJzTHBgpft2INGfA/0?wx_fmt=jpeg', // 分享图标
                success: function () {
                // 用户点击了分享后执行的回调函数
                }
            });
        });
    </script>
</body>
</html>
