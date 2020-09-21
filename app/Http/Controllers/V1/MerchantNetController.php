<?php

namespace App\Http\Controllers\V1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantNetRequest;
/**
 * @version [< 腾付通 商户入网进件 >] [<description>]
 */
class MerchantNetController extends Controller
{
    /**
     * @Author    Pudding
     * @DateTime  2020-09-10
     * @copyright [copyright]
     * @license   [license]
     * @version   [ APP 提交入网资料 - 写入商户表 - 提交腾付通 - 返回商户结果 ]
     * @param     Request     $request [description]
     * @return    [type]               [description]
     */
    public function netIn(Request $requests , MerchantNetRequest $request)
    {
    	$info = \App\Merchant::create([

    		'user_id'		=>	$requests->user->id,
    		'name'			=>	'个体户'.$request->name,
    		'name_attr'		=>	'个体户'.$request->name,
    		'mch_type'		=>	$request->merTypeRadio,
    		'mch_category'	=>	1,
    		'mcc'			=>	$request->MccCode,
    		'addr'			=>	$request->address,
    		'area_code'		=>	$request->regionCode,

    		'owner_name'	=>	$request->name,
    		'phone'			=>	$request->phone,
    		'owner_cert_id'	=>	$request->idcardNo,
    		'owner_cert_sdate'	=>	$request->cardStartDate,
    		'owner_cert_edate'	=>	$request->cardEndDate,

    		'settle_acc_no'		=>	$request->bankCardNo,
    		'settle_acc_name'	=>	$request->bankAccName,
    		'settle_bank_name'	=>	$request->bankName,

    		'settle_acc_type'	=>	$request->bankType,

    		'operate'			=>	$requests->user->operate
    	]);

    	$applation = new \App\Services\Tft\MerchantNet($info);

    	return $applation->in();

    }



    public function rate()
    {
        $info = \App\Merchant::where('id', 13)->first();

        $applation = new \App\Services\Tft\MerchantRate($info);

        $applation->query();

    }



    public function deduct()
    {

        /*$order = \App\MerchantsFrozenLog::where('id', 1)->first();

        $applation = new \App\Services\Tft\MerchantDeductionSim($order);

        return $applation->sim_deduct();
        */

        /*$temial = \App\Machine::where('id', 1)->first();


        $applation = new \App\Services\Tft\MerchantDeduction($temial);


        return $applation->deduct(); */   


        /*if($temial->policys->active_type == "1"){

            $applation = new \App\Services\Tft\MerchantDeduction($temial);

            return $applation->deduct();    

        }*/

        $order = \App\MerchantsFrozenLog::where('id', 6)->first();

        $applation = new \App\Services\Tft\MerchantDeductionSim($order);

        return $applation->sim_deduct(); 

    }


    public function queryBlance()
    {
        $info = \App\Merchant::where('id', 1)->first();

        $applation = new \App\Services\Tft\MerchantBlance($info);

        $applation->query();
    }

    public function drawBlance()
    {
        $info = \App\Merchant::where('id', 1)->first();

        $applation = new \App\Services\Tft\MerchantBlance($info);

        $applation->draw();  
    }


    public function queryCash()
    {
        $applation = new \App\Services\Tft\MerchantF();

        //$applation->drawQuery();

        //$applation->draw();   

        $applation->queryCash();   
    }


    public function fileUpload()
    {
        $applation = new \App\Services\Tft\MerchantF();

        $applation->fileUpload(); 
    }
}
