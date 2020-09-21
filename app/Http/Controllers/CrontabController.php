<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Jobs\NewSimFrozen;
use App\Http\Controllers\Controller;
use App\Services\Pmpos\PmposController;

/**
 * 
 */
class CrontabController extends Controller
{
	/**
	 * [$tradeOrder 交易记录订单号]
	 * @var [type]
	 */
	protected $tradeOrder;
	
	/**
	 * [froMachineActive 冻结机器激活方法]
	 * @return [type] [description]
	 */
	public function froMachineActive($temial, $orderNo)
	{

		$this->tradeOrder = $orderNo;

		$pattern = \App\AdminSetting::where('operate_number', $temial->operate)->value('pattern');
		
		// 添加分润记录、更新代理钱包余额
	    if ($pattern == 1) {
	    	// 联盟模式
	        $this->addUserBalance($temial->user_id, $temial->policys->default_active, 3, $temial->operate);
	    } else {
	    	// 工具模式
	        $this->toolCashBack($temial->user_id, $temial->policys->id, $temial->policys->default_active_set,$temial->operate);
	    }


	    $pUserId = \App\User::where('id', $temial->user_id)->value('parent');

		if ($pUserId > 0) {

			// 间推激活返现
			if ($temial->policys->indirect_active > 0) {
				$this->addUserBalance($pUserId, $temial->policys->indirect_active, 4, $temial->operate);
			}

			// 间间推激活返现
			$ppUserId = \App\User::where('id', $pUserId)->value('parent');
			if ($ppUserId > 0 && $temial->policys->in_indirect_active > 0) {
				$this->addUserBalance($ppUserId, $temial->policys->in_indirect_active, 5, $temial->operate);

			}

		}
	}

    /**
     * [工具模式直推激活返现]
     * @param  [type] $userId           [用户id]
     * @param  [type] $policyId         [活动id]
     * @param  [type] $defaultActiveSet [活动默认返现设置]
     * @param  [type] $operate          [操盘号]
     * @return [type]                   [description]
     */
    public function toolCashBack($userId, $policyId, $defaultActiveSet, $operate)
    {
        $prevReturnMoney = 0;

        $returnUserId = $userId;

        while ($returnUserId > 0) {

            // 用户返现金额
            $returnMoney = \App\UserPolicy::where('user_id', $returnUserId)->where('policy_id', $policyId)->value('default_active_set');

            // 未设置过用户的返现金额时，按默认激活返现金额处理
            if (empty($returnMoney)) {
                $defaultActive = json_decode($defaultActiveSet);
                $returnMoney = $defaultActive->default_money * 100;
            }

            $money = ($returnMoney - $prevReturnMoney) / 100;

            if ($money > 0) {
            	// 类型，3为直营激活返现，11为团队激活返现
            	$type = $returnUserId == $userId ? 3 : 11;
            	// 增加用户余额并添加分润记录
                $this->addUserBalance($returnUserId, $money, $type, $operate);
                $prevReturnMoney = $returnMoney;
            }

            $returnUserId = \App\User::where('id', $returnUserId)->value('parent');
        }
        
    }


    /**
     * [addUserBalance 增加用户余额 分润余额 分润记录]
     * @param [type]  $userId [用户id]
     * @param [type]  $money  [分润金额(元)]
     * @param [type]  $type   [类型，3激活返现(直营)，4激活返现(间推)，5激活返现(间间推)]
     * @param [type]  $operate [操盘号]
     */
    public function addUserBalance($userId, $money, $type, $operate)
    {
    	// 添加分润记录
    	\App\Cash::create([
    		'user_id'		=> $userId,
    		'cash_money'	=> $money * 100,
    		'is_run'		=> 0,
    		'cash_type'		=> $type,
    		'operate'		=> $operate,
    		'order'			=> $this->tradeOrder
    	]);
    	// 增加用户余额
    	\App\UserWallet::where('user_id', $userId)->increment('return_blance', $money * 100);
    }
}