<?php
namespace App\TransFormer;
/**
 * Created by PhpStorm.
 * User: songlu
 * Date: 2017/8/9
 * Time: 下午7:44
 */
class FixRateTransFormer extends BaseTransformer
{

    public function transformer($item)
    {
        /** @var $item UserPayPlans */
        return [
            'id' => $item->id,
            'uid' => $item->uid
        ];

    }


    public function transformerProjectInfo(array $project)
    {
        if(!is_array($project)){
            return false;
        }
        
    }


    /**
     * 对获取到的数据进行转换和校验
     * @param $source
     * @param $batch
     */
    public function transferRecordData(array $source ,$batch=true)
    {

        //定义请求必须的参数

        //定义请求返回的参数
//        ['uid','project_id','repay_periods','repay_current_period','repay_mode',
//            'user_order_id','user_order_amount','pay_plan_date', 'pay_plan_interest_days',
//            'interest_days','pay_plan_amount','pay_plan_interest_amount','pay_plan_principal_amount',
//        ];



        //获取请求渠道来源 和 请求记录的数据
        $channel = isset($source['channel_no']) ? $source['channel_no'] : '';
        $data = isset($source['record']) ? $source['record'] : [];
        //如果未发送渠道或者数据 则退出
        if(!$channel) {
            return funcReturn(self::ERROR_CODE_NO_CHANNEL,'channel 未定义');
        }
        if(!$data) {
            return funcReturn(self::ERROR_CODE_DATA_MISSING,'record 数据丢失');
        }
        //判断本次数据是否是批量数据处理
        if(!$batch) {
            $data = [$data];
        }
        //定义请求数据必须的字段
        $requireKeys = ['uid','walletChannel','money','requestNumber','customType','customRemark','customNumber','walletType'];
        //初始化返回数据
        $returnData = [];
        //轮询原始数据 对返回数组进行填充
        foreach ($data as $val) {
            $tmp=[];
            $tmp['walletChannel'] = $channel;
            if(isset($val['uid'])) {
                $tmp['uid'] = $val['uid'];
            }
            if(isset($val['action'])) {
                $tmp['walletType'] = $val['action'];
            }
            if(isset($val['money'])){
                $tmp['money'] = $val['money'];
            }
            if(isset($val['request_number'])) {
                $tmp['requestNumber'] = $val['request_number'];
            }
            if(isset($val['custom_type'])) {
                $tmp['customType'] = $val['custom_type'];
            }
            if(isset($val['custom_remark'])) {
                $tmp['customRemark'] = $val['custom_remark'];
            }
            if(isset($val['custom_number'])) {
                $tmp['customNumber'] = $val['custom_number'];
            }
            //校验传输的数组是否完整
            if(!isset($tmp['money']) || $tmp['money'] <= 0) {
                return funcReturn(self::ERROR_CODE_ACTION_MONEY,'缺少数据'.$key);
            }
            $keys = array_keys($tmp);
            foreach($requireKeys as $key) {
                if(!in_array($key,$keys)) {
                    return funcReturn(self::ERROR_CODE_DATA_MISSING,'缺少数据'.$key);
                }
            }

            $returnData[] = $tmp;
        }
        //返回数据
        return funcReturn(self::ERROR_CODE_NONE,'ok',$returnData);
    }



}