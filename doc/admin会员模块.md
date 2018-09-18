后台会员模块
url:
域名/admin/member/index

DDL
~~~
id int not null auto_increment
		primary key,
	account varchar(255) null comment '账号',
	psw varchar(255) null comment '密码',
	type tinyint(3) unsigned default '1' null comment '1，病人，2，药店，3.药品销售，4，代理商',
	pat_id int(10) unsigned null comment '对应病人表的id',
	openid varchar(240) null comment 'openid',
	phone varchar(255) null,
	avator varchar(255) null comment '头像',
	level tinyint(5) default '1' null comment '等级',
	sex tinyint(2) default '1' null comment '1,男2女',
	create_time datetime null comment '创建时间',
	level_imgurl varchar(255) null comment '等级图标',
	address varchar(255) null,
	pha int null comment '对应药店表的id',
	sale_id int null comment '药品销售id',
	agent_id int null comment '代理商id',
	status tinyint(2) default '1' null comment '0，注销，1启用',
	uname varchar(255) null comment '负责人姓名',
	shop_name varchar(255) null comment '店铺名字',
	fatherid int null,
	longitude varchar(255) null,
	latitude varchar(255) null,
	licence_imgurl varchar(255) null comment '营业执照',
	label varchar(255) null comment '标签',
	isclinic tinyint(5) default 1 comment '1,是药店2，是诊所',
~~~

1.1 列表

~~~
{
	"tag":"member",
	"op":"getlist",
	"condition":{
		"type":3	
	}
}
返回：
{
    "message": "success",
    "err": 6000,
    "data": {
        "list": [
            {
                "pat_id": 1,
                "openid": "awerqw",
                "phone": "13908109010",
                "avator": "/erq.jpg",
                "level": 1,
                "sex": 1,
                "create_time": "2018-08-21 14:03:48",
                "level_imgurl": "qwerq",
                "address": "qwerqwer",
                "status": 1,
                "uname": null,
                "shop_name": null,
                "fatherid": null,
                "longitude": null,
                "latitude": null,
                "licence_imgurl": null,
                "label": null
            }
        ],
        "total": 1
    }
}
~~~

1.2 新增：
  新增分4类  病人，药店，代理，销售
~~~
病人：
{
	"tag":"member",
	"op":"add",
	"data":{
		"type":1, //1，病人，2，药店，3.药品销售，4，代理商
		"openid":"",//默认空
		"phone":"13214123121",//必填
		"avator":"/asdfa",//头像  默认一个图片地址
		"level":"12",//等级
		"sex":"1",//1,男2女
		"level_imgurl":"asfda",//等级图片 
		"address":"asdf",//地址
		"status":1,//状态 0，注销，1启用
		"label":1212,//标签，
		"illnessid":"1"病人病的标签id
	}
}

药店：
{
	"tag":"member",
	"op":"add",
	"data":{
		"type":2, //1，病人，2，药店，3.药品销售，4，代理商
		"openid":"",//默认空
		"phone":"13214123121",//必填
		"avator":"/asdfa",//头像  默认一个图片地址
		"level":"12",//等级
		"sex":"1",//1,男2女
		"level_imgurl":"asfda",//等级图片 
		"address":"asdf",//地址
		"status":1,//状态 0，注销，1启用
		"uname":"123",//负责人名字
		"shop_name":"asdf", //药店名
		"longitude":"12413.3",//经度
		"latitude":"123",//纬度
		"licence_imgurl":"asdf",//营业执照地址
		"label":1212,//标签
		"be_good_at":"跳跳糖",//擅长治的病
	}
}

销售
{
	"tag":"member",
	"op":"add",
	"data":{
		"type":3, //1，病人，2，药店，3.药品销售，4，代理商
		"openid":"",//默认空
		"phone":"13214123121",//必填
		"avator":"/asdfa",//头像  默认一个图片地址
		"level":"12",//等级
		"sex":"1",//1,男2女
		"level_imgurl":"asfda",//等级图片 
		"address":"asdf",//地址
		"status":1,//状态 0，注销，1启用
		"uname":"123",//负责人名字
		"label":1212,//标签，
		"fatherid":1,//代理商id
		"childid"://药店id
		"sale_products":"1,2,3,4",//售卖的药品
		"sale_region":"成华区"//售卖的区域 手输
	}
}

