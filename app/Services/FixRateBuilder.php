<?php

/**
 * Created by PhpStorm.
 * User: songlu
 * Date: 2017/8/9
 * Time: 下午11:36
 */

namespace App\Services;

use App\Tools\DateAbout\DateMode;

class FixRateBuilder extends BaseCalculate
{

    /**
     * @var 产品还款时间段
     */
    protected $projectPrepaymentPeriod;


    public function __construct(array $project, array $orders)
    {
        parent::__construct($project, $orders);
    }

    /**
     * 生成用户还款计划
     * @return array
     */
    public function buildUserPayPlans()
    {
        //对获取到的数据进行验证和转换
        $transferRes = $this->transferRecordData($this->project, $this->orders);
        if ($transferRes['code']) {
            return $transferRes;
        }

        //拼装用户还款计划数据
        $userPayPlans = $this->buildUserPayPlansData();
        return funcReturn(MsgCode::SUCCESS_CODE, '还款信息', $userPayPlans);
    }

    /**
     * 过滤产品和订单数据
     * @return array
     */
    public function transferRecordData()
    {
        //如果处理结果有错误 则终止处理
        //过滤产品数据
        $projectData = $this->transFormer->transformerProject($this->project);
        if($projectData['code']){
            return funcReturn($projectData);
        }

        //过滤订单数据
        $ordersData = $this->transFormer->transformerOrders($this->orders);
        if($ordersData['code']){
            return funcReturn($projectData);
        }
        $this->project = $projectData['data'];
        $this->orders = $ordersData['data'];
        return funcReturn(MsgCode::SUCCESS_CODE, 'ok');
    }

    /**
     * 拼装用户还款计划数据
     * @return mixed
     */
    protected function buildUserPayPlansData()
    {
        //计算产品还款时间段
        $this->projectPrepaymentPeriod = DateMode::FixRate($this->project['start_interest_date'], $this->project['end_interest_date']);
        //初始化用户还款计划
        $userPayPlans = [];
        foreach ($this->orders as $order) {
            $tmp = [];
            //订单ID
            $tmp['order_id']            = $order['order_id'];
            //订单金额
            $tmp['order_amount']        = $order['order_amount'];
            //此订单用户ID
            $tmp['user_id']             = $order['user_id'];
            //产生利息的天数
            $tmp['interest_days']       = $this->projectPrepaymentPeriod['serials'][1]['days'];
            //融资方应还利息
            $tmp['financier_interest']  = $this->calculateInterest($tmp['order_amount'], $tmp['interest_days'], $this->project['annual_income'], $this->project['interest_base_days']);
            //投资人应得利息
            $tmp['user_interest']       = $this->calculateInterest($tmp['order_amount'], $tmp['interest_days'], $this->project['annual_income'], $this->project['interest_base_days']);
            //融资方应还本金
            $tmp['financier_principal'] = $tmp['order_amount'];
            //投资人应得本金
            $tmp['user_principal']      = $tmp['order_amount'];
            //融资方应还总额
            $tmp['financier_amount']    = displayAmountNoComma($tmp['financier_interest'] + $tmp['financier_principal']);
            //投资人应的总额
            $tmp['user_amount']         = displayAmountNoComma($tmp['user_interest'] + $tmp['user_principal']);
            //当前期数
            $tmp['current_period']      = 1;
            //总期数
            $tmp['period']              = $this->projectPrepaymentPeriod['no'];
            //贴息金额
            $tmp['discount']            = $tmp['user_amount'] - $tmp['financier_amount'];
            //贴息类型
            $tmp['discount_type']       = 0;
            //本期利息开始时间
            $tmp['start_interest_date'] = $this->project['start_interest_date'];
            //本期利息结束时间
            $tmp['end_interest_date'] = $this->project['end_interest_date'];
            $userPayPlans[] = $tmp;
        }
        $returnRes['project_id'] = $this->project['project_id'];
        $returnRes['project_name'] = $this->project['project_name'];
        $returnRes['period'] = $this->projectPrepaymentPeriod['no'];
        $returnRes['userPayPlans'] = $userPayPlans;
        return $returnRes;
    }


}