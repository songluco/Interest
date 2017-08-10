<?php
namespace App\Tools\DateAbout;
use Faker\Provider\zh_TW\DateTime;

/**
 * Created by PhpStorm.
 * User: songlu
 * Date: 2017/8/3
 * Time: 上午11:35
 */
class DateAbout
{

    /**
     * 求两个日期之间相差的天数
     * 备注:针对1970年1月1日之后的日期
     * @param $dayOne DateTime 日期一
     * @param $dayTwo DateTime 日期二
     * @return float
     */
    public static function diffBetweenTwoDays($dayOne, $dayTwo)
    {
        $secondOne = strtotime($dayOne);
        $secondTwo = strtotime($dayTwo);

        if ($secondOne < $secondTwo) {
            $tmp = $secondTwo;
            $secondTwo = $secondOne;
            $secondOne = $tmp;
        }
        return ($secondOne - $secondTwo) / 86400;
    }

    /**
     * 将日期转换为数组
     * @param $date DateTime 日期
     * @return array
     */
    public static function changeDateToArray($date)
    {
        return date_parse($date);
    }

    /**
     * （根据还款时间段的开始日期和还款日）计算还款时间段的结束日
     * @param $startDate DateTime 还款时间段的开始日期
     * @param $repaymentDay int 还款日
     * @return bool|string
     */
    public static function calculateRepaymentPeriodEndDate($startDate, $repaymentDay)
    {
        $startDateArr = DateAbout::changeDateToArray($startDate);
        if($startDateArr['day'] >= $repaymentDay){
            $endDate = date('Y-m-d',mktime(0, 0, 0, $startDateArr['month'] + 1, $repaymentDay, $startDateArr['year']));
        }else{
            $endDate = date('Y-m-d',mktime(0, 0, 0, $startDateArr['month'], $repaymentDay, $startDateArr['year']));
        }
        return $endDate;
    }
}