<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/13
 * Time: 10:45
 */

namespace app\index\controller;


class Upload extends BaseOfUser
{
    public function index()
    {
        list($msg,$err,$data) = (new \app\admin\model\Upload())->index($this->data['dic']);
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }
}