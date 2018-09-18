<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8
 * Time: 9:59
 */

namespace app\admin\model;
use think\Model;
use think\Db;
use think\Validate;
class News extends Model
{
    public function admin_news_list($page,$limit,$con)
    {
        try{
            $res['list'] = Db::name('news')
                ->where('status','>',0)
                //->where($con)
                ->page($page)
                ->limit($limit)
                ->order('id','desc')
                ->select();
            if($res['list'])
            {
                $res['total'] = Db::name('news')
                    ->where('status','>',0)
                    ->count();
                return ['成功',7000,$res];
            }else{
                return ['暂无咨讯',-7001,['list'=>[],'total'=>0]];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-7000,null];
        }
    }
    public function news_list($page,$limit,$con)
    {
        $con = array_filter($con);
        try{
            $res['list'] = Db::name('news')
                ->where('status','>',0)
                ->where($con)->page($page)
                ->order('id','desc')
                ->limit($limit)->select();
            if($res['list'])
            {
                //获取已读状态
                $readed = db('member_news_readed')
                    ->field('newsid')
                    ->where('status','>',0)
                    ->where($con)
                    ->order('id','desc')
                    ->where('mid','=',session('member_id'))
                    ->select();
                $newsid = array_column($readed,'newsid');
                $count = 0;
                foreach ($res['list'] as $k=>$v)
                {
                    $res['list'][$k]['text'] = strip_tags(htmlspecialchars_decode($v['text']));
                    if(!in_array($v['id'],$newsid))
                    {
                        $res['list'][$k]['read'] = 0;
                    }else{
                        $res['list'][$k]['read'] = 1;
                        $count ++;
                    }
                }
                $res['notread'] = $count;
                $res['total'] = Db::name('news')
                    ->where($con)->count();
                return ['成功',7000,$res];
            }else{
                return ['暂无咨讯',-7001,['list'=>[],'total'=>0]];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-7000,null];
        }
    }
    public function news_add($data)
    {
        $rule = [
            'title'=>'require|max:240',
            'text'=>'require',
            'status'=>'require|in:1,2'//0,删除，1，启用，2，禁用
        ];
        $msg = [
            'title.require'=>'标题必须',
            'title.max'=>'标题不超过60字',
            'status.require'=>'状态必须',
            'text.require'=>'内容不能为空',
            'status.in'=>'非法状态',
        ];
        $data['create_time'] = date('Y-m-d H:i:s');
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),-7002,null];
        }
        try{
            $res = Db::name('news')
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
    public function news_edit($con,$data)
    {
        if(!isset($con['id'])||!News::get($con['id']))
        {
            return ['未找到该咨讯',-7004,null];
        }
        $rule = [
            'title'=>'require|max:240',
            'text'=>'require',
            'status'=>'require|in:0,1,2'//0,删除，1，启用，2，禁用
        ];
        $msg = [
            'title.require'=>'标题必须',
            'title.max'=>'标题不超过60字',
            'status.require'=>'状态必须',
            'text.require'=>'内容不能为空',
            'status.in'=>'非法状态',
        ];
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),-7002,null];
        }
        try{
            $res = Db::name('news')
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

    public function news_state($con,$data)
    {
        if(!isset($con['id'])||!News::get($con['id']))
        {
            return ['未找到该咨讯',-7004,null];
        }
        try{
            $res = Db::name('news')
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
    public function news_one($con)
    {
        if(!isset($con['id'])||!News::get($con['id']))
        {
            return ['未找到该咨讯',-7004,null];
        }
        try{
            $res = Db::name('news')
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
}