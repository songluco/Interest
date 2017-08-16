<?php
/**
 * Created by PhpStorm.
 * User: songlu
 * Date: 2017/8/11
 * Time: 上午9:25
 */

namespace App\Services;


class MsgCode
{
/*
    const SUCCESS_CODE = [
        'code' => 0,
        'msg' => '数据正常'
    ];    //无异常

    */

    const SUCCESS_CODE = 0;//无异常
    const ERROR_CODE_DATA_MISSING = 1001; //未获取到传入数据
    const ERROR_CODE_DATA_LAW = 1002; //传入数据不合法
    const ERROR_CODE_NO_PROJECT_MODE = 1003; //不存在产品还款类型
    const ERROR_CODE_DATE = 1004; //产品起息日大于结束日
    const ERROR_CODE_DATA_FORMAT = 1005; //数据格式有误






}