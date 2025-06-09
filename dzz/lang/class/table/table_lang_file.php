<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_lang_file extends dzz_table
{
    public function __construct()
    {
        $this->_table = 'lang_file';
        $this->_pk = 'id';
        parent::__construct();
    }

    public function insertData($rid,$lang,$time){
        if($id = DB::result_first("select id from %t where rid = %s and lang = %s",[$this->_table,$rid,$lang])){
            return parent::update($id,array('dateline'=>$time));
        }else{
            return parent::insert(array('rid'=>$rid,'lang'=>$lang,'dateline'=>$time));
        }
    }




}