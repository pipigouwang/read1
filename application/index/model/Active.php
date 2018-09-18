<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/13
 * Time: 11:22
 */

namespace app\index\model;


use think\Db;
use think\Model;

class Active extends Model
{
    public function detail()
    {
        $data['times'] = db('activetimes')->select();
        $data['rules'] = db('active_config')->select();
        return ["成功",1000,$data];
    }
    //大转盘活动新增  修改即删除原有再新增
    public function add($data)
    {
        foreach ($data['rules'] as $k=>$v)
        {
            if(!is_numeric($v['min']) ||!is_numeric($v['max'])
            ||!is_numeric($v['v']))
            {
                return ['请务必输入正确的参数格式',-20000,null];
            }
        }
        try{
            Db::startTrans();
            $del = db('active_config')->where('id','>',0)->delete();
            $ins = db('active_config')->insertAll($data['rules']);
            //设置大转盘次数
            $del1 = db('activetimes')
                ->where('id','>',0)
                ->delete();
            $ins1 = db('activetimes')->insertAll($data['times']);
            if($ins&&$del&&$del1&&$ins1){
                Db::commit();
                return ['success',20000,$data];
            }else{
                Db::rollback();
                return ['fail',-20001,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-20000,null];
        }
    }
    //幸运大转盘
    public function run(){

        //判断是否还有资格
        list($err,$have,$total) = $this->havetimes(session('member_id'));
        if(!$err){
            return ['今日次数已完,明日再来',-10000,['havetimes'=>$have,'totaltimes'=>$total]];
        }
        $proArr = array();
        //v 是中奖概率 相加之后最好为100的整数倍 id为奖品编号 min max 分别为最大和最小角度
        $prize_arr = db('active_config')
            ->select();
       /* $prize_arr = array(
            '0' => array('id'=>1,'min'=>1,'max'=>29,'prize'=>'一等奖','v'=>10),
            '1' => array('id'=>2,'min'=>302,'max'=>328,'prize'=>'二等奖','v'=>20),
            '2' => array('id'=>3,'min'=>242,'max'=>268,'prize'=>'三等奖','v'=>30),
            '3' => array('id'=>4,'min'=>182,'max'=>208,'prize'=>'四等奖','v'=>10),
            '4' => array('id'=>5,'min'=>122,'max'=>148,'prize'=>'五等奖','v'=>10),
            '5' => array('id'=>6,'min'=>62,'max'=>88,'prize'=>'六等奖','v'=>10),
            '6' => array('id'=>7,'min'=>array(32,92,152,212,272,332), 'max'=>array(58,118,178,238,298,358),'prize'=>'七等奖','v'=>10)
        );*/
        //获取随机奖品
        foreach ($prize_arr as $v) {
            $proArr[$v['id']] = $v['v'];
        }
       // print_r($proArr);die;
        $rid = $this->getRand($proArr); //根据概率获取奖项id

        $res = $prize_arr[$rid-1]; //中奖项
        // dd($res);die;
        $min = $res['min'];
        $max = $res['max'];

        if($res['id']==7){ //七等奖
            $i = mt_rand(0,5);
            $result['angle'] = mt_rand($min[$i],$max[$i]);
        }else{
            $result['angle'] = mt_rand($min,$max); //随机生成一个角度
        }
        $result['prize'] = $res['prize'];
        $result['havetimes'] = $have;
        $result['totaltimes'] = $total;
        //写入中奖数据库
        db('member_active')->insert(
            [
                'mid'=>session('member_id'),
                'time'=>date('Y-m-d H:i:s'),
                'prize'=>$res['prize']
            ]
        );
        return ['success',10000,$result];
    }

    protected function getRand($proArr) {
        $result = '';

        //概率数组的总概率精度
        $proSum = array_sum($proArr);

        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);

        return $result;
    }

    //判断是否还可以再玩
    public function havetimes($mid)
    {
        //获取可以玩的次数
        $member = db('member')->where(['id'=>$mid])->find();
        $times = db('activetimes')
            ->where('minlevel','<=',$member['level'])
            ->where('maxlevel','>=',$member['level'])
            ->find()['times'];
        //已经玩的次数
        $havetimes = cache("active:{$mid}");
        if($havetimes>= $times){
            return [false,$havetimes,$times];//不能继续玩
        }else{
            $havetimes += 1;
            cache("active:{$mid}",$havetimes,['expire'=>strtotime(date('Y-m-d',strtotime("+1 day")))-time()]);
            return [true,$havetimes,$times];//继续玩
        }
    }
    //今日中奖纪录列表
    public function winprizelist($page,$limit)
    {
        $mid = session('member_id');
        //TODO 中奖列表
        $memberPrize['list'] = db('member_active')
            ->field('phone,name,time,prize')
            ->join('member','membre.id = member_active.mid')
            ->where('member_active.mid','=',$mid)
            ->page($page)
            ->limit($limit)
            ->select();
        $memberPrize['total'] = db('member_active')
            ->field('phone,name,time,prize')
            ->join('member','membre.id = member_active.mid')
            ->where('member_active.mid','=',$mid)
            ->count();
        if($memberPrize['total']>0){
            return ['成功',1000,$memberPrize];
        }else{
            return ['暂无',-1000,$memberPrize];
        }
    }    
}