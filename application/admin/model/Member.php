<?php
namespace app\admin\model;
use think\Model;
use think\Db;
use think\Validate;
use app\admin\validate\MemberV;
use app\admin\model\System;
class Member extends Model{
    public function member_list($page,$limit,$where)
    {
        try {
            $query = db('member');
            //1，病人，2，药店，3.药品销售，4，代理商
            if (isset($where['type']) && in_array($where['type'], [1, 2, 3, 4])) {
                $query->where('type', '=', $where['type']);
            }
            if(isset($where['phone'])&&!empty($where['phone']))
            {
                $query->where('phone','=',$where['phone']);
            }
            $query1 = clone($query);
            $member['list'] = $query
                ->field('id,openid,phone,avator,name,
                level,sex,create_time,level_imgurl,type,be_good_at,
                address,status,uname,shop_name,fatherid,shop_image,
                longitude,latitude,licence_imgurl,label,point,isclinic,
                region_city,region_province,region_district'
                )
                ->page($page)
                ->group('id')
                ->order('id','desc')
                ->limit($limit)->select();
            foreach ($member['list'] as $key=>$val)
            {
               $member['list'][$key]['agent_region'][0] = $val['region_province'];
               $member['list'][$key]['agent_region'][1] = $val['region_city'];
               $member['list'][$key]['agent_region'][2] = $val['region_district'];
               //上级电话
               if($val['fatherid']>0)//正常代理
               {
                   $father = db('member')
                       ->field('phone,uname')
                       ->where(['id'=>$val['fatherid']])
                       ->find();
                   $member['list'][$key]['fatherphone'] = $father['phone'];
                   $member['list'][$key]['fathername'] = $father['uname'];
               }elseif($val['fatherid'] == -1){//省代理
                   $father = db('member')
                       ->field('phone,uname')
                       ->where(['fatherid'=>-2])
                       ->find();
                   $member['list'][$key]['fatherphone'] = $father['phone'];
                   $member['list'][$key]['fathername'] = $father['uname'];
               }else{//总代理或者病人
                   $member['list'][$key]['fatherphone'] = "";
                   $member['list'][$key]['fathername'] = "";
               }
               $data2 = Db::name('member_product')
                    ->field('pid as id')
                    ->where('mid','=',$val['id'])
                    ->select();
                if($data2){
                    $member['list'][$key]['agent_products'] = array_column($data2,'id');
                }else{
                    $member['list'][$key]['agent_products'] = [];
                }
            }
            $member['total'] = $query1->count();
            if ($member['total'] == 0) {
                return ['没有获取到符合条件的数据', -6001, ['list'=>[],'total'=>0]];
            }
            return ['成功', 6000, $member];
        }catch (\Exception $e)
        {
            return [$e->getMessage(), -6000, null];
        }
    }
    public function member_add($data)
    {
        $memberV = new MemberV();
        $validate = new Validate($memberV->rule, $memberV->msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),-6003,null];
        }
        if(!isset($data['phone'])||$this->checkUniqPhone($data['phone'])){
            return ['电话号码已存在',-6008,null];
        }
        foreach ($data as $k=>$v)
        {
            if(is_array($v))
            {
                $data[$k] = json_encode($v);
            }
        }
        if( $data['fatherid'] != -2)//不是总代理
        {
            //查询father对应的账号类型
            $fathertype = db('member')->where(['id'=>$data['fatherid']])
                ->find()['type'];
            //药店的fatherid只能是销售
            if($data['type']==2 && $fathertype != 3){
                return ['上级必须是销售',-6001,null];
            }
            //销售的fatherid只能是代理
            if($data['type']==3 && $fathertype != 4){
                return ['上级必须是代理',-6001,null];
            }
            //代理的fatherid只能是代理或者-1，-2
            if($data['type']==4 && $fathertype != 4){
                return ['上级必须是代理',-6001,null];
            }
        }
        $data['create_time'] = date('Y-m-d H:i:s');
        try{
            Db::startTrans();
            $ins = Db::name('member')
                ->strict(false)
                ->insertGetId($data);
            if(!$ins)
            {
                Db::rollback();
                return ['添加失败',-6001,null];
            }

            if(isset($data['agent_products']) && $data['agent_products'])
            {
                $data2 = json_decode($data['agent_products'],true);
                $mp2 = [];
                if( $data['fatherid'] != -2)//不是总代理数量默认为0
                {
                    foreach ($data2 as $key=>$val)
                    {
                        $mp2[] = [
                            'mid'=>$ins,
                            'pid'=>$val
                        ];
                        $mp3[] = [
                            'mid'=>$ins,
                            'num'=>0,
                            'pid'=>$val
                        ];
                    }
                }else{//总代理需要添加数量
                    foreach ($data2 as $key=>$val)
                    {
                        $mp2[] = [
                            'mid'=>$ins,
                            'pid'=>$val['id']
                        ];
                        $mp3[] = [
                            'mid'=>$ins,
                            'num'=>$val['num2'],
                            'pid'=>$val['id']
                        ];
                    }
                }

                $ins3 = Db::name('stock')->insertAll($mp3);
                $ins2 = Db::name('member_product')->insertAll($mp2);
                if(!$ins2||!$ins3)
                {
                    Db::rollback();
                    return ['添加失败',-6001,null];
                }
            }
            System::systemlog(session('uid'),"添加了一个会员,会员电话为：{$data['phone']}");
            Db::commit();
            return ['添加成功',6001,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6001,null];
        }
    }
    public function member_edit($where,$data)
    {
        $memberV = new MemberV();
        $validate = new Validate($memberV->rule, $memberV->msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),-6003,null];
        }
        if(!isset($data['phone'])){
            $count = Db::name('member')
                ->where('phone','=',$data['phone'])
                ->count();
            if($count>1)
            {
                return ['电话号码已存在',-6008,null];
            }
        }
        if(!isset($where['id'])||!Member::get($where['id']))
        {
            return ['会员不存在',-6009,null];
        }
        try{
            if(isset($data['agent_products']) && $data['agent_products'])
            {
                $data2 = $data['agent_products'];
                $mp2 = [];
                foreach ($data2 as $key=>$val)
                {
                    $mp2[] = [
                        'mid'=>$where['id'],
                        'pid'=>$data['fatherid'] == -2?$val['id']:$val
                    ];
                }
                Db::startTrans();
                $del2 = Db::name('member_product')
                    ->where(['mid'=>$where['id']])->delete();
                $ins2 = Db::name('member_product')->insertAll($mp2);
                //如果是总代 修改库存
                if($data['fatherid'] == -2){
                    $del = db('stock')->where(['mid'=>$where['id']])
                        ->delete();
                    if(!$del)
                    {
                        Db::rollback();
                        return ['修改库存失败',-5001,null];
                    }
                    foreach ($data2 as $k=>$v)
                    {
                        $add = db('stock')->insert([
                            'num'=>$v['num2'],
                            'mid'=>$where['id'],
                            'pid'=>$v['id']
                        ]);
                        if(!$add)
                        {
                            Db::rollback();
                            return ['修改库存失败',-5001,null];
                        }
                    }
                }
                if(!$ins2||!$del2)
                {
                    Db::rollback();
                    return ['添加失败',-6001,null];
                }
            }
            $ins = Db::name('member')
                ->where('id','=',$where['id'])
                ->strict(false)
                ->update($data);
            if($ins === false)
            {
                Db::rollback();
                return ['修改失败',-6010,null];
            }
            Db::commit();
            System::systemlog(session('uid'),"修改了一个会员信息,会员电话为：{$data['phone']}");
            return ['修改成功',6001,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6011,null];
        }
    }
    public function member_state($where,$data)
    {
        if(!isset($where['id'])||!Member::get($where['id']))
        {
            return ['会员不存在',-6009,null];
        }
        if(!isset($data['status'])||!is_numeric($data['status'])
            ||!in_array($data['status'],[0,1]))
        {
            return ['状态值错误',-6004,null];
        }
        try{
            $ins = Db::name('member')
                ->where('id','=',$where['id'])
                ->update(['status'=>$data['status']]);
            if($ins)
            {
                return ['修改会员状态成功',6000,null];
            }
            $data['status'] == 0?$op = '禁用':$op = '启用';
            System::systemlog(session('uid'),
                "{$op}了一个会员,会员电话为：{$data['phone']}");
            return ['修改会员状态失败',-6004,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6005,null];
        }
    }
    /**修改会员角色
     * @param $where
     * @param $data
     * @return array
     *
     */
    public function member_role($where,$data)
    {
        if(!isset($where['id'])||!Member::get($where['id']))
        {
            return ['会员不存在',-6009,null];
        }
        if(!isset($data['type'])||!is_numeric($data['type'])
            ||!in_array($data['type'],[2,3,4]))
        {
            return ['类型值错误',-6004,null];
        }
        try{
            $ins = Db::name('member')
                ->where('id','=',$where['id'])
                ->update(['type'=>$data['type']]);
            if($ins)
            {
                return ['修改会员类型成功',6000,null];
            }
            switch ($data['type'])
            {
                case 2:
                    $role = '药店';
                    break;
                case 3:
                    $role = '销售';
                    break;
                case 4:
                    $role = '代理';
                    break;
                default:
                    return ['类型值错误',-6004,null];
            }
            System::systemlog(session('uid'),
                "修改了一个会员角色为{$role},会员电话为：{$data['phone']}");
            return ['修改会员状态失败',-6004,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6005,null];
        }
    }
    //会员信息，上下级信息，最后一次下单记录
    public function member_one($where)
    {
        if(!isset($where['id'])||!Member::get($where['id']))
        {
            return ['会员不存在',-6009,null];
        }
        try{
            $member = Db::name('member')
                ->field('openid,phone,avator,point,
                level,sex,create_time,level_imgurl,type,id,be_good_at,
                address,status,uname,shop_name,fatherid,shop_image,
                longitude,latitude,licence_imgurl,label,region_province,
                region_city,region_district')
                ->where('id','=',$where['id'])
                ->find();
            $member['agent_products'] = Db::name('member_product')
                ->join('product','product.id = member_product.pid')
                ->field('member_product.id,name')
                ->where('member_product.id','=',$member['id'])
                ->select();
            //最后采购记录
            $lastOrder = Db::name('order')->where('member_id','=',$where['id'])
                ->order('id','desc')->find();
            $lastOrder['info'] = Db::name('order_goods')
                ->field('num,company,specifications,price,imgurl,name')
                ->join('product','product.id= order_goods.goods_id')
                ->select();
            $member['lastOrder'] = $lastOrder;
            //上级
            $member['father'] = Db::name('member')
                ->field('phone,uname')
                ->where('id','=',$member['fatherid'])
                ->find();
            //下级
            $member['child'] = Db::name('member')
                ->field('phone,uname')
                ->where('fatherid','=',$member['id'])
                ->select();
            if($member)
            {
                return ['成功',6000,$member];
            }else{
                return ['未找到该会员信息',-6011,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6010,null];
        }

    }
    public function checkUniqPhone($phone)
    {
        if($phone){
            $exist = Db::name('member')
                ->where('phone','=',$phone)->find();
            if($exist)
            {
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    /**病人病历列表
     * @param $page
     * @param $limit
     * @param $where
     * @return array
     */
    public function member_case_history($page,$limit,$where)
    {
        if(!isset($where['id'])||!Member::get($where['id']))
        {
            return ['会员不存在',-6009,null];
        }
        try{
            $query = Db::name('case_history')
                ->field('case_history.id,mid,
                member.uname,case_history.gender,
                case_history.age,case_history.created_time
                ,case_history.remarks,shopname')
                ->join('member','member.id = case_history.mid')
                ->where('mid','=',$where['id']);
            $query1 = clone($query);
            $res['list'] = $query->page($page)->limit($limit)
                ->order('case_history.id','desc')->select();
            $res['total'] = $query1->count();
            if($res['total'] >0)
            {
                $ids = array_column($res['list'],'id');
                $infos = Db::name('case_history_detail')
                    ->field('case_history_detail.gid,case_history_id,product_name,product_sum,price,trueprice,product.imgurl')
                    ->join('product','product.id = case_history_detail.gid')
                    ->where('case_history_id','in',$ids)
                    ->select();
                foreach ($res['list'] as $key=>$val)
                {
                    foreach ($infos as $key1=>$val1)
                    {
                         if($val['id'] == $val1['case_history_id'])
                         {
                             $res['list'][$key]['goods'][] = $val1;
                         }
                    }
                }
                return ['成功',6000,$res];
            }else{
                return ['无病历',-6101,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6009,null];
        }
    }
    /**病历添加
     * @param $data
     * @return array
     */
    public function member_case_history_add($data)
    {
        $rule = [
          'mid'=>'require|number','shopid'=>'require|number'
            ,'gender'=>'require|in:1,2',
            'age'=>'require|max:124'
        ];
        $msg = [
            'mid.require'=>'病人id必须',
            'mid.number'=>'病人id为数字',
            'shopid.require'=>'诊所id必须',
            'shopid.number'=>'诊所id为数字',
/*            'shopname.require'=>'诊所名字必须',
            'shopname.max'=>'诊所名字最长不超过20字',*/
            'gender.require'=>'性别必须',
            'gender.in'=>'错误的性别',
            'age.require'=>'年龄必须',
             'age.max'=>'年龄不超过124岁'
        ];
        if(!isset($data['goods'])||empty($data['goods']))
        {
            return ['未创建药品',-5004,null];
        }
        $goods = $data['goods'];
        unset($data['goods']);
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),-5004,null];
        }
        //验证数据
        $data['created_time']=date('Y-m-d H:i:s');
        $data['shopname'] = db('member')
            ->field('shop_name')
            ->where(['id'=>$data['shopid']])
            ->find()['shop_name'];
        try{
            $gids = array_column($goods,'gid');
            $trueprice = db('product')
                ->field('id,trueprice')
                ->where('id','in',$gids)->select();
            $trueprice = array_column($trueprice,null,'id');
            Db::startTrans();
            $insId = Db::name('case_history')
                ->strict(false)
                ->insertGetId($data);
            foreach ($goods as $key=>$val)
            {
                $goods1[] = [
                    'case_history_id'=>$insId,
                    'gid'=>$val['gid'],
                    'product_name'=>$val['product_name'],
                    'product_sum'=>$val['product_sum']
                ];
                $goodsorder['goods'][] = [
                    'id'=>$val['gid'],
                    'num'=>$val['product_sum'],
                    'price'=>$trueprice[$val['gid']]['trueprice']
                ];
            }
            //新增订单
            $goodsorder['member_id'] = $data['mid'];
            $goodsorder['to_id'] = $data['shopid'];
            list($msg,$err,$data) = (new Order())->order_add($goodsorder);
            $ins1 = Db::name('case_history_detail')
                ->insertAll($goods1);
            if($insId&&$ins1&&$err)
            {
                Db::commit();
                return ['成功',6000,null];
            }else{
                Db::rollback();
                return ['添加病历失败',-6000,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6000,null];
        }
    }
    //前端会员积分历史
    //$monthnum 默认为true 即只显示最近三个月的 false显示全部
    public function member_point_history($page,$limit,$where,$monthnum=true)
    {
        //按月份
        try{
            $query = Db::name('member_point_history')
                ->field('point,phone,avator,level,level_imgurl
                ,pointsum,year(date) as year,month(date) as month,
                day(date) as day,tip,member_point_history.type,member.point')
                ->join('member','member.id = member_point_history.mid')
                ->where(['member.id'=>$where['id']]);
                if($monthnum)
                {
                    $query->whereTime('date',[date('Y-m-d',strtotime("-3 month")),date('Y-m-d')]);
                }
            $query1 = clone($query);
            $res['list'] = $query->page($page)->limit($limit)
                ->order('date','desc')->select();
            if($res['list'])
            {
                $res1['total'] = $query1->count();
                foreach ($res['list'] as $key=>$val)
                {
                    $res1['point']  = $val['point'];
                    $res3['list'][$val['year'].'-'.$val['month']]['time'] = $val['year'].'-'.$val['month'];
                    $res3['list'][$val['year'].'-'.$val['month']]['content'][] = $val;
                }
                $content = array_values($res3['list']);
                $res1['list'] = $content;
                return ['成功',6012,$res1];
            }else{
                return ['暂无数据',-6013,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6014,null];
        }
    }
    //后端会员积分历史
    //$monthnum 默认为true 即只显示最近三个月的 false显示全部
    public function point_history($page,$limit,$where,$monthnum=true)
    {
        //按月份
        try{
            $query = Db::name('member_point_history')
                ->field('point,phone,avator,level,level_imgurl
                ,pointsum,year(date) as year,month(date) as month,
                day(date) as day,tip,member_point_history.type,member.point')
                ->join('member','member.id = member_point_history.mid')
                ->where(['member.id'=>$where['id']]);
            if($monthnum)
            {
                $query->whereTime('date',[date('Y-m-d',strtotime("-3 month")),date('Y-m-d')]);
            }
            $query1 = clone($query);
            $res['list'] = $query->page($page)->limit($limit)
                ->order('date','desc')->select();
            if($res['list'])
            {
                $res['total'] = $query1->count();
                return ['成功',6012,$res];
            }else{
                return ['暂无数据',-6013,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6014,null];
        }
    }

    //用户积分操作,增加，减少
    public function member_point_operation($where,$data)
    {
        if(!isset($where['mid'])||!Member::get($where['mid']))
        {
            return ['会员不存在',-6009,null];
        }
        //数据验证
        $rule = [
          'pointsum'=>'require|number',
            'mid'=>'require|number',
            'type'=>'require|number',
            'tip'=>'requireWith:tip|max:240'
        ];
        $msg = [
          'pointsum.require'=>'积分数量必须',
            'pointsum.number'=>'积分必须为数字',
            'mid.require'=>'会员id必须',
            'mid.number'=>'会员id为数字',
            'type.require'=>'积分操作类型必须',
            'type.number'=>'积分操作类型为数字',
            'tip.max'=>'操作备注不能超过60字'
        ];
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($data);
        if(!$result)
        {
            return [$validate->getError(),-6004,null];
        }

        try{
            $data['date'] = date('Y-m-d');
            Db::startTrans();
            $oriPoint = Db::name('member')
                ->field('point')
                ->where('id','=',$where['mid'])
                ->find()['point'];
            if(($oriPoint + $data['pointsum'])<0)
            {
                Db::rollback();
                return ['用户积分不足',-6010,null];
            }
            $point = $oriPoint + $data['pointsum'];
            $ins = Db::name('member_point_history')
                ->insert($data);
            $ins1 = Db::name('member')->where('id','=',$where['mid'])
                ->update(['point'=>$point]);
            System::systemlog(session('uid'),"修改了会员积分");
            if($ins && $ins1){
                Db::commit();
                return ['成功',6010,null];
            }else{
                Db::rollback();
                return ['操作失败',-6010,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6015,null];
        }
    }
    /**根据省市区查询当前负责区域的代理商上级 返回其id
     * 注意  如果没有省信息 则为最大的代理
     *       如果没有市信息  则为省代理
     *       如果没有区信息  则为市代理
     *       如果没有市信息  则为区代理
     * @param $con
     * @param $data
     */
    public function member_findfather($data)
    {
        try{
            $query = db('member')
                ->field('id,uname,phone')
            //必须是销售或代理级别
                ->where('type','=',$data['type'])
            //必须是启用的账号
                ->where('status','=',1);
            if(isset($data['province']) && !empty($data['province'])
            && isset($data['city']) && !empty($data['city'])
                && isset($data['district']) && !empty($data['district'])
            )
            {
                $where['region_province'] = $data['province'];
                $where['region_city'] = $data['city'];
                $where['region_district'] = $data['district'];
                $father = $query->where($where)->find();
            }elseif (isset($data['province']) && !empty($data['province'])
                && isset($data['city']) && !empty($data['city']))
            {
                $where['region_province'] = $data['province'];
                $where['region_city'] = $data['city'];
                $father = $query->where($where)->find();
            }elseif(isset($data['province']) && !empty($data['province'])){
                $where['region_province'] = $data['province'];
                $father = $query->where($where)->find();
            }
            if($father){
                return ['成功',6011,$father];
            }
            return ['未找到',-6011,null];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6009,null];
        }
    }
    //根据电话号码获取会员姓名和昵称
    public function member_getuserbyphone($page,$limit,$con)
    {
        if(!isset($con['phone'])||!is_numeric($con['phone']))
        {
            return ['电话号码格式不正确',-9000,null];
        }
        try{
            $res = db('member')
                ->field('id,phone,name,uname')
                ->page($page)
                ->limit($limit)
                ->where('phone','like',"%{$con['phone']}%")
                ->select();
            if($res)
            {
                return ['成功',9000,$res];
            }else{
                return ['未找到该用户',-9001,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-9000,null];
        }
    }
    //根据用户id获取用户代理的商品
    public function member_getmemberproduct($page,$limit,$con)
    {
        try{
            $query = Db::name('member_product')
               ->field('product.name,stock.num,product.id,uname,product.trueprice as price')
                ->join('member','member.id = member_product.mid')
                ->join('product','product.id = member_product.pid')
                ->Leftjoin('stock','stock.pid = product.id')
                ->page($page)
                ->limit($limit)
                ->where(['stock.mid'=>$con['id']])
                ->where(['member_product.mid'=>$con['id']]);
            if(isset($where['stock'])&&!empty($where['stock']))
            {
                $query->where('num','<=',$where['stock']);
            }
            if(isset($where['name'])&&!empty($where['name']))
            {
                $query->whereLike('product.name',"%{$where['name']}%");
            }
            $product['list'] = $query->select();
            if($product['list'])
            {
                $arr = array_column($product['list'],'uname');
                $product['uname'] = $arr[0];
                $product['total'] = db('member_product')
                    ->join('product','product.id = member_product.pid')
                    ->join('stock','stock.pid = product.id')
                    ->where(['member_product.mid'=>$con['id']])
                    ->where(['stock.mid'=>$con['id']])
                    ->count();
                return ['成功',6014,$product];
            }else{
                $product['total'] = 0;
                return ['暂无数据',-6014,$product];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6015,null];
        }
    }
    //获取会员子级
    public function member_getchild($page,$limit,$where)
    {
        if(!isset($where['id']))
        {
            return ['会员id必须',-6014,null];
        }
        $res['list'] = db('member')
            ->field('id,phone,uname,name')
            ->where(['fatherid'=>$where['id']])
            ->page($page)
            ->limit($limit)
            ->select();
        $res['count'] = db('member')
            ->field('id,phone,uname,name')
            ->where(['fatherid'=>$where['id']])
            ->page($page)->count();
        if($res['count']>0){
            return ['成功',6015,$res];
        }
        return ['暂无数据',-6015,$res];
    }

    public function member_pointupdate($con,$data)
    {
        try{
            $oriPoint = db('member')
                ->field('point')
                ->where([
                'id'=>$con['id']
                ])->find()['point'];
            $con['add']? $ponit = $oriPoint+$data['point']: $ponit = $oriPoint-$data['point'];
            Db::startTrans();
            $ups = db('member')->where(['id'=>$con['id']])
                ->update(['point'=>$ponit]);
            $ins = db('member_point_history')->insert([
               'mid'=> $con['id'],
                'pointsum'=>$oriPoint-$ponit,
                'date'=>date('Y-m-d'),
                'type'=>2,
                'tip'=>'积分扣除'
            ]);
            if($ups&&$ins){
                Db::commit();
                return ['成功',6016,null];
            }else{
                Db::rollback();
                return ['修改失败',-6016,null];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-6015,null];
        }
    }

    public static function admingetrecentsearch($page,$limit,$mid)
    {
        $query = Db::name('recent_search')
            ->field('product.name,trueprice,price,product.imgurl')
            ->join('product','product.id = recent_search.pid')
            ->where(['mid'=>$mid]);
        $query1 = clone($query);
        $res['list'] = $query->page($page)->limit($limit)->select();
        if($res['list'])
        {
            $res['total'] = $query1->count();
            return ['成功',7000,$res];
        }else{
            $res['total'] = 0;
            return ['暂无数据',-7000,$res];
        }
    }

    //积分转换为等级
    public function pointtolevel($mid)
    {

    }
    //添加病人标签
    public function member_addsign($data)
    {
        try{
            $ins = db('patientsign')->insert([
                'sign'=>$data['sign']
            ]);
            if($ins){
                return ['成功',9000,null];
            }else{
                return ['失败',-9000,null];
            }
        }catch (\Exception $e){
            return [$e->getMessage(),-9000,null];
        }
    }
    //获取病人标签列表
    public function member_signlist($page,$limit,$where)
    {
        try{
            $query = db('patientsign')->where($where);
            $query1 = clone($query);

            $res['list'] = $query->page($page)->order('id','desc')->limit($limit)->select();
            $res['total'] = $query1->count();

            if($res['total']>0){
                return ['成功',9000,$res];
            }else{
                return ['失败',-9000,$res];
            }
        }catch (\Exception $e){
            return [$e->getMessage(),-9000,null];
        }
    }

    //根据id删除对应的病人标签
    public function member_delsign($where)
    {
        try{
            $del = db('patientsign')->where($where)->delete();
            if($del){
                return ['成功',9000,null];
            }else{
                return ['失败',-9000,null];
            }
        }catch (\Exception $e){
            return [$e->getMessage(),-9000,null];
        }
    }

    //根据病人id修改病人标签
    public function member_selectsign($mid,$signids)
    {
        try{
            $inssign = null;
            foreach ($signids as $v){
                $inssign[] = [
                    'mid' => $mid,
                    'signid'=>$v
                ];
            }
            Db::startTrans();
            $del = db('illnesswithsign')->where(['mid'=>$mid])->delete();
            $ins = db('illnesswithsign')->insertAll($inssign);
            if($del&&$ins){
                Db::commit();
                return ['成功',9000,null];
            }else{
                Db::rollback();
                return ['失败',-9000,null];
            }
        }catch (\Exception $e){
            return [$e->getMessage(),-9000,null];
        }
    }
}