<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $rows = DB::table("houses")->select("*")->where($conditions)
            ->orderBy($orderby,$order)->paginate($page_size)->toArray();

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

        $list = $rows['data'];
        foreach ($list as $k=>$v){

            foreach ($arr_cols as $_v){
                $v->{$_v} = $v->{$_v} ? explode(',',$v->{$_v}) : [];
            }
            $v->features = json_decode($v->features) ?? [];
            $v->beds = json_decode($v->beds) ?? [];
            $v->refund_rules = json_decode($v->refund_rules) ?? [];
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

    public function create(Request $request)
    {
        $whitelist = [
            "title",
            "description",
            "features",
            "landlord_id",
            "steward_id",
            "country_code",
            "prov_code",
            "city_code",
            "address",
            "address_number",
            "lng",
            "lat",
            "floors",
            "rooms",
            "halls",
            "kitchens",
            "verandas",
            "areas",
            "persons",
            "beds",
            "weight",
            "house_attr",
            "decorate_attr",
            "use_attrs",
            "spot_id",
            "basic_attrs",
            "bath_attrs",
            "kitchen_attrs",
            "play_attrs",
            "service_attrs",
            "bedroom_images",
            "hall_images",
            "kitchen_images",
            "environment_images",
            "price_basic",
            "price_weekend",
            "price_fest",
            "fee_plus",
            "max_plus",
            "gain_rate",
            "extra_info",
            "deposit",
            "checkin_time",
            "checkout_time",
            "service_open_time",
            "service_close_time",
            "refund_rules",
            "status",
        ];
        $row = array_select_by_keys($request->all(),$whitelist);

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
            if(isset($row[$v])){
                $row[$v] = implode(',',$row[$v]);
            }
        }

        # columns pass by json object;
        $obj_cols=[
            "features",
            "beds",
            "refund_rules"
        ];

        foreach ($obj_cols as $v){
            if(isset($row[$v])){
                $row[$v] = json_encode($row[$v],JSON_UNESCAPED_UNICODE);
            }
        }

        $row['created'] = date("Y-m-d H:i:s");
        $row['updated'] = date("Y-m-d H:i:s");

        if(!isset($row['landlord_id'])){
            $this->error(1,"房东必须");
        }

        DB::table("houses")->insert($row);

        return $this->success();

    }

    public function update(Request $request,$id)
    {
        $whitelist = [
            "title",
            "description",
            "features",
            "landlord_id",
            "steward_id",
            "country_code",
            "prov_code",
            "city_code",
            "address",
            "address_number",
            "lng",
            "lat",
            "floors",
            "rooms",
            "halls",
            "kitchens",
            "verandas",
            "areas",
            "persons",
            "beds",
            "weight",
            "house_attr",
            "decorate_attr",
            "use_attrs",
            "spot_id",
            "basic_attrs",
            "bath_attrs",
            "kitchen_attrs",
            "play_attrs",
            "service_attrs",
            "bedroom_images",
            "hall_images",
            "kitchen_images",
            "environment_images",
            "price_basic",
            "price_weekend",
            "price_fest",
            "fee_plus",
            "max_plus",
            "gain_rate",
            "extra_info",
            "deposit",
            "checkin_time",
            "checkout_time",
            "service_open_time",
            "service_close_time",
            "refund_rules",
            "status",
        ];
        $row = array_select_by_keys($request->all(),$whitelist);

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
            if(isset($row[$v])){
                $row[$v] = implode(',',$row[$v]);
            }
        }

        # columns pass by json object;
        $obj_cols=[
            "features",
            "beds",
            "refund_rules"
        ];

        foreach ($obj_cols as $v){
            if(isset($row[$v])){
                $row[$v] = json_encode($row[$v],JSON_UNESCAPED_UNICODE);
            }
        }

        $row['updated'] = date("Y-m-d H:i:s");

        if(!$row){
            $this->error(1,"修改数据为空");
        }

        DB::table("houses")->where("id",$id)->update($row);
        return $this->success();

    }


    //
}
