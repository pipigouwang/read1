<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2
 * Time: 11:37
 */
namespace app\admin\validate;
class ProductV {
    public $rule = [
        'name'=>'require|max:80',
        'company'=>'requireWith:company|max:40',
        'specifications'=>'require|max:40',
        'price'=>'require|number',
        'trueprice'=>'require|number'
        ,'sn'=>'require',
        'status'=>'require|in:0,1,2',
        'sta_goods'=>'require|in:1,2',
        'period'=>'require|number',
        'enddate'=>'require',
        'tag'=>'require|in:0,1',
        'detail'=>'require'
    ];

    public $msg = [
        'name.require'=>'药品名必须',
        'name.max'=>'药品名不超过多10字',
        'company.max'=>'公司必须',
        'specifications.require'=>'规格名必须',
        'specifications.max'=>'规格名不超过10字',
        'price.require'=>'价格必须',
        'price.number'=>'价格为数字',
        'trueprice.number'=>'价格为数字'
        ,'sn.require'=>'批次号必须',
        'status.require'=>'状态必须',
        'status.in'=>'状态为0,1,2',
        'sta_goods.require'=>'商品自身状态必须',
        'sta_goods.in'=>'商品自身状态在，1普通2折扣',
        'period.require'=>'保质期(天)必须',
        'period.number'=>'保质期(天)为整数',
        'enddate.require'=>'到期日期，2017-1-1 23:12:12',
        'tag.require'=>'是否推荐参数必须',
        'tag.in'=>'参数错误，必须在0，1之间',
        'detail.require'=>'商品描述必须'
    ];
}