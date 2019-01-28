<?php

namespace App\Services\Admin;

class UserService
{
    /**
     * 根据管理员id获取管理员详细资料
     * @param $id
     * @return mixed
     */
    public static function getAdminById(int $id)
    {
        $rows = app('db')->table("users")->select("*")->where('id',$id)->where('gids', 'like', '%0%')->first();
        if(!empty($rows)){
            //获取稀饭用户登陆账号
            $customer = app('db')->connection('mysql_tour')->table("customer")->select('phone','email')->where('customer_id',$id)->first();
            if(!empty($customer)){
                $rows->account = $customer->phone ?? $customer->email;
            }
            //获取用户权限
            $permissions = app('db')->connection('mysql')->table('permissions')->select('*')->whereIn( 'id' , explode(',',$rows->permissions) )->get();
            $rows->permissions = [];
            foreach ($permissions as $vo){
                array_push($rows->permissions , [ 'id'=>$vo->id ,'name'=>$vo->name ] );
            }
            //获取用户管辖区域
            $area = explode(',' , $rows->area_city_codes);
            $rows->area_city_codes = [];
            foreach ($area as $vo){
                $result = get_area($vo);
                $area_name = '';
                foreach ($result as $vo1){
                    $area_name.=$vo1->name.' ';
                }
                //获取该城市下的房源个数
                $house_count = app('db')->table('houses')->where(['city_code'=>$vo , 'status'=>1])->count();
                array_push($rows->area_city_codes ,['area_name'=>$area_name , 'house_count'=>$house_count] );
            }
        }
        return $rows;
    }

    /**
     * 验证稀饭旅行网账号，若存在则返回用户id
     * @param $account
     * @return bool
     */
    public static function getUserId($account){

        if($account){
            $customer = app('db')->connection('mysql_tour')->table("customer")->where('phone',$account)->orWhere('email', $account)->value('customer_id');
            if($customer){
             return $customer;
            }
        }
        return false;
    }

    /**
     * 获取管家详情
     * @param  $id
     * @return mixed
     */
    public static function getStewardById(int $id){
        $rows = app('db')->table("users")->select("*")->where('id',$id)->where('gids', 'like', '%2%')->first();
        if(!empty($rows)){
            $rows->hoses = app('db')->table('houses')->select('address','title')->where(['steward_id'=>$id , 'status'=>1])->get();
        }
        return $rows;
    }

    /**
     * 获取房东详情
     * @param  $id
     * @return mixed
     */
    public static function getLandlordById(int $id){
        $rows = app('db')->table("users")->leftJoin('landlords', 'users.id', '=', 'landlords.user_id')->select("*")->where('id',$id)->where('gids', 'like', '%1%')->first();
        if(!empty($rows)){
            //获取稀饭用户名称和邮箱
            $customer = app('db')->connection('mysql_tour')->table("customer")->select('chinese_name','email')->where('customer_id',$id)->first();
            if(empty($customer)){
                $customer->chinese_name = '未找到稀饭旅行网对应账号';
                $customer->email = '未找到稀饭旅行网对应账号';
            }
            $rows->name = $customer->chinese_name;
            $rows->email = $customer->email;
            //转义区域
            $result = get_area($rows->city_code);
            $rows->area_name = '';
            foreach ($result as $vo1){
                $rows->area_name.=$vo1->name.' ';
            }
            $rows->auth_type = change_auth_type($rows->auth_type);
            //获取房源
            $rows->hose_true = app('db')->table('houses')->where(['landlord_id'=>$id , 'status'=>1])->count();
            $rows->hose_false = app('db')->table('houses')->where(['landlord_id'=>$id , 'status'=>0])->count();
        }
        return $rows;
    }


}
