<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public $unpaid = ['code'=>0,'name'=>'待支付'];
    public $wait = ['code'=>1,'name'=>'未出行'];
    public $finish = ['code'=>2,'name'=>'已出行'];
    public $cancel = ['code'=>3,'name'=>'已取消'];
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 订单列表
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(int $id ,Request $request)
    {
        $type = $request->get('type',null);
        if($type==='tour'){
            $rows = $this->getTourOrder($id);
        }else if($type==='woo'){
            $rows = $this->getWooOrder($id);
        }else{
            $tour = $this->getTourOrder($id);
            $woo = $this->getWooOrder($id);
            $rows = array_merge($tour,$woo);
            array_multisort(array_column($rows,'created'),SORT_DESC,$rows);
        }
        $status = $request->get('status',null);
        if($status!=null){
            foreach ($rows as $key=>$vo){
                switch ($status){
                    case 'unpaid':
                        if($vo->status!=$this->unpaid){
                            unset($rows[$key]);
                        }
                        break;
                    case 'wait':
                        if($vo->status!=$this->wait){
                            unset($rows[$key]);
                        }
                        break;
                    case 'finish':
                        if($vo->status!=$this->finish){
                            unset($rows[$key]);
                        }
                        break;
                    case 'cancel':
                        if($vo->status!=$this->cancel){
                            unset($rows[$key]);
                        }
                        break;
                }
            }
        }
        return $this->success(array_values($rows));
    }

    /**
     * 民宿订单
     * @param $id
     * @return mixed
     */
    private function getWooOrder($id){
        $rows = app('db')->table('orders')->leftJoin('houses', 'houses.id', '=', 'orders.house_id')->select('orders.id','orders.status','orders.start_date','orders.end_date','orders.income','orders.house_id','houses.title','houses.bedroom_images','orders.created')->where('orders.customer_id','=',$id)->get()->toArray();
        foreach ($rows as $key=>$vo){
            if($vo->status==1){
                if(strtotime($vo->start_date)>time()){
                    $rows[$key]->status = $this->wait;
                }else{
                    $rows[$key]->status = $this->finish;
                }
            }else if($vo->status==2){
                $rows[$key]->status = $this->cancel;
            }else{
                $rows[$key]->status = $this->unpaid;
            }
            $rows[$key]->bedroom_images = explode(',',$vo->bedroom_images)[0];
            $rows[$key]->income =  '￥'.$vo->income;
            $rows[$key]->type = 'woo';
            $rows[$key]->day = (strtotime($vo->end_date) - strtotime($vo->start_date))/ 86400;
        }
        return $rows;
    }

    /**
     * 获取稀饭订单
     * @param $id
     * @return mixed
     */
    private function getTourOrder($id){
        $sql = "SELECT
	o.order_id,
	o.created,
	o.status,
	ot.value AS order_total,
	osd.NAME AS status_name,
	o.us_to_cny_rate
FROM
	`order` AS o,
	`order_total` AS ot,
	`order_status` AS os,
	order_status_description AS osd
WHERE
	o.customer_id = {$id}
AND o.order_id = ot.order_id
AND ot.class = 'ot_total'
AND os.order_status_id = osd.order_status_id
AND o. STATUS = os.order_status_id
AND osd.language_id = 3 
ORDER BY o.created DESC";
        $rows = app('db')->connection('mysql_tour')->select($sql);
        foreach ($rows as $key=>$vo){
            $product = app('db')->connection('mysql_tour')->table('order_product')->select('product_id','product_name','product_code','product_departure_date')->where('order_id','=',$vo->order_id)->get()->toArray();
            if(empty($product)){
                unset($rows[$key]);
                continue;
            }
            $rows[$key]->product_id = $product[0]->product_id;
            $rows[$key]->product_name = $product[0]->product_name;
            $rows[$key]->code = $product[0]->product_code;
            $image = app('db')->connection('mysql_tour')->table('product')->where('product_id','=',$product[0]->product_id)->value('image');
            $rows[$key]->image = getThumbName($image,800,800);
            //判断订单状态
            $rows[$key]->status = $this->checkOrderStatus($vo->order_id,$vo->status,$product);
            $rows[$key]->type = 'tour';

            $rows[$key]->usd_price = $vo->order_total;
            $rows[$key]->cny_price = round(floatval($vo->order_total) * floatval($vo->us_to_cny_rate),2);

            $rows[$key]->order_total = '$'.$vo->order_total;
            //获取产品出发日期
            $rows[$key]->product_departure_date = app('db')->connection('mysql_tour')->table('order_product')->where('product_id','=',$vo->product_id)->value('product_departure_date');

        }
        return $rows;
    }

    /**
     * 订单状态
     * @param  $id
     * @param $status
     * @param $product
     * @return int
     */
    private function checkOrderStatus(int $id ,int $status,$product){
        //可以在我的海行中再次支付的订单状态ID
        $again_id = app('db')->connection('mysql_tour')->table('configuration')->where('key','=','MODULE_PAYMENT_CAN_BE_PAY_IN_MY_ACCOUNT')->value('value');
        $again_id = array_map('intval', explode(',', $again_id));

        //已经成功支付的订单状态ID
        $success_id = app('db')->connection('mysql_tour')->table('configuration')->where('key','=','SUCCESS_HAS_BEEN_PAID')->value('value');
        $success_id = array_map('intval', explode(',', $success_id));
        $success = app('db')->connection('mysql_tour')->table('order_status_history')->where('order_id','=',$id)->whereIn('order_status_id',$success_id)->count();

        //已经取消的订单状态ID
        $cancel_id = app('db')->connection('mysql_tour')->table('configuration')->where('key','=','ORDER_CANCELED_STATUS')->value('value');
        $cancel_id = array_map('intval', explode(',', $cancel_id));
        $cancel = app('db')->connection('mysql_tour')->table('order_status_history')->where('order_id','=',$id)->whereIn('order_status_id',$cancel_id)->count();
        if (in_array($status, $again_id) && $success==0 && $cancel==0) {
            return $this->unpaid;
        }
        if($cancel>0){
            return $this->cancel;
        }
        if($success>0 && $cancel==0){
            foreach ($product as $vo){
                if(strtotime($vo->product_departure_date)>time()){
                    return $this->wait;break;
                }
                return $this->finish;
            }
        }

    }


    /**
     * 稀饭订单详情
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderTour(int $id){
        $order = app('db')->connection('mysql_tour')->table('order')->select('order_id','created','status')->where('order_id','=',$id)->first();
        $order_total = app('db')->connection('mysql_tour')->table('order_total')->where('order_id','=',$id)->where('class','=','ot_total')->value('text');
        $order->price = strip_tags($order_total);
        $sql = "SELECT
	op.product_id,
	op.product_name,
	op.total_room_adult_child_info,
	p.duration,
	p.duration_type,
	op.product_departure_date,
	op.order_product_id
FROM
	product p,
	order_product op
WHERE
	op.order_id = {$id}
AND p.product_id = op.product_id
ORDER BY
	op.product_departure_date ASC,
	op.sort_order ASC";
        $product = app('db')->connection('mysql_tour')->select($sql);
        $order->status = $this->checkOrderStatus($order->order_id,$order->status,$product);
        if(empty($product)){
            return $this->error(1,'无效订单');
        }
        $product = $product[0];
        $total_rooms = explode('###',$product->total_room_adult_child_info);
        $total_rooms = array_filter($total_rooms);

        $order->ault_count = 0;
        $order->child_count = 0;
        if($total_rooms[0] > 0){
            for($i=1;$i<count($total_rooms);$i++){
                $people = explode('!!',$total_rooms[$i]);
                $order->ault_count+=$people[0];
                $order->child_count+=$people[1];
            }
        }else{
            $people = explode('!!',$total_rooms[1]);
            $order->ault_count+=$people[0];
            $order->child_count+=$people[1];
        }


        $order->product_name = $product->product_name;
        $order->product_id = $product->product_id;
        $product_departure_city = app('db')->connection('mysql_tour')->table('product_departure_city')->leftJoin('tour_city_description','tour_city_description.tour_city_id','=','product_departure_city.tour_city_id')->where('product_departure_city.product_id','=',$product->product_id)->where('tour_city_description.language_id','=',3)->pluck('tour_city_description.name')->toArray();
        $order->product_departure_city = implode('/',$product_departure_city);
        $product_departure_end_city = app('db')->connection('mysql_tour')->table('product_departure_end_city')->leftJoin('tour_city_description','tour_city_description.tour_city_id','=','product_departure_end_city.tour_city_id')->where('product_departure_end_city.product_id','=',$product->product_id)->where('tour_city_description.language_id','=',3)->pluck('tour_city_description.name')->toArray();
        $order->product_departure_end_city = implode('/',$product_departure_end_city);
        switch ($product->duration_type){
            case 0:
                $product->duration .= 'day';
                break;
            case 1:
                $product->duration .= 'hours';
                break;
            case 2:
                $product->duration .= 'minute';
                break;
        }
        $order->product_departure_date = date('Y-m-d',strtotime($product->product_departure_date));
        $order->product_end_date = date('Y-m-d', strtotime ("+ {$product->duration}", strtotime($order->product_departure_date)));
        $order->attribute = app('db')->connection('mysql_tour')->table('order_product_attribute')->select('product_option','product_option_value')->where('order_id','=',$id)->where('order_product_id','=',$product->order_product_id)->get()->toArray();

        $eticket = app('db')->connection('mysql_tour')->table('order_product_eticket')->where('order_id','=',$id)->where('order_product_id','=',$product->order_product_id)->select('guest_name','guest_email')->first();
        //顾客信息
        $guestsNamesDb = explode('<::>', $eticket->guest_name);
        $guestsNamesDb = array_filter($guestsNamesDb);
        $guest = [];
        foreach ($guestsNamesDb as $key1=>$vo1){
            $guest[$key1]['name'] = explode('||', $vo1)[0];
        }
        //联系人
        $guestsEmailDb = explode('<::>', $eticket->guest_email);
        $guestsEmailDb = array_filter($guestsEmailDb);
        foreach ($guestsEmailDb as $key2=>$vo2){
            $email_phone =  explode('|##|', $vo2);
            $email_phone = array_filter($email_phone);
            $guest[$key2]['email'] = $email_phone[0]??'';
            $guest[$key2]['phone'] = $email_phone[2]??'';
        }
        if(empty($guestsEmailDb)){
            $contact_way = ['','',''];
        }else{
            $contact_way = explode('|##|', $guestsEmailDb[0]);
            $contact_way = array_filter($contact_way);
        }

        $order->contact_email = $contact_way[0]??'';
        $country_id = $contact_way[1]??'';
        $order->contact_country = app('db')->connection('mysql_tour')->table('country_description')->where('country_id','=',$country_id)->where('language_id','=',3)->value('name');
        $order->contact_phone = $contact_way[2]??'';
        $order->contact_name = app('db')->connection('mysql_tour')->table('order')->where('order_id','=',$order->order_id)->value('contact_name');
        $order->guest_name = $guest;
        //订单明细
        $order->detailed = app('db')->connection('mysql_tour')->table('order_total')->select('title','value','class')->where('order_id','=',$id)->get()->toArray();
        //获取订单人名币价格
        $currency = app('db')->connection('mysql_tour')->table('currency')->where('code','=','CNY')->value('xchg_rate');
        if($currency!=null){
            $amount=0;
            foreach ($order->detailed as $vo){
                if($vo->class=='ot_total'){
                    $amount = $vo->value;
                    break;
                }
            }
            $order->usd_price = $amount;
            $order->cny_price = round(floatval($amount) * floatval($currency),2);
        }
        return $this->success($order);
    }

    /**
     * 获取民宿顶订单详情
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderWoo(int $id){
        $order = app('db')->table('orders')->leftJoin('houses', 'houses.id', '=', 'orders.house_id')->leftJoin('landlords', 'houses.landlord_id', '=', 'landlords.user_id')->select('houses.id','houses.title','orders.house_id','landlords.real_name','orders.start_date','orders.end_date','houses.persons','orders.customer_name','orders.customer_mobile','orders.basic_fee','orders.basic_fee','orders.guest_number','houses.fee_plus','orders.deposit','orders.plus_fee','orders.discount','orders.income','orders.status','orders.created','houses.bedroom_images')->where('orders.id','=',$id)->first();
        $order->payment_date = app('db')->table('order_status')->where('order_id','=',$id)->where('status','=',1)->value('created');
        $order->people = app('db')->table('order_people')->where('order_id',$id)->get()->toArray();
        $order->day = (strtotime($order->end_date) - strtotime($order->start_date))/ 86400;
        $order->start_date = date('Y年m月d日',strtotime($order->start_date));
        $order->end_date = date('Y年m月d日',strtotime($order->end_date));
        $order->bedroom_images = explode(',',$order->bedroom_images)[0];
        return $this->success($order);
    }


}
