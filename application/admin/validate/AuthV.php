<?php
namespace app\admin\validate;
class AuthV{

    public $rule =[
            'auth_group'=>[
              'title'=>'require','status'=>'requireWith:status|in:0,1,2',
                'rules'=>'require'
            ],
            'auth_rule'=>[
                'title'=>'require','status'=>'requireWith:status|in:0,1,2',
                'name'=>'require'
            ],
            'user_add_auth'=>[
                'uid'=>'require',
                'group_id'=>'require'
            ],
            'user'=>[
                'account'=>'require|max:240|alphaNum',
                'password'=>'require|max:48|alphaNum',
                'name'=>'require|max:48',
                'state'=>'requireWith:state|in:0,1,2'
            ]
        ];
    public $msg = [
        'auth_group'=>[
            'title.require'=>'分组名不能为空','status.in'=>'status 为1正常，为0禁用',
            'rules.require'=>'该分组的规则id 格式:1,2,3'
        ],
        'auth_rule'=>[
            'title.require'=>'权限名称不能为空','status.in'=>'status 为1正常，为0禁用',
            'name.require'=>'权限 格式为：model-controller-action',
        ],
        'user_add_auth'=>[
            'uid.require'=>'管理员id必须',
            'group_id.require'=>'分组id必须'
        ],
        'user'=>[
            'account.require'=>'账号必须',
            'account.alphaNum'=>'账号必须为字符和数字',
            'password.require'=>'密码必须',
            'password.max'=>'密码不超过24个字符',
            'name.require'=>'名字必须',
            'name.max'=>'名字必须在12字之内',
            'state.in'=>'状态值不在范围内',
            'account.max'=>'账号最多24个字符'
        ]
    ];

}