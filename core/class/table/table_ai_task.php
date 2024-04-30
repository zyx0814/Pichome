<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_ai_task extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'ai_task';
        $this->_pk = 'id';
        parent::__construct();
    }

    public function insertData($setarr){
        if(DB::result_first("select id from %t where rid = %s and gettype = %d and tplid = %d and aikey=%s",
        [$this->_table,$setarr['rid'],$setarr['gettype'],$setarr['tplid'],$setarr['aikey']])){
            return true;
        }else{
            return parent::insert($setarr,1);
        }
    }

    public function fetchNumByAppid($appid){
        $data = [];
        foreach(DB::fetch_all("select count(id) as num,gettype from %t where appid = %s group by gettype",[$this->_table,$appid]) as $v){
            $data[$v['gettype']] = $v['num'];
        }
        return $data;
    }

}