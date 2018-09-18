<?php
namespace app\admin\controller;
class Member extends UserBase{
    private $page;
    private $limit;
    private $tag;
    private $op;
    private $data;
    private $con;
    public function __construct()
    {
        parent::__construct();
        $page = $this->request->get('page');
        $page? $this->page = $page :$this->page =1;
        $limit = $this->request->get('limit');
        $limit? $this->limit = $limit:$this->limit = 9;
        $post = $this->request->post();
        isset($post['op'])?$this->op = $post['op']:$this->op = null;
        isset($post['tag'])?$this->tag = $post['tag']:$this->tag = null;
        isset($post['data'])?$this->data = $post['data']:$this->data = null;
        isset($post['condition'])?$this->con = $post['condition']:$this->con = null;
    }

    public function index()
    {
        list($msg,$err) = $this->checkTagAndOp();
        if($err === false)
        {
            return json(['message'=>$msg,'err'=>-5000,'data'=>null]);
        }
        $member = new \app\admin\model\Member();
        switch ($this->op)
        {
            case 'getlist':
                list($msg,$err,$data) = $member
                    ->member_list($this->page,$this->limit,$this->con);
                break;
            case 'getone':
                list($msg,$err,$data) = $member
                    ->member_one($this->con);
                break;
            case 'add':
                list($msg,$err,$data) = $member
                    ->member_add($this->data);
                break;
            case 'state':
                list($msg,$err,$data) = $member
                    ->member_state($this->con,$this->data);
                break;
            case 'update':
                list($msg,$err,$data) = $member
                    ->member_edit($this->con,$this->data);
                break;
            case 'case_history':
                list($msg,$err,$data) = $member
                    ->member_case_history($this->page,$this->limit,$this->con);
                break;
            case 'case_history_add':
                list($msg,$err,$data) = $member
                    ->member_case_history_add($this->data);
                break;
            case 'point_history':
                list($msg,$err,$data) = $member->point_history($this->page,$this->limit,$this->con,false);
                break;
            case 'point_operation':
                list($msg,$err,$data) = $member
                    ->member_point_operation($this->con,$this->data);
                break;
            case 'role':
                list($msg,$err,$data) = $member
                    ->member_role($this->con,$this->data);
                break;
            case 'findfather':
                list($msg,$err,$data) = $member
                    ->member_findfather($this->con);
                break;
            case 'getuserbyphone':
                list($msg,$err,$data) = $member
                    ->member_getuserbyphone($this->page,$this->limit,$this->con);
                break;
            case 'getmemberproduct':
                list($msg,$err,$data) = $member
                    ->member_getmemberproduct($this->page,$this->limit,$this->con);
                break;
            case 'getchild':
                list($msg,$err,$data) = $member
                    ->member_getchild($this->page,$this->limit,$this->con);
                break;
            case 'recentsearch'://最近搜索
                $mid = $this->con['id'];
                list($msg,$err,$datas) = \app\admin\model\Member::admingetrecentsearch($this->page,$this->limit,$mid);
                return json(['message'=>$msg,'err'=>$err,'data'=>$datas]);
                break;
            case 'pointupdate'://积分操作
                list($msg,$err,$data) = $member
                    ->member_pointupdate($this->con,$this->data);
                break;
            case 'addsign'://添加病的标签
                list($msg,$err,$data) = $member
                    ->member_addsign($this->data);
                break;
            case 'signlist'://标签列表
                list($msg,$err,$data) = $member
                    ->member_signlist($this->page,$this->limit,$this->con);
                break;
            case 'delsign'://标签列表
                list($msg,$err,$data) = $member
                    ->member_delsign($this->con);
                break;
            default :
                return json(['message'=>'非法操作','err'=>-1000,'data'=>null]);
                break;

        }
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }

    private function checkTagAndOp()
    {
        $tags = [
            'member'
        ];
        $ops = [
            'getlist','getone','state','add','update','case_history'
            ,'case_history_add','point_history','point_operation','role'
            ,'findfather','getuserbyphone','getmemberproduct','getchild',
            'recentsearch','pointupdate','addsign','signlist','delsign'
        ];

        if(!in_array($this->op,$ops))
        {
            return ['操作不存在',false];
        }
        if(!in_array($this->tag,$tags))
        {
            return ['tag不存在',false];
        }
        return ['成功',true];
    }


}