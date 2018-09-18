<?php
namespace app\index\model;

class Product extends \app\admin\model\Product {

    //快速找药
    public function fast_find($page,$limit,$where){
        $where = array_filter($where);
        try{
            $res = db('product')
                ->field('product.name,shop_name,product.id as pid,
            longitude,latitude,product.imgurl,price,trueprice')
                ->join('member_product','product.id = member_product.pid')
                ->join('member','member.id =member_product.mid ')
                ->whereLike('product.name',"%{$where['name']}%")
                ->where('member.type','=',2)
                ->page($page)
                ->limit($limit)
                ->select();
            if($res)
            {
                foreach ($res as $key=>$val)
                {
                    $val['dis'] = getDistance($val['longitude'],$val['latitude'],
                        $where['lng2'],$where['lat2']);
                    $res1[$val['pid']][] = $val;
                }
                foreach ($res1 as $key=>$val)
                {
                    $res2['list'][$key]['productname'] = $val[0]['name'];
                    $res2['list'][$key]['price'] = $val[0]['price'];
                    $res2['list'][$key]['imgurl'] = $val[0]['imgurl'];
                    $res2['list'][$key]['trueprice'] = $val[0]['trueprice'];
                    $res2['list'][$key]['shopcount'] = count($val);
                    $res2['list'][$key]['shoplist'] = $val;
                }
                //记录最近搜索到缓存
                $this->add_recent_search($res2['list']);
                return ['success',7000,$res2];
            }else{
                return ['未找到相似药品',-7001,[]];
            }
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-7000,null];
        }
    }

    /**根据商品标签获取商品列表
     * @param $page
     * @param $limit
     * @param $where
     * @return array
     */
    public function permanentmedicine($page,$limit,$where)
    {
        try{
            $product = db('product')
                ->field('product.name,product.id as pid,
            product.imgurl,price,trueprice')
                ->where('tag','=',1)
                ->where('status','=',1)
                ->page($page)
                ->limit($limit)
                ->select();
            $pids = array_column($product,'pid');
            list($msg,$err,$data) = self::getstockbypid($page,$limit,$pids);
            return [$msg,$err,$data];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-7000,null];
        }
    }

    /**商品信息和附近商家，库存信息
     * @param $page  用于附近商家条数分页
     * @param $limit 用于附近商家条数分页
     * @param $where $where  lng2用户经度 lat2用户纬度 必传
     * @return array
     */
    public function detail($page,$limit,$where)
    {
        $where = array_filter($where);
        try{
            $res = db('product')
                ->field('product.name,product.id as pid,
            product.imgurl,price,trueprice')
               ->where('product.id','=',$where['id'])
                ->find();
            $pids = [$res['pid']];
            list($msg,$err,$data) = $this->getDisAndShop($page,$limit,$pids,$where);
            return [$msg,$err,array_values($data)];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-7000,null];
        }
    }

