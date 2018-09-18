<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 11:18
 */

namespace app\admin\model;
/**
公众号菜单相关设置模型
 *
 */

class Publicnumbermenuset
{
    private $appid = null;
    private $secrect = null;
    public $accesstoken = null;
    private $jspai_tiket = null;
    public $current_view_url = null;
    public function get_config()
    {
        //获取公众号配置信息(appid   sercert)
        $config = db('wx_public_number')->find();
        if(!$config)
        {
            return ['尚未配置微信公众号信息,请到表：wx_public_number 配置相关信息',false,null];
        }
        $this->appid = $config['appid'];
        $this->secrect = $config['appsecrect'];
    }
    
    public function get_access_token()
    {
        $this->get_config();
        if(!cache('accesstoken'))
        {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->secrect}";
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $res=json_decode(curl_exec($curl),true);
            /*        $errno = curl_errno( $curl );
                    $info  = curl_getinfo( $curl );*/
            $this->accesstoken = $res['access_token'];
            cache('accesstoken',$res['access_token'],['expire'=>7000]);
            curl_close($curl);
        }else{
            $this->accesstoken = cache('accesstoken');
        }
        return true;
    }

    public function set_current_view_url($url)
    {
        $this->current_view_url = $url;
    }
    // 获取签名
    public function getSignPackage() {
        try{
            // 获取token
            $this->get_access_token();
            // 获取ticket
            $this->get_jsapi_ticket_by_accesstoken();
            $ticket = $this->jspai_tiket;
            // 该URL为使用JSSDK接口的URL
            // 时间戳
            $timestamp = time();
            // 随机字符串
            $nonceStr = $this->createNoncestr();
            // 这里参数的顺序要按照 key 值 ASCII 码升序排序 j -> n -> t -> u
            $string = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$this->current_view_url";
            $signature = sha1($string);
            $signPackage = array (
                "appId" => $this->appid,
                "nonceStr" => $nonceStr,
                "timestamp" => $timestamp,
                "signature" => $signature
            );
            // 提供数据给前端
            return array('data' => $signPackage);
        }catch (\Exception $e)
        {
            throw exception($e->getMessage());
        }
    }

// 创建随机字符串
    private function createNoncestr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for($i = 0; $i < $length; $i ++) {
            $str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
        }
        return $str;
    }
    //获取用户jsapi的jsapi_ticket
    public function get_jsapi_ticket_by_accesstoken()
    {
        //判断缓存ticket
        if(!cache('ticket'))
        {
            //获取ticket
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$this->accesstoken}&type=jsapi";
            $ch = curl_init($url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $res=json_decode(curl_exec($ch),true);
            curl_close($ch);
            if($res['errcode'] == 0)
            {
                $this->jspai_tiket = $res['ticket'];
                cache('ticket',$res['ticket'],['expire'=>7000]);
                return $res['ticket'];
            }else{
                throw exception('获取ticket失败');
            }
        }else{
            $this->jspai_tiket = cache('ticket');
        }

    }
    public function menu_create()
    {
        $this->get_access_token();
        //创建菜单
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$this->accesstoken}";
        $menu =
       "{
     \"button\":[
     {    
          \"type\":\"view\",
          \"name\":\"爱医慧\",
          \"url\":\"https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appid}&redirect_uri=http://aiyihui.yongweisoft.cn/index/index/login&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect\"
      },
      {    
          \"type\":\"view\",
          \"name\":\"往期回顾\",
          \"url\":\"https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzU5NDY1MjI5Ng==&scene=126#wechat_redirect\"
      }]
 }";
        $url1 = "https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token={$this->accesstoken}";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);// https请求不验证证书和hosts
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $res = json_decode(curl_exec($curl), true);
        curl_close($curl);
         print_r($res);

        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $menu);
        $data = curl_exec($ch);
        curl_close($ch);
        print_r($data);
    }
    public function menu_del()
    {
        //菜单删除
    }

    public function getUserInfoByOpenid($userOpenid)
    {
        $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$this->accesstoken}&openid={$userOpenid}&lang=zh_CN";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);// https请求不验证证书和hosts
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $res = json_decode(curl_exec($curl), true);
        curl_close($curl);

        return $res;
    }
    //上传图文消息内的图片获取URL
    public function sendmsgs()
    {
        
    }

    public function add_news()
    {
        $obj = (new Publicnumbermenuset());
        $obj->get_access_token();
        $token = $obj->accesstoken;
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token='.$token;
        $con = [
            'title'=>'测试',
            'thumb_media_id'=>"bw0N2f42hKUsGQ2yDKO2pdrgV6uxvFYW62H5vh9n7ho",
            'author'=>'聂芳',
            'digest'=>'摘要摘要',
            'show_cover_pic'=>1,
            'content'=>'just a test news',
            'content_source_url'=>'http://aiyihui.yongweisoft.cn/aiyihui/www/userinformation.html'
        ];
        $data = array(
          "articles"=>json_encode($con)
        );
        $res = $this->https_request($url,$data);
        var_dump($res);
    }
    //新增永久媒体素材
    public function add_material(){

        $fi = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $fi->file('./test.jpg');
        $fileinfo = [
            'filename' =>'/test.jpg',
        ];
        $real_path = "{$_SERVER['DOCUMENT_ROOT']}{$fileinfo['filename']}";
        $obj = (new Publicnumbermenuset());
        $obj->get_access_token();
        $token = $obj->accesstoken;
        $filedata = array (
            "media" =>new \CURLFile($real_path,'image/jpg')
        );
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$token.'&type=image';
        $res = $this->https_request($url,$filedata);
        var_dump($res);
    }
    function https_request($url,$data = null)
    {
        $curl = curl_init();
        curl_setopt ( $curl, CURLOPT_SAFE_UPLOAD, true );
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}