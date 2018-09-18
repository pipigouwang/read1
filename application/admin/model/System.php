<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/31
 * Time: 17:55
 */
namespace app\admin\model;
use app\admin\validate\SystemV;
use think\Db;
use think\Model;
use think\Validate;

class System extends Model{

    public $tags = [
      'sms_config','wxpay_config','point_rule_config','share_config','customer_config'
       ,'aboutus_config','integral_mall','sign_point_config','moneytopoint'
    ];
    protected $table;
    protected $data;

    public function getWxConfig()
    {
        return Db::name('wx_public_number')->where(['id'=>1])->find();
    }
    public function setSystemTable($table)
    {
        $this->table = $table;
    }
    public function setUpdateData($data)
    {
        $this->data = $data;
    }
    /**获取系统设置
     * @return array
     */
    public function getSystemSet()
    {
        try{
            if($this->table == 'customer_config')
            {
                $res = Db::name($this->table)->select();
            }else{
                $res = Db::name($this->table)
                    ->find();
            }
            if($res){
                return ['成功',1000,$res];
            }
            return ['还未设置任何信息',-1002,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-1001,null];
        }
    }
    /**更新系统设置
     * @return array
     */
    public function setSystemSet()
    {
        //数据验证
        list($msg,$err) = $this->validateDatas($this->data);
        if(!$err)
        {
            return [$msg,-1003,null];
        }
        try{
            if($this->table == 'customer_config'){
                Db::startTrans();
                $res = Db::name($this->table)
                    ->where('id','>','0')->delete();
                $ins = Db::name($this->table)
                    ->insertAll($this->data);
                if($res&&$ins){
                    Db::commit();
                    return ['成功',1000,$res];
                }
                Db::rollback();
                return ['更新设置失败',-1003,null];
            }else{
                $res = Db::name($this->table)
                    ->where(['id'=>1])
                    ->update($this->data);
                if($res)
                {
                    return ['成功',1000,$res];
                }
                return ['更新设置失败',-1003,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-1001,null];
        }

    }

    /**验证更新的数据
     * @param $data
     * @return array
     */
    public function validateDatas($data)
    {
        $systemv = new SystemV();
        $rule = $systemv->rule[$this->table];
        $msg = $systemv->msg[$this->table];
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),false];
        }else{
            return ['成功',true];
        }
    }

    public function checkTagsExist($tag)
    {
        if(in_array($tag,$this->tags))
        {
            return true;
        }
        return false;
    }

    public function banner_or_ad_state($tag,$id,$state)
    {
        $this->table = $tag;
        try{
            $res = Db::name($this->table)
                ->where(['id'=>$id])
                ->update(['state'=>$state]);
            if($res)
            {
                return ['成功',1000,$res];
            }
            return ['更新设置失败',-1003,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-1001,null];
        }
    }
    public function banner_or_ad_update($tag,$data)
    {
        $this->table = $tag;
        //数据验证
        list($msg,$err) = $this->validateDatas($data);
        if(!$err)
        {
            return [$msg,-1003,null];
        }
        $id = $data['id'];
        unset($data['id']);
        try{
            $res = Db::name($this->table)
                ->where(['id'=>$id])
                ->update($data);
            if($res)
            {
                return ['',1000,$res];
            }
            return ['更新设置失败',-1003,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-1001,null];
        }
    }
    public function banner_or_ad_get($tag)
    {
        $this->table = $tag;
        try{
            $res['list'] = Db::name($this->table)
                ->where('state','>',0)
                ->select();
            $res['total'] = count($res['list']);
            if($res)
            {
                return ['成功',1000,$res];
            }
            return ['获取设置失败',-1003,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-1001,null];
        }
    }
    public function banner_or_ad_add($tag,$data)
    {
        $this->table = $tag;
        //数据验证
        list($msg,$err) = $this->validateDatas($data);
        if(!$err)
        {
            return [$msg,-1003,null];
        }
        try{
            $res = Db::name($this->table)
                ->insert($data);
            if($res)
            {
                return ['成功',1000,$res];
            }
            return ['添加设置失败',-1003,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-1001,null];
        }
    }

    public static function systemlog($uid,$op)
    {
        $data = [
          'time'=>date('Y-m-d H:i:s'),
            'op'=>$op,
            'uid'=>$uid
        ];
        Db::name('systemlog')->insert($data);
    }

 /*   public function member_online($mid)
    {
        $data = [
          'mid' => $mid,
          'date'=>date('Y-m-d')
        ];
        db('online_count')->insert($data);
    }*/

    public function version_reamrk()
    {
        
    }
}