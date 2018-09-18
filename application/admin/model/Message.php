<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8
 * Time: 11:01
 */

namespace app\admin\model;
use think\Model;
use think\Db;
use think\Validate;
class Message extends Model
{
    public function chosemember($where,$whereLike)
    {
        try{
            $query = Db::name('member')
                ->field('member.id,uname,name,shop_name');
            if(isset($where['illness'])&&$where['illness']){
                $query->join('illnesswithsign','illnesswithsign.mid = member.id')
                    ->distinct('member.id')
                    ->where('type','=',$where['type'])->where('signid','in',$where['illness']);
            }else{
                $query->where($where);
            }
            if($whereLike){
                $query->whereLike($whereLike[0],$whereLike[1]);
            }
            $query1 = clone($query);
            $res['list'] = $query->select();
            ///echo Db::name('member')->getLastSql();die;
            $res['total'] = $query1->count();
            if($res['total'] >0){
                return ['成功',7000,$res];
            }else{
                return ['暂无',-7000,$res];
            }
        }catch (\Exception $e){
            return [$e->getMessage(),-7000,$res];
        }

    }
    public function admin_message_list($page,$limit,$con)
    {
        try{
            $res['list'] = Db::name('system_msg')
                ->where('status','>',0)
                //->where($con)
                ->page($page)->limit($limit)
                ->order('id','desc')
                ->select();
            if($res['list'])
            {
                $res['total'] = Db::name('system_msg')
                    ->where('status','>',0)
                    ->where($con)->count();
                return ['成功',7000,$res];
            }else{
                return ['暂无消息',-7001,['list'=>[],'total'=>0]];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-7000,null];
        }
    }
    public function message_list($page,$limit,$con)
    {
        try{
            $res['list'] = Db::name('member_msg_readed')
                ->field('member_msg_readed.id,member_msg_readed.status,
                member_msg_readed.msgid,system_msg.time,system_msg.msg,
                system_msg.title')
                ->where('member_msg_readed.status','>',0)
                ->where('mid','=',session('member_id'))
                ->where($con)
                ->join('system_msg','system_msg.id = member_msg_readed.msgid')
                ->page($page)
                ->limit($limit)
                ->order('member_msg_readed.id','desc')
                ->select();
          //  var_dump(Db::name('member_msg_readed')->getLastSql());
            if($res['list'])
            {
                //获取已读状态
                $count = db('member_msg_readed')
                    ->where('member_msg_readed.status','>',0)
                    ->where($con)
                    ->join('system_msg','system_msg.id = member_msg_readed.msgid')
                    ->where('mid','=',session('member_id'))
                    ->count();
                $notRead = db('member_msg_readed')
                    ->where('member_msg_readed.status','=',1)
                    ->where($con)
                    ->join('system_msg','system_msg.id = member_msg_readed.msgid')
                    ->where('mid','=',session('member_id'))
                    ->count();
                $res['notread'] = $notRead;
                $res['total'] = $count;
                return ['成功',7000,$res];
            }else{
                return ['暂无消息',-7001,['list'=>[],'total'=>0]];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-7000,null];
        }
    }
    public function message_add($data)
    {
        $rule = [
            'title'=>'require|max:240',
            'msg'=>'require',
            'status'=>'require|in:1,2'//0,删除，1，启用，2，禁用
        ];
        $msg = [
            'title.require'=>'标题必须',
            'title.max'=>'标题不超过60字',
            'status.require'=>'状态必须',
            'msg.require'=>'内容不能为空',
            'status.in'=>'非法状态',
        ];
        $data['time'] = date('Y-m-d H:i:s');
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),-7002,null];
        }
        try{
            $res = Db::name('system_msg')
                ->insert($data);
            if($res)
            {
                return ['成功',7000,null];
            }else{
                return ['新增失败',-7003,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-7004,null];
        }
    }
    public function message_edit($con,$data)
    {
        $find = db('system_msg')->where(['id'=>$con['id']])->find();
        if(!isset($con['id'])||!$find)
        {

            return ['未找到该消息',-7004,null];
        }
        $rule = [
            'title'=>'require|max:240',
            'msg'=>'require',
            'status'=>'require|in:0,1,2'//0,删除，1，启用，2，禁用
        ];
        $msg = [
            'title.require'=>'标题必须',
            'title.max'=>'标题不超过60字',
            'status.require'=>'状态必须',
            'msg.require'=>'内容不能为空',
            'status.in'=>'非法状态',
        ];
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),-7002,null];
        }
        try{
            $res = Db::name('system_msg')
                ->where('id','=',$con['id'])
                ->update($data);
            if($res)
            {
                return ['成功',7000,$res];
            }else{
                return ['修改失败',-7003,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-7004,null];
        }
    }
    public function message_state($con,$data)
    {
        $find = db('system_msg')->where(['id'=>$con['id']])->find();
        if(!isset($con['id'])||!$find)
        {

            return ['未找到该消息',-7004,null];
        }
        try{
            $res = Db::name('system_msg')
                ->where('id','=',$con['id'])
                ->update(['status'=>$data['status']]);
            if($res)
            {
                return ['成功',7000,$res];
            }else{
                return ['修改失败',-7003,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-7005,null];
        }
    }
    public function message_one($con)
    {
        $find = db('system_msg')->where(['id'=>$con['id']])->find();
        if(!isset($con['id'])||!$find)
        {

            return ['未找到该消息',-7004,null];
        }
        try{
            $res = Db::name('system_msg')
                ->where('id','=',$con['id'])
                ->find();
            if($res)
            {
                return ['成功',7000,$res];
            }else{
                return ['暂无数据',-7007,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-7008,null];
        }
    }
    public function sendmsg($ids,$data)
    {
       try{
           Db::startTrans();
           //新增到系统消息
           $msgid = db('system_msg')->insertGetId([
               'msg'=>$data,
               'time'=>date('Y-m-d H:i:s'),
               'title'=>"",
               'status'=>1
           ]);
           //新增到未读表
           $ids = explode(',',$ids);
           foreach ($ids as $k=>$v){
               $res[] = [
                   'msgid'=> $msgid,
                   'mid'=> $v,
                   'status'=> 1,
               ];
           }
           $ins = db('member_msg_readed')->insertAll($res);
           if($ins&&$msgid){
               Db::commit();
               return ['成功',8000,null];
           }else{
               Db::rollback();
               return ['失败',-8001,null];
           }
       }catch (\Exception $e){
           return [$e->getMessage(),-8000,null];
       }
    }
}