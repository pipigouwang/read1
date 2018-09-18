<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 11:48
 */

namespace app\index\controller;

use sms\Smsconfig;
use think\Controller;

class Sendsms extends Controller
{
    public function code()
    {
           //获取验证码
        $phone = $this->request->post('phone');
        if(!$phone)
        {
            return json(['message'=>'电话号码格式不正确','err'=>-2000,'data'=>null]);
        }
        if(cache($phone.'times') > 35)
        {
            return json(['message'=>'发送过于频繁','err'=>-2000,'data'=>null]);
        }else{
            $code = $this->createCode();
            $con = '验证码为:'.$code;
            $sms = new Smsconfig();
            $time = cache($phone.'times') + 1;
            cache($phone.'times',$time,120);
            cache($phone,$code,['expire'=>300]);
            $data = $sms->send($phone,$con);
            if($data['code'] == 0)
            {
                return json(['message'=>'success','err'=>2000,'data'=>null]);
            }else{
                return json(['message'=>$data['message'],'err'=>-2000,'data'=>null]);
            }
        }
    }
    public function createCode($length = 6)
    {
        return rand(pow(10,($length-1)), pow(10,$length)-1);
    }
    public function checkCode($phone,$code)
    {
        //检查验证码
        if(cache($phone) !== $code)
        {
            return false;
        }else{
            return true;
        }
    }
}