代理
{
	"tag":"member",
	"op":"add",
	"data":{
		"type":1, //1，病人，2，药店，3.药品销售，4，代理商
		"openid":"",//默认空
		"phone":"13214123121",//必填
		"avator":"/asdfa",//头像  默认一个图片地址
		"level":"12",//等级
		"sex":"1",//1,男2女
		"level_imgurl":"asfda",//等级图片 
		"address":"asdf",//地址
		"status":1,//状态 0，注销，1启用
		"uname":"123",//负责人名字
		"fatherid":1,//代理上级id
		"licence_imgurl":"asdf",//营业执照地址
		"label":1212,//标签
		"agent_products":'1,2,3',
		"agent_region":"成华区"
	}
}

~~~

1.3 编辑会员信息 
 ~~~
 {
 	"tag":"member",
 	"op":"update",
    "condition":{
        "id":1	
    },
 	"data":{
 		"type":1, //1，病人，2，药店，3.药品销售，4，代理商
 		"openid":"",//默认空
 		"phone":"13214123121",//必填
 		"avator":"/asdfa",//头像  默认一个图片地址
 		"level":"12",//等级
 		"sex":"1",//1,男2女
 		"level_imgurl":"asfda",//等级图片 
 		"address":"asdf",//地址
 		"status":1,//状态 0，注销，1启用
 		"label":1212,//标签
 	}
 }
 
 
 药店：
 {
 	"tag":"member",
 	"op":"update",
    "condition":{
        "id":1	
    },
 	"data":{
 		"type":2, //1，病人，2，药店，3.药品销售，4，代理商
 		"openid":"",//默认空
 		"phone":"13214123121",//必填
 		"avator":"/asdfa",//头像  默认一个图片地址
 		"level":"12",//等级
 		"sex":"1",//1,男2女
 		"level_imgurl":"asfda",//等级图片 
 		"address":"asdf",//地址
 		"status":1,//状态 0，注销，1启用
 		"uname":"123",//负责人名字
 		"shop_name":"asdf", //药店名
 		"longitude":"12413.3",//经度
 		"latitude":"123",//纬度
 		"licence_imgurl":"asdf",//营业执照地址
 		"label":1212,//标签
 		"be_good_at":"跳跳糖",//擅长治的病
 	}
 }
 
 销售
 {
 	"tag":"member",
 	"op":"update",
    "condition":{
        "id":1	
    },
 	"data":{
 		"type":3, //1，病人，2，药店，3.药品销售，4，代理商
 		"openid":"",//默认空
 		"phone":"13214123121",//必填
 		"avator":"/asdfa",//头像  默认一个图片地址
 		"level":"12",//等级
 		"sex":"1",//1,男2女
 		"level_imgurl":"asfda",//等级图片 
 		"address":"asdf",//地址
 		"status":1,//状态 0，注销，1启用
 		"uname":"123",//负责人名字
 		"label":1212,//标签，
 		"fatherid":1,//代理商id
 		"childid"://药店id
 		"sale_products":"1,2,3,4",//售卖的药品
 		"sale_region":"成华区"//售卖的区域 手输
 	}
 }
 
 代理
 {
 	"tag":"member",
 	"op":"update",
    "condition":{
        "id":1	
    },
 	"data":{
 		"type":1, //1，病人，2，药店，3.药品销售，4，代理商
 		"openid":"",//默认空
 		"phone":"13214123121",//必填
 		"avator":"/asdfa",//头像  默认一个图片地址
 		"level":"12",//等级
 		"sex":"1",//1,男2女
 		"level_imgurl":"asfda",//等级图片 
 		"address":"asdf",//地址
 		"status":1,//状态 0，注销，1启用
 		"uname":"123",//负责人名字
 		"fatherid":1,//代理上级id
 		"licence_imgurl":"asdf",//营业执照地址
 		"label":1212,//标签
 		"agent_products":'1,2,3',
 		"agent_region":"成华区"
 	}
}
 ~~~
 
