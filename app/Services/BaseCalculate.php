<?php
/**
 * Created by PhpStorm.
 * User: songlu
 * Date: 2017/8/13
 * Time: 下午3:54
 */

namespace App\Services;


use App\TransFormer\BaseTransformer;

abstract class BaseCalculate
{
    /**
     * @var array 产品信息
     */
    protected $project;
    /**
     * @var array 订单信息
     */
    protected $orders;
    /**
     * @var mixed 产品ID
     */
    protected $projectId;
    /**
     * @var BaseTransformer 数据过滤类
     */
    protected $transFormer;
    /**
     * @var 产品还款时间段
     */
    protected $projectPrepaymentPeriod;

    public function __construct(array $project, array $orders)
    {
        $this->project = $project;
        $this->orders = $orders;
        $this->projectId = $this->project['project_id'];
        $this->transFormer = new BaseTransformer();
    }

    public function calculateInterest($principal_amount, $interest_days, $annual_income, $interest_base_days)
    {
        $interest = $principal_amount * $interest_days * $annual_income / 100 / $interest_base_days;
        $interestRes = sprintf("%.2f", substr(sprintf("%.4f", $interest), 0, -2));
        return $interestRes;
    }

    abstract public function buildUserPayPlans();

}