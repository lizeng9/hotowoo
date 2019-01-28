<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});


//后台接口
$app->group(['prefix' => 'api/admin/v1','middleware' => 'jwt','namespace'=>'Admin'], function($app)
 {
     //管理员
     $app->post('admin','UserController@createAdmin');//创建管理员
     $app->get('admin/{id}','UserController@getAdminById');//管理员详细信息
     $app->get('admin','UserController@getAdminList');//管理员列表
     $app->put('admin','UserController@updateAdmin');//修改管理员
     $app->delete('admin','UserController@deleteUser');//删除管理员
     $app->get('permissions','UserController@getPermissions');//权限列表
     $app->get('area/{parent_code}','UserController@getArea');//根据parent_code获取该区域下的子区域
     $app->get('houseCount/{city_code}','UserController@getHouseCount');//根据城市code获取该城市下房源总数

     //管家
     $app->post('steward','UserController@createSteward');//创建管家
     $app->get('steward/{id}','UserController@getStewardById');//管家详细信息
     $app->get('steward','UserController@getStewardList');//管家列表
     $app->put('steward','UserController@updateSteward');//修改管家
     $app->delete('steward','UserController@deleteUser');//删除管家

     //房东
     $app->post('landlord','UserController@createLandlord');//创建房东
     $app->get('landlord/{id}','UserController@getLandlordById');//房东详细信息
     $app->get('landlord','UserController@getLandlordList');//房东列表
     $app->put('landlord','UserController@updateLandlord');//修改房东
     $app->delete('landlord','UserController@undercarriageLandlord');//下架房东
     $app->get('bank','UserController@getBank');//银行

     //房态
     $app->get('houseState','SpotController@getSpotState');//房态管理列表
     $app->get('houseState/{spot_id}','SpotController@getSpotStateInfo');//房态管理详情
     $app->get('order/{id}','OrderController@getOrderById');//订单详情
     $app->post('orderComment','OrderController@createOrderComment');//新增订单备注
     $app->post('order','OrderController@createOrder');//新增订单

     //房间
     $app->get('house','HouseController@index');
     $app->get('house/{id}','HouseController@read');
     $app->post('house','HouseController@create');
     $app->put('house/{id}','HouseController@update');


     // 设置
     $app->get('attr/category','AttrController@category');//系统设置类别列表
     $app->get('attr','AttrController@index');//设置列表
     $app->post('attr','AttrController@create');//新增设置
     $app->put('attr/{id}','AttrController@update');//修改列表
     $app->delete('attr/{id}','AttrController@delete');//设置列表

     //景区
     $app->get('spot','SpotController@index');
     $app->get('spot/{id}','SpotController@read');
     $app->post('spot','SpotController@create');
     $app->put('spot/{id}','SpotController@update');
     $app->delete('spot/{id}','SpotController@delete');

 });

// 前台接口
$app->group(['prefix' => 'api/v1','middleware' => 'jwt'], function($app)
{

    //房间
    $app->get('house','HouseController@index');
    $app->get('house/{id}','HouseController@read');
    $app->get('house/{id}/date','HouseController@houseDate');//获取房间日历

    //景区
    $app->get('spot','Admin\SpotController@index');
    $app->get('spot/{id}','Admin\SpotController@read');
    $app->get('story','Admin\SpotController@read');

    $app->get('story','StoryController@index');



});

//订单
$app->get('api/v1/user/{id}/order','UserController@index');//订单列表
$app->get('api/v1/order/{id}/tour','UserController@getOrderTour');//稀饭订单详情
$app->get('api/v1/order/{id}/woo','UserController@getOrderWoo');//民宿订单详情

/**
 * 支付微服务
 * 客户提交参数
 * order_id, order_title, total_fee,fee_type,notify_url,success_url, failure_url
 */

$app->group(['middleware' => 'jwt'], function($app)
{
    $app->post('payment/mobile/checkout','PaymentController@checkout');
    $app->post('payment/mobile/wechatWap','PaymentController@wechatWap');
    $app->post('payment/mobile/wechatMp','PaymentController@wechatMp');
    $app->get('payment/mobile/wechatMp','PaymentController@wechatMp'); //微信再定向是GET
    $app->post('payment/mobile/alipayWap','PaymentController@alipayWap');
    $app->post('payment/mobile/paymentwall','PaymentController@paymentwallMobile');
    $app->get('payment/mobile/checkoutStatus/{id}','PaymentController@checkoutStatus');
    //app支付
    $app->post('payment/authorizeNet','AuthorizeNetController@charge');
    $app->post('payment/alipayApp','PaymentController@alipayApp');
    $app->post('payment/wechatApp','PaymentController@wechatApp');
});



$app->post('payment/wechatNotify','PaymentController@wechatNotify');
$app->post('payment/alipayNotify','PaymentController@alipayNotify');
$app->get('payment/paymentwallNotify','PaymentController@paymentwallNotify');
$app->post('payment/authorizeNetNotify','AuthorizeNetController@authorizeNetNotify');
$app->get('payment/authorizeNetNotify','AuthorizeNetController@authorizeNetNotify');

//app支付
$app->post('payment/authorizeNet','AuthorizeNetController@charge');
$app->post('payment/alipayApp','PaymentController@alipayApp');
$app->post('payment/wechatApp','PaymentController@wechatApp');


//支付客户端示范
$app->get('payment/example/order','PaymentExampleController@order');
$app->get('payment/example/success','PaymentExampleController@success_url');
$app->get('payment/example/notify','PaymentExampleController@notify_url');
$app->post('payment/example/notify','PaymentExampleController@notify_url');

$app->get('example/test','ExampleController@test');
$app->get('example/db','ExampleController@db');