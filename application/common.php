<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function randomkeys($length){
    $output='';
    for ($a = 0; $a<$length; $a++) {
        $output .= chr(mt_rand(33, 126));    //生成php随机数
    }
    return $output;
}

/*
    参数说明：
    $lng  经度
    $lat   纬度
    $distance  周边半径  默认是500米（0.5Km）
*/

function returnSquarePoint($lng, $lat,$distance = 0.5)
{
    $dlng =  2 * asin(sin($distance / (2 * 6371)) / cos(deg2rad($lat)));
    $dlng = rad2deg($dlng);
    $dlat = $distance/6371;
    $dlat = rad2deg($dlat);
    return array(
        'left-top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
        'right-top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
        'left-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
        'right-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
    );
}
/**
 * 计算两点地理坐标之间的距离
 * @param  Decimal $longitude1 起点经度
 * @param  Decimal $latitude1  起点纬度
 * @param  Decimal $longitude2 终点经度
 * @param  Decimal $latitude2  终点纬度
 * @param  Int     $unit       单位 1:米 2:公里
 * @param  Int     $decimal    精度 保留小数位数
 * @return Decimal
 */
function getDistance($longitude1, $latitude1, $longitude2, $latitude2, $unit=1, $decimal=0){

    $EARTH_RADIUS = 6370.996; // 地球半径系数
    $PI = 3.1415926;

    $radLat1 = $latitude1 * $PI / 180.0;
    $radLat2 = $latitude2 * $PI / 180.0;

    $radLng1 = $longitude1 * $PI / 180.0;
    $radLng2 = $longitude2 * $PI /180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
    $distance = $distance * $EARTH_RADIUS * 1000;

    if($unit==2){
        $distance = $distance / 1000;
    }

    return round($distance, $decimal);

}
function getclientip() {
    //strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
    return $res;
    //dump(phpinfo());//所有PHP配置信息
}