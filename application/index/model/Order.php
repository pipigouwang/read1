<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/13
 * Time: 14:04
 */

namespace app\index\model;

use think\Db;
class Order extends \app\admin\model\Order
{
    /**前端订单列表
     * @param $page
     * @param $limit
     * @param $where
     */
    public function order_list($page,$limit,$where)
    {
        try{
            $query = db('order')
                ->field('year(create_date) as year,month(create_date) as month,
                day(create_date) as day,order_id,status')
                ->where('status','>',0)
                ->where($where);
            $query1 = clone($query);
            $order['list'] = $query->page($page)->limit($limit)->select();

            if($order['list'])
            {
                $res1['total'] = $query1->count();
                foreach ($order['list'] as $key=>$val)
                {
                    $res2['list'][$val['year'].'-'.$val['month']]['time'] = $val['year'].'-'.$val['month'];
                    $res2['list'][$val['year'].'-'.$val['month']]['content'][] = $val;
                }
                $content = array_values($res2['list']);
                $res1['list'] = $content;
                return ['success',1100,$res1];
            }else{
                return ['暂无数据',-1100,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-1100,null];
        }
    }

    public function order_detail_by_orderid($con)
    {
        if(!$this->check_order_exist_by_orderid($con['id']))
        {
            return ['未找到该订单',-1103,null];
        }
        try{
            $order =  Db::name('order')
                ->field('member.name,member.uname,order.order_id,create_date,
                total_fee,phone,num,invoiceurl
               ,order.status,product.name as goodsname,product.imgurl,product.trueprice as price,
               sn')
                ->join('member','member.id = order.member_id')
                ->join('order_goods','order_goods.order_id = order.order_id')
                ->join('product','product.id = order_goods.goods_id')
                ->where('order.order_id','=',$con['id'])
                ->select();
            if($order){
                foreach ($order as $k=>$v){
                    $res['name'] = $v['name'];
                    $res['uname'] = $v['uname'];
                    $res['order_id'] = $v['order_id'];
                    $res['create_date'] = $v['create_date'];
                    $res['total_fee'] = $v['total_fee'];
                    $res['phone'] = $v['phone'];
                    $res['status'] = $v['status'];
                    $goods[] = [
                        'num'=>$v['num'],
                        'imgurl'=>$v['imgurl'],
                        'sn'=>$v['sn'],
                        'price'=>$v['price'],
                        'goodsname'=>$v['goodsname'],
                        'invoiceurl'=>$v['invoiceurl']
                    ];
                }
                $res['goods'] = $goods;
                return ['success',1100,$res];
            }else{
                return ['暂无数据',-1100,null];
            }

        }catch (\Exception $e)
        {
            return [$e->getMessage(),-1106,null];
        }
    }
}