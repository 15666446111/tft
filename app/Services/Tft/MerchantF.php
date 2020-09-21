<?php

namespace App\Services\Tft;

use App\Merchant;
use GuzzleHttp\Client as GuzzClient;

class MerchantF
{


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-10
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 初始化操作 赋值要操作的商户信息 ]
	 * @param     Merchant    $params [description]
	 */
	public function __construct()
	{

	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-17
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 查询商户余额 ]
	 * @return    [type]      [description]
	 */
	public function queryCash()
	{
		$body = array();

		dd($this->send($body));
	}

	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-17
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 商户余额提现]
	 * @return    [type]      [description]
	 */
	public function draw()
	{

		$body = array(
			'order_id' => "d123456789", 
			'amount' => "1000",
			'type'	 => "1",
			'bank_acc_no'	=>	$this->encrypt("6212261602008600316"),
			'bank_acc_name'	=>	$this->encrypt("巩克"),
			'bank_acc_type'	=>	"0",
			'bank_name'		=>	"中国工商银行",
		);

		dd($this->send($body, 'agent_profit_withdraw'));
	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-17
	 * @copyright [copyright]
	 * @license   [代理商提现查询]
	 * @version   [version]
	 * @return    [type]      [description]
	 */
	public function drawQuery()
	{
		$body = array('order_id' => "d12345678");

		dd($this->send($body, 'agent_withdraw_query'));
	}


	/**
	 * 对账单文件下载
	 */
	public function fileUpload()
	{
		$body = array('file_date' => "2020-09-17", 'file_type' => "4");
		dd($this->sendfile($body, 'acc_file_download'));
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
		$params['req_type']		=	$type ?? 'agent_profit_query';
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
	 * @version   [ 建立 http 链接 发送请求 ]
	 * @param     [type]      $params [description]
	 * @return    [type]              [description]
	 */
	public function sendfile($params, $type = null)
	{
		$params['version']		=	'1.0.0';
		$params['timestamp']	=	time();
		$params['nonce_str']	=	"A".time();
		$params['agent_id']		=	config('tft.tft_agent_id');
		$params['req_type']		=	$type ?? 'agent_profit_query';
		$params['sign']			=	$this->sign($params);

		$resource = fopen('./deduct.csv', 'w');

		$client     = new GuzzClient(['verify' => false ]);
		$result 	= $client->request('POST', config('tft.tft_url'), [ 'json' => $params, 'sink' => $resource ]);
        
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