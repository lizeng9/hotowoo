<?php
/**
 * Created by PhpStorm.
 * User: FXphp
 * Date: 2018/12/17
 * Time: 16:49
 */

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class OrderController extends Controller
{
    /**
     * 根据订单id获取订单详情
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderById(int $id){
        $row = app('db')->table('orders')->leftJoin('order_source', 'order_source.id', '=', 'orders.source_id')->select("orders.*",'order_source.name')->where('orders.id','=',$id)->first();
        return $this->success($row);
    }

    /**
     * 新增订单备注
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrderComment(Request $request){
        $user = $request->get('user',null);
        $row['order_id'] = $request->get('order_id',null);
        $row['comment'] = $request->get('comment',null);
        $row['status'] = 0;
        $row['created'] = date('Y-m-d');
        $row['operator_id'] = $user['uid'];
        app('db')->table('order_status')->insert($row);
        return $this->success();
    }

    public function createOrder(Request $request){
        $user = $request->get('user',null);
        $row['source_id'] = $request->get('source_id',null);
        $row['customer_name'] = $request->get('customer_name',null);
        $row['customer_mobile'] = $request->get('customer_mobile',null);
        $row['house_id'] = $request->get('house_id',null);
        $row['start_date'] = $request->get('start_date',null);
        $row['end_date'] = $request->get('end_date',null);
        $row['income'] = $request->get('income',null);
        $row['source_fee'] = $request->get('source_fee',null);
        $row['status'] = 1;
        $row['operator_id'] = $user['uid'];
        $row['created'] = date('Y-m-d');
        $row['comment'] = $request->get('comment',null);
        if(in_array(null,$row)){
            return $this->error(1,'上传数据不完整');
        }
        if(strtotime($row['start_date'])>strtotime($row['end_date'])){
            return $this->error(1,'开始时间不能大于结束时间');
        }
        try{
            app('db')->transaction(function () use ($row) {
                $comment = $row['comment'];
                unset($row['comment']);
                $order_id = app('db')->table('orders')->insertGetId($row);
                $order_status = [
                    'order_id'=>$order_id,
                    'status'=>0,
                    'comment'=>$comment,
                    'operator_id'=>$row['operator_id'],
                    'created'=>date('Y-m-d')
                ];
                app('db')->table('order_status')->insert($order_status);
                while (strtotime($row['start_date'])<strtotime($row['end_date'])) {
                    $house_date = [
                        'house_id'=>$row['house_id'],
                        'order_date'=>$row['start_date']
                    ];
                    app('db')->table('order_house_date')->insert($house_date);
                    $row['start_date'] = date("Y-m-d",strtotime("+1 day",strtotime($row['start_date'])));
                }

            });
        }catch(\Illuminate\Database\QueryException $e){
            $errorCode = $e->errorInfo[1];
            if($errorCode == '1062'){
                return $this->error(1,'该段时间内此房间已售出，请重新筛选时间');
            }
        }
        return $this->success();
    }
}