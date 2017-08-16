<?php
/**
 * Created by PhpStorm.
 * User: songlu
 * Date: 2017/8/3
 * Time: 下午1:30
 */

namespace App\Tools\DateAbout;


use Faker\Provider\zh_TW\DateTime;

class DateMode
{

    /**
     * 一次性还本付息还款时间段
     * @param $startDate DateTime 起息日期
     * @param $endDate DateTime 结束日期
     * @return mixed
     */
    public static function FixRate($startDate, $endDate)
    {
        $period['no'] = 1;
        $current_period = 1;
        $period['serials'][$current_period]['serials_no'] = 1;
        $period['serials'][$current_period]['start'] = $startDate;
        $period['serials'][$current_period]['end'] = $endDate;
        $period['serials'][$current_period]['days'] = DateAbout::diffBetweenTwoDays($startDate, $endDate);
        return $period;
    }

    /**
     * 按月付息还款时间段
     * @param $startDate DateTime 开始日期
     * @param $endDate DateTime 结束日期
     * @param $repaymentDay int 每月还款日
     * @return mixed
     */
    public static function MonthlyInterest($startDate, $endDate, $repaymentDay)
    {
        //第一期的时间段
        $i = 1;
        $serials[$i]['serial_no'] = 1;
        $serials[$i]['start'] = $startDate;
        $serials[$i]['end'] = DateAbout::calculateRepaymentPeriodEndDate($startDate, $repaymentDay);
        $currentPeriodEndDate = $serials[$i]['end'];
        $serials[$i]['days'] = DateAbout::diffBetweenTwoDays($serials[$i]['start'],$serials[$i]['end']);
        //第二期以及第二期之后的 日期
        while(strtotime($currentPeriodEndDate) < strtotime($endDate)){
            $i = $i + 1;
            $serials[$i]['serial_no'] = $i;
            $serials[$i]['start'] = $currentPeriodEndDate;
            //第二期以及第二期之后的结束日期
            $endDate2 = DateAbout::calculateRepaymentPeriodEndDate($currentPeriodEndDate, $repaymentDay);
            $serials[$i]['end'] = (strtotime($endDate2) >= strtotime($endDate)) ? $endDate : $endDate2;
            $currentPeriodEndDate = $serials[$i]['end'];
            $serials[$i]['days'] = DateAbout::diffBetweenTwoDays($serials[$i]['start'],$serials[$i]['end']);
        }
        $Monthly['serials'] = $serials;
        $Monthly['no'] = count($serials);
        return $Monthly;
    }





}