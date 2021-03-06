<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();


// 访问项目主地址
Route::get('/', 		'HomeController@index');

// 忘记密码地址
Route::get('/forget', 	'RegisterController@forget');

// 项目登录之后的主页面
Route::get('/home', 	'HomeController@home');


/**
 * @version [<通知接口>] [< 助代通通知接口  助代通开通通知 ， 交易流水通知>]
 * @version [<vector>]  [<系统总平台 包括操盘方 所有的通知接口都会推送到此地址>]
 */
Route::post('/trade', 	'TradeApiController@index');


/**
 * @version [<vector>] [<后台联动Select 查询符合条件的厂家>]
 */
Route::get('/api/getAdminFactory', 		'AdminApiController@getAdminFactory');

/**
 * @version [<vector>] [<后台联动Select 查询符合条件的型号>]
 */
Route::get('/api/getAdminStyle', 		'AdminApiController@getAdminStyle');

/**
 * @version [<vector>] [<后台联动Select 查询符合条件的活动>]
 */
Route::get('/api/getAdminUserGroup', 		'AdminApiController@getAdminUserGroup');

/**
 * @version [<vector>] [<后台联动Select 查询符合条件的活动>]
 */
Route::get('/api/getMachineSn', 		'AdminApiController@getMachineSn');

/**
 * @version [<vector>] [<后台联动Select 根据活动组 查询正常的活动 >]
 */
Route::get('/api/getPolicys', 			'AdminApiController@getPolicys');
/**
 * @version [<vector>] [<后台联动Select 根据操盘方 查询正常的活动组 >]
 */
Route::get('/api/getPolicyGroups', 		'AdminApiController@getPolicyGroups');



/**
 * @version [<团队邀请人注册 扫描二维码>] [<description>]
 * @author  [Pudding] <[755969423@qq.com]>
 * @version [<会员注册>] [<description>]
 */
Route::get('/team/{code}', 'RegisterController@team');

/**
 * @version [<团队邀请人注册 扫描二维码>] [<description>]
 * @author  [Pudding] <[755969423@qq.com]>
 * @version [<会员注册>] [<description>]
 */
Route::post('/team/{code}', 'RegisterController@team_in')->name('register');

/**
 * @version [< 商户注册 代理商分享注册二维码 由用户自主注册 >] [<description>]
 * @author  [Pudding] <[755969423@qq.com]>
 * @version [< 商户注册 >] [<description>]
 */
Route::get('/merchant/{code}', 'MerchantController@register');





// 注册发送验证码
Route::post('/getCode', 	'RegisterController@code');

/**
 * 支付宝回调修改订单状态
 */
Route::any('/callback ','V1\OrdersController@AliPayCallback ');


//微信支付服务端
Route::any('/wechat', 'WeChatController@serve');

/**
 * 计划任务
 */
// 冻结机器激活的计划任务
Route::get('/fro_machine_active', 	'CrontabController@froMachineActive');
Route::get('/sim_frozen', 	'CrontabController@simFrozen');

