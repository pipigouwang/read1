<?php
namespace app\index\controller;

use app\admin\model\Counter;
use app\admin\model\Publicnumbermenuset;
use think\Controller;

class Index extends Controller
{
    protected $page;
    protected $limit;
    protected $tag;
    protected $op;
    protected $data;
    protected $con;
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
    //公总号入口
    public function index()
    {
        $publicnumber = \wxlib\wxpublicnumber::getInstence();
        $publicnumber->handle();
    }
    //前端首页信息
    public function banner()
    {
        $this->checklogin();
        //banner
        $datas = db('banner_config')
            ->where('state','=',2)->select();
        return json(['message'=>'success','err'=>200,'data'=>$datas]);
    }

    public function recentsearch()
    {
        $this->checklogin();
        //最近搜索
        $mid = session('member_id');
        list($msg,$err,$datas) = \app\index\model\Product::getrecentsearch($this->page,$this->limit,$mid);
        return json(['message'=>$msg,'err'=>$err,'data'=>$datas]);
    }

    public function permanent()
    {
        $this->checklogin();
        list($msg,$err,$datas['permanentmedicine']) = (new \app\index\model\Product())
            ->permanentmedicine($this->page,$this->limit,$this->con);
        return json(['message'=>$msg,'err'=>$err,'data'=>$datas]);
    }

    //用户公众号静默授权入口  该入口负责注册默认用户信息，跳转对应页面
    public function login()
    {
        //获取用户code 通过code 获取accesstoken  通过token 获取openid并判断是否注册或是否绑定
        $code = $_GET['code'];
        if(!$code){
            $this->redirect('http://aiyihui.yongweisoft.cn/aiyihui/www/404.html',302);
        }
        //获取accesstoken openid
        $userOpenidAndAccesstoken = (new \wxlib\wxtool())->getAccessTokenByCode($code);
        //查询用户是否已经注册
        $find = db('member')
            //->field('phone,type')
            ->where('openid','=',$userOpenidAndAccesstoken['openid'])
            ->find();
        //返回用户电话和类型 先根据用户电话是否存在判断是否绑定电话  再根据类型选择入口
        if(!$find)//没注册 就注册
        {
            $user = [
                "type"=>1, //1，病人，2，药店，3.药品销售，4，代理商
                "openid"=>$userOpenidAndAccesstoken['openid'],//默认空
                "phone"=>"",//必填
                "avator"=>"/uploads/avator.png",//头像  默认一个图片地址
                "level"=>"1",//等级
                "sex"=>"1",//1,男2女
                "level_imgurl"=>"asfda",//等级图片
                "address"=>"",//地址
                "status"=>1,//状态 0，注销，1启用
                "label"=>"",//标签
            ];
            list($msg,$err,$data) = (new \app\index\model\Member())->member_add($user);
            if($err>0)//注册成功
            {
                $user1 = db('member')->where(['openid'=>$userOpenidAndAccesstoken['openid']])->find();
                //跳转到绑定手机号页面 --》绑定之后跳转到病人页面
                session('member_id',$user1['id']);
                session('member_info',$user1);
                //添加用户登录
                Counter::addOnline($user1['id']);
                $this->redirect('http://aiyihui.yongweisoft.cn/aiyihui/www/login.html?openid='.$userOpenidAndAccesstoken['openid'],302);
            }else{
                //注册失败  跳转到系统错误页
                $this->redirect('http://aiyihui.yongweisoft.cn/aiyihui/www/404.html',302);
            }
        }else{ //已经注册
            if($find['status'] == 0)//用户被禁用
            {
                $this->redirect('http://aiyihui.yongweisoft.cn/aiyihui/www/404.html?openid='.$userOpenidAndAccesstoken['openid'],302);
            }
            session('member_info',$find);
            session('member_id',$find['id']);
            //添加用户登录
            Counter::addOnline($find['id']);
            if($find['phone'])
            {
                //已绑定手机号  根据会员类型 跳转首页
                switch ($find['type'])
                {
                    case 1:
                        $this->redirect('http://aiyihui.yongweisoft.cn/aiyihui/www/userindex.html?openid='.$userOpenidAndAccesstoken['openid'],302);
                        break;
                    case 2:
                        $this->redirect('http://aiyihui.yongweisoft.cn/aiyihui/www/pharindex.html?openid='.$userOpenidAndAccesstoken['openid'],302);
                        break;
                    case 3:
                        $this->redirect('http://aiyihui.yongweisoft.cn/aiyihui/www/salesindex.html?openid='.$userOpenidAndAccesstoken['openid'],302);
                        break;
                    case 4:
                        $this->redirect('http://aiyihui.yongweisoft.cn/aiyihui/www/agentindex.html?openid='.$userOpenidAndAccesstoken['openid'],302);
                        break;
                    default :
                        //跳转到系统错误页面
                        $this->redirect('http://aiyihui.yongweisoft.cn/aiyihui/www/404.html?openid='.$userOpenidAndAccesstoken['openid'],302);
                }
            }else{
                $this->redirect('http://aiyihui.yongweisoft.cn/aiyihui/www/login.html?openid='.$userOpenidAndAccesstoken['openid'],302);
                //未绑定手机号  跳转到绑定手机号页面 --》绑定之后跳转到病人页面
            }

        }
    }
    //获取用户jsapi的调用配置参数
    public function geography()
    {
        $url = $this->request->post('url');
        $p = new Publicnumbermenuset();
        $p->set_current_view_url($url);
        $res = $p->getSignPackage();
        return json(['message'=>'success','err'=>22,'data'=>$res]);
    }
    //用户绑定手机号接口
    public function bandphone()
    {
        $phone = $this->request->post('phone');
        $code = $this->request->post('code');
        if(!isset($_GET['openid']))
        {
            return json(['message'=>'请务从微信入口进入','err'=>-2004,'data'=>null]);
        }
        //checkphone
        $res = (new Publicnumbermenuset())->getUserInfoByOpenid($_GET['openid']);
        $sms = new Sendsms();
        if($sms->checkCode($phone,$code))
        {
            $repeat = db('member')->where(['phone'=>$phone])->count();
            if($repeat>1){
                return json(['message'=>'电话已存在,请联系后台管理员','err'=>-2004,'data'=>null]);
            }
            $exist = db('member')->where(['phone'=>$phone])->find();
            if($exist)
            {
                db('member')
                    ->where(['openid'=>$_GET['openid']])->delete();
                //绑定openid到手机号
                $update = db('member')
                    ->where(['phone'=>$phone])
                    ->update([
                        'openid'=>$_GET['openid'],
                        'avator'=>$res['headimgurl'],
                        'sex'=>$res['sex'],
                        'name'=>$res['nickname'],
                        'address'=>$res['country'].'-'.$res['province'].'-'.$res['city']
                    ]);
                 if($update)
                {
                    $user = db('member')
                        ->where(['openid'=>$_GET['openid']])->find();
                    session('member_id',$user['id']);
                    session('member_info',$user);
                    return json(['message'=>'绑定成功','err'=>2003,'data'=>$user['type']]);
                }
                return json(['message'=>'绑定失败，请重试','err'=>-2003,'data'=>$_GET['openid']]);
            }
            //绑定手机号到openid
            $update = db('member')
                ->where(['openid'=>$_GET['openid']])
                ->update([
                    'phone'=>$phone,
                    'avator'=>$res['headimgurl'],
                    'sex'=>$res['sex'],
                    'name'=>$res['nickname'],
                    'address'=>$res['country'].'-'.$res['province'].'-'.$res['city']
                ]);
            if($update)
            {
                $user = db('member')
                    ->where(['openid'=>$_GET['openid']])->find();
                session('member_id',$user['id']);
                session('member_info',$user);
                return json(['message'=>'绑定成功','err'=>2003,'data'=>1]);
            }
            return json(['message'=>'绑定失败，请重试','err'=>-2003,'data'=>$_GET['openid']]);
        }else{
            return json(['message'=>'验证码不匹配','err'=>-2004,'data'=>null]);
        }
    }

