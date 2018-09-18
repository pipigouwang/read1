<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/20
 * Time: 10:55
 */

namespace app\admin\model;
use Redis;
/**
   用户在线情况统计类
 *
 *
 *
 * 调用实例：
   添加用户登录状态：Counter::addOnline(12312341);
   计算当前在线数量:Counter::getCurrentOnlinePeopleNum();
print_r($res);
   计算今日在线数量:Counter::getOnlineToday();
print_r($res1);
 *
 *
 *
 */
class Counter
{
    private function __construct()
    {
    }
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    //获取redis
    public static function redishandle()
    {
        // 获取缓存对象句柄
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        return $redis;
    }
    //统计当前在线人数(非精确)
    public static function getCurrentOnlinePeopleNum()
    {
        $redis = self::redishandle();
        $start = time()-3600;
        $end = time();
        return $redis->zRangeByScore('online',$start,$end);
    }
    //统计今日在线人数
    public static function getOnlineToday()
    {
        $redis = self::redishandle();
        $start = strtotime(date('Y-m-d'));
        $end = time();
        return  $redis->zRangeByScore('online',$start,$end);
    }
    //统计本月在线人数
    public static function getOnlineMonth()
    {
        $redis = self::redishandle();
        $start = strtotime(date('Y-m-1'));
        $end = time();
        return  $redis->zRangeByScore('online',$start,$end);
    }
    //添加用户上线
    public static function addOnline($mid)
    {
        $redis = self::redishandle();
        $redis->zAdd('online',$mid,time());
    }


}