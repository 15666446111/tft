<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class MerchantController extends Controller
{

	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-22
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 商户注册 代理商分享注册海报 ]
	 * @param     Request     $request [description]
	 * @return    [type]               [description]
	 */
	public function register(Request $request)
	{
        try{
            $result = Hashids::decode($request->route('code'));

            if(empty($result)) return response()->json(['error'=>['message' => '解密失败!']]);

            return view('merchant.register');

        } catch (\Exception $e) {

            return response()->json(['error'=>['message' => '系统错误,联系客服!']]);

        }
	}






}
