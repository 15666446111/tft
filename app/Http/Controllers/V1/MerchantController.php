<?php

namespace App\Http\Controllers\V1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \App\Services\Pmpos\PmposController;

class MerchantController extends Controller
{
    
	/**
	 * @Author    Pudding
	 * @DateTime  2020-07-28
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 首页 - 终端绑定 - 获取商户列表 ]
	 * @param     Request     $request [description]
	 * @return    [type]               [description]
	 */
    public function getNoBindList(Request $request)
    {
    	try{

            $mech = \App\Merchant::where('user_id', $request->user->id)->where('verfity_state', 1)->where('state', 1)->select('code', 'name')->get();
            				
           	return response()->json(['success'=>['message' => '获取成功!', 'data' => $mech]]);

    	} catch (\Exception $e) {

            return response()->json(['error'=>['message' => '系统错误,联系客服!']]);

        }
	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-11
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 首页 - 终端绑定 - 获取未绑定机器 ]
	 * @param     Request     $request [description]
	 * @return    [type]               [description]
	 */
	public function getNoBindMachines(Request $request)
	{
    	try{

            $list = \App\Machine::where('user_id', $request->user->id)->where('bind_status', 0)->where('merchant_id', 0)->select('sn')->get();
            				
           	return response()->json(['success'=>['message' => '获取成功!', 'data' => $list]]);

    	} catch (\Exception $e) {

            return response()->json(['error'=>['message' => '系统错误,联系客服!']]);

        }
	}




	/**
	 * @Author    Pudding
	 * @DateTime  2020-07-06
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 首页 - 绑定终端 ]
	 * @param     Request     $request [description]
	 * @return    [type]               [description]
	 */
    public function bindMerchant(Request $request)
    {
        try{ 
			if(!$request->merchant_sn) 	return response()->json(['error'=>['message' => '请选择终端']]);
            if(!$request->merch_no) 	return response()->json(['error'=>['message' => '请选择商户']]);

            $temial = \App\Machine::where('user_id',$request->user->id)->where('sn',$request->merchant_sn)->first();
            if(!$temial or empty( $temial )) return response()->json(['error'=>['message' => '该终端不存在!']]);

            $mech   = \App\Merchant::where('code', $request->merch_no)->where('user_id',$request->user->id)->first();
			if(!$mech or empty( $mech )) return response()->json(['error'=>['message' => '该商户不存在!']]);

			// 验证终端是否已绑定
			if($temial->bind_status != "0"){
				return response()->json(['error'=>['message' => '该终端状态不正常']]);
			}

			// 验证商户是否已审核过
			if($mech->verfity_state != "1"){
				return response()->json(['error'=>['message' => '该商户未审核通过']]);
			}

			if($mech->state != "1"){
				return response()->json(['error'=>['message' => '该商户为无效商户']]);
			}

	    	$applation = new \App\Services\Tft\MerchantBind($temial, $mech);

	    	return $applation->bind();

    	} catch (\Exception $e) {

            return response()->json(['error'=>['message' => '系统错误,请联系客服']]);

        }
	}




	/**
	 * @Author    Pudding
	 * @DateTime  2020-07-06
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 首页 - 商户管理 - 商户列表 ]
	 * @param     Request     $request [description]
	 * @return    [type]               [description]
	 */
    public function merchantsList(Request $request)
    {
        try{ 

        	$page = $request->page ?? 1;

			$data = \App\Merchant::where('user_id', $request->user->id)->where('verfity_state', 1)->orderBy('created_at', 'desc')/*->offset($page * 10 - 10)->limit(10)*/->get();
			
			$arrs = [];

			$arrs['Bound'] 	= array();
			$arrs['UnBound'] = array();
			if(!$data or empty($data)){ 
				$arrs['Bound'] = array();
			}else{

				foreach($data as $key=>$value){

					if(!$value->machines->isEmpty()){
						$sn = $value->machines->pluck('sn')->toArray();
						$sn = implode('|', $sn);

						$totalMoney = $value->trades->where('is_repeat', 0)->where('is_invalid', 0)->sum('amount');
						$arrs['Bound'][] = array(
							'id'				=>		$value->id,
							'merchant_name'		=>		$value->name,
							'machine_phone'		=>		$value->phone,
							'merchant_sn'		=>		$sn,
							'money'				=>		number_format($totalMoney / 100, 2, '.', ','),
							'merchant_number'	=>		$value->code,
							'bind_time'			=>		$value->machines->first()->bind_time,
						);		
					}else{
						$arrs['UnBound'][] = array(
							'id'				=>		$value->id,
							'merchant_name'		=>		$value->name,
							'machine_phone'		=>		$value->phone,
							//'merchant_sn'		=>		$sn,
							//'money'				=>		number_format($totalMoney / 100, 2, '.', ','),
							'merchant_number'	=>		$value->code,
							//'bind_time'			=>		$value->machines->first()->bind_time,
						);	
					}
				}
			}
            return response()->json(['success'=>['message' => '获取成功!', 'data' => $arrs]]); 
   		} catch (\Exception $e) {
            return response()->json(['error'=>['message' => '系统错误,联系客服!']]);
        }
    }
	

