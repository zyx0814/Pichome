<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    
    class table_pichome_resources_relation extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_resources_relation';
            $this->_pk = 'id';
            $this->_pre_cache_key = 'pichome_resources_relation';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        
        public function insert($setarr)
        {
            if (DB::result_first("select count(rid) from %t where rid = %s", array($this->_table, $setarr['rid']))) {
                $rid = $setarr['rid'];
                unset($setarr['rid']);
                return parent::update($rid, $setarr);
            } else {
                return parent::insert($setarr);
            }
        }
        public function fetch_by_orid_appid($orid,$appid){
            return DB::fetch_first("select * from %t where orid = %s and appid = %s",$this->_table,$orid,$appid);
        }
        
        
        
    }