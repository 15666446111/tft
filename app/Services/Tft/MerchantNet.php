<?php

namespace App\Services\Tft;

use App\Merchant;
use GuzzleHttp\Client as GuzzClient;

class MerchantNet
{
	/**
	 * [$data  要操作的商户信息]
	 * @var [type]
	 */
	protected $data;

	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-10
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 初始化操作 赋值要操作的商户信息 ]
	 * @param     Merchant    $params [description]
	 */
	public function __construct(Merchant $params)
	{
		$this->data 	= $params;
	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-10
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 商户入网 讲信息提交到腾付通入网 ]
	 * @return    [type]      [description]
	 */
	public function in()
	{

		set_time_limit(0); 
		ignore_user_abort(1); 

		$body = array(
			'mch_name'		=>	$this->data->name,					// 商户名称
			'mch_abbr'		=>	$this->data->name_attr,				// 商户简称
			'mch_type'		=>	(string)$this->data->mch_type,				// 商户类型
			'mch_category'	=>	(string)$this->data->mch_category,			// 商户业务类别 0-实体 1-电签pos 2-收单 3-MPOS 4-POS
			'mcc'			=>	$this->data->mcc,					// MCC
			'addr'			=>	$this->data->addr,					// 商户经营地址
			'area_code'		=>	$this->data->area_code,				// 商户地区码
			'owner_name'	=>	$this->encrypt($this->data->owner_name),			// 商户法人姓名
			'owner_mobile'	=>	$this->data->phone,   				// 商户法人手机号
			'owner_cert_id'	=>	$this->encrypt($this->data->owner_cert_id),	// 商户法人身份证号(加密)
			'owner_cert_sdate'	=>	$this->data->owner_cert_sdate,	// 身份证有效期起始时间 	YYYY-MM-DD
			'owner_cert_edate'	=>	$this->data->owner_cert_edate,	// 身份证有效期结束时间		YYYY-MM-DD
			'settle_acc_no'		=>	$this->encrypt($this->data->settle_acc_no),		// 结算银行账户账号(加密)
			'settle_acc_name'	=>	$this->encrypt($this->data->settle_acc_name),	// 结算银行账户户名(加密)
			'settle_acc_type'	=>	(string)$this->data->settle_acc_type,	// 银行卡账户类型(0-对私账户，2-对公账户)
			'settle_bank_name'	=>	$this->data->settle_bank_name,	// 结算银行名称
			'd0_flag'			=>	$this->data->d0_flag,			// D0结算标志(0-不开通，1-开通)，不填默认开通
			'contact_name'		=>	$this->data->owner_name,		//	联系人姓名
			'contact_mobile'	=>	$this->data->phone,				//	联系手机号(商户平台登录账号)
		);


		if($this->data->mch_type != '2'){
			$body['license_no']		=	$this->data->license_no;		//	营业执照号mch_type=0或者1时必填
			$body['license_sdate']	=	$this->data->license_sdate;		//	营业执照有效期开始日期(YYYY-MM-DD)	mch_type=0或者1时必填
			$body['license_edate']	=	$this->data->license_edate;		//	营业执照有效期结束日期(YYYY-MM-DD)	mch_type=0或者1时必填
		}

		if($this->data->settle_acc_type == "2"){
			$body['settle_bank_branch']		=	$this->data->settle_bank_branch;	//结算银行支行名称(银行账户类型是对公时必填)
			$body['settle_bank_branch_no']	=	$this->data->settle_bank_branch_no; //结算银行联行号(银行账户类型是对公时必填)
		}

		$result = json_decode($this->send($body));

		$this->data->api_return = json_encode($result);

		if($result->resp_code != "00"){
			$this->data->verfity_state 	= -1;
			$this->data->remark 		= $result->resp_msg;
			$this->data->save();
			return response()->json(['error'=>['message' => $result->resp_msg]]);
		}else{

			$this->data->verfity_state 	= 1;
			$this->data->code 		= $result->mch_id;
			$this->data->mech_key 	= $result->mch_key;
			$this->data->save();

			$applation = new \App\Services\Tft\MerchantRate($this->data);

			$rate = $applation->in();

			/**
			 *  费率设置信息
			 */
			if($rate->resp_code != "0"){
				$this->data->remark 		= '业务开通失败';
				$this->data->save();
				return response()->json(['error'=>['message' => '业务开通失败!']]);
			}else{
				return response()->json(['success'=>['message' => '商户开通成功,请绑定机器!']]);
			}
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
		$params['req_type']		=	'mch_add';
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