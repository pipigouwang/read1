<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/7
 * Time: 15:54
 */

namespace app\admin\model;


use think\Db;
use think\Model;

class DataCenter extends Model
{
    /*订单统计*/
    public function order_statistics()
    {
        
    }

    public function sale_statistics()
    {
        //根据区域统计售卖情况  获取所有销售员的区域 在根据区域获取所有销售员售卖的产品的数量之和
        $sale = Db::name('member')
            ->field('id as mid,sale_region')
            ->where('type','=',3)
            ->where('status','=',1)
            ->select();
        $arr = array_values(array_unique(array_column($sale,'sale_region')));
        foreach ($arr as $key=>$val)
        {
            foreach ($sale as $k=>$v)
            {
                if($v['sale_region'] === $val )
                {
                    $res[$key][] = $v['mid'];
                }
            }
        }
        $result = null;
        foreach ($res as $k=>$v)
        {
            $order[$k] = Db::name('order')
                ->field('total_fee')
                ->where('status','=',3)
                ->where('member_id','in',$v)
                ->select();
            $result[$k]['order_total'] = count($order[$k]);
            $result[$k]['money_total'] = array_sum(array_column($order[$k],'total_fee'));
        }
        foreach ($arr as $key => $value)
        {
            foreach ($result as $key1=>$val1)
            {
                if($key==$key1)
                {
                    $result1[$value] = $val1;
                }
            }
        }
        print_r($result1);die;

    }
}