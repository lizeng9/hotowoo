<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Yansongda\Pay\Pay;
use App\Services\WxJsApiPay;
use Illuminate\Support\Facades\Log;


/**
 * 支付微服务
 * Class PaymentController
 * @package App\Http\Controllers
 */
class PaymentController extends Controller
{

    protected $inputs;
    protected $exchange_rates;
    protected $config_wechat;
    protected $config_alipay;
    protected $config_paymentwall;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->inputs = ["order_id", "order_title", "total_fee", "fee_type", "notify_url", "success_url", "failure_url"];
        $this->exchange_rates['USD']['CNY'] = 6.8;
        $this->config_wechat = [
            'appid' => env('WECHAT_APPID'),                  // APP APPID
            'app_id' => env('WECHAT_APP_ID'),                // 公众号 APPID
            'miniapp_id' => env('WECHAT_MINIAPP_ID'),        // 小程序 APPID
            'mch_id' => env('WECHAT_MCH_ID'),
            'key' => env('WECHAT_KEY'),
            'notify_url' => '',
//            'cert_client' => './cert/apiclient_cert.pem',   // optional，退款等情况时用到
//            'cert_key' => './cert/apiclient_key.pem',       // optional，退款等情况时用到
            'log' => [ // optional
                'file' => base_path('storage/logs/pay_wechat.log'),
                'level' => 'info',                          // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single',                         // optional, 可选 daily.
                'max_file' => 30,                           // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
                // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
            ],
            // 'mode' => 'dev', // optional, dev/hk;当为 `hk` 时，为香港 gateway。
        ];

        $this->config_alipay = [
            'app_id' => env('ALI_APP_ID'),
            'notify_url' => '',
            'return_url' => '',
            'ali_public_key' => env('ALI_PUBLIC_KEY'),
            // 加密方式： **RSA2**
            'private_key' => env('ALI_PRIVATE_KEY'),
            'log' => [ // optional
                'file' => base_path('storage/logs/pay_alipay.log'),
                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0
            ],
            # 'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
        ];

