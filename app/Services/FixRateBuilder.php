<?php

/**
 * Created by PhpStorm.
 * User: songlu
 * Date: 2017/8/9
 * Time: 下午11:36
 */

namespace App\Services;

use App\Tools\DateAbout\DateAbout;
use App\Tools\DateAbout\DateMode;

class FixRateBuilder
{
    const ERROR_CODE_NONE = 0;    //无异常
    const ERROR_CODE_DATA_MISSING = 1001; //未获取到传入数据
    const ERROR_CODE_RAWDATA = 1002; //传入数据不合法
    const ERROR_CODE_MODE_ERROR = 1003; //传入产品还款类型不能使用当前的类进行计算

    const PROJECT_MODE = 1; //一次性还本付息产品还款方式


    /**
     * @var array 产品
     */
    protected $project;

    /**
     * @var array 产品下所有订单信息
     */
    protected $orders;

    /**
     * @var 单笔订单信息
     */
    protected $singleOrder;
    /**
     * @var 产品还款时间段
     */
    protected $projectPrepaymentPeriod;

    public function __construct(array $project, array $orders)
    {
        $this->project = $project;
        $this->orders = $orders;
    }

    public function build()
    {
        //对获取到的数据进行验证和转换
        $transferRes = $this->transferRecordData($this->project, $this->orders);
        if ($transferRes) {
            return $transferRes;
        }
        //验证产品还款方式
        if ($this->project['project_mode'] != self::PROJECT_MODE) {
            return funcReturn(self::ERROR_CODE_MODE_ERROR, '还款类型有误' . $this->project['project_mode']);
        }
        //计算用户还款计划
//        $userPayPlans = $this->calculateUserPayPlans();
//        $userPayPlans2 = $this->calculateUserPayPlans2();
        $userPayPlans = $this->calculateUserPayplansByMonthly();

        return funcReturn(self::ERROR_CODE_NONE, '还款信息', $userPayPlans);
    }

    public function transferRecordData(array $project, array $orders)
    {
        //如果处理结果有错误 则终止处理
        $transferProjectRes = $this->transferRecordProjectData($project);
        if ($transferProjectRes['code']) {
            return $transferProjectRes;
        }

        $transferOrderRes = $this->transferRecordOrderData($orders);
        if ($transferOrderRes['code']) {
            return $transferOrderRes;
        }
        $this->project = $transferProjectRes['data'];
        $this->orders = $transferOrderRes['data'];
    }

    /**
     * 对获取到的产品数据进行转换和校验
     * @param $project
     * @return array
     */
    public function transferRecordProjectData($project)
    {
        //请求时,产品必须的字段
        $projectRequireKeys = [
            'project_id', //产品ID
            'project_mode', //产品还款类型
            'start_interest_date', //产品起息日
            'end_interest_date', //产品结束日
            'annual_income', //产品年化收益率
            'interest_base_days', //计息天数基数
            'financier_id', //融资方id
        ];


        //初始化返回数据
        $returnData = [];
        //验证数据,并对数据进行填充
        if (isset($project['project_id'])) {
            $returnData['project_id'] = $project['project_id'];
        }
        if (isset($project['project_name'])) {
            $returnData['project_name'] = $project['project_name'];
        }
        if (isset($project['project_mode'])) {
            $returnData['project_mode'] = $project['project_mode'];
        }
        if (isset($project['start_interest_date'])) {
            $returnData['start_interest_date'] = $project['start_interest_date'];
        }
        if (isset($project['end_interest_date'])) {
            $returnData['end_interest_date'] = $project['end_interest_date'];
        }
        if (isset($project['annual_income'])) {
            $returnData['annual_income'] = $project['annual_income'];
        }
        if (isset($project['interest_base_days'])) {
            $returnData['interest_base_days'] = $project['interest_base_days'];
        }
        if (isset($project['financier_id'])) {
            $returnData['financier_id'] = $project['financier_id'];
        }

        $keys = array_keys($returnData);

        //校验传输的数组是否完整
        foreach ($projectRequireKeys as $key) {
            if (!in_array($key, $keys)) {
                return funcReturn(self::ERROR_CODE_DATA_MISSING, '缺少数据' . $key);
            }
        }
        //返回数据
        return funcReturn(self::ERROR_CODE_NONE, 'ok', $returnData);
    }

