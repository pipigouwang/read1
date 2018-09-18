<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/4
 * Time: 10:53
 */

namespace app\admin\model;

class Newspush
{
    /**
        微信公众号永久素材管理，推送图文消息类
     *
     */
    private $accesstoken;

    public function __construct()
    {
        $obj = (new Publicnumbermenuset());
        $obj->get_access_token();
        $this->accesstoken = $obj->accesstoken;
    }
    public function send_news($mediaid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='.$this->accesstoken;
        $data = [
            'filter'=>[
                'is_to_all'=>true,
            ],
            'mpnews'=>[
                'media_id'=>$mediaid
            ],
            'msgtype'=>'mpnews',
            'send_ignore_reprint'=>0
        ];
        return $this->https_request($url,json_encode($data));
    }

    public function del_material($mediaid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/material/del_material?access_token='.$this->accesstoken;
        $data = array(
            "media_id"=>$mediaid,
        );
        return $this->https_request($url,json_encode($data));
    }
    public function add_news($data)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token='.$this->accesstoken;
        $con = [
            [
                'title'=>$data['title'],
                'thumb_media_id'=>$data['thumb_media_id'],
                'author'=>$data['author'],
                'digest'=>$data['digest'],
                'show_cover_pic'=>1,
                'content'=>$data['content'],
                'content_source_url'=>$data['content_source_url']
            ]
        ];
        $data = array(
            "articles"=>$con
        );
        return $this->https_request($url,json_encode($data));
    }
    //新增永久媒体素材
    public function add_material(){
        list($msg,$err,$filename) = (new \app\admin\model\Upload())
            ->index('material');
        if(!$err){
            return ['message'=>$msg,'errcode'=>1000,null];
        }
        $real_path = "{$_SERVER['DOCUMENT_ROOT']}{$filename}";
        $fi = new \finfo(FILEINFO_MIME_TYPE);
        $mimetype = $fi->file($real_path);
        $filedata = array (
            "media" =>new \CURLFile($real_path,$mimetype)
        );
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->accesstoken.'&type=image';
        $res = $this->https_request($url,$filedata);
        if(isset($res['media_id'])){
            return ['message'=>$msg,'errcode'=>1000,$res];
        }
        return ['message'=>$msg,'errcode'=>-1000,$res];
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
    //获取素材列表
    public function get_mediaList($type="image",$offset=0,$count=20){
        $data = array(
            'type'=>$type,
            'offset'=>$offset,
            'count'=>$count
        );
        $curl = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$this->accesstoken;
        return $this->https_request($curl,json_encode($data));
    }

}