1.4 修改会员状态 

~~~
{
	"tag":"member",
	"op":"state",
	"condition":{
		"id":1	
	},
	"data":{
		"status":0,
	}
}
~~~
1.5 会员详情  
~~~
{
	"tag":"member",
	"op":"getone",
	"condition":{
		"id":1	
	}
}

返回：
{
    "message": "success",
    "err": 6000,
    "data": {
        "openid": "",  
        "phone": "13214123112",
        "avator": "/asdfa",
        "level": 12,
        "sex": 1,
        "create_time": "2018-08-21 14:03:48",
        "level_imgurl": "asfda",
        "type": 2,
        "address": "asdf",
        "status": 0,
        "uname": "123",
        "shop_name": "asdf",
        "fatherid": 1,
        "longitude": "12413.3",
        "latitude": "123",
        "licence_imgurl": "asdf",
        "label": "1212",
        "lastOrder": {  //最后下单情况
            "id": 7,
            "member_id": 1,
            "type": 3,
            "status": 1,
            "total_fee": "90.00",
            "create_date": "2018-08-03",
            "to_id": 0,
            "order_id": "4428874656569",
            "info": [
                {
                    "num": 1,
                    "company": "刘氏药业",
                    "specifications": "颗",
                    "price": "120.00",
                    "imgurl": "https://abc.yongweisoft.cn./technicianHeadImg/simge/b4cebefeab694f37d4340f0759ec8588.jpg",
                    "name": "test1"
                },
                {
                    "num": 4,
                    "company": "刘氏药业",
                    "specifications": "颗",
                    "price": "120.00",
                    "imgurl": "https://abc.yongweisoft.cn./technicianHeadImg/simge/b4cebefeab694f37d4340f0759ec8588.jpg",
                    "name": "test1"
                },
                {
                    "num": 1,
                    "company": "刘氏药业",
                    "specifications": "颗",
                    "price": "120.00",
                    "imgurl": "https://abc.yongweisoft.cn./technicianHeadImg/simge/b4cebefeab694f37d4340f0759ec8588.jpg",
                    "name": "test1"
                },
                {
                    "num": 4,
                    "company": "刘氏药业",
                    "specifications": "颗",
                    "price": "120.00",
                    "imgurl": "https://abc.yongweisoft.cn./technicianHeadImg/simge/b4cebefeab694f37d4340f0759ec8588.jpg",
                    "name": "test1"
                }
            ]
        },
        "father": { //上级信息
            "phone": "13214123112",
            "uname": "123"
        }
    }
}
~~~

1.6 查看会员病历 （病人）
~~~
{
	"tag":"member",
	"op":"case_history",
	"condition":{
		"id":1	
	}
}

返回：
{
    "message": "success",
    "err": 6000,
    "data": {
        "list": [
            {
                "id": 29,
                "mid": 1,
                "shopid": 2,
                "shopname": "dian名",
                "gender": 1,
                "age": 23,
                "created_time": "2018-08-06 17:53:04",
                "remarks": "重要治标不治本",
                "goods": [
                    {
                        "id": 14,
                        "case_history_id": 29,
                        "gid": 1,
                        "product_name": "红花药",
                        "product_sum": 1
                    },
                    {
                        "id": 15,
                        "case_history_id": 29,
                        "gid": 1,
                        "product_name": "红花药",
                        "product_sum": 2
                    }
                ]
            }
        ],
        "total": 1
    }
}

