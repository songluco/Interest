<?php

namespace App\Http\Controllers;

use App\Services\FixRateBuilder;
use App\Services\MonthlyInterestRepayMode;
use App\Tools\DateAbout\DateAbout;
use App\Tools\DateAbout\DateMode;
use Illuminate\Http\Request;

class TestController extends Controller
{
    //
    public function anyIndex()
    {

        $project = [
            'project_id'          => 1002, //产品ID
            'project_name'        => '计息系统计算用户还款计划产品1002', //产品ID
            'project_mode'        => 1, //产品还款类型
            'start_interest_date' => '2017-08-10', //产品起息日
            'end_interest_date'   => '2018-8-30', //产品结束日
            'annual_income'       => '6', //产品年化收益率
            'interest_base_days'  => 360, //计息天数基数
            'financier_id'        => 2, //融资方id
            'project_repay_day'   => 2, //产品还款日（非必填字段）
        ];

        $orders[1] = [
            'order_amount'    => 2000, //订单金额
            'user_id'         => 10086001, //此订单的用户ID
            'order_id'        => 20086001, //订单ID
        ];
        $orders[2] = [
            'order_amount'    => 1000, //订单金额
            'user_id'         => 10086002, //此订单的用户ID
            'order_id'        => 20086002, //订单ID
        ];
        $orders[3] = [
            'order_amount'    => 3000, //订单金额
            'user_id'         => 10086003, //此订单的用户ID
            'order_id'        => 20086003, //订单ID
        ];

        $model = new MonthlyInterestRepayMode($project, $orders);
        $data = $model->buildUserPayPlans();

        dd($data);
//        $projectRequireKeys = [
//            'project_id', //产品ID
//            'project_mode', //产品还款类型
//            'start_interest_date', //产品起息日
//            'end_interest_date', //产品结束日
//            'annual_income', //产品年化收益率
//            'interest_base_days', //计息天数基数
//            'financier_id', //融资方id
//        ];

        $data = new FixRateBuilder();



        $init = [
            'code' => 0,
            'msg' => '处理成功!',
            'data' => []
        ];

        dd(returnData($init));




        $dataA = '2017-08-09';
        $dataB = '2018-08-08';
        $result = DateAbout::diffBetweenTwoDays($dataA, $dataB);
//        $monthly = DateMode::Monthly($dataA, $dataB, 5);
        dd($result);
    }
}