	/**
	 * @Author    Pudding
	 * @DateTime  2020-07-06
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 首页 - 商户管理 - 商户详情 ]
	 * @param     Request     $request [description]
	 * @return    [type]               [description]
	 */
    public function merchantInfo(Request $request)
    {
        try{ 

            if(!$request->id) return response()->json(['error'=>['message' => '请选择商户']]);

            $data=\App\Merchant::where('user_id',$request->user->id)->where('verfity_state', 1)->where('id',$request->id)->first();

            if(!$data or empty($data)) return response()->json(['error'=>['message' => '商户信息不存在!']]);

            return response()->json(['success'=>['message' => '获取成功!', 'data' => $data]]);   
            
    	} catch (\Exception $e) {
            return response()->json(['error'=>['message' => '系统错误,联系客服!']]);
        }
	}

	/**
	 * @Author    Pudding
	 * @DateTime  2020-07-06
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 首页 - 商户管理 - 交易明细 ]
	 * @param     Request     $request [description]
	 */
    public function MerchantDetails(Request $request)
    {
        try{ 
            if(!$request->merchant) return response()->json(['error'=>['message' => '缺少必要参数:商户号']]);

            $merchant = \App\Merchant::where('id', $request->merchant)->first();

            if(!$merchant or empty($merchant)) return response()->json(['error'=>['message' => '该商户不存在！']]);

            switch ($request->data_type) {
                case 'month':
                    $StartTime = Carbon::now()->startOfMonth()->toDateTimeString();
                    break;
                case 'day':
                    $StartTime = Carbon::today()->toDateTimeString();
                    break;
                case 'count':
                    $StartTime = Carbon::createFromFormat('Y-m-d H', '1970-01-01 00')->toDateTimeString();
                    break;
                default:
                    $StartTime = Carbon::today()->toDateTimeString();
                    break;
            }
            $EndTime = Carbon::now()->toDateTimeString();

            $data = \App\Trade::select('card_type', 'sn as merchant_sn','amount as money','trade_time')
            			->where('merchant_code', $merchant->code)->whereBetween('trade_time', [$StartTime,  $EndTime])
            			->where('is_repeat', 0)->where('is_invalid', 0)
            			->orderBy('trade_time', 'desc')
						->get();
			if($data->isEmpty()){
				$data = array();
			} else{
				foreach ($data as $key => $value) {
					$data[$key]['money'] = number_format($value['money'] / 100, 2, '.', ',');
					
				}
			}
            return response()->json(['success'=>['message' => '获取成功!', 'data'=>$data]]);
        } catch (\Exception $e) {
            return response()->json(['error'=>['message' => '系统错误，请联系客服']]);
        }
    }


	
	/**
	 * @Author    Pudding
	 * @DateTime  2020-07-28
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 首页 - 机具管理 - 机具统计信息 ]
	 * @param     Request     $request [description]
	 * @return    [type]               [description]
	 */
	public function getBind(Request $request)
	{
		try{
			//获取用户的伙伴
			$userAll = \App\UserRelation::where('parents', 'like', "%\_".$request->user->id."\_%")->pluck('user_id')->toArray();
			$data=[];
			if(!$userAll){
				$data['friend']['all'] = 0;
				$data['friend']['NoMerchant'] =0;
				$data['friend']['Merchant'] =0;
				$data['friend']['Merchant_status'] = 0;
				$data['friend']['standard_statis'] =0;
			}else{
				//获取伙伴机器总数
				$data['friend']['all'] = \App\Machine::whereIn('user_id', $userAll)->count();
				//获取伙伴未绑定机器总数
				$data['friend']['NoMerchant'] = \App\Machine::whereIn('user_id', $userAll)->where('bind_status', '0')->count();
				//查询伙伴已绑定机器总数
				$data['friend']['Merchant'] = \App\Machine::whereIn('user_id', $userAll)->where('bind_status', '1')->count();
				//查询伙伴已激活机器总数
				$data['friend']['Merchant_status'] = \App\Machine::whereIn('user_id', $userAll)->where('activate_state', '1')->count();
				//查询伙伴已达标机器总数
				$data['friend']['standard_statis'] = \App\Machine::whereIn('user_id', $userAll)->where('standard_status', '1')->count();
			}
			
			//获取用户机器总数
			$data['user']['all'] = \App\Machine::where('user_id', $request->user->id)->count();
			//获取用户未绑定机器总数
			$data['user']['NoMerchant'] = \App\Machine::where('user_id', $request->user->id)->where('bind_status', '0')->count();
			//查询用户已绑定机器总数
			$data['user']['Merchant'] = \App\Machine::where('user_id', $request->user->id)->where('bind_status', '1')->count();
			//查询用户已激活机器总数
			$data['user']['Merchant_status'] = \App\Machine::where('user_id', $request->user->id)->where('activate_state', '1')->count();
			//查询用户已达标机器总数
			$data['user']['standard_statis'] = \App\Machine::where('user_id', $request->user->id)->where('standard_status', '1')->count();
			
			//获取全部机器总数
			$data['count']['all']=$data['friend']['all']+$data['user']['all'];
			//获取用户未绑定机器总数
			$data['count']['NoMerchant']=$data['friend']['NoMerchant']+$data['user']['NoMerchant'];
			//查询用户已绑定机器总数
			$data['count']['Merchant']=$data['friend']['Merchant']+$data['user']['Merchant'];
			//查询伙伴已激活机器总数
			$data['count']['Merchant_status']=$data['friend']['Merchant_status']+$data['user']['Merchant_status'];
			//查询用户已达标机器总数
			$data['count']['standard_statis']=$data['friend']['standard_statis']+$data['user']['standard_statis'];

           	return response()->json(['success'=>['message' => '获取成功!', 'data' => $data]]);
    	} catch (\Exception $e) {
            return response()->json(['error'=>['message' => '系统错误,联系客服!']]);
		}
    }
    