    /**
     * @param $fastfindresult   fast_find()方法查找的结果
     */
    public function add_recent_search($fastfindresult)
    {
        $res = null;
        $data = null;
        foreach ($fastfindresult as $v)
        {
            $have = db("recent_search")
                ->where(['mid'=>session('member_id'),'pid'=>$v['shoplist'][0]['pid']])
                ->find();
            if(!$have)
            {
                $data[] = [
                    'mid'=>session('member_id'),
                    'pid'=>$v['shoplist'][0]['pid']
                ];
            }
        }
        if($data)
        {
            db('recent_search')->insertAll($data);
        }
    }
    //根据会员id查询最近搜索商品
    public static function getrecentsearch($page,$limit,$mid)
    {
        try{
            //查询该会员的最近9条查询商品id
            $recentsearch_pid = db('recent_search')
                ->field('mid,pid')
                ->join('member','member.id = recent_search.mid')
                ->where(['mid'=>$mid])
                ->where('member.type','=','2')
                ->distinct(true)
                ->limit('9')
                ->order('recent_search.id','desc')
                ->select();
            $pids = array_column($recentsearch_pid,'pid');
            if(empty($pids)){
                return ['暂无数据',-8002,[]];
            }
            list($msg,$err,$data) = self::getstockbypid($page,$limit,$pids);
            return [$msg,$err,$data];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-8001,[]];
        }
    }

    //根据pid查询拥有该商品的店铺库存
    public static function getstockbypid($page,$limit,$pids)
    {
        try{
            $product = [];
            foreach ($pids as $k=>$v)
            {
                $product[$v]['list'] = db('member_product')
                    ->field('member_product.mid as shopid,stock.pid,stock.num,product.name,
                        product.price,product.trueprice,product.imgurl,product.detail')
                    ->leftJoin('product','product.id = member_product.pid')
                    ->join('member','member.id = member_product.mid')
                    ->leftJoin('stock','stock.mid = member_product.mid')
                    ->where(['stock.pid'=>$v])
                    ->where(['member.type'=>2])
                    ->where('stock.num','>',0)
                    ->page($page)
                    ->limit($limit)
                    ->where(['member_product.pid'=>$v])
                    ->select();
                $shop = [];
                if($product[$v]['list']){
                    foreach ($product[$v]['list'] as $k1=>$v1)
                    {

                        if($v1['num'] > 0)
                        {
                            array_push($shop,$v1['shopid']);
                        }
                        $product1[$v]['list'][] = ['num'=>$v1['num'],
                            'shopid'=>$v1['shopid']];
                    }
                    $product1[$v]['id'] = $product[$v]['list'][0]['pid'];
                    $product1[$v]['name'] = $product[$v]['list'][0]['name'];
                    $product1[$v]['price'] = $product[$v]['list'][0]['price'];
                    $product1[$v]['trueprice'] = $product[$v]['list'][0]['trueprice'];
                    $product1[$v]['imgurl'] = $product[$v]['list'][0]['imgurl'];
                    $product1[$v]['detail'] = strip_tags($product[$v]['list'][0]['detail']);
                    $product1[$v]['shoptotal'] = count(array_flip($shop));
                    $product1[$v]['ptotal'] = array_sum(array_column($product[$v]['list'],'num'));
                }
            }
            foreach ($product1 as $k=>$v)
            {
                if(empty($product1[$k]['list'])){
                    unset($product1[$k]);
                }
            }
            return ['success',8000,$product1];
        }catch (\Exception $e)
        {
            return [$e->getMessage(),-8001,[]];
        }
    }

    /**根据药品id和经纬度获取  店铺到客户的距离，店铺对应药品的基本信息和库存
     * @param $page
     * @param $limit
     * @param $pids array  商品id [1,2,3,4]
     * @param $where  lng2用户经度 lat2用户纬度 必传
     * @return array
     */
    public function getDisAndShop($page,$limit,$pids,$where)
    {
        list($msg,$err,$data) = self::getstockbypid($page,$limit,$pids);
        foreach ($data as $k=>$v)
        {
            $shopids = array_unique(array_column($v['list'],'shopid'));
            $shopinfo = db('member')
                ->field('longitude,latitude,shop_name,id')
                ->where('id','in',$shopids)
                ->select();
            foreach ($v['list'] as $k1=>$v1)
            {
                foreach ($shopinfo as $k2=>$v2)
                {
                    if($v1['shopid'] == $v2['id'])
                    {
                        $data[$k]['list'][$k1]['shopname'] = $v2['shop_name'];
                        $data[$k]['list'][$k1]['longitude'] = $v2['longitude'];
                        $data[$k]['list'][$k1]['latitude'] = $v2['latitude'];
                        $data[$k]['list'][$k1]['dis'] = getDistance($where['lng2'],$where['lat2'],$v2['longitude'],$v2['latitude']);
                    }
                }
            }
        }
        return [$msg,$err,$data];
    }
}