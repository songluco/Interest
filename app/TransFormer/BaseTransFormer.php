<?php
namespace App\TransFormer;
use App\Services\MsgCode;

/**
 * Created by PhpStorm.
 * User: songlu
 * Date: 2017/8/9
 * Time: 下午7:37
 */
class BaseTransformer
{
    /**
     * 生成用户还款计划之前过滤外部传递过来的产品数据
     * @param $item
     * @return array
     */
    public function transformerProject($item)
    {
        $data['project_id'] = isset($item['project_id']) ? $item['project_id'] : '';
        $data['project_name'] = isset($item['project_name']) ? $item['project_name'] : '';
        $data['project_mode'] = isset($item['project_mode']) ? $item['project_mode'] : '';
        $data['start_interest_date'] = isset($item['start_interest_date']) ? $item['start_interest_date'] : '';
        $data['end_interest_date'] = isset($item['end_interest_date']) ? $item['end_interest_date'] : '';
        $data['annual_income'] = isset($item['annual_income']) ? $item['annual_income'] : '';
        $data['interest_base_days'] = isset($item['interest_base_days']) ? $item['interest_base_days'] : '';
        $data['financier_id'] = isset($item['financier_id']) ? $item['financier_id'] : '';
        $data['project_repay_day'] = isset($item['project_repay_day']) ? $item['project_repay_day'] : '';


        //验证数据是否有空值
        foreach ($data as $key => $value) {
            if(empty($value)){
                return funcReturn(MsgCode::ERROR_CODE_DATA_MISSING, '缺少数据' . $key);
            }
        }

        //产品ID,融资方ID,产品还款类型
        if(!is_int($data['project_id']) || !is_int($data['financier_id']) || !is_int($data['project_mode'])){
            return funcReturn(MsgCode::ERROR_CODE_DATA_FORMAT, '产品数据格式有误!产品ID:' . $data['project_id']);
        }

        //产品起息日 大于 产品结束日
        if($data['start_interest_date'] >= $data['end_interest_date']){
            return funcReturn(MsgCode::ERROR_CODE_DATE, '产品起息日大于结束日,产品ID:' . $data['project_id']);
        }

        //返回数据
        return funcReturn(MsgCode::SUCCESS_CODE, 'ok', $data);
    }

    /**
     * 生成用户还款计划之前过滤外部传递过来的订单数据
     * @param $orders
     * @return array
     */
    public function transformerOrders($orders)
    {
        foreach ($orders as $item) {
            $singleData = [];
            $singleData['order_amount'] = isset($item['order_amount']) ? $item['order_amount'] : '';
            $singleData['user_id'] = isset($item['user_id']) ? $item['user_id'] : '';
            $singleData['order_id'] = isset($item['order_id']) ? $item['order_id'] : '';

            if(in_array('',$singleData)){
                return funcReturn(MsgCode::ERROR_CODE_DATA_MISSING, '缺少数据');
            }

            //订单ID,投资人ID,订单金额
            if(!is_int($singleData['order_id']) || !is_int($singleData['user_id']) || !is_numeric($singleData['order_amount'])){
                return funcReturn(MsgCode::ERROR_CODE_DATA_FORMAT, '订单数据格式有误!产品ID:' . $singleData['project_id']);
            }
        }

        //返回数据
        return funcReturn(MsgCode::SUCCESS_CODE, 'ok', $orders);
    }
}