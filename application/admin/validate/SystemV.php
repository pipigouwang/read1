<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/31
 * Time: 18:19
 */
namespace app\admin\validate;
class SystemV{

    public  $rule = [
               'sms_config'=>[
                    'username'=>'require','password'=>'require','sign'=>'require','time'=>'require'
               ],
               'wxpay_config'=>[
                      'mchid'=>'require','apikey'=>'require','certpem'=>'require','keypem'=>'require'
                ],
                'point_rule_config'=>[

                ],
                'share_config'=>[
                    'title'=>'require|max:240','dec'=>'require|max:240','point'=>'require|number',
                    'click_point'=>'require|number','content'=>'require'
                ],
                'customer_config'=>[
                    'phone'=>'require',
                ],
                'aboutus_config'=>[
                    'text'=>'require'
                ],
                'banner_config'=>[
                    'url'=>'require','title'=>'require','img'=>'require','state'=>'require|in:0,1,2'
                ],
                'integral_mall'=>[
                    'img'=>'require','state'=>'requireWith:state'
                ],
                'ad_config'=>[
                    'url'=>'require','title'=>'require','img'=>'require','state'=>'require|in:0,1,2'
                ],
                'sign_point_config'=>[
                    'point'=>'require'
                ],

        ];


    public $msg = [
        'sms_config'=>[
            'username.require'=>'用户名必填','password.require'=>'密码必填','sign.require'=>'签名必填','time.require'=>'有效时间必填'
        ],
        'wxpay_config'=>[
            'mchid.require'=>'微信支付商户号必填','apikey.require'=>'商户支付密钥必填','certpem.require'=>'cert证书路径必填','keypem.require'=>'keypem证书路径必填'
        ],
        'point_rule_config'=>[

        ],
        'share_config'=>[
            'title.require'=>'标题必须','title.max'=>'标题不能超过60字','dec.require'=>'描述必须','dec.max'=>'描述不超过60字',
            'point.require'=>'积分必须设置','point.number'=>'积分必须为整数',
            'click_point.require'=>'点击获得积分必须设置','click_point.number'=>'积分必须为整数'
            ,'content.require'=>'内容不能为空'
        ],
        'customer_config'=>[
                    'phone.require'=>'联系电话必须配置',
        ],
        'aboutus_config'=>[
            'text.require'=>'内容必填'
        ],
        'banner_config'=>[
            'url.require'=>'跳转地址必须','title.require'=>'标题必须',
            'img.require'=>'图片必须','state.require'=>'状态必须','state.in'=>'state 0，删除，1，禁用2，启用'
        ],
        'integral_mall'=>[
            'img.require'=>'图片必须'
        ],
        'ad_config'=>[
            'url.require'=>'跳转地址必须','title.require'=>'标题必须',
            'img.require'=>'图片必须','state.require'=>'状态必须','state.in'=>'state 0，删除，1，禁用2，启用'
        ],
        'sign_point_config'=>[
            'point.require'=>'积分必填'
        ],
    ];







}