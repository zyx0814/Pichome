<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_views extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_views';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_views';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }
    public function delete_by_appid($appid){
        DB::delete($this->_table,array('appid'=>$appid));
    }

    public function delete_by_rid($rids){
        if(!is_array($rids)) $rids = (array)$rids;
        DB::delete($this->_table,'rid in ('.dimplode($rids).')');
    }
}