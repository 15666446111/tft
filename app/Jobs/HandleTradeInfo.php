<?php

namespace App\Jobs;

use App\Trade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleTradeInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * [$trade 用来接收参数的变量]
     * @var [type]
     */
    protected $trade;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    
    /**
     * 任务可以执行的最大秒数 (超时时间)。
     *
     * @var int
     */
    public $timeout = 180;



    public function __construct(Trade $params)
    {
        $this->trade        = $params;
    }

    

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        /**
         * @version [<vector>] [< 检查该机器是否入库>]
         */
        if (empty($this->trade->merchants_sn)) {
            $this->trade->remark = '仓库中无此终端号!';
            $this->trade->save();
            return false;
        }

        /**
         * @version [<vector>] [< 检查该机器是否发货>]
         */
        if (!$this->trade->merchants_sn->user_id || $this->trade->merchants_sn->user_id == "null") {
            $this->trade->remark = '该机器还未发货!';
            $this->trade->save();
            return false;
        }

        /**
         * @version [<vector>] [< 检查是否是重复推送的数据 >]
         * trans_date: 接口推送的交易日期
         * rrn: 参考号
         */
        $sameTrade = \App\Trade::where('trade_no', $this->trade->trade_no)->where('id', '<>', $this->trade->id)->first();
        if (!empty($sameTrade)) {
            $this->trade->remark    = '该交易为重复推送数据';
            $this->trade->is_repeat = 1;
            $this->trade->save();
            return false;
        }


        /**
         * @version [<vector>] [< 更新机器开通状态和过期状态 >]
         */
        if ($this->trade->merchants_sn->open_state == 0) {

            $this->trade->merchants_sn->open_state = 1;

            $this->trade->merchants_sn->open_time  = $this->trade->trade_time;

            // 更新机器过期状态
            if (!empty($this->trade->merchants_sn->active_end_time) &&
                 date('Y-m-d H:i:s', strtotime($this->trade->trade_time . '+1 day')) > $this->trade->merchants_sn->active_end_time) {

                $this->trade->merchants_sn->overdue_state = 1;

            }

            $this->trade->merchants_sn->save();
        }
        

        /**
         * @version [< 给当前交易进行分润发放 >]
         */
        try {
        	
            $cash = new \App\Http\Controllers\CashController($this->trade);

            $cashResult = $cash->cash();

            $this->trade->remark = $this->trade->remark."<br/>分润:".$cashResult['message'];

            if($cashResult['status'] && $cashResult['status'] !== false){
                $this->trade->is_send = 1;
            }

            $this->trade->save();

        } catch (\Exception $e) {
        	
            $this->trade->remark = $this->trade->remark."<br/>分润:".json_encode($e->getMessage());
            $this->trade->save();
        }
        
        

        /**
         * @version [< 激活返现处理 >]
         */
        try {
            $cash = new \App\Http\Controllers\ActiveMerchantController($this->trade);

            $returnCash = $cash->active();

            if (!empty($returnCash['message'])) {
                $this->trade->remark = $this->trade->remark."<br/>激活:".$returnCash['message'];
            }

            $this->trade->save();

        } catch (\Exception $e) {
            $this->trade->remark = $this->trade->remark."<br/>激活:".json_encode($e->getMessage());
            $this->trade->save();
        }


        /**
         * @version [< 达标返现处理 >]
         */
        try {
            $standard = new \App\Http\Controllers\StandardController($this->trade);

            $standardCash = $standard->standard();

            if (!empty($standardCash['message'])) {
                $this->trade->remark = $this->trade->remark."<br/>达标:".$standardCash['message'];
            }

            $this->trade->save();

        } catch (\Exception $e) {
            $this->trade->remark = $this->trade->remark."<br/>达标:".json_encode($e->getMessage());
            $this->trade->save();
        }
    }
}
