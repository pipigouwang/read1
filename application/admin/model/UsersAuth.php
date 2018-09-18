<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/31
 * Time: 16:02
 */
namespace app\admin\model;
use think\Db;
use think\Model;
use think\Validate;
use app\admin\validate\AuthV;
class UsersAuth extends Model{

    protected $tags = [
      'auth_rule','auth_group','auth_group_access','user'
    ];
    protected $table;

    protected $data;

    public function setTables($table)
    {
        $this->table = $table;
    }
    public function setDatas($data)
    {
        $this->data = $data;
    }

    /**根据tag对权限表的添加
     * @return array
     */
    public function add_auth()
    {
        //数据验证
        list($msg,$err) = $this->validateDatas($this->data);
        if(!$err)
        {
            return [$msg,-2003,null];
        }
        try{
            $res = Db::name($this->table)
                ->insert($this->data);
            if($res)
            {
                return ['成功',2000,null];
            }
            return ['fail',-2000,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-2001,null];
        }
    }
    /**根据tag对权限表修改
     * @return array
     */
    public function edit_auth()
    {
        //数据验证
        list($msg,$err) = $this->validateDatas($this->data);
        if(!$err)
        {
            return [$msg,-2003,null];
        }
        $data = $this->data;
        $id = $data['id'];
        unset($data['id']);
        try{
            $res = Db::name($this->table)
                ->where(['id'=>$id])
                ->update($data);
            if($res)
            {
                return ['成功',2000,null];
            }
            return ['fail',-2005,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-2004,null];
        }
    }
    /**根据tag对权限表更改状态
     * @return array
     */
    public function state_auth()
    {
        $data = $this->data;
        $id = $data['id'];
        unset($data['id']);
        $state = $data['status'];
        try{
            $res = Db::name($this->table)
                ->where(['id'=>$id])
                ->update(['status'=>$state]);
            if($res)
            {
                return ['成功',2000,null];
            }
            return ['fail',-2008,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-2009,null];
        }
    }
    /**获取权限列表
     * @param $page
     * @param $limit
     * @return array
     */
    public function get_auth($page,$limit,$ispage=true)
    {
        try{
            $query = Db::name($this->table);
            if($ispage){
                $query->page($page)
                    ->limit($limit);
            }
            $res['list'] = $query->select();
            if($this->table == 'auth_group'){
                foreach ($res['list'] as $k=>$v){
                    if($res['list'][$k]['rules']){
                        $res['list'][$k]['rulesarr'] = explode(',',$res['list'][$k]['rules']);
                        foreach ($res['list'][$k]['rulesarr'] as $k2=>$v2){
                            $res['list'][$k]['rulesarr'][$k2] = intval($v2);
                        }
                        $rules_id=explode(',',$res['list'][$k]['rules']);
                        if($rules_id){
                            $res['list'][$k]['rules_name']=Db::name('auth_rule')->where('id','in',$rules_id)->column('title');
                        }else{
                            $res['list'][$k]['rules_name']=[];
                        }
                    }
                }
            }
            $res['total'] = Db::name($this->table)->count();
            return ['success',2001,$res];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-2007,null];
        }
    }

    /**添加管理权限
     * @return array
     */
    public function user_add_auth()
    {
        $groupids = explode(',',$this->data['group_ids']);
        if(!empty($groupids))
        {
            $groupids = array_filter($groupids);
        }else{
            return ['分组情况不能为空',-2011,null];
        }
        foreach ($groupids as $v)
        {
            $data[]=[
                'uid'=>$this->data['uid'],
                'group_id'=>$v
            ];
        }
        try{
            $res = Db::name($this->table)
                ->insertAll($data);
            if($res)
            {
                return ['成功',2003,null];
            }
            return ['fail',-2011,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-2010,null];
        }
    }
    /**更新管理员权限
     * @return array
     */
    public function user_update_auth()
    {
        $groupids = explode(',',$this->data['group_ids']);
        if(!empty($groupids))
        {
            $groupids = array_filter($groupids);
        }else{
            return ['分组情况不能为空',-2011,null];
        }
        foreach ($groupids as $v)
        {
            $data[]=[
                'uid'=>$this->data['uid'],
                'group_id'=>$v
            ];
        }
        try{
            Db::startTrans();
                $del = Db::name($this->table)
                    ->where(['uid'=>$this->data['uid']])
                    ->delete();
                $add = Db::name($this->table)
                    ->insertAll($data);
            if($del&&$add)
            {
                return ['成功',2001,null];
            }
            return ['fail',-2012,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-2011,null];
        }
    }
    /**创建管理员
     * @return array
     */
    public function user_create()
    {
        //数据验证
        list($msg,$err) = $this->validateDatas($this->data);
        if(!$err)
        {
            return [$msg,-2003,null];
        }
        $this->data['password'] = md5(config('password_salt').$this->data['password']);
        $this->data['create_time'] = date('Y-m-d H:i:s');
        $this->data['state'] = 2;
        $existaccount = db('user')->where(['account'=>$this->data['account']])->find();
        if($existaccount){
            return ['账号已存在',-2014,null];
        }
        try{
            $res = Db::name($this->table)
                ->insert($this->data);
            $uid = Db::name($this->table)->getLastInsID();
            if($res)
            {
                System::systemlog(session('uid'),"新增了一个管理员:".$this->data['name']);
                return ['成功',2014,['user_id'=>$uid]];
            }else{
                return ['fail',-2014,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-2015,null];
        }
    }

    public function user_edit($con)
    {
        //数据验证
        list($msg,$err) = $this->validateDatas($this->data);
        if(!$err)
        {
            return [$msg,-2003,null];
        }
        $this->data['password'] = md5(config('password_salt').$this->data['password']);
        $uid = $con['id'];
        $data = $this->data;
        unset($data['id']);
        try{
            $res = Db::name($this->table)
                ->where(['id'=>$uid])
                ->update($data);
            if($res)
            {
                System::systemlog(session('uid'),"修改了一个管理员:".$this->data['name']);
                return ['成功',2014,null];
            }else{
                return ['fail',-2014,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-2015,null];
        }
    }

    public function user_login()
    {
        //数据验证
        $rule = [
            'account'=>'require|max:240|alphaNum',
            'password'=>'require|max:48|alphaNum',
        ];
        $msg = [
            'account.require'=>'账号必须',
            'account.alphaNum'=>'账号必须为字符和数字',
            'password.require'=>'密码必须',
            'password.max'=>'密码不超过24个字符',
            'account.max'=>'账号最多24个字符'
        ];
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($this->data);
        if(!$result)
        {
            return [$validate->getError(),-2007,null];
        }
        //print_r($this->data);
        $this->data['password'] = md5(config('password_salt').$this->data['password']);
        //print_r($this->data);
        try{
            $res = Db::name($this->table)
                ->where(['account'=>$this->data['account']
                    ,'password'=>$this->data['password']])
                ->find();
            $ip = getclientip();
            $disable = db('disable_ip')->where(['ip'=>$ip])->find();
            if($disable){
                return ['该ip已被禁用',-2014,null];
            }
            if($res)
            {
                if($res['state'] != 2){
                    return ['该账号已被禁用或删除',-2014,null];
                }
                session('uid',$res['id']);
                $time = date('Y-m-d H:i:s');
                cache("userlogintimes:{$ip}",null);
                System::systemlog(session('uid'),"管理员{$res['name']}在{$time}登录,登录ip为：{$ip}");
                return ['登录成功',2014,null];
            }else{
                if(cache("userlogintimes:{$ip}")){
                    if(cache("userlogintimes:{$ip}")<=20){
                        $num = cache("userlogintimes:{$ip}")+1;
                    }else{
                        db('disable_ip')->insert([
                           'ip'=>$ip,
                            'date'=>date('Y-m-d H:i:s'),
                            'reason'=>'后台频繁登录，疑是非法登录'
                        ]);
                        return ['已禁用该ip',-2014,null];//禁用该ip
                    }
                }else{
                    $num = 1;
                }
                cache("userlogintimes:{$ip}",$num,['expire'=>300]);
                return ['账号或密码错误',-2014,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-2015,null];
        }
    }

    public function user_loginout()
    {
        session('uid',null);
        return ['退出成功',2014,null];
    }
    /**检查tag
     * @param $tag
     * @return bool
     */
    public function checkTagsExist($tag)
    {
        if(in_array($tag,$this->tags))
        {
            return true;
        }
        return false;
    }

    /**检查操作方法
     * @param $op
     * @return bool
     */
    public function checkOpsExist($op)
    {
        $ops = ['update','add','get','state',
            'user_add_auth','user_update_auth',
            'user_create','user_edit','user_login','user_list'
        ];
        if(!in_array($op,$ops))
        {
            return false;
        }
        return true;
    }

    /**验证更新的数据
     * @param $data
     * @return array
     */
    public function validateDatas($data)
    {
        $auth = new AuthV();
        $rule = $auth->rule[$this->table];
        $msg = $auth->msg[$this->table];
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),false];
        }else{
            return ['成功',true];
        }
    }

    public function get_user_list($page,$limit)
    {
        $res['list'] = db('user')->page($page)
            ->where('state','>',0)
            ->limit($limit)->select();
        if($res['list'])
        {
            foreach ($res['list'] as $k=>$v){
                $res['list'][$k]['roles'] = db('auth_group_access')
                    ->where('uid','=',$v['id'])
                    ->select();
            }
            $res['total'] = db('user')
                ->where('state','>',0)
                ->page($page)
                ->limit($limit)->count();
            return ['成功',1000,$res];
        }else{
            $res['total'] = 0;
            return ['失败',-1000,$res];
        }
    }

}