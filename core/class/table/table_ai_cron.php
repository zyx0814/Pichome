<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_ai_cron extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'ai_cron';
        $this->_pk = 'id';
        parent::__construct();
    }

    public function insertData($setarr){
        return parent::insert($setarr,1);
    }

}