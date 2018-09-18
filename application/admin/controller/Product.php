<?php
namespace app\admin\controller;

class Product extends UserBase {
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
        $product = new \app\admin\model\Product();
        switch ($this->op)
        {
            case 'getlist':
                list($msg,$err,$data) = $product
                    ->product_list($this->page,$this->limit,$this->con,null);
                break;
            case 'getone':
                list($msg,$err,$data) = $product
                    ->product_one($this->con,$this->data);
                break;
            case 'update':
                list($msg,$err,$data) = $product
                    ->product_edit($this->con,$this->data);
                break;
            case 'add':
                list($msg,$err,$data) = $product
                    ->product_add($this->data);
                break;
            case 'state':
                list($msg,$err,$data) = $product
                    ->product_state($this->con,$this->data);
                break;
        }
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }

    private function checkTagAndOp()
    {
        $tags = [
            'product'
        ];
        $ops = [
          'getlist','getone','update','state','add'
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