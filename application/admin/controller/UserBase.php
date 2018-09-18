<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/31
 * Time: 15:59
 */
namespace app\admin\controller;
use think\facade\Request;
use think\Controller;
class UserBase extends Controller{
    public function  __construct()
    {
        parent::__construct();
        session('uid',1);
        if(!session('uid'))
        {
            echo json_encode(['message'=>'请先登录','err'=>-1000]);die;
        }
        $auth = new \Auth\Auth();
        $request = Request::instance();
        if (!$auth->check($request->module()
            . '-' . $request->controller() . '-' .
            $request->action(), 1)) {// 第一个参数是规则名称,第二个参数是用户UID
            echo json_encode(['message'=>'没有权限','err'=>-1000]);die;
        }
    }
}