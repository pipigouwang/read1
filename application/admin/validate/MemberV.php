<?php
namespace app\admin\validate;
class MemberV{

    public $rule = [
      'type'=>'require|in:1,2,3,4','openid'=>'requireWith:openid','phone'=>'require|mobile'
        ,'avator'=>'requireWith:avator','level'=>'require|number','sex'=>'require|in:2,1'
        ,'level_imgurl'=>'requireWith:level_imgurl','address'=>'requireWith:address'
        ,'status'=>'require|in:0,1','name'=>'requireWith:name|max:40'
        ,'uname'=>'requireWith:uname|max:40','shop_name'=>'requireWith:shop_name|max:80',
        'fatherid'=>'requireWith:fatherid|number','longitude'=>'requireWith:longitude|float'
        ,'latitude'=>'requireWith:latitude|float'
        ,'licence_imgurl'=>'requireWith:licence_imgurl','label'=>'requireWith:label',
        'isclinic'=>'requireWith:isclinic|in:1,2'
    ];
    public $msg = [
        'isclinic.in'=>'药店或诊所类型不正确',
        'type.require'=>'会员类型必须',
        'type.in'=>'不存在的会员类型',
        'openid.require'=>'openid必须',
        'phone.require'=>'电话号码必须',
        'phone.mobile'=>'电话号码格式不正确'
        ,'level.require'=>'会员等级必须',
        'sex.require'=>'会员性别必须'
        ,'level_imgurl.require'=>'等级图片必须','address.require'=>'地址必须'
        ,'status.require'=>'状态必须'
        ,'uname.require'=>'负责人姓名必须'
        ,'uname.max'=>'负责人姓名不超出10个字',
        'shop_name.require'=>'店铺名必须',
        'shop_name.max'=>'店铺名不超出20个字',
        'fatherid.number'=>'父id为数字',
        'longitude.require'=>'经度必须'
        ,'latitude.require'=>'纬度必须'
        ,'licence_imgurl.requireWith'=>'营业执照图片必须',
        'label.require'=>'标签必须',
        'name.max'=>'姓名不超过10个字',
    ];

}
