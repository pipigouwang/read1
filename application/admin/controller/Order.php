<?php
namespace app\admin\controller;
class Order extends UserBase{
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
        $product = new \app\admin\model\Order();
        switch ($this->op)
        {
            case 'getlist':
                list($msg,$err,$data) = $product
                    ->order_list($this->page,$this->limit,$this->con);
                break;
            case 'getone':
                list($msg,$err,$data) = $product
                    ->order_one($this->page,$this->limit,$this->con);
                break;
            case 'add':
                list($msg,$err,$data) = $product
                    ->order_add($this->data);
                break;
            case 'state':
                list($msg,$err,$data) = $product
                    ->order_state($this->con,$this->data);
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
            'order'
        ];
        $ops = [
            'getlist','getone','state','add'
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