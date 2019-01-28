
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><!--STATUS OK-->
<HTML>
<HEAD>
    <TITLE> 稀饭旅行网—专业华人旅游网，美国、加拿大、墨西哥当地旅游地接 </TITLE>
    <META http-equiv="content-type" content="text/html;charset=utf-8">
    <META http-equiv="Pragma" content="no-cache">
    <META name="wap-font-scale" content="no">
    <META http-equiv="Cache-Control" content="no-cache">
    <META name="Keywords" content="收银台">
    <META name="Description" content="收银台">
    <LINK rel="shortcut icon" href="/assets/img/favicon.ico" type="image/x-icon" />
    <LINK rel="stylesheet" href="/assets/css/reset.css">
    <script type="text/javascript" src="/assets/js/zepto.min.js"></script>
    <script type="text/javascript" src="/assets/js/layer/layer.js"></script>

    <script type="text/javascript">
        var u = navigator.userAgent;
        var ua = navigator.userAgent.toLowerCase();
        var isiOS =!!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
        if(isiOS==true){
            (function (doc, win) {
                var docEl = doc.documentElement,
                    resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
                    recalc = function () {
                        var clientWidth = docEl.clientWidth;
                        if (!clientWidth) return;
                        if(clientWidth>=720){
                            docEl.style.fontSize = '100px';
                        }else{
                            docEl.style.fontSize = 100 * (clientWidth / 720) + 'px';
                        }
                    };
                if (!doc.addEventListener) return;
                win.addEventListener(resizeEvt, recalc, false);
                doc.addEventListener('DOMContentLoaded', recalc, false);
            })(document, window);
        }
        else {
            (function (doc, win) {
                var docEl = doc.documentElement,
                    resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
                    recalc = function () {
                        var clientWidth = docEl.clientWidth;
                        if (!clientWidth) return;
                        if(clientWidth>=720){
                            docEl.style.fontSize = '100px';
                        }else{
                            docEl.style.fontSize = 100 * (clientWidth / 720) + 'px';
                        }
                    };
                if (!doc.addEventListener) return;
                win.addEventListener(resizeEvt, recalc, false);
                doc.addEventListener('DOMContentLoaded', recalc, false);
            })(document, window);
        }

        function layerShow(data,is_reload,time){
            layer.open({
                content: data,
                style: 'background-color:white; color:#111; border:none;',
                time: time?time:3
            });
            if(is_reload===true){
                setTimeout(function(){
                    window.location.reload();
                },2000)
            }
        }

    </script>
    <!--<meta name="full-screen" content="yes">-->
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">


</HEAD>
<BODY>

