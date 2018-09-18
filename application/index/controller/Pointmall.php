<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 9:21
 */

namespace app\index\controller;


class Pointmall extends BaseOfUser
{
    //积分商城
    public function index()
    {
        if($this->tag !== 'pointmall'||$this->op !== 'getone')
        {
            return json(['message'=>'无此操作','err'=>-1000,'data'=>null]);
        }
        try{
            $res = db('integral_mall')->where('state','=',1)
                ->find();
            if(!$res){
                return json(['message'=>'暂无积分兑换活动','err'=>-1000,'data'=>null]);
            }else{
                return json(['message'=>'success','err'=>1000,'data'=>$res]);
            }
        }catch (\Exception $e)
        {
            return json(['message'=>$e->getMessage(),'err'=>-1000,'data'=>null]);
        }
    }
    //积分商城
    public function rule()
    {
        if($this->tag !== 'pointrule'||$this->op !== 'getone')
        {
            return json(['message'=>'无此操作','err'=>-1000,'data'=>null]);
        }
        try{
            $res = db('point_rule_config')
                ->find();
            if(!$res){
                return json(['message'=>'暂无积分规则','err'=>-1000,'data'=>null]);
            }else{
                return json(['message'=>'success','err'=>1000,'data'=>$res]);
            }
        }catch (\Exception $e)
        {
            return json(['message'=>$e->getMessage(),'err'=>-1000,'data'=>null]);
        }
    }
}