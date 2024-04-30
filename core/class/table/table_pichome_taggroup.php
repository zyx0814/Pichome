<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    
    class table_pichome_taggroup extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_taggroup';
            $this->_pk = 'cid';
            $this->_pre_cache_key = 'pichome_taggroup';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        
        public function insert($setarr)
        {
            $cid = ($setarr['cid']) ? $setarr['cid']:$this->createcid($setarr['appid']);
            if ($taggroup = DB::fetch_first("select * from %t  where cid = %s and appid = %s", array($this->_table, $cid,$setarr['appid']))) {
                unset($setarr['cid']);
                if ($taggroup['catname'] != $setarr['catname']) $this->updateBycid($cid, $setarr);
                return $cid;
            } else {
                $setarr['cid'] = $cid;
                if (parent::insert($setarr)) return $cid;
            }
        }
        public function updateBycid($cid,$setarr){
            $setarr['cid'] = $cid;
            Hook::listen('taggroupupdateBefore',$setarr);
            if($setarr) parent::update($cid, $setarr);
            return true;
        }
        public function createcid($appid){
            $cid = random(13).$appid;
            if(DB::result_first("select count(cid) from %t where cid = %s",array($this->_table,$cid))){
                $this->createcid($appid);
            }
            return $cid;
        }
        public function fetch_all_by_appid_pcid($appid,$pcid = 0){
            $catdata = [];
           foreach(DB::fetch_all("select * from %t  where appid = %s and pcid = %d ",array($this->_table,$appid,$pcid)) as $v){
               $catdata[$v['cid']] = $v['catname'];
           }
           return $catdata;
        }
        public function fetch_tagcatandnum_by_pcid($appid,$pcid=0){
            $catdata = [];
            foreach(DB::fetch_all("select * from %t  where appid = %s and pcid = %d order by disp ",array($this->_table,$appid,$pcid)) as $v){
                $catdata[$v['cid']]['tagname'] = $v['catname'];
                $catdata[$v['cid']]['num'] = DB::result_first("select count(id) from %t where cid = %s",array('pichome_tagrelation',$v['cid']));
            }
            return $catdata;
        }
        public function fetch_cid_by_appid($appid)
        {
            $cids = [];
            foreach (DB::fetch_all("select cid from %t where appid = %s", array($this->_table, $appid)) as $v) {
                $cids[] = $v['cid'];
            }
            return $cids;
        }
        
        public function delete_by_cids($cids)
        {
            if (!is_array($cids)) $cids = (array)$cids;
            if (!empty($cids)) {
                C::t('pichome_tagrelation')->delete_by_cids($cids);
                parent::delete($cids);
            }
            
        }
        
        public function delete_by_appid($appid)
        {
            $delid = [];
            foreach (DB::fetch_all("select cid from %t where appid = %s", array($this->_table, $appid)) as $v) {
                $delid[] = $v['cid'];
            }
            return parent::delete($delid);
        }
        
        public function fetch_by_appid($appid)
        {
            $data = [];
            return DB::fetch_all("select * from %t where appid = %s", array($this->_table, $appid));
            
        }
    
        
    }