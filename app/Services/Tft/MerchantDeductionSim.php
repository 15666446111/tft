<?php

namespace App\Services\Tft;

use Carbon\Carbon;
use App\MerchantsFrozenLog;
use GuzzleHttp\Client as GuzzClient;

class MerchantDeductionSim
{
	/**
	 * [$data  要操作的订单 ]
	 * @var [type]
	 */
	protected $order;


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-10
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 初始化操作 赋值要操作的商户信息 ]
	 * @param     Merchant    $params [description]
	 */
	public function __construct(MerchantsFrozenLog $order)
	{
		$this->order 	= $order;
	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-10
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 终端绑定  ]
	 * @return    [type]      [description]
	 */
	public function sim_deduct()
	{

		// 查询出机器
		$temial = \App\Machine::where('sn', $this->order->sn)->first();

		if(empty($temial)){
			$this->order->remark = '未找到终端机器';
			$this->order->save();
			return json_encode(['code' => 10099, 'message'	=>	'未找到终端']);
		}

		$body = array(
			'order_id'	=>	$this->order->order_no,

			'mf_id'		=>	$temial->machines_styles->machines_fact->fid,

			'term_type'	=>	$temial->machines_styles->style_name,

			'term_sn'	=>	$temial->sn,

			'deduct_type'	=>	"1",

			'deduct_amount'	=>	(string)($this->order->frozen_money),

			'valid_start_date'	=>	date('Y-m-d', strtotime($this->order->start_time)),

			'valid_end_date'	=>	date('Y-m-d', strtotime($this->order->end_time)),
		);

		$this->order->send_data = json_encode($body);

		$result = json_decode($this->send($body));

		$this->order->return_data = json_encode($result);

		if($result->resp_code != "00"){

			$this->order->remark = $result->resp_msg;

			$this->order->save();

			return response()->json(['code' => 10098, 'message' => $result->resp_msg]);

		}else{
			// 添加冻结信息
			$this->order->state = 3;  //  申请提交 但未冻结完成

			$this->order->save();

			return response()->json(['code' => 10000, 'message' => 'SIM冻结申请成功!']);
		}
	}


	/**
	 * @Author    Pudding
	 * @DateTime  2020-09-16
	 * @copyright [copyright]
	 * @license   [license]
	 * @version   [ 删除流量卡费]
	 * @return    [type]      [description]
	 */
	public function simDeductDelete()
	{
		$body = array('order_id' => $this->order->order_no);

		dd(json_decode($this->send($body, 'term_fee_deduct_delete')));
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
		$params['req_type']		=	$type ?? 'term_fee_deduct_set';
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