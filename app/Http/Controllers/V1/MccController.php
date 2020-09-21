<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MccController extends Controller
{	

    /**
     * @Author    Pudding
     * @DateTime  2020-09-16
     * @copyright [copyright]
     * @license   [license]
     * @version   [ 获取大分类的MCC]
     * @param     Request     $request [description]
     * @return    [type]               [description]
     */
    public function bigMcc(Request $request)
    {
        $bigCate = \App\Mcc::whereNotNull('big_type_code')->distinct('big_type_code')->select('big_type_code', 'big_type_name as name')->get()->toArray();

        return response()->json(['success'=>['message' => '获取成功!', 'data' => $bigCate ]]);
    }

	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-09
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 获取MCC ]
	 * @return    [type]      [description]
	 */
    public function index(Request $request)
    {
    	$mcc = \App\Mcc::where('big_type_code', $request->big)->select('mcc as id', 'name')->get()->toArray();

    	return response()->json(['success'=>['message' => '获取成功!', 'data' => $mcc ]]);
    }


    /**
     * @Author    Pudding
     * @DateTime  2020-09-09
     * @copyright [copyright]
     * @license   [license]
     * @version   [ 获取所有省份 ]
     * @param     Request     $request [description]
     * @return    [type]               [description]
     */
    public function province(Request $request)
    {
    	$province = \App\Region::whereNotNull('province_code')->distinct('province')->select('province_code', 'province')->get()->toArray();

    	return response()->json(['success'=>['message' => '获取成功!', 'data' => $province ]]);
    }

    /**
     * @Author    Pudding
     * @DateTime  2020-09-09
     * @copyright [copyright]
     * @license   [license]
     * @version   [获取省份下面的所有城市]
     * @param     Request     $request [description]
     * @return    [type]               [description]
     */
    public function city(Request $request)
    {
    	if(!$request->province_code) return response()->json(['error'=>['message' => '请选择省份!' ]]);

    	$city = \App\Region::where('province_code', $request->province_code)->distinct('city_code')->select('city_code', 'city')->get()->toArray();

    	return response()->json(['success'=>['message' => '获取成功!', 'data' => $city ]]);
    }


    /**
     * @Author    Pudding
     * @DateTime  2020-09-09
     * @copyright [copyright]
     * @license   [license]
     * @version   [ 获取区域 ]
     * @param     Request     $request [description]
     * @return    [type]               [description]
     */
    public function area(Request $request)
    {
    	if(!$request->city_code) return response()->json(['error'=>['message' => '请选择城市!' ]]);

    	$area = \App\Region::where('city_code', $request->city_code)->distinct('area_code')->select('area_code', 'area')->get()->toArray();

    	return response()->json(['success'=>['message' => '获取成功!', 'data' => $area ]]);
    }
}
