<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/13
 * Time: 9:25
 */

namespace app\index\controller;

class Member extends BaseOfUser
{
    public function index()
    {
        list($msg,$err) = $this->checkTagAndOp();
        if($err === false)
        {
            return json(['message'=>$msg,'err'=>-5000,'data'=>null]);
        }
        switch ($this->op)
        {
            case 'message':
               // $this->con['member_msg_readed.status'] = 1;
                list($msg,$err,$data) = (new \app\admin\model\Message())
                    ->message_list($this->page,$this->limit,$this->con);
                break;
            case 'getone':
                list($msg,$err,$data) = (new \app\index\model\Member())
                    ->personalcentercenter();
                break;
            case 'news':
                $this->con['status'] = 1;
                list($msg,$err,$data) = (new \app\admin\model\News())
                    ->news_list($this->page,$this->limit,$this->con);
                break;
            case 'editprofile':
                $where['id'] = session('member_id');
                $data['avator'] = $this->data['avator'];//头像
                $data['name'] = $this->data['account'];//昵称
                list($msg,$err,$data) = (new \app\index\model\Member())
                    ->member_edit($where,$data);
                break;
            case 'active':
                list($msg,$err,$data) = (new \app\index\model\Active())
                    ->run();
                break;
            case 'orderlist':
                if(isset($this->con['type'])&&!empty($this->con['type']))
                {
                    $where['type'] = $this->con['type'];//进货2 出货1
                    if($where['type'] == 2){
                        $where['member_id'] = session('member_id');
                    }elseif($where['type'] == 1)
                    {
                        $where['to_id'] = session('member_id');
                    }else{
                        $where['member_id'] = session('member_id');
                    }
                }else{
                    $where['member_id'] = session('member_id');
                }
                list($msg,$err,$data) = (new \app\index\model\Order())
                    ->order_list($this->page,$this->limit,$where);
                break;
            case 'orderone':
                list($msg,$err,$data) = (new \app\index\model\Order())
                    ->order_detail_by_orderid($this->con);
                break;
            case 'readmsg':
                list($msg,$err,$data) = (new \app\index\model\Newsandmsg())
                    ->readmsg(session('member_id'),$this->con);
                break;
            case 'readnews':
                list($msg,$err,$data) = (new \app\index\model\Newsandmsg())
                    ->readnews(session('member_id'),$this->con);
                break;
            case 'sign':
                list($msg,$err,$data) = (new \app\index\model\Member())
                    ->sign(session('member_id'));
                break;
            case 'selectsign':
                list($msg,$err,$data) = (new \app\admin\model\Member())
                    ->member_selectsign(session('member_id'),$this->data);
                break;
            default :
                $msg = '非法操作';
                $err = -1000;
                $data = null;
        }
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }

    private function checkTagAndOp()
    {
        $tags = [
            'member'
        ];
        $ops = [
            'message','getone','news','editprofile','active','orderlist','orderone'
            ,'agentproduct','saleproduct','agentregion','readmsg','readnews','sign',
            'selectsign'
        ];
        if(!in_array($this->op,$ops))
        {
            return ['操作不存在',false];
        }
        if(!in_array($this->tag,$tags))
        {
            return ['tag不存在',false];
        }
        return ['success',true];
    }

    public function agent()
    {
        //代理药品
        //下级店铺
        //下级代理
        $tag = 'member';
        $ops = [
          'agent'
        ];
        if($this->tag != $tag || !in_array($this->op,$ops))
        {
            return json(['message'=>'无此操作','err'=>-5000,'data'=>null]);
        }
        $member = session('member_info');
        $this->con['id'] = session('member_id');
       // $member['type'] = 2;
        //1，病人，2，药店，3.药品销售，4，代理商
        switch ($member['type'])
        {
            case 2://药店查看药品
                $this->con['status'] = 1;
                list($msg,$err,$data) = (new \app\index\model\Member())
                    ->agent_product($this->page,$this->limit,$this->con);
                break;
            case 3://销售查看药店
                $this->con['status'] = 1;
                list($msg,$err,$data) = (new \app\index\model\Member())
                    ->agent_shop($this->page,$this->limit,$this->con);
                break;
            case 4://代理查看代理或销售
                $this->con['status'] = 1;
                list($msg,$err,$data) = (new \app\index\model\Member())
                    ->agent_agenter($this->page,$this->limit,$this->con);
                break;
            default :
                $msg = '非法操作';
                $err = -1000;
                $data = null;
        }
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }

    //查询某用户某商品的购买历史
    public function purchasehistory()
    {
        $tag = 'member';
        $ops = [
            'purchasehistory'
        ];
        if($this->tag != $tag || !in_array($this->op,$ops))
        {
            return json(['message'=>'无此操作','err'=>-5000,'data'=>null]);
        }
        $this->con['id'] = session('member_id');
        list($msg,$err,$data) = (new \app\index\model\Member())
            ->buyhistoryByPidAndMid($this->page,$this->limit,$this->con);
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }
   // 查询某用户的下  一   级代理区域的集合
    public function agentorigen()
    {
        $tag = 'member';
        $ops = [
            'agentorigen'
        ];
        if($this->tag != $tag || !in_array($this->op,$ops))
        {
            return json(['message'=>'无此操作','err'=>-5000,'data'=>null]);
        }
        $this->con['id'] = session('member_id');
        list($msg,$err,$data) = (new \app\index\model\Member())
            ->agentorigens($this->page,$this->limit,$this->con);
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }
    //获取已登录会员的代理产品
    public function memberproduct()
    {
        $tag = 'member';
        $ops = [
            'memberproduct'
        ];
        if($this->tag != $tag || !in_array($this->op,$ops))
        {
            return json(['message'=>'无此操作','err'=>-5000,'data'=>null]);
        }
        $this->con['id'] = session('member_id');
        list($msg,$err,$data) = (new \app\index\model\Member())
            ->agent_product($this->page,$this->limit,$this->con);
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }
    //获取会员最近进货记录
    public function buyhistoryByPidAndMid()
    {
        $tag = 'member';
        $ops = [
            'buyhistoryByPidAndMid'
        ];
        if($this->tag != $tag || !in_array($this->op,$ops))
        {
            return json(['message'=>'无此操作','err'=>-5000,'data'=>null]);
        }
        $this->con['id'] = session('member_id');
        list($msg,$err,$data) = (new \app\index\model\Member())
            ->buyhistoryByPidAndMid($this->page,$this->limit,$this->con);
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }
    //会员中奖纪录
    public function prize()
    {
        $tag = 'member';
        $ops = [
            'prize'
        ];
        if($this->tag != $tag || !in_array($this->op,$ops))
        {
            return json(['message'=>'无此操作','err'=>-5000,'data'=>null]);
        }
        $this->con['id'] = session('member_id');
        list($msg,$err,$data) = (new \app\index\model\Active())
            ->winprizelist($this->page,$this->limit);
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }

}