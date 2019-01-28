<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * 支付微服务 客户端示范
 * Class PaymentController
 * @package App\Http\Controllers
 */
class PaymentExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function success_url(Request $request){
        $data = $request->all();
        print_r($data);
        return;
    }

    public function notify_url(Request $request){
        $data = $request->all();
        Log::debug("notify info:",$data);
        return $this->success($data);
    }


    public function order(Request $request){

        $order_id  = mt_rand(1000,9999);
        $data = [
            'url_root' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'],
            'user_id' => mt_rand(100,999),
            'order_id' => $order_id,
            'order_title' => "测试订单　$order_id" ,
            'total_fee' => 1 ,
        ];
        render_view('example/order.php',$data);

    }

}
