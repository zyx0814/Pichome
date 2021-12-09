<?php
    if(!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    class table_pichome_tagrelation extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_tagrelation';
            $this->_pk = 'id';
            $this->_pre_cache_key = 'pichome_tagrelation';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        public function insert($setarr){
            if($id = DB::fetch_first("select id from %t  where cid = %s and tid = %d",array($this->_table,$setarr['cid'],$setarr['tid']))){
                return $id;
            }else{
                //同一库下一个标签只能归属于一个分类
                $this->delete_by_tpid($setarr['tid'],$setarr['appid']);
               return parent::insert($setarr);
            }
        }
        public function delete_by_tpid($tid,$appid){
            //兼容以修复已经出现错误的数据
            $ids = [];
            foreach(DB::fetch_all("select id from %t where tid = %d and appid =%s",array($this->_table,$tid,$appid)) as $v){
                $ids[] = $v['id'];
            }
            //$id = DB::result_first("select id from %t where tid = %d and appid =%s",array($this->_table,$tid,$appid));
            if(!empty($ids))return parent::delete($ids);
            return true;
        }
        public function delete_by_cids($cids){
            if(!is_array($cids)) $cids = (array)$cids;
            $ids = [];
            foreach(DB::fetch_all("select id from %t where cid in(%n)",array($this->_table,$cids)) as $v){
                $ids[] = $v['id'];
            }
            parent::delete($ids);
        }
        public function delete_by_appid($appid)
        {
            $delid =[];
            foreach (DB::fetch_all("select id from %t where appid = %s",array($this->_table,$appid)) as $v){
                $delid[] = $v['id'];
            }
            return parent::delete($delid);
        }
    }