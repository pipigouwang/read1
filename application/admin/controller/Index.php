<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/21
 * Time: 9:48
 */

namespace app\admin\controller;


use app\admin\model\Counter;

class Index extends UserBase
{
    //新增会员
    public function membercount()
    {
        $res['todayjoin']['today'] = db('member')//今日新增会员总数
            ->whereTime('create_time','today')->count();
        $res['todayjoin']['themonth'] = db('member')//本月新增总数
        ->whereTime('create_time','month')->count();
        return json(['message'=>'成功','err'=>2000,'data'=>$res]);
    }
    //版本修改历史
    public function version()
    {
        $res = db('version')->order('id','desc')->select();
        if($res){
            return json(['message'=>'成功','err'=>2001,'data'=>$res]);
        }
        return json(['message'=>'暂无','err'=>-2001,'data'=>null]);
    }
    //添加版本记录
    public function addversion()
    {
        $data = [
          'version'=>$this->request->post('version'),
            'remark'=>$this->request->post('remark'),
            'date'=>date('Y-m-d H:i:s')
        ];
        $res = db('version')
            ->insert($data);
        if($res){
            return json(['message'=>'成功','err'=>2001,'data'=>null]);
        }
        return json(['message'=>'暂无','err'=>-2001,'data'=>null]);
    }

    //首页活跃用户数据
    public function activepeople()
    {
        $today = Counter::getOnlineToday();
        $month = Counter::getOnlineMonth();
        return json(['message'=>'成功','err'=>1000,'data'=>['today'=>$today,'month'=>$month]]);
    }
    //订单数据
    public function ordercounter()
    {
        $res['todayjoin']['today'] = db('order')->where('status','>',6)//今日新增会员总数
        ->whereTime('create_date','today')->count();
        $res['todayjoin']['themonth'] = db('order')->where('status','>',6)//本月新增总数
        ->whereTime('create_date','month')->count();
        return json(['message'=>'成功','err'=>2000,'data'=>$res]);
    }
}