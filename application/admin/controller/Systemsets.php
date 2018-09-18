<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/31
 * Time: 17:12
 */
namespace app\admin\controller;
use app\admin\model\System;
use app\index\model\Active;

class Systemsets extends UserBase{

    private $isUpdate = false;//判断是否是更新 前端提供isUpdate值1则为更新 否则为获取
    public function __construct()
    {
        parent::__construct();
        $isUpdate = $this->request->post('isUpdate');
        $isUpdate==1?$this->isUpdate = true:$this->isUpdate = false;
    }
    /**获取，或设置系统设置
     * @return \think\response\Json
     */
    public function system_set()
    {
        $oriData = $this->request->post();//tag为对应table的名字
        isset($oriData['tag'])?$tag = $oriData['tag']:$tag = null;
        isset($oriData['data'])?$data = $oriData['data']:$data= null;
        $system = new System();
        if(!$tag||!$system->checkTagsExist($tag))
        {
            return json(['message'=>'tag不存在','err'=>-1004]);
        }
        if($this->isUpdate)
        {//更新系统设置
            $system->setSystemTable($tag);
            $system->setUpdateData($data);
            list($msg,$err,$datas) = $system->setSystemSet();
        }else{//获取系统设置
            $system->setSystemTable($tag);
            list($msg,$err,$datas) = $system->getSystemSet();
        }
        return json(['message'=>$msg,'err'=>$err,'data'=>$datas]);
    }
    public function banner_or_ad_set()
    {
        $oriData = $this->request->post();//tag为对应table的名字
        isset($oriData['op'])?$op = $oriData['op']:$op = null;
        isset($oriData['tag'])?$tag = $oriData['tag']:$tag = null;
        isset($oriData['data'])?$data = $oriData['data']: $data =null;
        $system = new System();
        if(!$tag ||($tag!=='ad_config'&& $tag!=='banner_config'))
        {
            return ['非法tag',-1005,null];
        }
        switch ($op)
        {
            case 'update':
                list($msg,$err,$datas) = $system->banner_or_ad_update($tag,$data);
                break;
            case 'get':
                list($msg,$err,$datas) = $system->banner_or_ad_get($tag);
                break;
            case 'add':
                list($msg,$err,$datas) = $system->banner_or_ad_add($tag,$data);
                break;
            case 'state':
                list($msg,$err,$datas) = $system->banner_or_ad_state($tag,$data['id'],$data['state']);
                break;
            default :
                return json(['message'=>'不合法的操作','err'=>-1004,'data'=>null]);
        }
        return json(['message'=>$msg,'err'=>$err,'data'=>$datas]);
    }
    //设置大转盘游戏参数
    public function active_set()
    {
        if(!$this->request->post()){
            list($msg,$err,$data1) = (new Active())->detail();
            return json(['message'=>$msg,'err'=>$err,'data'=>$data1]);
        }else{
            $data = $this->request->post('data');
            list($msg,$err,$data1) = (new Active())->add($data);
            return json(['message'=>$msg,'err'=>$err,'data'=>$data1]);
        }

    }

    public function systemlog()
    {
        $page = $this->request->get('page');
        $page? :$page =1;
        $limit = $this->request->get('limit');
        $limit? :$limit = 9;
        $post = $this->request->post();
        isset($post['op'])?$op = $post['op']:$op = null;
        isset($post['tag'])?$tag = $post['tag']:$tag = null;
        isset($post['condition'])?$con = $post['condition']:$con = null;
        $query = db('systemlog')
            ->join('user','user.id = systemlog.uid');
        $query1 = clone($query);
        if(isset($con['date'])&&!empty($query)){
            $query->whereTime('time','>',$con['date']);
            $query1->whereTime('time','>',$con['date']);
        }

        $res['list'] = $query->page($page)->limit($limit)->select();
        if($res['list'])
        {
            $res['total'] = $query1->count();
            return json(['message'=>'成功','err'=>1000,'data'=>$res]);
        }else{
            $res['total'] = 0;
            return json(['message'=>"暂无数据",'err'=>-1000,'data'=>$res]);
        }
    }

    //发送系统消息时选取 需要发送的人
    public function getmember()
    {
        $where['type'] = $this->request->post('type');
        $phone = $this->request->post('phone');
        if($phone){
            $whereLike[0] = "phone";
            $whereLike[1] = "%{$phone}%";
        }else{
            $whereLike = null;
        }
        if($where['type'] == 2){//药店或诊所isclinic 1 为药店 2 为诊所
            $where['isclinic'] = $this->request->post('isclinic');
            $where['isclinic']? $where['isclinic'] = 1:$where['isclinic'] = 2;
        }
        if($where['type'] == 1){//病人 有illness（病标签） 否则全选
            $isall = $this->request->post('illness');
            $isall? $where['illness'] = $isall:null;
        }
        list($msg,$err,$data) = (new \app\admin\model\Message())
            ->chosemember($where,$whereLike);
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }
    //发送系统消息
    public function sendmsg()
    {

    }
}