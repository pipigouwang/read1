<?php
namespace app\admin\model;
use think\Model;
use think\Db;
use think\Validate;
class Order extends Model{
    //订单号 手机号（下单） 下单日期  状态，订单类型（上家，下单家）筛选
    public function order_list($page,$limit,$where)
    {
        try{
            $query = Db::name('order')
                    ->field('order.id as oid,member_id,order.status,order.remark,invoiceurl,order.remarks,
                    total_fee,create_date,to_id,order.type,order_id,member.phone')
                    ->join('member m ','m.id = order.member_id');
            if(isset($where['order_id'])&&!empty($where['order_id']))
            {
                $query->where('order_id','=',$where['order_id']);
            }
            if(isset($where['phone'])&&!empty($where['phone']))
            {
                $query->where(['m.phone'=>$where['phone']]);
            }
            if(isset($where['status'])&&is_numeric($where['status']))
            {
                $query->where(['order.status'=>$where['status']]);
            }
            if(isset($where['type'])&&is_numeric($where['type']))
            {
                $query->where(['order.type'=>$where['type']]);
            }
            if(isset($where['create_date'])&&!empty($where['create_date']))
            {
                $query->whereTime('create_date', '>=', $where['create_date']);
            }
            $query->where('order.status','>',0);
            $query1 = clone($query);
            //药品名 批次号 过期日期 库存
            $res['list'] = $query->page($page)->limit($limit)
                ->order('order.id','desc')->select();
            //echo Db::name('product')->getLastSql();die;
            $res['total'] = $query1->count();
            if($res['total'] == 0)
            {
                return ['没有获取到符合条件的数据',-5001,['list'=>[],'total'=>0]];
            }
            return ['成功',5000,$res];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-5000,null];
        }
    }