    /**
     * @Author    Pudding
     * @DateTime  2020-07-28
     * @copyright [copyright]
     * @license   [license]
     * @version   [ 首页 - 机具管理 - 机具详情 ]
     * @param     Request     $request [description]
     * @return    [type]               [description]
     */
    public function getMerchantsTail(Request $request)
    {
        try{
            //参数 friends伙伴  count总  user用户
			$Type = $request->Type;
			
            if(!$Type) return response()->json(['error'=>['message' => '缺少必要参数:详情类型']]);

            $server = new \App\Http\Controllers\V1\ServersController($Type, $request->user);
            $data = $server->getInfo();
            return response()->json(['success'=>['message' => '获取成功!', 'data'=>$data]]); 
        } catch (\Exception $e) {
			return response()->json(['error'=>['message' => '系统错误,联系客服!']]);
		}
	}
	

	/**
	 * @Author    Pudding
	 * @DateTime  2020-06-07
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [获取机器的活动详情]
	 * @param     Request     $request [description]
	 * @return    [type]               [description]
	 */
    public function getActiveDetail(Request $request)
    {	
    	/**
    	 * @var 返回该机器的活动情况
    	 */
    	if(!$request->terminal ) return response()->json(['error'=>['message' => '缺少机器终端']]);

    	$ActiveInfo = array();

    	$countTradeMoney =  \App\Trade::where('sn', $request->terminal )->sum('amount');
    	
    	// 获得该机器总交易额
    	$ActiveInfo['countTradeMoney'] =number_format($countTradeMoney / 100, 2, '.', ',');


    	$ActiveInfo['tips'] = "达标统计的是T0、T1、云闪付贷记卡交易之和，非总交易额";
    	

    	return response()->json(['success'=>['message' => '获取成功', 'data' =>$ActiveInfo ]]);

	}

