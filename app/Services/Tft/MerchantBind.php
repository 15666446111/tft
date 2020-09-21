<?php

namespace App\Services\Tft;

use App\Machine;
use App\Merchant;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzClient;

class MerchantBind
{
	/**
	 * [$data  要操作终端信息 ]
	 * @var [type]
	 */
	protected $temial;


	/**
	 * [$mechant 操作的商户信息 ]
	 * @var [type]
	 */
	protected $mechant;


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-10
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 初始化操作 赋值要操作的商户信息 ]
	 * @param     Merchant    $params [description]
	 */
	public function __construct(Machine $temial, Merchant $params)
	{
		$this->temial 	= $temial;

		$this->mechant  = $params;
	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-10
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 终端绑定  ]
	 * @return    [type]      [description]
	 */
	public function bind()
	{
		$body = array( 
			'mch_id'	=>	$this->mechant->code, 
			'term_sn'	=>	$this->temial->sn,
			'mf_id'		=>	$this->temial->machines_styles->machines_fact->fid,
		);

		$result = json_decode($this->send($body));

		if($result->resp_code != "00"){

			return response()->json(['error'=>['message' => $result->resp_msg]]);

		}else{
			
			$this->temial->temial_no    = $result->term_id; 
			$this->temial->merchant_id 	= $this->mechant->id;
			$this->temial->bind_status 	= 1;
			$this->temial->bind_time 	= Carbon::now()->toDateTimeString();
			$this->temial->save();	

			// 添加绑定记录
			\App\MerchantsBindLog::create([
				'merchant_code'	=>	$this->mechant->code,
				'sn'			=>	$this->temial->sn,
				'bind_state'	=>	1
			]);

			// 如果机器所属活动为冻结激活
			if($this->temial->policys->active_type == "1" ){

				$applation = new \App\Services\Tft\MerchantDeduction($this->temial);
				$deduct    = $applation->deduct();

			}


			$policyInfo = $this->temial->policys;

	        $startTime = $policyInfo->sim_delay == "0" ? Carbon::now() : Carbon::now()->addMonths($policyInfo->sim_delay);

	        $endTime   = $startTime->copy()->addMonths($policyInfo->sim_cycle);

	        $nextTime  = $endTime->copy()->addDays(1);

			/**
			 * 创建冻结流量卡费订单
			 */
			$SimOrder = \App\MerchantsFrozenLog::create([
				'order_no'			=>	'SIM'.time(),
				'merchant_code'		=>	$this->mechant->code,
				'sn'				=>	$this->temial->sn,
				'type'				=>	2,
				'frozen_money'		=>	(int)$this->temial->policys->sim_charge * 100,
				'state'				=>	0,
				'sim_agent_state'	=>	0,
				'sim_agent_time'	=>	$nextTime->toDateTimeString(),
				'start_time'		=>	$startTime->toDateTimeString(),
				'end_time'			=>	$endTime->toDateTimeString(),
			]);

			$SimDeduct = new \App\Services\Tft\MerchantDeductionSim($SimOrder);

			$SimResult = $SimDeduct->sim_deduct();

			return response()->json(['success'=>['message' => '终端绑定成功!']]);
		}
	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-10
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 建立 http 链接 发送请求 ]
	 * @param     [type]      $params [description]
	 * @return    [type]              [description]
	 */
	public function send($params)
	{
		$params['version']		=	'1.0.0';
		$params['timestamp']	=	time();
		$params['nonce_str']	=	"A".time();
		$params['agent_id']		=	config('tft.tft_agent_id');
		$params['req_type']		=	'mch_term_bind';
		$params['sign']			=	$this->sign($params);

		$client     = new GuzzClient(['verify' => false ]);

		$result 	= $client->request('POST', config('tft.tft_url'), [ 'json' => $params ]);

        return $result->getBody()->getContents();
	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-10
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 敏感参数 aes加密 ]
	 * @param     [type]      $data [description]
	 * @return    [type]            [description]
	 */
	public function encrypt($data)
	{
        return base64_encode(openssl_encrypt($data, 'aes-128-ecb', config('tft.tft_sign_key'), OPENSSL_RAW_DATA));
	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-10
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 数据签名 ]
	 * @param     [type]      $params [description]
	 * @return    [type]              [description]
	 */
	public function sign($params)
	{

		foreach ($params as $key => $value) {
			if($value == "" && $value !== 0) unset($params[$key]);
		}

		$private_key = file_get_contents(storage_path('app/public/rsa/tft/rsa_private_key_1024.pem'));

		ksort($params);

		$requestString = "";
		foreach( $params as $k => $v ) {
	       $requestString .= $k . '=' . $v . '&';
	   	}

	   	$requestString .="key=".config('tft.tft_sign_key');

	   	$pi_key =  openssl_get_privatekey($private_key);
	   	
	   	openssl_sign($requestString, $binary_signature, $pi_key, OPENSSL_ALGO_SHA1);
	   	
	   	openssl_free_key($pi_key);

	   	return base64_encode($binary_signature);
	}
}