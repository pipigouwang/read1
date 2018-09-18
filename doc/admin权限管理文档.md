请求地址：
http://www.huahong.com/admin/users/rulegroup
* tag:表名
* op:操作名
0.8 修改管理员（修改状态也用这个）
~~~
 {
 	"tag":"user",
 	"op":"user_edit"，
 	"data":{
 	    "account":"",
 	    "name":"铁拐婷",
 	    "password":"123"，
 	    "state":2/////0删除1警用2启用
 	}
 }
 返回：
 {
     "message": "success",
     "err": 2000,
     "data": 
 }
 ~~~
0.9 新增管理员
~~~
 {
 	"tag":"user",
 	"op":"user_create"，
 	"data":{
 	    "account":"",
 	    "name":"铁拐婷",
 	    "password":"123"
 	}
 }
 返回：
 {
     "message": "success",
     "err": 2000,
     "data": 
 }
 ~~~
1.0 获取管理员列表
~~~
 {
 	"tag":"user",
 	"op":"user_list"
 }
 返回：
 {
     "message": "success",
     "err": 2000,
     "data": null
 }
 ~~~
1.1新增权限:（表 auth_rule）
 
 请求实例
 ~~~
 {
 	"tag":"auth_rule",
 	"op":"add",
 	"data":{
 		"name":"admin-user-test",
 		"title":"测试权限",
 		"status":"1"
 	}
 }
 返回：
 {
     "message": "success",
     "err": 2000,
     "data": null
 }
 ~~~
 1.2 修改权限名 （表 auth_rule）
 
 ~~~
 {
 	"tag":"auth_rule",
 	"op":"update",
 	"data":{
 		"id":5,
 		"name":"admin-user-test",
 		"title":"测试权限",
 		"status":"1"
 	}
 }
 ~~~
 1.3 获取权限列表
  
 分页参数get方式提交
 page:页码 默认第一页
 limit:获取的总条数 默认9条
 ~~~
 {
 	"tag":"auth_rule",
 	"op":"get",
 }
 返回：
 {
     "message": "success",
     "err": 2001,
     "data": {
         "list": [
             {
                 "id": 1,
                 "name": "admin-users-index",
                 "title": "管理员权限",
                 "type": 1,
                 "status": 1,
                 "condition": ""
             },
             {
                 "id": 2,
                 "name": "admin-systemsets-system_set",
                 "title": "系统管理权限",
                 "type": 1,
                 "status": 1,
                 "condition": ""
             }
    
         ],
         "total": 2
     }
 }
 ~~~
 1.4 启用禁用删除 权限（表 auth_rule）
 
 ~~~
 {
 	"tag":"auth_rule",
 	"op":"state",
 	"data":{
 		"id":5,
 		"status":"0"
 	}
 }
 返回
 {
     "message": "success",
     "err": 2000,
     "data": null
 }
 ~~~
 
 2.1 权限组添加 （表：auth_group）
 
 ~~~
 {
 	"tag":"auth_group",
 	"op":"add",
 	"data":{
 		"title":"会计权限组",
 		"status":"0",
 		"rules":"1,2,3,4"
 	}
 }
 ~~~
 2.2 权限组修改 
 
 ~~~
 {
 	"tag":"auth_group",
 	"op":"update",
 	"data":{
 		"id":2,
 		"title":"会计权限组",
 		"status":"1",
 		"rules":"1,2,3,4,6"
 	}
 }
 ~~~
 2.3 权限组获取
 
 分页同上
 ~~~
 {
 	"tag":"auth_group",
 	"op":"get",
 }
 返回：
 {
     "message": "success",
     "err": 2001,
     "data": {
         "list": [
             {
                 "id": 1,
                 "title": "超级管理员",
                 "status": 1,
                 "rules": "1,2,3"
             },
             {
                 "id": 2,
                 "title": "会计权限组",
                 "status": 1,
                 "rules": "1,2,3,4,6"
             }
         ],
         "total": 2
     }
 }
 ~~~
 2.4 权限组状态修改 
 
 ~~~
 {
 	"tag":"auth_group",
 	"op":"state",
 	"data":{
 		"id":2,
 		"status":"0"
 	}
 }
 ~~~
 
 3.1 会员权限添加
 
 ~~~
 {
 	"tag":"auth_group_access",
 	"op":"user_add_auth",
 	"data":{
 		"uid":2,
 		"group_ids":",1,2,3,"
 	}
 }
 ~~~
 3.2 会员权限修改
 
 ~~~
 {
 	"tag":"auth_group_access",
 	"op":"user_update_auth",
 	"data":{
 		"uid":2,
 		"group_ids":",1,2,3,"
 	}
 }
 ~~~