<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_lang_en_US extends dzz_table
{
    public function __construct()
    {
        $this->_table = 'lang_en_US';
        $this->_pk    = 'id';
        parent::__construct();
    }

    public function insert_data($setarr)
    {
        if($setarr['skey']) $setarr['skey'] = getstr($setarr['skey']);
        else return true;
        if($id = DB::result_first("select id from %t where skey = %s",[$this->_table,$setarr['skey']])){
            parent::update($id,$setarr);
            return true;
        }else{
            return parent::insert($setarr,1);
        }
    }

}