<style>
    body{
        background: #f5f5f5;
    }
    /*开始*/
    .fill_header{
        width: 100%;
        overflow: hidden;
        max-width: 720px;
        margin: 0 auto;
        height: .79rem;
        background: #099fde;
        position: relative;
    }
    .fill_go_back{
        position: absolute;
        top: 25%;
        left: .3rem;
        width: .25rem;
        height: .43rem;
    }
    .fill_header{
        font-size: .32rem;
        line-height: .79rem;
        text-align: center;
        color: white;
    }
    .grey_line{
        width: 100%;
        height: .21rem;
        max-width: 720px;
        margin: 0 auto;
        background: #f5f5f5;
        border-bottom: 1px solid rgba(209, 209, 209, 0.34);
        border-top: 1px solid rgba(209, 209, 209, 0.34);
    }
    body .bottom_border_none{
        border-bottom: none;
    }
    .payment_wrap{
        width: 100%;
        margin: 0 auto;
        overflow: hidden;
        max-width: 720px;
        font-size: .27rem;
        color:#333333;
        background: white;
    }
    .payment{
        font-weight: 600;
        line-height: .45rem;
        width: 91%;
        margin: 0 auto;
        overflow: hidden;
        padding: .15rem 0;
        border-bottom: 1px dashed #c7c7c7;
    }
    .product_no{
        padding-left: .15rem;
    }
    .please_pay{
        color:#666666;
        float: left;
    }
    .product_price{
        font-size: 0.5rem;
        color:#f15353;
        float: right;
    }
    .payment_ways_wrap{
        width: 100%;
        margin: 0 auto;
        overflow: hidden;
        max-width: 720px;
        border-bottom: 1px solid #e4e4e4;
        background: white;
    }
    .pays_wrap{
        background: white;
        width: 100%;
        margin: 0 auto;
        overflow: hidden;
        max-width: 720px;
    }
    .payment_ways_head{
        width: 91%;
        margin: 0 auto;
        overflow: hidden;
        color: #666666;
        padding: .30rem 0;
        font-size: .27rem;
        font-weight: bold;
    }
    .pays{
        width: 91%;
        margin: 0 auto;
        overflow: hidden;
        display: -webkit-box;
        display: -moz-box;
        display: -ms-flexbox;
        display: flex;
        flex-flow: wrap row;
        padding: .20rem 0;
        border-bottom: 1px dashed #b2b2b2;
    }
    .pays_inner_left{
        -moz-box-flex: 1;
        -webkit-box-flex: 1;
        -ms-flex: 1;
        flex: 1;
        display: -webkit-box;
        display: -moz-box;
        display: -ms-flexbox;
        display: flex;
        flex-flow: wrap row;
        font-size: .30rem;
        color:#333333;

    }
    .pays_inner_right{
        width: .45rem;
    }
    .pays_inner_right img{
        display: table-cell;
        width: .45rem;
        height: .45rem;
        vertical-align: middle;
        margin-top: .12rem;
    }
    .pays_inner_left_inner1 img{
        width: .68rem;
        height: .71rem;
        vertical-align: middle;
    }
    .pays_inner_left_inner2 p{
        padding-left: .20rem;
    }
    .pays_inner_left_inner2 img{
        width: .68rem;
        height: .71rem;
        vertical-align: middle;
    }
    .pays_inner_left_p2{
        color:#777777;
        font-size: .23rem;
    }

    .now_pay_btn{
        display: block;
        outline: none;
        margin: 10px auto 0;
        overflow: hidden;
        font-size: .30rem;
        color:#fff;
        font-weight: bold;
        width: 91%;
        border: none;
        max-width: 720px;
        text-align: center;
        line-height: .85rem;
        height: .85rem;
        border-radius: 5px;
        background: #ff8a00;
        font-family: 'Microsoft YaHei', 'Hiragino Sans GB', Helvetica, Arial, 'Lucida Grande', sans-serif;
    }

</style>
<div class="fill_header">
    <a href="javascript:history.go(-1);">
        <img class="fill_go_back" src="/assets/img/white_left.png" alt="">
    </a>
    收银台
</div>
<div class="grey_line"></div>
<div class="payment_wrap">
    <div class="payment_wrap_inner">
        <div class="payment">
            <?= $order_title ?>
        </div>
    </div>
    <div class="payment bottom_border_none">
        <div class="please_pay">订单金额</div>
        <div class="product_price"><span><?= $fee_type_char ?></span><span><?= $total_fee/100 ?></span></div>
    </div>
</div>

<div class="grey_line"></div>
<div class="payment_ways_wrap">
    <div class="payment_ways_head">
        选择支付方式
    </div>
