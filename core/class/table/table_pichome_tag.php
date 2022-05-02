<?php
    if(!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    class table_pichome_tag extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_tag';
            $this->_pk = 'tid';
            $this->_pre_cache_key = 'pichome_tag';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        public function insert($tagname,$nohots = 0){
            $setarr =[];
            if($data = DB::fetch_first("select tid,hots,initial from %t where tagname = %s",array($this->_table,$tagname))){
                if(!$data['initial']){
                    $setarr['initial'] = $this->getInitial($tagname);
                    parent::update($data['tid'],$setarr);
                }
                return $data['tid'];
            }else{
                $setarr['tagname'] = $tagname;
                $setarr['initial'] = $this->getInitial($tagname);
                return parent::insert($setarr,1);
            }
        }
        public function add_hots_by_tid($tid){
            $tagdata = parent::fetch($tid);
            if(!$tagdata) return false;
            $setarr['hots'] = intval($tagdata['hots']) +1;
            return parent::update($tid,$setarr);
        }
        
        public function delete_by_tid($tid){
            if(!$data = parent::fetch($tid)) return ;
            if($data['hots'] > 1){
                return parent::update($tid,array('hots'=>$data['hots']-1));
            }else{
                return parent::delete($tid);
            }
        }
        public function getInitial($str){
            $py=pinyin::encode($str);
            $initial=substr($py,0,1);
            if(empty($initial) || !preg_match("/[A-Z]/i",$initial)){
                $initial='#';
            }
            return strtoupper($initial);
        }
        //依据标签热度获取标签及对应图
        public function fetch_data_by_hot($limit=16){
            $hotsdata = [];
            //此处暂未确定图片以何种方式取，后面方法补充完成后修改
            foreach(DB::fetch_all("select tid,tagname from %t where 1 order by hots desc limit  0,$limit",array($this->_table)) as $v){
                $sourcesdata = DB::fetch_first("select r.name,r.rid,r.ext,r.type,r.hasthumb,ra.path from %t  rt
                    left join %t  r on rt.rid = r.rid left join %t ra on ra.rid=rt.rid  where rt.tid=%d",
                    array('pichome_resourcestag','pichome_resources','pichome_resources_attr',$v['tid']));
                /*$sourcesdata = DB::fetch_first("select  r.name,r.rid,r.ext,r.type,r.hasthumb,ra.path  from %t  ra left join %t  r on ra.rid = r.rid where  find_in_set(%d,ra.tag)",
                    array('pichome_resources_attr','pichome_resources',$v['tid']));*/
           
                if ($sourcesdata['hasthumb']) {
                    $filename = str_replace(strrchr($sourcesdata['name'], "."), "", $sourcesdata['name']);
                    $filepath = dirname($sourcesdata['path']);
                    $thumbpath = DZZ_ROOT.'library/' . $filepath . '/' . $filename . '_thumbnail.png';
                   /* if (!file_exists($thumbpath)) {
                        $thumbpath = iconv('UTF-8', 'GB2312', $thumbpath);
                    }
                    if (file_exists($thumbpath)) {*/
                        $thumbpath = $thumbpath = str_replace(DZZ_ROOT, '', $thumbpath);
                        $v['icondata'] = $thumbpath;
                   // }
                }
                else {
                    if ($sourcesdata['type'] == 'commonimage') {
                        $v['icondata'] = 'library/' . $sourcesdata['path'];
                    }
                    else {
                        $v['icondata'] = geticonfromext($sourcesdata['ext'], $sourcesdata['type']);
                    }
                }
                $hotsdata[] = $v;
            }
            return $hotsdata;
        }
    
        public function get_tid_by_tagname($keywords){
            if(!is_array($keywords)) $keywords = (array)$keywords;
            $tdata = [];
            foreach(DB::fetch_all("select tid,tagname from %t where tagname in(%n) ",array($this->_table,$keywords)) as $v){
                $tdata[$v['tid']] = $v['tagname'];
            }
            return $tdata;
        }
        public function fetch_like_words($keyword,$limit=10){
            $likewords = [];
            $presql = " case when tagname like %s then 3 when tagname like %s then 2 when tagname like %s then 1 end as rn";
            $wheresql = " tagname like %s";
            $params =    [$keyword . '%', '%' . $keyword,'%'.$keyword.'%',$this->_table,'%'.$keyword.'%'];
            foreach(DB::fetch_all("select tagname,$presql from %t where $wheresql order by rn desc  limit 0,$limit",$params) as $v){
                $likewords[] = $v['tagname'];
            }
            return $likewords;
        }
    }