    public function order_add($data)
    {
       // print_r($data);die;
        if($data['member_id'] == $data['to_id'])
        {
            return ['收货人和发货人一样',-5004,null];
        }
        //根据下单人id判断 该订单类型
        $mem = Db::name('member')->where(['id'=>$data['member_id']])->find();
        if($mem['type'] == 0){
            return ['下单人账号已被系统禁用',-5004,null];
        }

        $tomem = Db::name('member')->where(['id'=>$data['to_id']])->find();
        if($tomem['type'] == 0){
            return ['接单人账号已被系统禁用',-5004,null];
        }
        $order['member_id'] = $data['member_id'];
        //检查数据
        $rule = [
            'member_id'=>'require|number',
        ];
        $msg = [
            'member_id.require'=>'下单人id必须',
            'member_id.num'=>'id必须为整数',
        ];
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($order);
        if(!$result)
        {
            return [$validate->getError(),-5004,null];
        }
        $order['create_date'] = date('Y-m-d');
        $order['status'] = 1;
        $order['order_id'] = base_convert(uniqid(), 12, 10);
        try{
            static $total_fee = 0;
            Db::startTrans();
            $stock = null;
            foreach ($data['goods'] as $val)
            {
                 $total_fee += ($val['num']*$val['price']);
                $goods_order[] = [
                    'goods_id'=>$val['id'],
                    'num'=>$val['num'],
                    'order_id'=>$order['order_id']
                ];
                $stock[] = [
                    'pid'=>$val['id'],
                    'mid'=>$data['member_id'],
                    'num'=>0
                ];
            }
            //买方库存(除病人外)
            if($mem['type'] != 1){
                $have = db('stock')->where(['mid'=>$data['member_id']])
                    ->select();
                if($have){
                    $have = array_column($have,'pid');
                }else{
                    $have = [];
                }

                foreach ($stock as $k=>$v)
                {
                    //买方库存处理
                    if(!in_array($v['pid'],$have)){
                        $add = db('stock')->insert([
                            'num'=>0,
                            'mid'=>$data['member_id'],
                            'pid'=>$v['pid']
                        ]);
                        if(!$add)
                        {
                            Db::rollback();
                            return ['新增库存失败',-5001,null];
                        }
                    }
                }
            }
            $order['total_fee'] = $total_fee - $data['discount'];
            $order['to_id'] = $data['to_id'];
            $order['invoiceurl'] = $data['invoiceurl'];
            $order['remarks'] = $data['remarks'];
            $goodsins = Db::name('order_goods')->insertAll($goods_order);
            $orderins = Db::name('order')->insert($order);

            if($goodsins&&$orderins)
            {
                Db::commit();
                System::systemlog(session('uid'),"新增了一条订单:".$order['order_id']);
                //如果下单人是药店  判断该药品是否已经代理
                if($mem['type'] == 2)
                {
                    //添加到药店代理关系表
                    $ids = db('stock')
                        ->field('pid')
                        ->where(['mid'=>$data['member_id']])
                        ->select();
                    foreach ($ids as $v){
                        $datas[] = [
                            'mid'=>$data['member_id'],
                            'pid'=>$v['pid']
                        ];
                    }
                    //删除原有关系建立信管系
                    db('member_product')->where(['mid'=>$data['member_id']])->delete();
                    db('member_product')->insertAll($datas);
                }
                return ['成功',5000,null];
            }else{
                Db::rollback();
                return ['添加失败',-5002,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-5001,null];
        }
    }

    public function order_state($con,$data)
    {
        if(!isset($data['status'])||!is_numeric($data['status'])
            ||!in_array($data['status'],[0,1,2,3,4,5,6,7]))
        {
            return ['状态值错误',-5004,null];
        }
        if(!$this->check_order_exist_by_orderid($con['id']))
        {
            return ['未找到该订单',-5003,null];
        }
        try{
            $order1 = Db::name('order')
                ->where('order_id','=',$con['id'])->find();
            Db::startTrans();
            //如果修改状态为 6 （已发货） 则修改卖家库存
            $order = Db::name('order_goods')
                ->field('goods_id as pid,num,member_id as mid')
                ->join('order','order_goods.order_id = order.order_id')
                ->where('order.order_id','=',$con['id'])
                ->select();
            //买方库存
            $have = db('stock')->where(['mid'=>$order1['member_id']])
                ->select();
            if($have){
                $have = array_column($have,'pid');
            }else{
                $have = [];
            }

            $have1 = db('stock')->where(['mid'=>$order1['to_id']])
                ->select();

            if($data['status'] == 6)
            {
                foreach ($order as $k=>$v)
                {
                    //买方库存处理
                    if(in_array($v['pid'],$have)){
                       // /更新已有药品的库存
                            $up = db('stock')->where(['mid'=>$order1['member_id'],
                                'pid'=>$v['pid']])->setInc('num',$v['num']);
                            if(!$up)
                            {
                                Db::rollback();
                                return ['修改订单状态失败',-5000,null];
                            }
                    }else{
                        //没有的新增
                        $add = db('stock')->insert([
                            'num'=>$v['num'],
                            'mid'=>$order1['member_id'],
                            'pid'=>$v['pid']
                        ]);
                        if(!$add)
                        {
                            Db::rollback();
                            return ['修改订单状态失败',-5001,null];
                        }
                    }
                    //卖方库存处理     顶层代理库存不做处理
                    foreach ($have1 as $k2=>$v2)
                    {
                        if($v['pid'] == $v2['pid'])
                        {//更新已有药品的库存
                            $oriNu = db('stock')->where(['mid'=>$order1['to_id'],
                                'pid'=>$v['pid']])->find()['num'];
                            if(($oriNu - $v['num'])<0)
                            {
                                return ['修改订单状态失败,库存不足',-5002,null];
                            }
                            $up = db('stock')->where(['mid'=>$order1['to_id'],
                                'pid'=>$v['pid']])->setDec('num',$v['num']);
                            if(!$up)
                            {
                                Db::rollback();
                                return ['修改订单状态失败',-5002,null];
                            }
                        }
                    }
                }
            }

            $ins = Db::name('order')
                ->where('order_id','=',$con['id'])
                ->update(['status'=>$data['status'],'remark'=>$data['remark']]);
            if(!$ins)
            {
                Db::rollback();
                return ['修改订单状态失败',-5000,null];
            }else{
                Db::commit();
                //修改用户等级和总花费金额
                $speed = db('moneytopoint')->find()['point'];
                $spend = $order1['total_fee'] * $speed;
                db('member')->where(['id'=>$order1['member_id']])
                    ->setInc('spendmoney',$spend);
                System::systemlog(session('uid'),"修改了一条订单:".$con['id']);
                return ['修改订单状态成功',5000,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-5005,null];
        }
    }

    public function order_one($page,$limit,$con)
    {
        if(!$this->check_order_exist_by_orderid($con['id']))
        {
            return ['未找到该订单',-5003,null];
        }
        try{
            $order =  Db::name('order')
                ->where('order_id','=',$con['id'])
                ->find();
            $order['buyer'] = Db::name('member')
                ->where('id','=',$order['member_id'])
                ->find();
            $order['saler'] = Db::name('member')
                ->where('id','=',$order['to_id'])
                ->find();
            $query  =  Db::name('order_goods')
                ->field('num,goods_id')
                ->where('order_id','=',$con['id']);
            $query1 = clone($query);
            $goodionfos = $query->page($page)->limit($limit)
                ->select();

            $order['goodsinfo']['total'] = $query1->count();
            if($goodionfos)
            {
                $goodsids = array_column($goodionfos,'goods_id');
                $goodionfo = Db::name('product')
                    ->where('id','in',$goodsids)
                    ->select();
                foreach ($goodionfos as $k1=>$v1)
                {
                    foreach ($goodionfo as $k2=>$v2)
                    {
                        if($v1['goods_id'] == $v2['id'])
                        {
                            $v2['totalprice'] = $v1['num']*$v2['trueprice'];
                            $result[] = array_merge($v1,$v2);
                        }
                    }
                }
                $order['goodsinfo']['list'] = $result;
            }
            return ['成功',5000,$order];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-5006,null];
        }
    }

    public function check_order_exist_by_orderid($orderid)
    {
        $exist_order = Db::name('order')
            ->where('order_id','=',$orderid)
            ->find();
        if(!isset($orderid)||!is_numeric($orderid)||!$exist_order)
        {
            return false;
        }
        return true;
    }
}