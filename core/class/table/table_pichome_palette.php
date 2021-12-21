<?php
    if(!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    class table_pichome_palette extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_palette';
            $this->_pk = 'id';
            $this->_pre_cache_key = 'pichome_palette';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        //根据appid删除目录
        public function delete_by_appid($appid){
            $ids = [];
            foreach(DB::fetch_all("select id from %t where appid = %s",array($this->_table,$appid)) as $v){
                $ids[] = $v['id'];
            }
            return parent::delete($ids);
        }
        
        public function delete_by_rid($rid){
            if(!is_array($rid)) $rid = (array) $rid;
            $delids = [];
            foreach (DB::fetch_all("select id from %t where rid in(%n)",array($this->_table,$rid)) as $v){
                $delids[] = $v['id'];
            }
            return parent::delete($delids);
        }

        public function fetch_rid_by_color($color,$persion=0,$appid='',$limit=6,$rid=''){
            //设定基础相似度为80%,当精度调整时，相似度根据精度数值相应增加，调整范围为0-100,实际计算用20的差异量进行转换
            $similarity = 90+(10/100)*$persion;
            //此值为rgb(0,0,0)与rgb(255,255,255)最大色差值，用以计算相似度的被除数
            $maxColDist = 764.8339663572415;
            //获取颜色的rgb,该操作为十六进制转换为rgb
            $rgbcolor = $this->hex2rgb($color);
            //色差计算公式为sqrt((2 + $rmean / 256) * (pow($r, 2)) + 4 * (pow($g, 2)) + (2 + (255 - $rmean) / 256) * (pow($b, 2)))
            //公式中rgb值为连个rgb的差值，$rmean为r值之和的二分之一
            if($appid){
                $sql = 'select distinct  p.rid from %t  p left join %t  r on p.rid = r.rid';
                $wheresql = "  r.appid = %s and p.rid != %s
                and  round((%d-sqrt((((2+(p.r+%d)/2)/256)*(pow((%d-p.r),2))+(4*pow((%d-p.g),2)) + (((2+(255-(p.r+%d)/2))/256))*(pow((%d-p.b), 2)))))/%d,4)*100 >= %d ";
                $ordersql = ' order by round((%d-sqrt((((2+(p.r+%d)/2)/256)*(pow((%d-p.r),2))+(4*pow((%d-p.g),2)) + (((2+(255-(p.r+%d)/2))/256))*(pow((%d-p.b), 2)))))/%d,4)*100*weight ';
                $params = array($this->_table,'pichome_resources',$appid,$rid,$maxColDist,$rgbcolor['r'],$rgbcolor['r'],$rgbcolor['g'],$rgbcolor['r'],$rgbcolor['b'],$maxColDist,$similarity,
                    $maxColDist,$rgbcolor['r'],$rgbcolor['r'],$rgbcolor['g'],$rgbcolor['r'],$rgbcolor['b'],$maxColDist,$similarity);
            }else{
                $sql = 'select distinct  rid from %t  ';
                $wheresql = " rid != %s
                and  round((%d-sqrt((((2+(r+%d)/2)/256)*(pow((%d-r),2))+(4*pow((%d-g),2)) + (((2+(255-(r+%d)/2))/256))*(pow((%d-b), 2)))))/%d,4)*100 >= %d ";
                $params = array($this->_table,$rid,$maxColDist,$rgbcolor['r'],$rgbcolor['r'],$rgbcolor['g'],$rgbcolor['r'],$rgbcolor['b'],$maxColDist,$similarity,
                    $maxColDist,$rgbcolor['r'],$rgbcolor['r'],$rgbcolor['g'],$rgbcolor['r'],$rgbcolor['b'],$maxColDist,$similarity);
            }
           $riddata =  DB::fetch_all("$sql  where $wheresql $ordersql  desc limit  0,$limit  ",$params);
           $rids= [];
           foreach ($riddata as $v){
               $rids[] = $v['rid'];
           }
           return $rids;
        }
        //转换十六进制颜色为rgb
       public  function hex2rgb($hexColor){
            $color=str_replace('#','',$hexColor);
            if (strlen($color)> 3){
                $rgb=array(
                    'r'=>hexdec(substr($color,0,2)),
                    'g'=>hexdec(substr($color,2,2)),
                    'b'=>hexdec(substr($color,4,2))
                );
            }else{
                $r=substr($color,0,1). substr($color,0,1);
                $g=substr($color,1,1). substr($color,1,1);
                $b=substr($color,2,1). substr($color,2,1);
                $rgb=array(
                    'r'=>hexdec($r),
                    'g'=>hexdec($g),
                    'b'=>hexdec($b)
                );
            }
            return $rgb;
        }
        function RGBToHex($rgb){
            $regexp = "/^rgb\(([0-9]{0,3})\,\s*([0-9]{0,3})\,\s*([0-9]{0,3})\)/";
            $re = preg_match($regexp, $rgb, $match);

            $re = array_shift($match);

            $hexColor = "";

            $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');

            for ($i = 0; $i < 3; $i++) {
                $r = null;

                $c = $match[$i];

                $hexAr = array();

                while ($c > 16) {
                    $r = $c % 16;

                    $c = ($c / 16) >> 0;

                    array_push($hexAr, $hex[$r]);

                }

                array_push($hexAr, $hex[$c]);

                $ret = array_reverse($hexAr);

                $item = implode('', $ret);

                $item = str_pad($item, 2, '0', STR_PAD_LEFT);

                $hexColor .= $item;

            }

            return $hexColor;

        }
        public function fetch_colordata_by_rid($rid){
            $data = [];
            foreach(DB::fetch_all("select r,g,b,weight from %t where rid = %s",array($this->_table,$rid)) as $v){
                $rgbdata = 'rgb('.$v['r'].','.$v['g'].','.$v['b'].')';
                $weight = $v['weight']*100;
                $v['color'] = $this->RGBToHex($rgbdata);
                $data[] = ['color'=>$v['color'],'weight'=>$weight];
            }
            $distance = [];
            $colordata = [];
         foreach($data as $v){
             $distance[] = $v['weight'];

         }
            array_multisort($distance, SORT_DESC, $data);
         foreach($data as $v){
             $colordata[] = $v['color'];
         }
            return $colordata;
        }
        /*
         *$colors 该参数需带权重，以键为权重值，此处查询优先权重最大的查询近似颜色值
         *  ***/
        public function fetch_rids_by_clolos($colors,$limit=6){
                $rids = [];
                //此处颜色值按权重顺序,优先寻找权重最大的,如果权重最大的得到足够数据，则不再查找其它颜色相似数据
                foreach($colors as $k=>$v){
                        foreach($this->fetch_rid_by_color($v) as $v){
                            $rids[] = $v;
                        }
                        if(count($rids) >= $limit) break;
                }
                return $rids;
        }
    }