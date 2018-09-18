<?php

/**

 * Created by PhpStorm.

 * User: Administrator

 * Date: 2018/8/1

 * Time: 16:42

 */

namespace app\index\controller;

use app\admin\model\Counter;
use app\admin\model\System;

use think\Controller;



class BaseOfUser extends Controller{

    protected $page;

    protected $limit;

    protected $tag;

    protected $op;

    protected $data;

    protected $con;

    public function __construct()

    {

        parent::__construct();

        $mid = session('member_id');

        if($mid)

        {  //统计日在线人数

            Counter::addOnline($mid);

            //检查用户角色变化 反馈给前端

            $userinfo = db('member')->where('id','=',$mid)->find();

            if($userinfo !== session('member_info')) //更新用户信息

            {

                $oUserinfo = session('member_info');

                if($userinfo['type'] != $oUserinfo['type'])

                {

                    echo json_encode(['message'=>'会员角色发生改变','err'=>-100,'type'=>$userinfo['type'].$oUserinfo['type']]);die;

                }

                session('member_info',$userinfo);

            }

        }else{
            if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') != true){

            }else {
                echo json_encode(['message' => '请先登录', 'err' => -1000]);
                die;

            }
        }

      //  var_dump(session('member_info'));die;

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


}