	/**
	 * [获取商户费率]
	 * @param Request $request [description]
	 */
	public function MerchantsRate(Request $request)
	{
		try{
			$merInfo = \App\Merchant::where('code', $request->code)->first();
			if ($merInfo->user_id != $request->user->id) {
				return response()->json(['error'=>['message' => '商户信息有误，请重试']]);
			}

			// 只有工具版本才可以设置商户费率
            $setting = \App\AdminSetting::where('operate_number', $request->user->operate)->first();
            if(!$setting or empty($setting)) return response()->json(['error'=>['message' => '未找到操盘方信息']]); 

            if($setting->pattern != '2') return response()->json(['error'=>['message' => '非工具版本不能设置']]);

			// 机具信息
			$machines = \App\Machine::where('merchant_id', $merInfo->id)->get();

			// 活动组id
			$policyGroupId = 0;
			// 检查商户活动组信息
			$policyGroupArr = [];
			//dd($machines);
			foreach ($machines as $k => $v) {
				$policyGroupArr[$v->policys->policy_group_id] = $v->policys->policy_group_id;
				$policyGroupId = $v->policys->policy_group_id;
			}
			//dd($policyGroupId);
			if (count($policyGroupArr) != 1) {
				return response()->json(['error'=>['message' => '商户活动组信息有误，请联系客服']]);
			}

			// 查询商户费率
			$pmpos = new PmposController($request->code, '');
			$rateData = json_decode( $pmpos->getMerchantFee() );

			if ($rateData->code !== '00') {
				return response()->json(['error'=>['message' => $rateData->message]]);
			}

			// 需要返回的数据
			$data = [];

			// 活动组对应的费率信息
			$groupRate = \App\PolicyGroupRate::where('policy_group_id', $policyGroupId)
							->where('is_abjustable', 1)->get();

			foreach ($groupRate as $k => $v) {

				## 最小可设置费率：不低于活动组设置的最低可设置费率，不低于用户对应的结算价
				#  最小可设置费率
				$minRate = $v->min_rate;

				if (!empty($v->rate_types->trade_type_id)) {

					// 查询用户当前费率对应交易类型的结算价
					$userSettle = $this->getToolUserSettle($request->user->id, $policyGroupId, $v->rate_types->trade_type_id);

					$minRate = max($v->min_rate, $userSettle);
				}
				
				
				foreach ($rateData->data as $rateKey => $rateVal) {
					
					if ($rateKey == $v->rate_types->type) {
						$data[] = [
							'index'				=> $v->rate_type_id,
							'title'				=> $v->rate_types->type_name,
							'min_rate'			=> $minRate,
							'max_rate'			=> $v->max_rate,
							'is_top'			=> $v->rate_types->is_top,
							'default_rate'		=> $rateVal * 1000
						];
						break;
					}

				}

			}

			return response()->json(['success'=>['data' => $data]]);

        } catch (\Exception $e) {
			return response()->json(['error'=>['message' => '系统错误,联系客服!']]);
		}
	}