    /**
     * 对获取到的订单数据进行转换和校验
     * @param $orders
     * @return array
     */
    public function transferRecordOrderData($orders)
    {
        //请求时,订单必须的字段
        $orderRequireKeys = [
            'order_amount', //订单金额
            'user_id', //此订单的用户ID
            'order_id', //订单ID
        ];

        //初始化返回数据
        $returnData = [];
        //验证数据,并对数据进行填充
        foreach ($orders as $order) {
            $tmp = [];
            if (isset($order['order_amount'])) {
                $tmp['order_amount'] = $order['order_amount'];
            }
            if (isset($order['user_id'])) {
                $tmp['user_id'] = $order['user_id'];
            }
            if (isset($order['order_id'])) {
                $tmp['order_id'] = $order['order_id'];
            }
            //校验订单金额
            if (!isset($tmp['order_amount']) || $tmp['order_amount'] <= 0) {
                return funcReturn(self::ERROR_CODE_RAWDATA, '数据不合法' . 'order_amount');
            }

            //校验传输的数组是否完整
            $keys = array_keys($tmp);
            foreach ($orderRequireKeys as $key) {
                if (!in_array($key, $keys)) {
                    return funcReturn(self::ERROR_CODE_DATA_MISSING, '缺少数据' . $key);
                }
            }
            $returnData[] = $tmp;
        }
        //返回数据

        return funcReturn(self::ERROR_CODE_NONE, 'ok', $returnData);
    }

    public function calculateUserPayplansByMonthly()
    {
        //计算产品还款时间段
        $dataA = '2017-08-09';
        $dataB = '2018-08-08';
        $this->projectPrepaymentPeriod = DateMode::Monthly($dataA, $dataB, 5);
        $userPayPlans = [];
        foreach ($this->orders as $order) {
            $this->singleOrder = $order;
            $userPayPlans[$order['order_id']] = $this->singleOrderBuildUserPayPlans();
        }
        $returnRes['project_id'] = $this->project['project_id'];
        $returnRes['project_name'] = $this->project['project_name'];
        $returnRes['userPayPlans'] = $userPayPlans;
        return $returnRes;
    }

    public function singleOrderBuildUserPayPlans()
    {
        foreach ($this->projectPrepaymentPeriod['serials'] as $serials) {
            $tmp = [];
            $tmp['order_id']            = $this->singleOrder['order_id'];
            $tmp['order_amount']        = $this->singleOrder['order_amount'];
            $tmp['user_id']             = $this->singleOrder['user_id'];
            $tmp['interest_days']       = $serials['days'];
            $tmp['financier_interest']  = $this->calculateInterest($tmp['order_amount'], $tmp['interest_days'], $this->project['annual_income'], $this->project['interest_base_days']);
            $tmp['user_interest']       = $this->calculateInterest($tmp['order_amount'], $tmp['interest_days'], $this->project['annual_income'], $this->project['interest_base_days']);
            $tmp['financier_principal'] = $tmp['order_amount'];
            $tmp['user_principal']      = $tmp['order_amount'];
            $tmp['financier_amount']    = displayAmountNoComma($tmp['financier_interest'] + $tmp['financier_principal']);
            $tmp['user_amount']         = displayAmountNoComma($tmp['user_interest'] + $tmp['user_principal']);
            $tmp['current_period']      = $serials['serial_no'];
            $tmp['period']              = $this->projectPrepaymentPeriod['no'];
            $tmp['discount']            = $tmp['user_amount'] - $tmp['financier_amount'];
            $tmp['discount_type']       = 0;
            $tmp['start_interest_date'] = $serials['start'];
            $tmp['end_interest_date']   = $serials['end'];
            $userPayPlans[] = $tmp;
        }
        return $userPayPlans;
    }
    


