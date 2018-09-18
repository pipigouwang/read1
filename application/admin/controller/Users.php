<?php
namespace app\admin\controller;
use app\admin\model\UsersAuth;

class Users extends UserBase
{
    protected $op;//操作
    protected $tag;//标签
    protected $data;//数据
    protected $con;//条件
    public function __construct()
    {
        parent::__construct();
        $oriData = $this->request->post();
        isset($oriData['op'])?$this->op = $oriData['op']:$this->op = null;
        isset($oriData['tag'])?$this->tag = $oriData['tag']:$this->tag = null;
        isset($oriData['condition'])?$this->con = $oriData['condition']:$this->con = null;
        isset($oriData['data'])?$this->data = $oriData['data']: $this->data =null;
    }

    /**权限相关
     * @return \think\response\Json
     */
    public function rulegroup()
    {
        $usersAuth = new UsersAuth();
        if(!$this->tag||!$usersAuth->checkTagsExist($this->tag))
        {
            return json(['message'=>'tag不存在','err'=>-1004]);
        }
        if(!$this->op ||!$usersAuth->checkOpsExist($this->op))
        {
            return json(['message'=>'op不存在','err'=>-1004]);
        }
        $usersAuth->setDatas($this->data);
        $usersAuth->setTables($this->tag);
        switch ($this->op)
        {
            case 'add':
                list($msg,$err,$datas) = $usersAuth->add_auth();
                break;
            case 'update':
                list($msg,$err,$datas) = $usersAuth->edit_auth();
                break;
            case 'get':
                $page = $this->request->get('page');
                $limit = $this->request->get('limit');
                $ispage = $this->request->post('ispage');
                $ispage? :$ispage = false;
                $page? :$page = 1;
                $limit? :$limit = 9;
                list($msg,$err,$datas) = $usersAuth->get_auth($page,$limit,$ispage);
                break;
            case 'state':
                list($msg,$err,$datas) = $usersAuth->state_auth();
                break;
            case 'user_add_auth':
                list($msg,$err,$datas) = $usersAuth->user_add_auth();
                break;
            case 'user_update_auth':
                list($msg,$err,$datas) = $usersAuth->user_update_auth();
                break;
            case 'user_create':
                list($msg,$err,$datas) = $usersAuth->user_create();
                break;
            case 'user_edit':
                list($msg,$err,$datas) = $usersAuth->user_edit($this->con);
                break;
            case 'user_login':
                list($msg,$err,$datas) = $usersAuth->user_login();
                break;
            case 'user_loginout':
                list($msg,$err,$datas) = $usersAuth->user_loginout();
                break;
            case 'user_list':
                $page = $this->request->get('page');
                $limit = $this->request->get('limit');
                $page? :$page = 1;
                $limit? :$limit = 9;
                list($msg,$err,$datas) = $usersAuth->get_user_list($page,$limit);
                break;
            default :
                return json(['message'=>'不合法的操作','err'=>-1004,'data'=>null]);
        }
        return json(['message'=>$msg,'err'=>$err,'data'=>$datas]);
    }
}
