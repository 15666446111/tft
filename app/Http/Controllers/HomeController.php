<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{	

	/**
	 * @version [<vector>] [< 访问项目主目录 处理控制器>]
	 * @author  [Pudding] <[< 755969423@qq.com >]>
	 */
    public function index(Request $request)
    {

        $a = json_decode((json_encode(array("aa" => "11"))));

        file_put_contents("./aa.txt", json_encode($a));

        dd($a);



    	return view('login');
    }


    /**
     * [home 项目主页面]
     * @author Pudding
     * @DateTime 2020-04-17T14:29:43+0800
     * @param    Request                  $request [description]
     * @return   [type]                            [description]
     */
    public function home(Request $request)
    {
    	return view('home');
    }
}
