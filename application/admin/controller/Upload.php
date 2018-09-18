<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17
 * Time: 15:52
 */

namespace app\admin\controller;


use think\Controller;

class Upload   extends Controller
{
    //上传图片
    public function index()
    {
        $dic = $this->request->post('dic');
        list($msg,$err,$data) = (new \app\admin\model\Upload())->index($dic);
        return json(['message'=>$msg,'err'=>$err,'data'=>$data]);
    }

    public function add()
    {
        
    }
}