<?php

namespace App\Services\Tft;

use App\Machine;
use App\Merchant;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzClient;

class MerchantDeduction
{
	/**
	 * [$data  要操作终端信息 ]
	 * @var [type]
	 */
	protected $temial;


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-10
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 初始化操作 赋值要操作的商户信息 ]
	 * @param     Merchant    $params [description]
	 */
	public function __construct(Machine $temial)
	{
		$this->temial 	= $temial;
	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-10
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 终端绑定  ]
	 * @return    [type]      [description]
	 */
	public function deduct()
	{
		$body = array(
			'freeze_type'	=>	"0",
			'freeze_amount'	=>	(string)($this->temial->policys->active_price * 100),
			'limit_days'	=>	"1",
			'unfreeze_rule'	=>	json_encode([ ['achieve_amount'=>500000000, 'unfreeze_amount' => (int)$this->temial->policys->active_price * 100] ]),
			'term_sn'		=>	$this->temial->sn,
			'mf_id'			=>	$this->temial->machines_styles->machines_fact->fid,
		);

		$result = json_decode($this->send($body));

		if($result->resp_code != "00"){

			return response()->json(['error'=>['message' => $result->resp_msg]]);

		}else{
			// 添加冻结信息
			\App\MerchantsFrozenLog::create([
				'merchant_code'	=>	$this->temial->merchants->code,
				'sn'			=>	$this->temial->sn,
				'type'			=>	1,
				'frozen_money'	=>	(int)$this->temial->policys->active_price * 100,
				'state'			=>	3,
				'send_data'		=>	json_encode($body),
				'return_data'	=>	json_encode($result),
				'remark'		=>	'机具费用冻结成功!'
			]);

			return response()->json(['error'=>['message' => '机具费用冻结成功!']]);
		}
	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-15
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 删除冻结规则 ]
	 * @return    [type]      [description]
	 */
	public function deleteDeduct()
	{
		$body = array(
			'mf_id'		=>	$this->temial->machines_styles->machines_fact->fid,
			'term_sn'	=>	$this->temial->sn	
		);

		$result = json_decode($this->send($body, 'term_fee_freeze_delete'));

		if($result->resp_code != "00"){

			return response()->json(['error'=>['message' => $result->resp_msg]]);

		}else{

			return response()->json(['error'=>['message' => '机具费用解冻成功!']]);
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
	public function send($params, $type = null)
	{
		$params['version']		=	'1.0.0';
		$params['timestamp']	=	time();
		$params['nonce_str']	=	"A".time();
		$params['agent_id']		=	config('tft.tft_agent_id');
		$params['req_type']		=	$type ?? 'term_fee_freeze_set';
		$params['sign']			=	$this->sign($params);

		$client     = new GuzzClient(['verify' => false ]);
		//dd($params);
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