<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class HouseController extends Controller
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

    public function index(Request $request)
    {
        $page_size = $request->get("page_size","10");
        $country_code = $request->get("country_code","");
        $prov_code = $request->get("prov_code","");
        $city_code = $request->get("city_code","");
        $keyword = $request->get("keyword","");
        $orderby = $request->get("orderby","created");
        $order = $request->get("order","desc");

        $conditions = [];
        if($country_code){
            $conditions[] = ['country_code','=',$country_code];
        }
        if($prov_code){
            $conditions[] = ['prov_code','=',$prov_code];
        }
        if($city_code){
            $conditions[] = ['city_code','=',$city_code];
        }
        if($keyword){
            $conditions[] = ['title','like',"%$keyword%"];
            $conditions[] = ['description','like',"%$keyword%"];
        }

        $rows = DB::table("houses")->select('title','persons','beds','price_basic','bedroom_images')->where($conditions)
            ->orderBy($orderby,$order)->paginate($page_size)->toArray();


        $list = $rows['data'];
        foreach ($list as $k=>$v){
            $v->beds = json_decode($v->beds) ?? [];
            $v->bedroom_images = $v->bedroom_images ? explode(',',$v->bedroom_images) : [];
            $list[$k] = $v;
        }

        return $this->success($rows['data'],format_pagination($rows));

    }

    public function read(Request $request,$id)
    {

        $row = DB::table("houses")->select("*")->where("id",$id)->first();
        $row = (array) $row;
        # columns pass by json array;
        $arr_cols=[
            "use_attrs",
            "basic_attrs",
            "bath_attrs",
            "kitchen_attrs",
            "play_attrs",
            "service_attrs",
            "bedroom_images",
            "hall_images",
            "kitchen_images",
            "environment_images",
        ];

        foreach ($arr_cols as $v){
            $row[$v] = $row[$v]  ? explode(',',$row[$v]) : [];
        }

        # columns pass by json object;
        $obj_cols=[
            "features",
            "beds",
            "refund_rules"
        ];
        foreach ($obj_cols as $v){
            if(isset($row[$v])){
                $row[$v] = json_decode($row[$v]) ?? [];
            }
        }

        return $this->success($row);

    }


    /**
     * 根据房间id获取该房间的日历
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function houseDate(int $id){
        //查询该房间默认价格
        $house = app('db')->table('houses')->select('id','price_basic','price_weekend','price_fest')->where('id',$id)->first();
        if($house==null){
            return $this->error(1,'该房间不存在');
        }
        //查询假日时间
        $star_date = date('Y-m-1');
        $end_date = date('Y-m-d',strtotime('+6 month -1 day'));
        $holiday_date = app('db')->table('fests')->where('start_date','<=',$end_date)->where('end_date','>=',$star_date)->get();
        //查询特价
        $special_price = app('db')->table('house_prices')->where('house_id',$id)->get();
        //查询该房间订单
        $order = app('db')->table('orders')->select('start_date','end_date')->where('house_id','=',$id)->where('status','=',1)->where('start_date','<=',$end_date)->where('end_date','>',$star_date)->get();
        $month_key = trim(date('Y-m'));
        $row = [];
        for($i=1;$i<6;$i++){
            $row["$month_key"] = [];
            $day_count = date("t",strtotime($month_key));
            for ($j=1;$j<=$day_count;$j++){
                $date = $month_key.'-'.$j;
                $week = date('w',strtotime($date));
                $price = ($week=='5' or $week=='6')?$house->price_weekend:$house->price_basic;
                //匹配假日价格
                foreach ($holiday_date as $vo){
                    if(strtotime($date)>=$vo->start_date && strtotime($date)<=$vo->end_date){
                        $price = $house->price_fest;
                        break;
                    }
                }
                //匹配特价
                foreach ($special_price as $v1){
                    if(strtotime($date)== strtotime($v1->date)){
                        $price = $v1->price;
                    }
                }
                //判断是否可预定
                $status = true;
                if(strtotime($date)<strtotime(date('Y-m-d'))){
                    $status = false;
                }else{
                    foreach ($order as $v2){
                        if(strtotime($date)>=strtotime($v2->start_date) && strtotime($date)<strtotime($v2->end_date)){
                            $status = false;
                            break;
                        }
                    }
                }
                $arr = [$j=>[
                    'price'=>floatval($price),
                    'status'=>$status,
                    'week'=>$week,
                    'is_holiday'=>false
                ]];
                array_push($row["$month_key"],$arr);
            }
            $month_key = trim(date('Y-m ',strtotime("$month_key +1 month")));
        }
        return $this->success($row);
    }


    //
}
