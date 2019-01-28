<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>支付中 ... </title>
</head>
<body>
</body>
</html>
<script>

function onBridgeReady(){
    var param  = <?= json_encode($pay) ?>;
    WeixinJSBridge.invoke(
        'getBrandWCPayRequest', param,
        function(res){
            alert(res);
            if(res.err_msg == "get_brand_wcpay_request:ok" ){
                // alert("ok");
            }else{
                // alert("fail");
            }
        });
}
if (typeof WeixinJSBridge == "undefined"){
    if( document.addEventListener ){
        document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
    }else if (document.attachEvent){
        document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
        document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
    }
}else{
    onBridgeReady();
}

</script>