</div>
<form data-ajax="false" id="payform" method="POST" action="">
    <input type="hidden" id="order_id" name="order_id" value="<?= $order_id ?>" />
    <input type="hidden" id="order_title" name="order_title" value="<?= $order_title ?>" />
    <input type="hidden" id="user_id" name="user_id" value="<?= $user_id ?>" />
    <input type="hidden" id="total_fee" name="total_fee" value="<?= $total_fee ?>" />
    <input type="hidden" id="fee_type" name="fee_type" value="<?= $fee_type ?>" />
    <input type="hidden" id="notify_url" name="notify_url" value="<?= $notify_url ?>" />
    <input type="hidden" id="success_url" name="success_url" value="<?= $success_url ?>" />
    <input type="hidden" id="failure_url" name="failure_url" value="<?= $failure_url ?>" />
    <input type="hidden" id="paytype" name="paytype" />

    <div class="pays_wrap">
        <div class="pays" id="wechatWap">
            <div class="pays_inner_left">
                <div class="pays_inner_left_inner1">
                    <img src="/assets/img/wechat.png" alt="">
                </div>
                <div class="pays_inner_left_inner2">
                    <p class="pays_inner_left_p1">微信支付</p>
                    <p class="pays_inner_left_p2">微信安全支付</p>
                </div>
            </div>
            <div class="pays_inner_right">
                <img payment_type="wechatWap" src="/assets/img/checkbox.png" alt="">
            </div>
        </div>
        <div class="pays" id="wechatMp">
            <div class="pays_inner_left">
                <div class="pays_inner_left_inner1">
                    <img src="/assets/img/wechat.png" alt="">
                </div>
                <div class="pays_inner_left_inner2">
                    <p class="pays_inner_left_p1">微信支付(公众号里)</p>
                    <p class="pays_inner_left_p2">微信安全支付</p>
                </div>
            </div>
            <div class="pays_inner_right">
                <img payment_type="wechatMp" src="/assets/img/checkbox.png" alt="">
            </div>
        </div>
        <div class="pays" id="alipay">
            <div class="pays_inner_left">
                <div class="pays_inner_left_inner1">
                    <img src="/assets/img/alipay.png" alt="">
                </div>
                <div class="pays_inner_left_inner2">
                    <p class="pays_inner_left_p1">支付宝账号支付</p>
                    <p class="pays_inner_left_p2">推荐有支付宝账号的用户使用</p>
                </div>
            </div>
            <div class="pays_inner_right">
                <img payment_type="alipayWap" src="/assets/img/checkbox.png" alt="">
            </div>
        </div>
        <div class="pays" id="paymentWall">
            <div class="pays_inner_left">
                <div class="pays_inner_left_inner1">
                    <img src="/assets/img/paymentwall.png" alt="">
                </div>
                <div class="pays_inner_left_inner2">
                    <p class="pays_inner_left_p1">信用卡支付</p>
                    <p class="pays_inner_left_p2">推荐有VISA信用卡的用户使用</p>
                </div>
            </div>
            <div class="pays_inner_right">
                <img payment_type="paymentwall" src="/assets/img/checkbox.png" alt="">
            </div>
        </div>
        <!--div class="pays" style="padding:0.2rem 0 0.4rem;border: none">
            <div class="pays_inner_left">
                <div class="pays_inner_left_inner1">
                    <img src="/img/payments/union_pay.png" alt="">
                </div>
                <div class="pays_inner_left_inner2">
                    <p class="pays_inner_left_p1">线下支付</p>
                    <p class="pays_inner_left_p2">支持储蓄卡、信用卡支付</p>
                </div>
            </div>
            <div class="pays_inner_right">
                <img payment_type="unionpay" src="/img/payments/grey_circle.png" alt="">
            </div>
        </div-->
    </div>
    <input type="submit" value="立即支付" class="now_pay_btn" >
</form>
<script>
    var is_inside_wechat =  function(){
        var ua = navigator.userAgent.toLowerCase();//获取判断用的对象
        return ua.match(/MicroMessenger/i) == "micromessenger";
    }

    $(function(){

        $(".pays_inner_right img").click(function(){
            $('.pays_inner_right img').addClass("taped").attr("src","/assets/img/checkbox.png")
            $(this).removeClass("taped").attr("src","/assets/img/checkbox_checked.png")
            $('#paytype').val($(this).attr('payment_type'));
            $("#payform").attr("action", $(this).attr('payment_type'));
        });
        $('#payform').submit(function(){
            var paytype = $.trim($('#paytype').val());
            if( paytype==''){
                layerShow('请选择支付方式');
                return false;
            }else if(paytype=='wechatMp' && !is_inside_wechat() ){
                layerShow('微信公众号支付，请在微信里打开');
                return false;
            }else if ( paytype=='paymentWall'){
                var api = 'https://m.tourscool.com/cart/CheckoutProcess';
                api = api + "?" + $(this).serialize();

                $.ajax({
                    url:api,
                    dataType:'json',
                    success:function (data) {
                        console.log(data);
                        var jumpToStin = data.iframe_code;
                        var tourl = "https://m.tourscool.com/cart/PaymentWall"+"?code="+jumpToStin;

                        window.location.href = tourl;
                    }
                });
                return false;
            }
        })
    })
</script>
</BODY>
</HTML>
