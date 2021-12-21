<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    
    class table_pichome_taggroup_relation extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_taggroup_relation';
            $this->_pk = 'id';
            $this->_pre_cache_key = 'pichome_taggroup_relation';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        
        //获取库下所有标签分类对应数据
        public function fetch_by_appid($appid){
            $data = [];
           foreach(DB::fetch_all("select * from %t where appid = %s",array($this->_table,$appid)) as $v){
               $data[$v['gid']] = $v;
           }
           return $data;
        }
        public function fetch_cid_by_gid($gid,$appid){
            return DB::result_first("select cid from %t where gid = %d and appid = %s",array($this->_table,$gid,$appid));
        }

        public function insertdata($setarr){
            if($id=DB::result_first("select id from %t where tid = %d and appid = %s",array($this->_table,$setarr['tid'],$setarr['appid']))){
                parent::update($id,$setarr);
            }else{
                parent::insert($setarr);
            }
            return true;
        }
    }