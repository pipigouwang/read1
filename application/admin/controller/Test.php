<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/20
 * Time: 14:32
 */

namespace app\admin\controller;


use app\admin\model\Counter;
use app\admin\model\Publicnumbermenuset;
use think\Controller;
use wxlib\http;
use wxlib\wxcustom;
use wxlib\wxtool;

class Test extends Controller
{
/*
    public function dir()
    {
        $dir = 'E:\PHPTutorial\WWW\read1';
        $tree = $this->recurDir($dir);
        echo "<pre>";
        print_r($tree);
        echo "</pre>";
        echo "<br>------------------------------------------<br>";
        $data = $this->bl($tree,'——|');
        echo "<pre>";
        file_put_contents("E:/PHPTutorial/WWW/read1/dir.txt",$data);
        print_r($data);
        echo "</pre>";
    }
    function recurDir($pathName) {
        $result = array();
        $temp = array();
        if( !is_dir($pathName) || !is_readable($pathName) ){
            return null;
        }
        $allFiles = scandir($pathName);
        //print_r($allFiles);
        foreach($allFiles as $fileName){
            if( in_array($fileName, array('.', '..')) ) continue;
            $fullName = $pathName . '/' . $fileName;
            if( is_dir($fullName) ){
                $result[$fileName] = $this->recurDir($fullName);
            }else{
                $temp[] = $fileName;
            }
        }
       // print_r($temp);
        if($temp){
            foreach( $temp as $f ){
                $result[] = $f;
            }
        }
       // print_r($result);die;
        return $result;
    }
//递归遍历生成目录
//www.jbxue.com
    function bl($arr, $l = '++++|'){
        static $l = '';
        static $str = '';
        foreach($arr as $key=>$val){
            if(is_array($arr[$key])){
                //echo $val . "<br>";
                //echo $l . $key . "<br>";
                $str .= $l . $key . "<br>";
                $l .= '-|';
                $this->bl($arr[$key], $l);
            }else{
                //echo $l . $val . "<br>";
                $str .= $l . $val . "<br>";
            }
        }
        $l = '';
        return $str;
    }*/


    public function index()
    {
        (new Publicnumbermenuset())->menu_create();
    }

    public function gethistory()
    {
        $obj = (new Publicnumbermenuset());
        $obj->get_access_token();
        $token = $obj->accesstoken;
        $url = 'https://api.weixin.qq.com/datacube/getarticletotal?access_token='.$token;
        $data = [
          'begin_date'=>'2018-09-04',
          'end_date'=>'2018-09-04'
        ];
        //var_dump(json_encode($data));
        $res = $this->https_request($url,json_encode($data));
        var_dump($res);
    }
    public function send_news()
    {
        $obj = (new Publicnumbermenuset());
        $obj->get_access_token();
        $token = $obj->accesstoken;
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='.$token;

        $data = [
            'filter'=>[
                'is_to_all'=>true,
            ],
            'mpnews'=>[
                'media_id'=>'bw0N2f42hKUsGQ2yDKO2pWSDTtAuV5KyObNXITBoVEU'
            ],
            'msgtype'=>'mpnews',
            'send_ignore_reprint'=>0
        ];
        $res = $this->https_request($url,json_encode($data));
        var_dump($res);

    }

    //
    public function del_material()
    {
        $obj = (new Publicnumbermenuset());
        $obj->get_access_token();
        $token = $obj->accesstoken;
        $url = 'https://api.weixin.qq.com/cgi-bin/material/del_material?access_token='.$token;
        $data = array(
            "media_id"=>2247483652,
        );
        print_r($data);
        $res = $this->https_request($url,json_encode($data));
        var_dump($res);
    }
    public function add_news()
    {
        //bw0N2f42hKUsGQ2yDKO2pWSDTtAuV5KyObNXITBoVEU
        $obj = (new Publicnumbermenuset());
        $obj->get_access_token();
        $token = $obj->accesstoken;
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token='.$token;
        $con = [
            [
                'title'=>'测试',
                'thumb_media_id'=>"bw0N2f42hKUsGQ2yDKO2pdrgV6uxvFYW62H5vh9n7ho",
                'author'=>'聂芳',
                'digest'=>'摘要摘要',
                'show_cover_pic'=>1,
                'content'=>'just a test news',
                'content_source_url'=>'http://aiyihui.yongweisoft.cn/aiyihui/www/userinformation.html'
            ]
          ];
        $data = array(
            "articles"=>$con
        );
        print_r($data);
        $res = $this->https_request($url,json_encode($data));
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

    //获取素材列表
    public function get_mediaList($type="image",$offset=0,$count=20){
        $obj = (new Publicnumbermenuset());
        $obj->get_access_token();
        $token = $obj->accesstoken;
        $data = array(
            'type'=>$type,
            'offset'=>$offset,
            'count'=>$count
        );
        $curl = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$token;
        $res = $this->https_request($curl,json_encode($data));
        var_dump($res);
    }



}