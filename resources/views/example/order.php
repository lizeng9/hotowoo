<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>支付测试</title>
<LINK rel="shortcut icon" href="/assets/img/favicon.ico" type="image/x-icon" />
<LINK rel="stylesheet" href="/assets/css/reset.css">
<script type="text/javascript" src="/assets/js/zepto.min.js"></script>
<style>
body{
    margin: 1rem;
}
form{
}
p,input,select{
    font-size: 1.5rem;
}
p{
    margin-top: 0.5rem;
}
input[type=text]{
    width: 50rem;
}
</style>
</head>
<body>
<form action="../mobile/checkout" method="post">
    <h1>测试下单</h1>
    <p>用户id user_id：</p>
    <input type="text" name="user_id" value="<?= $user_id ?>">
    <p>订单号 order_id：</p>
    <input type="text" name="order_id" value="<?= $order_id ?>">
    <p>订单标题 order_title:</p>
    <input type="text" name="order_title" value="<?= $order_title ?>">
    <p>总金额(按分计) total_fee:</p>
    <input type="text" name="total_fee" value="<?= $total_fee ?>">
    <p>货币类型 fee_type:
    <select name="fee_type">
        <option value="CNY">CNY</option>
        <option value="USD">USD</option>
    </select>
    </p>
    <p>后台通知地址 notify_url:</p>
    <input type="text" name="notify_url" value="<?= $url_root ?>/payment/example/notify" readonly>
    <p>支付成功跳转地址:success_url:</p>
    <input type="text" name="success_url" value="<?= $url_root ?>/payment/example/success?order_id=123456" readonly>
    <p>支付失败跳转地址:failure_url:</p>
    <input type="text" name="failure_url" value="<?= $url_root ?>/payment/example/order" readonly>
    <p></p>
    <input type="submit">
</form>
</body>
</html>