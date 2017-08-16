<?php
/**
 * Created by PhpStorm.
 * User: songlu
 * Date: 2017/8/10
 * Time: 下午3:59
 */

namespace App\Services;


class EntryInterest
{
    /**
     * @var 产品信息
     */
    protected $project;

    /**
     * @var 产品下所有订单信息
     */
    protected $orders;

    /**
     * @var 产品ID
     */
    protected $projectId;


    /**
     * @var 支持的所有产品还款类型
     */
    protected $supportiveProjectRepayModes = [
        '1' => 'FixRateRepayMode',
        '2' => 'MonthlyInterestRepayMode'
    ];

    /**
     * @var 产品还款类型
     */
    protected $repayMode;





//    protected $repayModel = [
//        '1' => 'FixRateRepayMode',
//        '2' => 'MonthlyInterestRepayMode'
//    ];

    public function __construct(array $project, array $orders)
    {
        $this->project = $project;
        $this->orders = $orders;
        $this->projectId = $this->project['project_id'];
    }

    public function buildUserPayPlans()
    {
        //获取产品还款类型
        $repayModeRes = $this->getProjectRepayMode();
        if($repayModeRes['code']){
            return $repayModeRes;
        }

        //计算用户还款计划数据
        $this->repayMode->buildUserPayPlans();





    }

    public function getProjectRepayMode()
    {
        //产品还款类
        $class = __NAMESPACE__ . '\\' . $this->supportiveProjectRepayModes[$this->project['repay_mode']];

        //验证 产品还款类 是否存在
        if(!class_exists($class)){
            $resMsg = '产品还款类型不存在!产品ID:' . $this->projectId;
            return funcReturn(MsgCode::ERROR_CODE_NO_PROJECT_MODE, $resMsg);
        }

        $this->repayMode = new $class;
        return funcReturn(MsgCode::SUCCESS_CODE, 'ok');
    }



}