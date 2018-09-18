<?php
namespace app\admin\model;
use think\Db;
use think\Model;
use app\admin\validate\ProductV;
use think\Validate;
class Product extends Model{

    public function product_list($page,$limit,$where,$field)
    {
        try{
            $query = Db::name('product');
            if(isset($where['sn'])&&!empty($where['sn']))
            {
                $query->where(['sn'=>$where['sn']]);
            }
            if(isset($where['enddate'])&&!empty($where['enddate']))
            {
                $query->whereTime('enddate', '<=', $where['enddate']);
            }
            if(isset($where['name'])&&!empty($where['name']))
            {
                $query->whereLike('name',"%{$where['name']}%");
            }
            if(isset($where['status'])&&!empty($where['status']))
            {
                $query->where('status','=',$where['status']);
            }else{
                $query->where('status','>',0);
            }
            $query1 = clone($query);
            //药品名 批次号 过期日期 库存
            $query->page($page)->limit($limit)->order('id','desc');
            if($field !== null){
                $res['list'] = $query->field($field)->select();
            }else{
                $res['list'] = $query->select();
            }
            //echo Db::name('product')->getLastSql();die;
            $res['total'] = $query1->count();
            if($res['total'] == 0)
            {
                return ['没有获取到符合条件的数据',-4001,['list'=>[],'total'=>0]];
            }
            if(isset($where['mid'])&&is_numeric($where['mid'])){
                //查询库存
                $havestock = db('stock')
                    ->field('num,pid')
                    ->where('mid','=',$where['mid'])
                    ->select();
                $havestock = array_column($havestock,null,'pid');
                foreach ($res['list'] as $k=>$v){
                    if(in_array($v['id'],array_column($havestock,'pid'))){
                        $res['list'][$k]['stocknum'] =  $havestock[$v['id']]['num'];
                    }else{
                        $res['list'][$k]['stocknum'] =  0;
                    }
                }
            }
            return ['成功',4000,$res];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-4000,null];
        }
    }

    public function product_add($data)
    {
        //检查数据
        $auth = new ProductV();
        $rule = $auth->rule;
        $msg = $auth->msg;

        $validate = new Validate($rule, $msg);

        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),-4001,null];
        }
        $data['created_time'] = date('Y-m-d H:i:s');
        try{
            $ins = Db::name('product')
                ->insert($data);
            if($ins)
            {
                return ['成功',4000,null];
            }
            return ['添加失败',-4002,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-4001,null];
        }
    }

    public function product_edit($con,$data)
    {
        if(!isset($con['id'])||!is_numeric($con['id'])||!Product::get($con['id']))
        {
            return ['未找到该商品',-4003,null];
        }
        //检查数据
        $auth = new ProductV();
        $rule = $auth->rule;
        $msg = $auth->msg;
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),-4009,null];
        }
        try{
            $ins = Db::name('product')
                ->where('id','=',$con['id'])
                ->update($data);
            if($ins)
            {
                return ['修改成功',4000,null];
            }
            return ['修改失败',-4004,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-4005,null];
        }
    }
    public function product_state($con,$data)
    {
        if(!isset($con['id'])||!is_numeric($con['id'])||!Product::get($con['id']))
        {
            return ['未找到该商品',-4003,null];
        }

        try{
            $ins = Db::name('product')
                ->where('id','=',$con['id'])
                ->update(['status'=>$data['status']]);
            if($ins)
            {
                return ['修改成功',4000,null];
            }
            return ['修改失败',-4004,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-4005,null];
        }
    }

}