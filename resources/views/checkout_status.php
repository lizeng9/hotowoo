<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<LINK rel="shortcut icon" href="/assets/img/favicon.ico" type="image/x-icon" />
<LINK rel="stylesheet" href="/assets/css/reset.css">
<script type="text/javascript" src="/assets/js/zepto.min.js"></script>
<title>支付状态</title>
    <style>
        div{
            padding: 0.5rem;
            font-size: 1.5rem;
            text-align: center;
        }
        div.info{
            font-size: 1rem;
            text-align: left;
        }
        button{
            margin: 0.2rem;
            display: block;
        }

        a{
            font-size: 1rem;
            padding: 0.2rem;
            margin: 0.2rem;
            border-radius: 0.8rem;
            border: solid 1px darkgrey;
        }
        a:hover{
            border-radius: 0.8rem;
        }

        .btn-nav{
            display: flex;
            flex-direction: column;
        }
        .status-view{
            display: none;
        }
        .status-view.wait{
            display: block;
        }
    </style>
</head>
<body>
<div class="pay-status">
    <div class="order-info">
        支付金额：<span class="fee" style="color: red;"><?= $fee_type_char ?><?= $total_fee ?></span>
    </div>
    <div class="status-view wait">支付中 ... </div>
    <div class="status-view success">支付成功</div>
    <div class="status-view fail">支付失败</div>
    <div class="status-view unknown">支付异常</div>
    <div class="status-view unknown info">
        <p>可能原因:</p>
        <p>1. 用户未支付</p>
        <p>2. 暂未收到支付网关的支付确认信息</p>
        <p>联系客服：400-12345678</p>
    </div
</div>
<div class="btn-nav">
    <a class="status-view unknown" href="<?= $success_url ?>">已完成支付</a>
    <a class="status-view success" href="<?= $success_url ?>">查看订单</a>
    <a class="status-view fail unknown" href="<?= $failure_url ?>">继续支付</a>
</div>
</body>
</html>
<script>
var update_view=function (status) {
    $(".status-view").hide();
    $("." + status).show();
}
var status_refresh = function () {
    var counter = 0;
    var max_try= 3;
    var polling =  function () {
        var interval = 5000; //3 seconds
        var api = window.location.href + "?" + Date.now();
        $.getJSON(api,function (res) {
            counter++;
            if(counter>max_try){
                update_view('unknown');
                return;
            }else{
                if(res.data.status ==2){
                    update_view('success');
                    return;
                }else if(res.data.status ==3){
                    update_view('fail');
                    return;
                }
            }
            console.log(res,counter);
            setTimeout(polling,interval);
        })
    }
    polling();
}
status_refresh();
</script>