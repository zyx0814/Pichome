<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    
    class table_pichome_folder_relation extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_folder_relation';
            $this->_pk = 'id';
            $this->_pre_cache_key = 'pichome_folder_relation';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        
        public function fetch_by_ofid_appid($ofid,$appid){
            return DB::fetch_first("select * from %t where nfid = %d and appid = %s",array($this->_table,$ofid,$appid));
        }
        
        
        
    }