<?php
namespace app\index\controller;
use app\admin\model\Member;
use app\admin\model\Order;

class Patient extends BaseOfUser {

    //病人病历
    public function casehistory()
    {
        $member = new Member();
        $mid['id'] = session('member_id');
        list($msg,$err,$data) = $member->member_case_history($this->page,$this->limit,$mid);
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }
    //病人购药记录
    public function orderhistory()
    {

       /// return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }
    //积分历史
    public function pointhistory()
    {
        //当前积分  每月积分情况列表
        $where['id'] = session('member_id');
        list($msg,$err,$data) = (new Member())
            ->member_point_history($this->page,$this->limit,$where);
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }

}