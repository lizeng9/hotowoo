<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\UserService;


class UserController extends Controller
{
    /**
     * 管理员列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminList(){
        $rows = app('db')->table("users")->where('gids', 'like', '%0%')->select('id','last_login','created')->orderBy("created","asc")->paginate(2)->toArray();
        foreach ($rows['data'] as $key=>$vo){
            $customer = app('db')->connection('mysql_tour')->table("customer")->select('phone','email')->where('customer_id',$vo->id)->first();

            if(!empty($customer)){
                $account = $customer->phone ?? $customer->email;
            }else{
                $account = '未找到稀饭旅行网对应账号';
            }
            $rows['data'][$key]->account = $account;
        }
        return $this->success($rows['data'],format_pagination($rows));
    }

    /**
     * 根据用户Id获取管理员基本资料
     * @param  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminById(int $id){
        $row = UserService::getAdminById($id);
        return $this->success($row);
    }

    /**
     * 权限列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissions(){
        $rows = app('db')->table('permissions')->select('id','name')->get();
        return $this->success($rows);
    }

    /**
     * 根据parent_code获取该区域下的子区域
     * @param  $parent_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getArea(int $parent_code){
        $rows = app('db')->table('areas')->where('parent_code',$parent_code)->get();
        return $this->success($rows);
    }

    /**
     * 创建管理员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAdmin(Request $request){
        $account = $request->get('account',null);
        //获取稀饭用户id，不存在返回false
        $user_id = UserService::getUserId($account);
        if(!$user_id){
            return $this->error(1,'未匹配到稀饭旅行网账号');
        }
        //判断users表中是否已经有该账号
        $flag = app('db')->table('users')->where('id',$user_id)->count();
        if( $flag>0 ){
            return $this->error(1,'该账号已存在');
        }
        $row = [
            'id' => $user_id ,
            'phone' => $request->get('phone',null) ,
            'wechat' => $request->get('wechat',null) ,
            'permissions' => implode(',' , $request->get('permissions',[]) ) ,
            'area_city_codes' => implode(',' , $request->get('area_city_codes',[]) ) ,
            'gids' => 0 ,
            'money' => 0 ,
            'created' => date('Y-m-d')
        ];
        app('db')->table("users")->insert($row);
        return $this->success();
    }

    /**
     * 修改管理员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAdmin(Request $request){
        $id = $request->get('id',null);
        $row = [
            'permissions' => implode(',' , $request->get('permissions',[]) ) ,
            'area_city_codes' => implode(',' , $request->get('area_city_codes',[]) )
        ];
        app('db')->table("users")->where('id',$id)->update($row);
        return $this->success();
    }

    /**
     * 删除用户
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser(Request $request){
        $id = $request->get('id',null);
        app('db')->table("users")->where('id',$id)->delete();
        return $this->success();
    }

    /**
     * 根据城市code获取该城市下房源总数
     * @param  $city_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHouseCount(int $city_code){
        $house_count = app('db')->table('houses')->where(['city_code'=>$city_code , 'status'=>1])->count();
        return $this->success($house_count);
    }

    /**
     * 创建管家
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSteward(Request $request){
        $account = $request->get('account',null);
        //获取稀饭用户id，不存在返回false
        $user_id = UserService::getUserId($account);
        if(!$user_id){
            return $this->error(1,'未匹配到稀饭旅行网账号');
        }
        //判断users表中是否已经有该账号
        $flag = app('db')->table('users')->where('id',$user_id)->count();
        if( $flag>0 ){
            return $this->error(1,'该账号已存在');
        }
        $row = [
            'id' => $user_id ,
            'phone' => $request->get('phone',null) ,
            'wechat' => $request->get('wechat',null) ,
            'gids' => 2 ,
            'money' => 0 ,
            'created' => date('Y-m-d')
        ];
        app('db')->table("users")->insert($row);
        return $this->success();
    }

    /**
     * 管家详情
     * @param  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStewardById(int $id){
        $row = UserService::getStewardById($id);
        return $this->success($row);
    }

    /**
     * 管家列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStewardList(){
        $rows = app('db')->table("users")->where('gids', 'like', '%2%')->select('id','last_login','created')->orderBy("created","asc")->paginate(2)->toArray();
        foreach ($rows['data'] as $key=>$vo){
            //获取稀饭账号
            $customer = app('db')->connection('mysql_tour')->table("customer")->select('phone','email')->where('customer_id',$vo->id)->first();
            if(!empty($customer)){
                $account = $customer->phone ?? $customer->email;
            }else{
                $account = '未找到稀饭旅行网对应账号';
            }
            $rows['data'][$key]->account = $account;
            //获取管家管理房源总数
            $rows['data'][$key]->house_count = app('db')->table("houses")->where(['steward_id'=>$vo->id , 'status'=>1])->count();
        }
        return $this->success($rows['data'],format_pagination($rows));
    }

    /**
     * 修改管家资料
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSteward(Request $request){
        $id = $request->get('id',null);
        $row = [
            'phone' => $request->get('phone',null) ,
            'wechat' => $request->get('wechat',null)
        ];
        app('db')->table("users")->where('id',$id)->update($row);
        return $this->success();
    }


    /**
     * 创建房东
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createLandlord(Request $request){
        $account = $request->get('account',null);
        //获取稀饭用户id，不存在返回false
        $user_id = UserService::getUserId($account);
        if(!$user_id){
            return $this->error(1,'未匹配到稀饭旅行网账号');
        }
        //判断users表中是否已经有该账号
        $flag = app('db')->table('users')->where('id',$user_id)->count();
        if( $flag>0 ){
            return $this->error(1,'该账号已存在');
        }
        $user = [
            'id' => $user_id ,
            'phone' => $request->get('phone',null) ,
            'gids' => 1 ,
            'created' => date('Y-m-d')
        ];
        $landlord = [
            'user_id' => $user_id ,
            'country_code' => $request->get('country_code',null) ,
            'prov_code' => $request->get('prov_code',null) ,
            'city_code' => $request->get('city_code',null) ,
            'auth_type' => $request->get('auth_type',null) ,
            'real_name' => $request->get('real_name',null) ,
            'idcard' => $request->get('idcard',null) ,
            'account_type' => $request->get('account_type',null) ,
            'account_name' => $request->get('account_name',null) ,
            'account_no' => $request->get('account_no',null) ,
            'account_bank' => $request->get('account_bank',null) ,
            'company_name' => $request->get('company_name',null) ,
            'company_code' => $request->get('company_code',null) ,
            'company_legal' => $request->get('company_legal',null) ,
            'contracts' => $request->get('contracts',null) ,
            'contract_start' => $request->get('contract_start',null) ,
            'contract_end' => $request->get('contract_end',null) ,
            'created' => date('Y-m-d')
        ];
        app('db')->transaction(function () use ($user,$landlord) {
            app('db')->table("users")->insert($user);
            app('db')->table("landlords")->insert($landlord);
        });
        return $this->success();
    }

    /**
     * 获取房东详情
     * @param  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLandlordById(int $id){
        $row = UserService::getLandlordById($id);
        return $this->success($row);
    }

    /**
     * 房东列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLandlordList(Request $request){
        $city_code = $request->get('city_code',null);
        $key_words = $request->get('key_words',null);
        $conditions = [];
        if($city_code){
            $conditions[] = ['landlords.city_code','=',$city_code];
        }
        $bulder = app('db')->table("users")->leftJoin('landlords', 'users.id', '=', 'landlords.user_id')->where($conditions)->where('gids', 'like', '%1%');
        if($key_words){
            //先去稀饭把用户id查出来
            $user_id = app('db')->connection('mysql_tour')->table('customer')->where('chinese_name','like','%'.$key_words.'%')->pluck('customer_id')->toArray();
            $bulder->whereIn('users.id',$user_id);
        }
        $rows = $bulder->select('users.id','users.phone','landlords.city_code','users.created')->orderBy("users.created","asc")->paginate(2)->toArray();
        foreach ($rows['data'] as $key=>$vo){
            //获取稀饭昵称头像
            $customer = app('db')->connection('mysql_tour')->table("customer")->select('chinese_name','face')->where('customer_id',$vo->id)->first();
            $rows['data'][$key]->name = $customer->chinese_name ?? '未命名';
            $rows['data'][$key]->photo = ($customer->face == null) ? 'https://www.tourscool.com/img/common/head.png' : 'https://www.tourscool.com/images'.$customer->face;
            //获取城市名
            $rows['data'][$key]->city_name = app('db')->table('areas')->where('code',$vo->city_code)->value('name');
            //房间数量
            $rows['data'][$key]->house_all_count = app('db')->table('houses')->where(['landlord_id'=>$vo->id , 'status'=>1])->count();
            //今日入住房间量
            $rows['data'][$key]->house_online_count = app('db')->table('orders')->leftJoin('houses', 'houses.id', '=', 'orders.house_id')->where(['orders.status'=>1 ,'houses.landlord_id'=>$vo->id])->where('orders.start_date','<=',date('Y-m-d'))->where('orders.end_date','>',date('Y-m-d'))->count();
        }
        return $this->success($rows['data'],format_pagination($rows));
    }

    /**
     * 修改房东资料
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLandlord(Request $request){
        $id = $request->get('id',null);
        $phone = $request->get('phone',null);
        $row['country_code'] = $request->get('country_code',null);
        $row['prov_code'] = $request->get('prov_code',null);
        $row['city_code'] = $request->get('city_code',null);
        $row['auth_type'] = $request->get('auth_type',null);
        $row['real_name'] = $request->get('real_name',null);
        $row['idcard'] = $request->get('idcard',null);
        $row['company_name'] = $request->get('company_name',null);
        $row['company_code'] = $request->get('company_code',null);
        $row['company_legal'] = $request->get('company_legal',null);
        $row['account_type'] = $request->get('account_type',null);
        $row['account_name'] = $request->get('account_name',null);
        $row['account_no'] = $request->get('account_no',null);
        $row['account_bank'] = $request->get('account_bank',null);
        $row = array_filter($row);
        app('db')->transaction(function () use ($id,$phone,$row) {
            if($phone){
                app('db')->table("users")->where('id',$id)->update(['phone'=>$phone]);
            }
            app('db')->table("landlords")->where('user_id',$id)->update($row);
        });
        return $this->success();
    }

    /**
     * 下架房东
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function undercarriageLandlord(Request $request){
        $id = $request->get('id',null);
        app('db')->transaction(function () use ($id) {
            app('db')->table("landlords")->where('user_id',$id)->update(['status'=>0]);
            app('db')->table("houses")->where('landlord_id',$id)->update(['status'=>0]);
        });
        return $this->success();
    }

    /**
     * 获取银行
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBank(){
        $row = app('db')->table('attrs')->where('category_id',10)->get();
        return $this->success($row);
    }


}
