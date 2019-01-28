<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AttrController extends Controller
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
        $category_id = $request->get("category_id",'');

        $conditions = [];
        if($category_id){
            $conditions[] = ['category_id','=',$category_id];
        }

        $rows = DB::table("attrs as a")
            ->join('attr_category as b', 'a.category_id', '=', 'b.id')
            ->select('a.id','a.name','a.icon_url','a.category_id','b.name as category_name')->where($conditions)
          ->orderBy("created","asc")->get();
        $rst = [];
        foreach($rows  as $v ){
            $rst[$v->category_id][]=$v;
        }

        return $this->success($rst);

    }

    public function category(Request $request)
    {

        $rows = DB::table("attr_category")->select('id','name')
          ->orderBy("id","asc")->get();

        return $this->success($rows);

    }


    public function create(Request $request)
    {
        $row = array_select_by_keys($request->all(),['name', 'icon_url','category_id']);

        if(!isset($row['name'])){
            $this->error(1,"标题必须");
        }

        $row['created'] = date("Y-m-d H:i:s");


        DB::table("attrs")->insert($row);
        return $this->success();

    }

    public function update(Request $request,$id)
    {

        $row = array_select_by_keys($request->all(),['name', 'icon_url','category_id']);

        if(!$row){
            $this->error(1,"修改数据为空");
        }

        DB::table("attrs")->where("id",$id)->update($row);
        return $this->success();

    }


    public function delete($id)
    {
        DB::table("attrs")->where("id",$id)->delete();

        return $this->success();

    }

    //
}
