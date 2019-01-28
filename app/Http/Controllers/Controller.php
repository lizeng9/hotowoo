<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    
    public function error($code, $msg = "error")
    {
        $resp = [
            "code" => $code,
            "msg"  => $msg,
        ];
        return response()->json($resp,200,[],JSON_UNESCAPED_UNICODE);
    }

    public function success($data = [],$pagination=null)
    {
        $resp = [
            "code" => 0,
            "msg"  => 'ok',
            "data" => $data,
        ];

        if($pagination){
            $resp["pagination"] = $pagination;
        }

        return response()->json($resp,200,[],JSON_UNESCAPED_UNICODE);
    }
}
