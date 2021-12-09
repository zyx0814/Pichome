<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
class table_pichome_imagickrecord extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_imagickrecord';
        $this->_pk = 'rid';
        $this->_pre_cache_key = 'pichome_imagickrecord';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }
    public function insert($setarr,$isforce = 0){
        $rid = $setarr['rid'];
        if(DB::result_first("select rid from %t where rid = %s",array($this->_table,$rid))){
            if(!$isforce) return $rid;
            else {
                parent::delete($rid);
                return parent::insert($setarr);
            }
        }else{
            return parent::insert($setarr);
        }
    }


}
