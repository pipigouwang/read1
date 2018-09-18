<?php
namespace app\index\controller;
class Product extends BaseOfUser{

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
                $field = [
                    'name','id','price','sta_goods','company'
                    ,'specifications','sn','period','enddate',
                    'imgurl','remarks','tag','trueprice'
                ];
                $this->con['is_on_sale'] = 1;
                $this->con['status'] = 1;
                list($msg,$err,$data) = $product
                    ->product_list($this->page,$this->limit,$this->con,$field);
                break;
            case 'getone':
                $this->con['is_on_sale'] = 1;
                $this->con['status'] = 1;
                list($msg,$err,$data) = (new \app\index\model\Product())
                    ->detail($this->page,$this->limit,$this->con);
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
            'getlist','getone'
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
}


