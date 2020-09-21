<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Jobs\HandleTradeInfo;
use App\Jobs\HandleMachineInfo;

use Illuminate\Http\Request;
use App\Http\Controllers\TestController;

class TradeApiController extends Controller
{
    

    /**
     * @Author    Pudding
     * @DateTime  2020-09-16
     * @copyright [copyright]
     * @license   [license]
     * @version   [ 腾付通异步通知推送 ]
     * @param     Request     $request [description]
     * @return    [type]               [description]
     */
    public function index(Request $request)
    {
        // 写入到推送信息
        $trade_push = \App\RegisterNotice::create([
            'title'     =>  '腾付通异步通知推送数据',
            'content'   =>  json_encode($request->all()),
            'other'     =>  json_encode([
                '请求方式'  => $request->getMethod(), 
                '请求地址'  => $request->ip(), 
                '端口'     => $request->getPort(), 
                '请求头'   => $request->header('Connection')
            ]),
        ]);


        /**
         * 验签
         
        {"agent_id":"854658000180","amount":"1900","card_type":"2","channel_reference_number":"181007028283","fee":"7","mch_id":"484451956410001","notify_type":"pay_notify","order_id":"2020091518100791771001","pay_time":"2020-09-15 18:10:07","pay_type":"1","pos_batch_no":"000002","pos_flow_id":"000007","sign":"IkDZClZsNNeThQY\/mMBO9nyWcN4fOYbJ9co3VUUo6UtBTXIK38kG8OpxQD91ayTfbEm3znyKM4A2gIb2HcujUyDxmhMAgXuS+MHph3hmRIiuu08nZMvSetrx5U7i6BUGk9zrU07h9NFseb6EgzY1ZDbxim1MLJt689ONImdNgRE=","term_id":"61123089","term_sn":"00006502V90T1S003001"}

        */

        $temial = \App\Machine::whereSn($request->term_sn)->where('temial_no', $request->term_id)->first();

        /**
         * [ 商户交易 通知 ]
         * @var [type]
         */
        if($request->notify_type == "pay_notify"){

            $type = 0;     
            if($request->pay_type == "0"){
                $type = 0; 
            }
            if($request->pay_type == "1"){
                $type = 1;
            }

            if($request->pay_type == "2" ){
                $type = 4;
            }

            if($request->pay_type == "3" ){
                $type = 2;
            }

            if($request->pay_type == "4" ){
                $type = 3;
            }


            $tradeOrder = \App\Trade::create([
                'trade_no'  =>  $request->order_id,
                'user_id'   =>  $temial->user_id,
                'machine_id'=>  $temial->id,
                'term_id'   =>  $request->term_id,
                'sn'        =>  $request->term_sn,
                'merchant_name'     =>  $temial->merchants->name,
                'merchant_phone'    =>  $temial->merchants->phone,
                'merchant_code'     =>  $request->mch_id,
                'agt_merchant_id'   =>  $request->mch_id,
                'sys_trace_no'      =>  $request->pos_batch_no,
                'rrn'               =>  $request->channel_reference_number,
                'amount'            =>  $request->amount,
                'settle_amount'     =>  $request->amount-$request->fee,
                'card_type'         =>  $request->card_type -1,
                'trans_date'        =>  date('Ymd', strtotime($request->pay_time)),
                'trade_time'        =>  $request->pay_time,
                'trace_no'          =>  $request->pos_flow_id,
                'fee_type'          =>  'B',
                'operate'           =>  $temial->operate,
                'tran_code'         =>  $type,
                'agent_id'          =>  $request->agent_id,
                'input_mode'        =>  0,
            ]);

            HandleTradeInfo::dispatch($tradeOrder)->onQueue('trade');


            /**
             *  @验证是否是激活交易
             */
            $deduct = \App\MerchantsFrozenLog::where('sn', $request->term_sn)->where('type', 1)->where('frozen_money', $request->amount)->where('state', 3)->where('merchant_code', $request->mch_id)->first();

            if(!empty($deduct)){

                $deduct->state = 4;
                $deduct->save();

                // 去激活返现
                $temial->activate_state = 1;
                $temial->activate_time  = Carbon::now()->toDateTimeString();

                $temial->save();

                $temial->merchants->activate_sn = $request->term_sn;
                $temial->merchants->save();

                \App\ActivesLog::create([ 
                    'merchant_code'     => $temial->merchants->code,
                    'sn'                => $request->term_sn,
                    'user_id'           => $temial->user_id,
                    'type'              => 1
                ]);

                /**
                 * 发放激活返现
                 */
                $applation = new \App\Http\Controllers\CrontabController();
                $result    = $applation->froMachineActive($temial, $tradeOrder->trade_no);

            }
        }

        
        die('SUCCESS');
    }
}