    public function calculateUserPayPlans()
    {
        //计算产品还款时间段
        $this->projectPrepaymentPeriod = $this->projectPrepaymentPeriod();
        //初始化用户还款计划
        $userPayPlans = [];
        foreach ($this->orders as $order) {
            $tmp = [];
            $tmp['order_id']            = $order['order_id'];
            $tmp['order_amount']        = $order['order_amount'];
            $tmp['user_id']             = $order['user_id'];
            $tmp['interest_days']       = $this->projectPrepaymentPeriod['serials'][1]['days'];
            $tmp['financier_interest']  = $this->calculateInterest($tmp['order_amount'], $tmp['interest_days'], $this->project['annual_income'], $this->project['interest_base_days']);
            $tmp['user_interest']       = $this->calculateInterest($tmp['order_amount'], $tmp['interest_days'], $this->project['annual_income'], $this->project['interest_base_days']);
            $tmp['financier_principal'] = $tmp['order_amount'];
            $tmp['user_principal']      = $tmp['order_amount'];
            $tmp['financier_amount']    = displayAmountNoComma($tmp['financier_interest'] + $tmp['financier_principal']);
            $tmp['user_amount']         = displayAmountNoComma($tmp['user_interest'] + $tmp['user_principal']);
            $tmp['current_period']      = 1;
            $tmp['discount']            = $tmp['user_amount'] - $tmp['financier_amount'];
            $tmp['discount_type']       = 0;
            $tmp['start_interest_date'] = $this->project['start_interest_date'];
            $tmp['end_interest_date'] = $this->project['end_interest_date'];
            $userPayPlans[] = $tmp;
        }
        $returnRes['project_id'] = $this->project['project_id'];
        $returnRes['period'] = $this->projectPrepaymentPeriod['no'];
        $returnRes['userPayPlans'] = $userPayPlans;
        return $returnRes;
    }


    public function calculateUserPayPlans2()
    {
        //计算产品还款时间段
        $this->projectPrepaymentPeriod = $this->projectPrepaymentPeriod();
        //初始化用户还款计划
        $userPayPlans = [];

        foreach ($this->orders as $order) {
            $tmp = [];
            $tmp['order_id']            = $order['order_id'];
            $tmp['order_amount']        = $order['order_amount'];
            $tmp['user_id']             = $order['user_id'];
//            $tmp['project_id']          = $this->project['project_id'];
//            $tmp['project_mode']        = $this->project['project_mode'];
//            $tmp['financier_id']        = $this->project['financier_id'];
//            $tmp['annual_income']       = $this->project['annual_income'];
            $tmp['interest_days']       = $this->projectPrepaymentPeriod['serials'][1]['days'];
            $tmp['financier_interest']  = $this->calculateInterest($tmp['order_amount'], $tmp['interest_days'], $this->project['annual_income'], $this->project['interest_base_days']);
            $tmp['user_interest']       = $this->calculateInterest($tmp['order_amount'], $tmp['interest_days'], $this->project['annual_income'], $this->project['interest_base_days']);
            $tmp['financier_principal'] = $tmp['order_amount'];
            $tmp['user_principal']      = $tmp['order_amount'];
            $tmp['financier_amount']    = displayAmountNoComma($tmp['financier_interest'] + $tmp['financier_principal']);
            $tmp['user_amount']         = displayAmountNoComma($tmp['user_interest'] + $tmp['user_principal']);
            $tmp['current_period']      = 1;
            $tmp['period']              = 1;
            $tmp['start_interest_date'] = $this->project['start_interest_date'];
            $tmp['end_interest_date'] = $this->project['end_interest_date'];
            $userPayPlans[$order['order_id']][1] = $tmp;
        }
        $returnRes['project'] = $this->project;
        $returnRes['userPayPlans'] = $userPayPlans;
        return $returnRes;
    }

    public function calculateInterest($principal_amount, $interest_days, $annual_income, $interest_base_days)
    {
        $interest = $principal_amount * $interest_days * $annual_income / 100 / $interest_base_days;
        $interestRes = sprintf("%.2f", substr(sprintf("%.4f", $interest), 0, -2));
        return $interestRes;
    }


    public function projectPrepaymentPeriod()
    {
        $period['no'] = 1;
        $current_period = 1;
        $period['serials'][$current_period]['serials_no'] = 1;
        $period['serials'][$current_period]['start'] = $this->project['start_interest_date'];
        $period['serials'][$current_period]['end'] = $this->project['end_interest_date'];
        $period['serials'][$current_period]['days'] = DateAbout::diffBetweenTwoDays($this->project['start_interest_date'], $this->project['end_interest_date']);
        return $period;
    }

}