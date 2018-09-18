<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/13
 * Time: 10:28
 */

namespace app\admin\model;

class Upload
{
    protected $allow = [
        "mavator","product","shop","other","invoice","material"//会员头像上传路径
    ];
    public function index($dic){
        if(!in_array($dic,$this->allow))
        {
            return ["非法上传",-10000,''];
        }
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->validate(['size'=>1567800,'ext'=>'jpg,png,pdf'])
            ->move( './uploads/'.$dic);
        if($info){
            // 成功上传后 获取上传信息
            $path = $info->getSaveName();

            return ['成功',10000,'/uploads/'.$dic.'/'.$path];
            // 输出 jpg
           // echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
           // echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
           // echo $info->getFilename();
        }else{
            // 上传失败获取错误信息
            return [$file->getError(),-10000,''];
        }
    }
}