	/**
	 * [ 修改商户费率 ]
	 * @param Request $request [description]
	 */
	public function setRate(Request $request)
	{
		try{
            
			if (empty($request->code)) {
				return response()->json(['error'=>['message' => '缺少必要参数:商户号']]);
			}
			if (!is_array($request->rate)) {
				return response()->json(['error'=>['message' => '参数需为数组格式']]);
			}
			
			// 只有工具版本才可以设置商户费率
            $setting = \App\AdminSetting::where('operate_number', $request->user->operate)->first();
            if(!$setting or empty($setting)) return response()->json(['error'=>['message' => '未找到操盘方信息']]); 

            if($setting->pattern != '2') return response()->json(['error'=>['message' => '非工具版本不能设置']]); 

			// 商户信息
			$merInfo = \App\Merchant::where('code', $request->code)->first();
			// 机具信息
			$machines = \App\Machine::where('merchant_id', $merInfo->id)->get();

			if ($merInfo->user_id != $request->user->id) {
				return response()->json(['error'=>['message' => '商户信息有误，请重试']]);
			}

			## 检查商户活动组信息
			$policyGroupArr = [];
			foreach ($machines as $k => $v) {
				$policyGroupArr[$v->policys->policy_group_id] = $v->policys->policy_group_id;
				$policyGroupId = $v->policys->policy_group_id;
			}

			if (count($policyGroupArr) != 1) {
				return response()->json(['error'=>['message' => '商户活动组信息有误，请联系客服']]);
			}

			// 查询商户费率
			$pmpos = new PmposController($request->code, '');
			$rateData = json_decode( $pmpos->getMerchantFee() );

			if ($rateData->code !== '00') {
				return response()->json(['error'=>['message' => $rateData->message]]);
			}

			## 整理需要修改的费率信息
			$data = [];
			foreach ($request->rate as $k => $v) {
				
				if (empty($v['index']) || empty($v['default_rate'])) {
					return response()->json(['error'=>['message' => '参数错误']]);
				}

				// 活动组费率设置信息
				$groupRate = \App\PolicyGroupRate::where('policy_group_id', $policyGroupId)->where('rate_type_id', $v['index'])->first();

				if (!$groupRate || $groupRate->is_abjustable == 0) {
					return response()->json(['error'=>['message' => '数据异常，请联系客服']]);
				}

				## 最小可设置费率：不低于活动组设置的最低可设置费率，不低于用户对应的结算价
				#  最小可设置费率
				$minRate = $groupRate->min_rate;

				if (!empty($groupRate->rate_types->trade_type_id)) {
					// 查询用户当前费率对应交易类型的结算价
					$userSettle = $this->getToolUserSettle($request->user->id, $policyGroupId, $groupRate->rate_types->trade_type_id);

					$minRate = max($groupRate->min_rate, $userSettle);
				}
				
				if ($v['default_rate'] > $groupRate->max_rate || $v['default_rate'] < $minRate) {
					return response()->json(['error'=>['message' => '设置费率不在合理区间内']]);
				}

				$divisor = $groupRate->rate_types->is_top == 1 ? 100000 : 1000;
				$data[$groupRate->rate_types->type] = bcdiv($v['default_rate'], $divisor, 3);

			}

			$reData = json_decode( $pmpos->updateNonAudit($data) );

			if ($reData->code == '00') {

				$newRateData = json_decode( $pmpos->getMerchantFee() );
				\App\MerchantsRateLog::create([
					'merchant_code'		=> $request->code,
					'policy_group_id'	=> $policyGroupId,
					'original_rate'		=> json_encode( $rateData->data ),
					'adjust_rate'		=> $newRateData->code == '00' ? json_encode($newRateData->data) : '',
					'adjust_user_id'	=> $request->user->id,
					'operate'			=> $request->user->operate
				]);

				return response()->json(['success'=>['message' => '修改成功']]);

			} else {

				return response()->json(['error'=>['message' => $reData->message]]);

			}

        } catch (\Exception $e) {
			return response()->json(['error'=>['message' => '系统错误,联系客服!']]);
		}
	}
	
	/**
	 * [ 获取用户结算价（工具模式）]
     * @param  integer $userId        [用户id]
     * @param  integer $policyGroupId [活动组id]
     * @param  integer $tradeTypeId   [交易类型id]
	 * @return [type]                 [description]
	 */
	public function getToolUserSettle($userId=0, $policyGroupId=0, $tradeTypeId=0)
	{
		$userSettle = 0;

        // 用户结算价
        $settleStr = \App\UserFee::where('user_id', $userId)
                                ->where('policy_group_id', $policyGroupId)
                                ->value('price');

        if (empty($settleStr)) {

            // 用户没有设置结算价时，获取默认结算价
            $userSettle = \App\PolicyGroupSettlement::where('policy_group_id', $policyGroupId)
                                                    ->where('trade_type_id', $tradeTypeId)
                                                    ->value('default_price');

        } else {

            foreach (json_decode($settleStr) as $k => $v) {
                if ($v->index == $tradeTypeId) $userSettle = $v->price;
            }

        }

        return $userSettle;
	}
}
