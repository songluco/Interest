<?php

/**
 * 函数返回值封装
 * @param $code
 * @param $msg
 * @param $data
 * @return array
 */
if (!function_exists('funcReturn')) {
    function funcReturn($code, $msg = '', $data = [])
    {
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}

/**
 * 舍去法保留2位小数,不要逗号
 * @param $amount
 * @return string
 */
if(!function_exists('displayAmountNoComma')){
    function displayAmountNoComma($amount)
    {
        $amountRes = sprintf("%.2f", substr(sprintf("%.4f", $amount), 0, -2));
        return $amountRes;
    }
}



