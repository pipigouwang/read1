<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17
 * Time: 13:57
 */
namespace app\index\model;
use think\Model;

class Newsandmsg extends Model{

    public function readmsg($mid,$msgid)
    {
        $detail = db('system_msg')->where('id','=',$msgid['id'])->find();
        $detail['msg'] = strip_tags($detail['msg']);
        db('member_msg_readed')
            ->where([
                'msgid'=>$msgid['id'],
                'mid'=>$mid
            ])->update(['status'=>2]);

        return ['success',1101,$detail];
    }

    public function readnews($mid,$newsid)
    {
        $detail = db('news')->where('id','=',$newsid['id'])->find();
        $detail['text'] = strip_tags($detail['text']);
        if(db('member_news_readed')->where([
            'newsid'=>$newsid['id'],
            'mid'=>$mid
        ])->find()){
            return ['success',1102,$detail];
        }else{
            db('member_news_readed')
                ->insert([
                    'newsid'=>$newsid['id'],
                    'mid'=>$mid
                ]);

           return ['success',1102,$detail];
        }
    }
}