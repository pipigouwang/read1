<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/1
 * Time: 16:47
 */
namespace app\index\model;
use think\Cache;
use think\Db;
use think\Validate;
class Member extends \app\admin\model\Member {

    public function login_handle($account,$password)
    {
        $rule = [
            'account'=>'require|max:240',
            'psw'=>'require|max:240|alphaNum'
        ];
        $msg = [
          'account.require'=>  '账号长度不正确',
            'account.max'=>  '账号错误',
            'psw.require'=>  '密码长度不正确',
            'psw.max'=>  '密码长度不正确',
            'psw.alphaNum'=>  '密码含有非法字符',
        ];
        $data = [
            'psw'=>$password,
            'account'=>$account
        ];
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),-3000,null];
        }
        try{
            $find = Db::name('member')
                ->where(['account'=>$account,'psw'=>md5(config('password_salt').$password)])->find();
            if($find)
            {
                return ['登录成功',3000,null];
            }
            $num = Cache::get($account);
            if($num>=5)
            {
                return ['输出错误次数过多，请两小时后再试',-3000,null];
            }
            $num +=1;
            Cache::set($account,$num,3600);
            return ['账号或密码错误',-3000,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-3001,null];
        }
    }

    /**定单记录
     * @param $page
     * @param $limit
     * @param $where
     * @return array
     */
    public function orderRecord($page,$limit,$where)
    {
        $con = array_filter($where);
        try{
            $res['list'] = Db::name('order_goods')
                ->field('product.name,product.price,')
                ->join('order','order_goods.order_id = order.order_id')
                ->join('product','product.id = order_goods.goods_id')
                ->where($con)->page($page)->limit($limit)->select();
            if($res['list'])
            {
                $res['total'] = Db::name('news')
                    ->where($con)->count();
                return ['success',7000,$res];
            }else{
                return ['暂无',-7001,['list'=>[],'total'=>0]];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-7000,null];
        }
    }

    public function shopdetail($where)
    {
        if(!isset($where['id'])||!Member::get($where['id']))
        {
            return ['未找到该店铺',-8002,null];
        }
        try{
            $res = db('member')
                ->field('phone,avator,
                id,be_good_at,address,status,uname,shop_name,
                longitude,latitude,licence_imgurl,shop_image,label')
                ->where('type','=',2)
                ->where('status','=',1)
                ->where('id','=',$where['id'])
                ->find();
            if($res)
            {
                $res['sale_products'] = db('stock')
                    ->join('product','product.id = stock.pid')
                    ->field('product.name,product.id')
                    ->where('mid','=',$where['id'])
                    ->select();
                return ['success',8002,$res];
            }else{
                return ['未找到该店铺',-8002,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-8001,null];
        }
    }

    public function personalcentercenter()
    {
        if(session('member_info'))
        {
            $userinfo = session('member_info');
            //未读消息，咨讯数量//system_msg
            $readmsg = db('member_msg_readed')
                ->where(['mid'=>session('member_id')])
                ->join('system_msg','system_msg.id = member_msg_readed.id')
                ->where(['member_msg_readed.status'=>1])
                ->count();
            $readnews = db('member_news_readed')
                ->where(['mid'=>session('member_id')])
                ->count();
            $news = db('news')
                ->where(['status'=>1])->count();
            $user = [
                'id'=>$userinfo['id'],
               'avator'=>$userinfo['avator'],
                'shop_name'=>$userinfo['shop_name'],
               'phone'=>substr_replace($userinfo['phone'],'***','4','3')
                ,'level'=>$userinfo['level'],
                'level_imgurl'=>$userinfo['level_imgurl'],
                'notreadmsg'=>$readmsg,
                'notreadnews'=>$news - $readnews,
               // 'time'=>date('Y-m-d H:i:s')
            ];
            return ['success',9000,$user];
        }else{
            return ['用户尚未登录',-9000,null];
        }
    }

    public function member_edit($where,$data)
    {
        $rule = [
            'avator'=>'require',
            'account'=>'require|max:40'
        ];
        $msg = [
            'avator.require'=>'头像必须',
            'account.require'=>'昵称必须',
            'account.max'=>'昵称最长不超过40字符',
        ];
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),-6003,null];
        }
        if(!isset($where['id'])||!Member::get($where['id']))
        {
            return ['会员不存在',-6009,null];
        }
        try{
            $ins = Db::name('member')
                ->where('id','=',$where['id'])
                ->update($data);
            if(!$ins)
            {
                return ['修改失败',-6010,null];
            }
            return ['修改成功',6001,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6011,null];
        }
    }

    /**通过会员账号id获取代理的药品
     * @param $page
     * @param $limit
     * @param $con
     */
    public function agent_product($page,$limit,$con)
    {
        try{
            $query = db('member_product')
                ->field('product.name,num as stock,imgurl,product.id')
                ->join('product','product.id = member_product.pid')
                ->join('stock','stock.pid = member_product.pid')
                ->where('member_product.mid','=',$con['id'])
                ->where('stock.mid','=',$con['id']);
            $query1 = clone($query);
            $res['list'] = $query->page($page)
                ->limit($limit)->select();
            if($res['list'])
            {
                $res['total'] = $query1->count();
                return ['success',6011,$res];
            }

            return ['暂无数据',-6012,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6011,null];
        }
    }
    /**通过药品id和会员id 获取会员最近下单记录
     * @param $where
     */
    public function buyhistoryByPidAndMid($page,$limit,$con)
    {
        if(!isset($con['pid']) || !isset($con['id'])
            || empty($con['id']) || empty($con['pid']))
        {
            return ['参数不正确',-6012,null];
        }
        try{
            $query = db('order_goods')
                ->field('order.create_date,num,sn')
                ->join('order','order_goods.order_id = order.order_id')
                ->join('product','product.id = order_goods.goods_id')
                ->distinct(true)
                ->where('order_goods.goods_id','=',$con['pid'])
                ->where('order.member_id','=',$con['id']);
            $query1 = clone($query);
            $res['list'] = $query->page($page)
                ->limit($limit)->select();
            if($res['list'])
            {
                $res['total'] = $query1->count();
                return ['success',6011,$res];
            }
            return ['暂无数据',-6012,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6011,null];
        }
    }
    /**通过销售商id获取该销售商代理的各个药店信息
     * @param $page
     * @param $limit
     * @param $con
     */
    public function agent_shop($page,$limit,$con)
    {
        try{
            $query = db('member')
                ->field('shop_name,longitude,latitude,id,
                shop_image,phone,address')
                ->where('fatherid','=',$con['id']);
            $query1 = clone($query);
            $res['list'] = $query->page($page)
                ->limit($limit)->select();
            if($res['list'])
            {
                $res['total'] = $query1->count();
                return ['success',6011,$res];
            }
            return ['暂无数据',-6012,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6011,null];
        }
    }

    /**根据登录的代理用户信息查询其子代理的区域名
     * @param $page
     * @param $limit
     * @param $con
     */
    public function agentorigens($page,$limit,$con)
    {
        try{
            $query = db('member')
                ->field('shop_name,longitude,latitude,
                shop_image,phone,address')
                ->where('fatherid','=',$con['id']);
            $query1 = clone($query);
            $res['list'] = $query->page($page)
                ->limit($limit)->select();
            if($res['list'])
            {
                $res['total'] = $query1->count();
                return ['success',6011,$res];
            }
            return ['暂无数据',-6012,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6011,null];
        }
    }
    /**通过该代理商的Id   根据区域 获取下级代理商的信息
     * @param $page
     * @param $limit
     * @param $con
     */
    public function agent_agenter($page,$limit,$con)
    {
        try{
            $query = db('member')
                ->field('uname,
                avator,phone,address')
                ->where('fatherid','=',$con['id']);
            $query1 = clone($query);
            $res['list'] = $query->page($page)
                ->limit($limit)->select();
            if($res['list'])
            {
                $res['total'] = $query1->count();
                return ['success',6011,$res];
            }
            return ['暂无数据',-6012,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6011,null];
        }
    }
    /**无电话号码的注册  用于前端通过openid注册
     * @param $data
     * @return arra
     */
    public function member_add($data)
    {
        foreach ($data as $k=>$v)
        {
            if(is_array($v))
            {
                $data[$k] = json_encode($v);
            }
        }
        $data['create_time'] = date('Y-m-d H:i:s');
        try{
            Db::startTrans();
            $ins = Db::name('member')
                ->insertGetId($data);
            if(!$ins)
            {
                Db::rollback();
                return ['添加失败',-6001,null];
            }
            if(isset($data['sale_products']) && $data['sale_products'])
            {
                $data1 = json_decode($data['sale_products'],true);
                $mp = [];
                foreach ($data1 as $key=>$val)
                {
                    $mp[] = [
                        'mid'=>$ins,
                        'pid'=>$val
                    ];
                }
                $ins1 = Db::name('member_product')->insertAll($mp);
                if(!$ins1)
                {
                    Db::rollback();
                    return ['添加失败',-6001,null];
                }
            }
            if(isset($data['agent_products']) && $data['agent_products'])
            {
                $data2 = json_decode($data['agent_products'],true);
                $mp2 = [];
                foreach ($data2 as $key=>$val)
                {
                    $mp2[] = [
                        'mid'=>$ins,
                        'pid'=>$val
                    ];
                }
                $ins2 = Db::name('member_product')->insertAll($mp2);
                if(!$ins2)
                {
                    Db::rollback();
                    return ['添加失败',-6001,null];
                }
            }
            Db::commit();
            return ['添加成功',6001,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6001,null];
        }
    }
    //签到处理
    public function sign($mid)
    {
        //判断今日是否签到
        if( cache("sign:{$mid}"))
        {
            return ['今日已签到',-6001,null];
        }
        try{
            //获取签到配置的积分
            $point = db('sign_point_config')->where(['id'=>1])->find()['point'];
            $point? :$point=0;
            //添加积分历史
            $data = [
                'pointsum'=>$point,
                'mid'  => $mid,
                'date'=>date('Y-m-d H:i:s'),
                'type'=>4,
                'tip'=>'签到得积分'
            ];
            Db::startTrans();
            $ins = db('member_point_history')->insert($data);
            $ins1 = db('sign_count')->insert(['mid'=>session('member_id'),'date'=>date('Y-m-d')]);
            $ups = db('member')->where(['id'=>$mid])->setInc('point',$point);
            //更新用户积分
            if(($ins&&$ups&&$ins1)||($ins&&$ups==0&&$ins1)){
                Db::commit();
                //添加今天签到的缓存
                cache("sign:{$mid}",1,['expire'=>strtotime(date('Y-m-d',strtotime("+1 day")))-time()]);
                return ['签到成功',6001,$point];
            }
            Db::rollback();
            return ['签到失败',-6001,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6001,null];
        }
    }
}