        $this->config_paymentwall = [
            'public_key' => env('PAYMENTALL_PUBLIC_KEY'),
            'private_key' => env('PAYMENTALL_PRIVATE_KEY')
        ];


    }

    private function generate_out_trade_no(){
        return date("YmdHisu"). mt_rand(100,999);
    }

    /**　收银台
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request){
        $requires = $this->inputs;
        $requires[] = 'user_id';
        $data = array_select_by_keys($request->all(),$requires);

        foreach ($this->inputs as $v){
            if(!(isset($data[$v]) && $data[$v])) return $this->error("1", "$v 必须");
        }
        $fee_types = [
            "CNY" => '￥',
            "USD" => '$'
        ];
        $data["fee_type_char"] =  $fee_types[$data['fee_type']];
        render_view('checkout.php',$data);
    }

    public function checkoutStatus(Request $request,$id){

        $row = DB::table("service_payments")->where('id',$id)->first();
        if(!$row) abort(404);
        $row = (array) $row;
        $fee_types = [
            "CNY" => '￥',
            "USD" => '$'
        ];
        $row["fee_type_char"] =  $fee_types[$row['fee_type']];
        $row["total_fee"] =  $row['total_fee']/100;
        if($request->ajax()){
            return $this->success(['status'=>$row['status']]);
        }else{
            render_view('checkout_status.php',$row);
        }
    }


    /** 微信手机网站支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function wechatWap(Request $request){

        $data = array_select_by_keys($request->all(),$this->inputs);
        foreach ($this->inputs as $v){
            if(!(isset($data[$v]) && $data[$v])) return $this->error("1", "$v 必须");
        }

        $out_trade_no = $this->generate_out_trade_no();
        $data['id'] = $out_trade_no;
        $data['user_id'] = $request->get("user")['uid'];
        $data['status'] = 0; //待支付
        $data['pay_type'] = 'wechat_wap';
        $data['created'] = date("Y-m-d H:i:s");
        DB::table("service_payments")->insert($data);

        $order = [
            'out_trade_no' => $out_trade_no,
            'total_fee' => (int)$data["total_fee"], // **单位：分**
            'body' => $data["order_title"]
        ];
        $config  = $this->config_wechat;
        $config['notify_url'] = url('payment/wechatNotify');
        $config['return_url'] = url('payment/mobile/checkoutStatus/' . $out_trade_no);
        Pay::wechat($config)->wap($order)->send();
        exit;
    }

    /**
     * 微信公众号支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function wechatMp(Request $request){

        //get openid
        $tool = new WxJsApiPay([
            "app_id"=>env('WECHAT_APP_ID'),
            "app_secret"=>env('WECHAT_KEY')
        ]);
        $openid = $tool->GetOpenid();

        $data = array_select_by_keys($request->all(),$this->inputs);
        foreach ($this->inputs as $v){
            if(!(isset($data[$v]) && $data[$v])) return $this->error("1", "$v 必须");
        }

        $out_trade_no = $this->generate_out_trade_no();
        $data['id'] = $out_trade_no;
        $data['user_id'] = $request->get("user")['uid'];
        $data['status'] = 0; //待支付
        $data['pay_type'] = 'wechat_mp';
        $data['created'] = date("Y-m-d H:i:s");
        DB::table("service_payments")->insert($data);

        $order = [
            'out_trade_no' => $out_trade_no,
            'total_fee' => (int) $data["total_fee"], // **单位：分**
            'body' => $data["order_title"],
            'openid' => $openid
        ];
        $config  = $this->config_wechat;
        $config['notify_url'] = url('payment/wechatNotify');
        $config['return_url'] = url('payment/mobile/checkoutStatus/' .$out_trade_no);
        $pay = Pay::wechat($config)->mp($order);
        $pay = (array) $pay;
        render_view('wechat_mp.php',["pay"=>$pay]);

    }

    /**
     * 微信手机APP支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function wechatApp(Request $request){

        $out_trade_no = $this->generate_out_trade_no();
        $order_id = $request->get('order_id');
        $order_title = $request->get('order_title');
        $total_fee = (int)$request->get('total_fee');
        $fee_type = $request->get('fee_type','CNY');

        $data['id'] = $out_trade_no;
        $data['user_id'] = $request->get("user")['uid'];
        $data['order_id'] = $order_id;
        $data['order_title'] = $order_title;
        $data['total_fee'] = $total_fee;
        $data['fee_type'] = $fee_type;
        $data['status'] = 0; //待支付
        $data['pay_type'] = 'wechat_app';
        $data['created'] = date("Y-m-d H:i:s");
        $data['notify_url'] = 'http://m20.tourscool.net/api/tour/v1/payment/notify';

        DB::table("service_payments")->insert($data);

        $order = [
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_fee,
            'body' => get_abbrev_str($order_title,40),
        ];
        $config  = $this->config_wechat;
        $config['notify_url'] = url('payment/wechatNotify');
        $orderInfo = Pay::wechat($config)->app($order)->getContent();
        $orderInfo = json_decode($orderInfo,true);
        return $this->success($orderInfo);

    }

    public function wechatPc(Request $request){


    }

    /**
     * 支付宝手机网站支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function alipayWap(Request $request){

        $data = array_select_by_keys($request->all(),$this->inputs);
        foreach ($this->inputs as $v){
            if(!(isset($data[$v]) && $data[$v])) return $this->error("1", "$v 必须");
        }

        $out_trade_no = $this->generate_out_trade_no();
        $data['id'] = $out_trade_no;
        $data['status'] = 0; //待支付
        $data['user_id'] = $request->get("user")['uid'];
        $data['pay_type'] = 'alipay_wap';
        $data['created'] = date("Y-m-d H:i:s");
        DB::table("service_payments")->insert($data);

        $total_fee = (int) $data['total_fee'];

        $order = [
            'out_trade_no' => $out_trade_no,
            'total_amount' => $total_fee/100,
            'subject' => $data["order_title"],
        ];
        $config  = $this->config_alipay;
        $config['notify_url'] = url('payment/alipayNotify');
        $config['return_url'] = url('payment/mobile/checkoutStatus/' . $out_trade_no);
        Pay::alipay($config)->wap($order)->send();
        exit;
    }

    /**
     * 支付宝手机APP支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function alipayApp(Request $request){

        $out_trade_no = $this->generate_out_trade_no();
        $order_id = $request->get('order_id');
        $order_title = $request->get('order_title');
        $total_fee = (int)$request->get('total_fee');
        $fee_type = $request->get('fee_type','CNY');

        $data['id'] = $out_trade_no;
        $data['user_id'] = $request->get("user")['uid'];
        $data['order_id'] = $order_id;
        $data['order_title'] = $order_title;
        $data['total_fee'] = $total_fee;
        $data['fee_type'] = $fee_type;
        $data['status'] = 0; //待支付
        $data['pay_type'] = 'alipay_app';
        $data['created'] = date("Y-m-d H:i:s");
        $data['notify_url'] = 'http://m20.tourscool.net/api/tour/v1/payment/notify';

        DB::table("service_payments")->insert($data);

        $order = [
            'out_trade_no' => $out_trade_no,
            'total_amount' => $total_fee/100,
            'order_id' => $order_id,
            'subject' => get_abbrev_str($order_title,40),
        ];

        $config  = $this->config_alipay;
        $config['notify_url'] = url('payment/alipayNotify');

        $orderInfo = Pay::alipay($config)->app($order)->getContent();
        return $this->success($orderInfo);

    }

    public function alipayPc(Request $request){


    }

    /**
     * paymentwall 支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentwallMobile(Request $request)
    {
        require base_path('vendor/paymentwall/paymentwall-php/lib/paymentwall.php');


        $data = array_select_by_keys($request->all(),$this->inputs);
        foreach ($this->inputs as $v){
            if(!(isset($data[$v]) && $data[$v])) return $this->error("1", "$v 必须");
        }

        $out_trade_no = $this->generate_out_trade_no();
        $data['id'] = $out_trade_no;
        $data['user_id'] = $request->get("user")['uid'];
        $data['status'] = 0; //待支付
        $data['pay_type'] = 'paymentwall';
        $data['created'] = date("Y-m-d H:i:s");
        DB::table("service_payments")->insert($data);

        #test
        $pubKey = env('PAYMENTALL_PUBLIC_KEY');
        $privateKey = env('PAYMENTALL_PRIVATE_KEY');

        \Paymentwall_Config::getInstance()->set(array(
            'api_type' => \Paymentwall_Config::API_GOODS,
            'public_key' => $pubKey,
            'private_key' => $privateKey
        ));

        $total_fee = (int)$data['total_fee'];

        $widget = new \Paymentwall_Widget(
            $data['user_id'],         // user_id
            'p1_1',          // p1_1  m2_1 widget code
            array(
                new \Paymentwall_Product(
                    $data['order_id'],               // id of the product in your system
                    $data['total_fee']/100,           // price
                    $data['fee_type'],                      // currency code
                    $data['order_title'],                          // product name
                    \Paymentwall_Product::TYPE_FIXED
                )
            ),
            array(
                'pingback_url' => url('payment/paymentwallNotify'),
                'success_url'  => url('payment/mobile/checkoutStatus/' . $out_trade_no),//支付成功跳转链接
                'failure_url'  => $data['failure_url'],//支付失败跳转链接
                'total_amount'=>  $data['total_fee'],
                'currency_code'=> $data['fee_type'],
                'transaction_id'=> $out_trade_no,
                'test_mode' =>1,
                // 'ps' => 'test',
            )// additional parameters
        );
        echo $widget->getHtmlCode();
        exit;
    }

    public function wechatNotify(Request $request)
    {

        Log::debug('Wechat raw notify -------------->\n' .  $request->getContent());

        $pay = Pay::wechat($this->config_wechat);

        try{
            $data = $pay->verify();
            $resp= $data->all();
            $id =  $resp['out_trade_no'];
            $transaction_id = $resp['transaction_id'];

            if($resp['result_code'] == 'SUCCESS' && $resp['return_code']=='SUCCESS'){
                $new_status = 1; //成功
            }else{
                $new_status = 2;
            }

            Log::debug('Wechat notify -------------->', $data->all());

            DB::transaction(function () use ($id,$new_status,$transaction_id,$pay) {
                $row = DB::table('service_payments')->where('id',$id)->lockForUpdate()
                    ->select('id','order_id','notify_url','pay_type','status')->first();
                if(!$row){
                    throw new \Exception("payment id is wrong");
                }
                // 状态:0待支付,1支付成功,2,支付失败
                if($row->status !=0){
                    //重复通知
                    return '';
                }

                $info =[
                    'order_id' => $row->order_id,
                    'status' => $new_status,
                    'transaction_id' => $transaction_id,
                    'pay_type' => $row->pay_type,
                ];

                $this->notifyClient($row->notify_url,$info);

                $updates=[
                    'status' => $new_status,
                    'transaction_id' => $transaction_id,
                    'updated' => date("Y-m-d H:i:s"),
                ];
                DB::table('service_payments')->where('id',$id)->update($updates);

            });

        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $pay->success();

    }

    public function alipayNotify(Request $request)
    {

        Log::debug('Alipay raw notify -------------->\n' . $request->getContent());

        $pay = Pay::alipay($this->config_alipay);

        try{
            $data = $pay->verify();
            $resp= $data->all();
            $id =  $resp['out_trade_no'];
            $transaction_id = $resp['trade_no'];

            if($resp['trade_status'] == 'TRADE_SUCCESS' || $resp['trade_status']=='TRADE_FINISHED'){
                $new_status = 1; //成功
            }else{
                $new_status = 2;
            }

            Log::debug('Alipay notify -------------->', $data->all());

            DB::transaction(function () use ($id,$new_status,$transaction_id,$pay) {
                $row = DB::table('service_payments')->where('id',$id)->lockForUpdate()
                    ->select('id','order_id','notify_url','pay_type','status')->first();
                if(!$row){
                    throw new \Exception("payment id is wrong");
                }
                // 状态:0待支付,1支付成功,2,支付失败
                if($row->status !=0){
                    //重复通知
                    return '';
                }

                $info =[
                    'order_id' => $row->order_id,
                    'status' => $new_status,
                    'transaction_id' => $transaction_id,
                    'pay_type' => $row->pay_type,
                ];

                $this->notifyClient($row->notify_url,$info);

                $updates=[
                    'status' => $new_status,
                    'transaction_id' => $transaction_id,
                    'updated' => date("Y-m-d H:i:s"),
                ];
                DB::table('service_payments')->where('id',$id)->update($updates);

            });

        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $pay->success();

    }

    public function paymentwallNotify(Request $request)
    {

        require base_path('vendor/paymentwall/paymentwall-php/lib/paymentwall.php');

        $pubKey = env('PAYMENTALL_PUBLIC_KEY');
        $privateKey = env('PAYMENTALL_PRIVATE_KEY');

        \Paymentwall_Config::getInstance()->set(array(
            'api_type' => \Paymentwall_Config::API_GOODS,
            'public_key' => $pubKey,
            'private_key' => $privateKey
        ));

        $pingback = new \Paymentwall_Pingback($_GET, $_SERVER['REMOTE_ADDR']);

        Log::debug('paymentwall notify -------------->', $_GET);

        if ($pingback->validate()) {
            if (! $pingback->isDeliverable()) return "FAIL";

            $id =  $_GET['transaction_id'];
            $transaction_id = $_GET['ref']; //paymentwall 交易号
            $new_status = 1; //成功

            try{
                DB::transaction(function () use ($id,$new_status,$transaction_id) {
                    $row = DB::table('service_payments')->where('id',$id)->lockForUpdate()
                        ->select('id','order_id','notify_url','pay_type','status')->first();
                    if(!$row){
                        throw new \Exception("payment id is wrong");
                    }
                    // 状态:1待支付,2支付成功,3,支付失败
                    if($row->status !=0){
                        //重复通知
                        return '';
                    }

                    $info =[
                        'order_id' => $row->order_id,
                        'status' => $new_status,
                        'transaction_id' => $transaction_id,
                        'pay_type' => $row->pay_type,
                    ];

                    $this->notifyClient($row->notify_url,$info);

                    $updates=[
                        'status' => $new_status,
                        'transaction_id' => $transaction_id,
                        'updated' => date("Y-m-d H:i:s"),
                    ];
                    DB::table('service_payments')->where('id',$id)->update($updates);

                });
            }catch (\Exception $e) {
                return $e->getMessage();
            }
            return 'OK'; // Paymentwall expects response to be OK, otherwise the pingback will be resent
        } else {
            return $pingback->getErrorSummary();
        }

    }

    private function notifyClient($url,$data){

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $url, [
            'json' => [
                'order_id'=>$data['order_id'],
                'trade_no'=>$data['transaction_id'],
                "payment_type"=>$data['pay_type'],
                "status"=>$data['status'],
            ]
        ]);
        $resp = json_decode($res->getBody(),true);
        $code = $resp['code'] ?? 1;
        if($code != 0){
            throw new \Exception('client process failed');
        }

    }

}
