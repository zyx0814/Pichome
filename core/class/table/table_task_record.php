<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
class table_task_record extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'task_record';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'task_record';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }

    public function addData($setarr)
    {
        if($id = DB::result_first("select id from %t where idvalue=%s and idtype = %d" ,array($this->_table,$setarr['idvalue'],$setarr['idtype']))){
            $processname = 'DZZ_LOCK_TASKRECORD_'.$id;
            dzz_process::addlock($processname,3600);
            if(parent::update($id,$setarr)){
                dzz_process::unlock($processname);
            }else{
                dzz_process::unlock($processname);
            }

        }else{
            $id = parent::insert($setarr,1);
        }
        dfsockopen(getglobal('localurl') . 'misc.php?mod=dotaskrecord&id='.$id, 0, '', '', false, '',1);
    }
    public function delete_by_idtype_idvalue($idtype,$idvalue){
        $id = DB::result_first("select id from %t where idvalue=%s and idtype = %d" ,array($this->_table,$idvalue,$idtype));
        if($id) return parent::delete($id);
        else return false;
    }
}