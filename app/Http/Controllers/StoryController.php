<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StoryController extends Controller
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

    public function index()
    {
        $data =[
            [
                'icon' =>'https://dummyimage.com/60x60',
                'city' => "南昌",
                'author' => "张三",
                'link' => 'https://www.tourscool.com/custom/story?story=1'
            ],
            [
                'icon' =>'https://dummyimage.com/60x60',
                'city' => "南昌",
                'author' => "张三",
                'link' => 'https://www.tourscool.com/custom/story?story=1'
            ],
            [
                'icon' =>'https://dummyimage.com/60x60',
                'city' => "南昌",
                'author' => "张三",
                'link' => 'https://www.tourscool.com/custom/story?story=1'
            ],
        ];

        return $this->success($data);
    }


}
