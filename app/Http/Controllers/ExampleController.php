<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExampleController extends Controller
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

    public function test()
    {
        return 'OK';
        Cache::put("sessionid","1234567890",10);

        return $this->success(Cache::get("sessionid"));

//
//        $pre = env("DB_PREFIX");
//        $sql = "select * from yii_overseatours.customer where customer_id > ? limit 10";
//        $rows = DB::select($sql, [1]);
//        return $this->success($rows);
    }

    /**
     * tool list table columns
     * @param Request $request
     */
    public function db(Request $request)
    {
        $table = $request->get("table","users");
        $columns = Schema::getColumnListing($table);
        echo '[ "' . join('","', $columns) . '" ]';
//        return $this->success($columns);
    }
    //
}