~~~
1.7 添加病历
~~~
{
	"tag":"member",
	"op":"case_history_add",
	"condition":{
		"id":1	
	},
	"data":{
		"mid":1,
		"shopid":2,
		"shopname":"dian名",
		"gender":1,
		"age":23,
		"remarks":"重要治标不治本",
		"goods":[
			{
				"gid":1,
			"product_name":"红花药",
			"product_sum":1
			},{
				"gid":1,
			"product_name":"红花药",
			"product_sum":2
			}
		]
	}
}
~~~

1.8 会员积分历史 

~~~
//查询某会员的历史记录
{
	"tag":"member",
	"op":"point_history",
	"condition":{    
		"mid":1	
	}
}
//查询所有会员的历史记录
{
	"tag":"member",
	"op":"point_history"
}

//返回   
{
    "message": "success",
    "err": 6012,
    "data": {
        "list": {
            "2018-8": {
                "6": {  //2018-8-6 日的消费
                    "point": null,
                    "phone": "13214123112",
                    "avator": "/asdfa",
                    "level": 12,
                    "level_imgurl": "asfda",
                    "pointsum": 20,
                    "year": 2018,
                    "month": 8,
                    "day": 6,
                    "tip": "消费获得",
                    "type": 2
                },
                "7": {
                    "point": null,
                    "phone": "13214123112",
                    "avator": "/asdfa",
                    "level": 12,
                    "level_imgurl": "asfda",
                    "pointsum": -10,
                    "year": 2018,
                    "month": 8,
                    "day": 7,
                    "tip": "游戏获得积分",
                    "type": 1
                }
            }
        },
        "total": 2
    }
}
~~~

1.9 会员积分操作（新增积分减少积分等）

~~~
用户增加积分，消费积分  pointsum为正数  新增积分 负数减少积分
积分类型做成选项 每个选项对应有type和pointsum

{
	"tag":"member",
	"op":"point_operation",
	"condition":{
		"mid":1	
	},
	"data":{
		"type":2,//1，活动获取，2，消费获取，3，兑换扣除  
		"mid":1,
		"pointsum":-100,
		"tip":"积分赠送"
	}
}
~~~

1.9 会员角色修改（病人修改为其他类型）

~~~
{
	"tag":"member",
	"op":"role",
	"condition":{
		"id":1
	},
	"data":{
		"type":2
	}
	
}

返回
{
    "message": "修改会员类型成功",
    "err": 6000,
    "data": null
}
~~~

3.  新增  会员所属上级

url /admin/member/index

~~~
{
	"tag":"member",
	"op":"findfather",
	"condition":{
		"province":"四川省",
			"district":"温江区",
				"city":"成都市",
				"type": 3,////3.药店新增父级，4销售新增父级4，代理新增父级
	}
}

{
    "message": "success",
    "err": 6011,
    "data": {
        "id": 1,
        "uname": "123",
        "phone": "13214123112"
    }
}
~~~

4.通过会员手机号查询会员 负责人 电话和名字

url：/admin/member/index
 ~~~
 {
 	"tag":"member",
 	"op":"getuserbyphone",
 	"condition":{
 		"phone":"123123123"
 	}
 }
~~~
5.根据用户id获取用户代理的商品
url：/admin/member/index
 ~~~
 {
 	"tag":"member",
 	"op":"getmemberproduct",
 	"condition":{
 		"id":"75"
 		"sotck":"12",
 		"name":"药品"
 	}
 }
~~~
6.根据用户id获取用户下级
url:url：/admin/member/index
 ~~~
 {
 	"tag":"member",
 	"op":"getchild",
 	"condition":{
 		"id":"75"
 	}
 }
~~~
7.新增病人标签
url  /admin/member/index
 ~~~
 {
 	"tag":"member",
 	"op":"addsign",
 	"data":{
 		"sign":"情流感"
 	}
 }
~~~
7.1病人标签列表
url  /admin/member/index

有分页
 ~~~
 {
 	"tag":"member",
 	"op":"signlist",
 
 }
~~~
7.2 删除病人标签
url  /admin/member/index
 ~~~
 {
 	"tag":"member",
 	"op":"delsign",
 	"condition":{
 		"id":"1"
 	}
 }
~~~