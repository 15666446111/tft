<?php

namespace App\Services\Tft;

use App\Merchant;
use GuzzleHttp\Client as GuzzClient;

class MerchantRate
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
		$body = array( 'mch_id'	=>	$this->data->code, 'biz_fee' =>	json_encode($this->getFee()));

		$result = json_decode($this->send($body));

		return $result;
	}

	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-16
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [version]
	 * @return    [type]      [description]
	 */
	public function query()
	{
		$body = array( 'mch_id'	=>	$this->data->code);
	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-11
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 整理商户费率 ]
	 * @return    [type]      [description]
	 */
	public function getFee()
	{	
		// 0-POS银行卡 贷记卡
		$bankPosD = array(
			'biz_type'	=>	0,
			'card_type'	=>	2,
			'fee_mode'	=>	0,
			'fee_rule'	=>	array(
				'compute_mode'	=>	0,
				'fee_rate'		=>	58,
				'fixed_fee'		=>	0,
				'fee_min'		=>	0,
				'fee_max'		=>	0,
			),
			'd0_fee_rate'	=>	2,
			'd0_extra_fee'	=>	0,
		);

		// 0-POS银行卡 借记卡
		$bankPosJ = array(
			'biz_type'	=>	0,
			'card_type'	=>	1,
			'fee_mode'	=>	0,
			'fee_rule'	=>	array(
				'compute_mode'	=>	0,
				'fee_rate'		=>	48,
				'fixed_fee'		=>	0,
				'fee_min'		=>	0,
				'fee_max'		=>	2500,
			),
			'd0_fee_rate'	=>	2,
			'd0_extra_fee'	=>	0,
		);

		// 银行卡闪付 1000以内免密支付
		$cardQuickD = array(
			'biz_type'	=>	1,
			'card_type'	=>	2,
			'fee_mode'	=>	0,
			'fee_rule'	=>	array(
				'compute_mode'	=>	0,
				'fee_rate'		=>	36,
				'fixed_fee'		=>	0,
				'fee_min'		=>	0,
				'fee_max'		=>	0,
			),
			'd0_fee_rate'	=>	2,
			'd0_extra_fee'	=>	0,
		);
		// 银行卡闪付 借记卡 1000以内
		$cardQuickJ = array(
			'biz_type'	=>	1,
			'card_type'	=>	1,
			'fee_mode'	=>	0,
			'fee_rule'	=>	array(
				'compute_mode'	=>	0,
				'fee_rate'		=>	36,
				'fixed_fee'		=>	0,
				'fee_min'		=>	0,
				'fee_max'		=>	0,
			),
			'd0_fee_rate'	=>	2,
			'd0_extra_fee'	=>	0,
		);

		// 微信
		$wx = array(
			'biz_type'	=>	2,
			'card_type'	=>	0,
			'fee_mode'	=>	0,
			'fee_rule'	=>	array(
				'compute_mode'	=>	0,
				'fee_rate'		=>	36,
				'fixed_fee'		=>	0,
				'fee_min'		=>	0,
				'fee_max'		=>	0,
			),
			'd0_fee_rate'	=>	2,
			'd0_extra_fee'	=>	0,
		);

		// 支付宝
		$ali = array(
			'biz_type'	=>	3,
			'card_type'	=>	0,
			'fee_mode'	=>	0,
			'fee_rule'	=>	array(
				'compute_mode'	=>	0,
				'fee_rate'		=>	36,
				'fixed_fee'		=>	0,
				'fee_min'		=>	0,
				'fee_max'		=>	0,
			),
			'd0_fee_rate'	=>	2,
			'd0_extra_fee'	=>	0,
		);

/*		// 银联二维码。 贷记卡
		$codeD = array(
			'biz_type'	=>	4,
			'card_type'	=>	2,
			'fee_mode'	=>	1,
			'fee_rule'	=>	array(
				array(
					'start_amount'	=>	0,
					'end_amount'	=>	100000,
					'compute_mode'	=>	0,
					'fee_rate'		=>	36,
					'fixed_fee'		=>	0,
					'fee_min'		=>	0,
					'fee_max'		=>	0,
				),array(
					'start_amount'	=>	100001,
					'end_amount'	=>	-1,
					'compute_mode'	=>	0,
					'fee_rate'		=>	58,
					'fixed_fee'		=>	0,
					'fee_min'		=>	0,
					'fee_max'		=>	0,
				)	
			),
			'd0_fee_rate'	=>	2,
			'd0_extra_fee'	=>	0,
		);

		// 银联二维码。 借记卡
		$codeJ = array(
			'biz_type'	=>	4,
			'card_type'	=>	1,
			'fee_mode'	=>	1,
			'fee_rule'	=>	array(
				array(
					'start_amount'	=>	0,
					'end_amount'	=>	100000,
					'compute_mode'	=>	0,
					'fee_rate'		=>	36,
					'fixed_fee'		=>	0,
					'fee_min'		=>	0,
					'fee_max'		=>	0,
				),array(
					'start_amount'	=>	100001,
					'end_amount'	=>	-1,
					'compute_mode'	=>	0,
					'fee_rate'		=>	48,
					'fixed_fee'		=>	0,
					'fee_min'		=>	0,
					'fee_max'		=>	2500,
				)	
			),
			'd0_fee_rate'	=>	2,
			'd0_extra_fee'	=>	0,
		);*/


		return array($bankPosD, $bankPosJ, $cardQuickD, $cardQuickJ, $wx, $ali, /*$codeD, $codeJ*/);
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
		$params['req_type']		=	$type ?? 'mch_biz_open_m';
		$params['sign']			=	$this->sign($params);


		$client     = new GuzzClient(['verify' => false ]);

	    $result 	= $client->request('POST', config('tft.tft_url'), [ 'json' => $params  ]);

	    $resultBody = $result->getBody()->getContents();

	    return	$resultBody;
		
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