    public function checklogin()
    {
        $mid = session('member_id');
        //统计日在线人数
        //(new System())->member_online($mid);
        if($mid)
        {
            //检查用户角色变化 反馈给前端
            $userinfo = db('member')->where('id','=',$mid)->find();
            $oriUser = session('member_info');
            if($userinfo['type'] != $oriUser['type']) //更新用户信息
            {
                //err 为 -100 前端判断并跳转对应type的页面
                session('member_info',$userinfo);
                echo json_encode(['message'=>'会员角色发生改变','err'=>-100,'type'=>$userinfo['type'],'openid'=>$userinfo['openid']]);die;
            }
            return;
        }else{

            echo json_encode(['message'=>'请先登录','err'=>-1000]);die;
        }
    }

    //前端分享成功回调接口  -》写入分享数据库方便统计
    public function share()
    {
        $post = $this->request->post();
        $data = [
            'date'=>date('Y-m-d'),
            'desc'=>$post['desc'],
            'title'=>$post['title'],
            'url'=>$post['url'],
            'img'=>$post['img'],
           // 'uuid'=>$post['uuid'],
            'mid'=>session('member_id')
        ];
        db('share_count')->insert($data);
    }
    //前端广告弹窗  前端根据此接口判断是否显示弹窗广告
    public function advertisement()
    {
        $res = db('ad_config')->where(['state'=>2])->find();
        if($res)
        {
            return json(['message'=>'success','err'=>3000,'data'=>$res]);
        }else{
            return json(['message'=>'success','err'=>-3000,'data'=>null]);
        }
    }

    //公众号入口之最新资讯
    public function news()
    {
        echo 123;
    }
}
