<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SpotController extends Controller
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
            $conditions[] = ['name','like',"%$keyword%"];
        }

        print_r($conditions);
        $rows = DB::table("spots")->select("*")->where($conditions)
          ->orderBy($orderby,$order)->paginate($page_size)->toArray();

        return $this->success($rows['data'],format_pagination($rows));

    }

    public function read(Request $request,$id)
    {

        $row = DB::table("spots")->select("*")->where('id',$id)->first();

        return $this->success($row);

    }

    public function create(Request $request)
    {
        $whitelist = [
            'country_code','prov_code','city_code','name','introduce',
            'image_url','weight'
        ];
        $row = array_select_by_keys($request->all(),$whitelist);

        $row['created'] = date("Y-m-d H:i:s");

        if(!isset($row['name'])){
            $this->error(1,"名称必须");
        }

        DB::table("spots")->insert($row);

        return $this->success();

    }

    public function update(Request $request,$id)
    {
        $whitelist = [
            'country_code','prov_code','city_code','name','introduce',
            'image_url','weight'
        ];
        $row = array_select_by_keys($request->all(),$whitelist);

        if(!$row){
            $this->error(1,"修改数据为空");
        }

        DB::table("spots")->where("id",$id)->update($row);
        return $this->success();

    }

    public function delete($id)
    {

        DB::table("spots")->where("id",$id)->delete();

        return $this->success();

    }

    /**
     * 房态管理列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpotState(Request $request){
        $country_code = $request->get('country_code',null);
        $prov_code = $request->get('prov_code',null);
        $city_code = $request->get('city_code',null);
        $key_words = $request->get('key_words',null);
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
        if($key_words){
            $conditions[] = ['name','like','%'.$key_words.'%'];
        }
        $rows = app('db')->table('spots')->select('*')->where($conditions)->orderBy("created","desc")->paginate(2)->toArray();
        foreach ($rows['data'] as $key=>$vo){
            //获取区域名
            $rows['data'][$key]->country_code = app('db')->table('areas')->where('code',$vo->country_code)->value('name');
            $rows['data'][$key]->prov_code = app('db')->table('areas')->where('code',$vo->prov_code)->value('name');
            $rows['data'][$key]->city_name = app('db')->table('areas')->where('code',$vo->city_code)->value('name');
            //获取该景点下房间入住情况
            //今日入住房间量
            $rows['data'][$key]->house_online_count = app('db')->table('orders')->leftJoin('houses', 'houses.id', '=', 'orders.house_id')->where(['orders.status'=>1 ,'houses.spot_id'=>$vo->id])->where('orders.start_date','<=',date('Y-m-d'))->where('orders.end_date','>',date('Y-m-d'))->count();
            //获取该景点总房间数
            $house_count = app('db')->table('houses')->where('spot_id',$vo->id)->count();
            //剩余房间数
            $rows['data'][$key]->house_surplus_count = $house_count-$rows['data'][$key]->house_online_count;
        }
        return $this->success($rows['data'],format_pagination($rows));
    }

    /**
     * 房态详情
     * @param  $spot_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpotStateInfo(int $spot_id,Request $request){
        $key_words = $request->get('key_words',null);
        $conditions = [];
        if($key_words){
            $conditions[] = ['title','like','%'.$key_words.'%'];
        }
        $month = $request->get('time',date('Y-m'));
        $day_count = date("t",strtotime($month));
        $spot = app('db')->table('houses')->select('id','title','address')->where($conditions)->where('spot_id','=',$spot_id)->get();
        $order = [];
        for ($i=0;$i<count($spot);$i++){
            $star_date = $month.'-1';
            $end_date =  date("Y-m-d",strtotime("+1 day",strtotime($month.'-'.$day_count)));
            $result = app('db')->table('orders')->select('orders.id','orders.customer_name','order_source.name','orders.start_date','orders.end_date')->leftJoin('order_source', 'order_source.id', '=', 'orders.source_id')->where(['orders.status'=>1 ,'orders.house_id'=>$spot[$i]->id])->where('orders.start_date','>=',$star_date)->where('orders.end_date','<=',$end_date)->get();
            array_push($order,$result);
        }
        $rows = ['row'=>$spot,'column'=>[]];
        for($i=1;$i<=$day_count;$i++){
            $date = $month.'-'.$i;
            $week = date('w',strtotime($date));
            $week_cn = array('日','一','二','三','四','五','六');
            $temp = ['d'=>$i,'week'=>$week_cn[$week],'data'=>[]];
            foreach ($order as $vo){
                $temp_data = [];
                if(!empty($vo)){
                    foreach ($vo as $v1){
                        if(strtotime($date)>=strtotime($v1->start_date) && strtotime($date)<strtotime($v1->end_date)){
                            $temp_data = $v1;
                            break;
                        }
                    }
                }
                array_push($temp['data'],$temp_data);
            }
            array_push($rows['column'],$temp);
        }
        return $this->success($rows);
    }

    //
}
