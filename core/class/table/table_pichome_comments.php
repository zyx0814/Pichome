<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    
    class table_pichome_comments extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_comments';
            $this->_pk = 'id';
            $this->_pre_cache_key = 'pichome_comments';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        
        public function insert($setarr)
        {
            if (DB::result_first("select count(*) from %t where id = %s", array($this->_table, $setarr['id']))) {
                parent::update($setarr['id'], $setarr);
            } else {
                parent::insert($setarr);
            }
            return $setarr['id'];
        }
        
        public function fetch_id_by_rid($rid)
        {
            $ids = [];
            foreach(DB::fetch_all("select id from %t where rid = %s",array($this->_table,$rid)) as $v){
                $ids[] = $v['id'];
            }
            return $ids;
        }
        
        //根据appid删除数据
        public function delete_by_appid($appid)
        {
            $dids = [];
            foreach (DB::fetch_all("select fid from %t where appid = %s", array($this->_table, $appid)) as $v) {
                $dids[] = $v['fid'];
            }
            return parent::delete($dids);
        }
        
        public function delete_by_rid($rid)
        {
            if(!is_array($rid)) $rid = (array) $rid;
            $delids = [];
            foreach (DB::fetch_all("select id from %t where rid in(%n)", array($this->_table, $rid)) as $v) {
                $delids[] = $v['id'];
            }
            return parent::delete($delids);
        }
        
        public function fetch_annonationnum_by_rids($rids){
            if(!is_array($rids)) $rids = (array)$rids;
            $data = [];
            foreach(DB::fetch_all("select count(id) as num,rid from %t where rid in(%n) group  by rid",array($this->_table,$rids)) as $v){
                $data[$v['rid']] = $v['num'];
            }
            return $data;
        }
    
        public function fetch_like_words($keyword,$limit=10){
            $likewords = [];
            $presql = " case when annotation like %s then 3 when annotation like %s then 2 when annotation like %s then 1 end as rn";
            $wheresql = " annotation like %s";
            $params =    [$keyword . '%', '%' . $keyword,'%'.$keyword.'%',$this->_table,'%'.$keyword.'%'];
            foreach(DB::fetch_all("select annotation,$presql from %t where $wheresql order by rn desc  limit 0,$limit",$params) as $v){
                $likewords[] = $v['annotation'];
            }
            return $likewords;
        }
    }
