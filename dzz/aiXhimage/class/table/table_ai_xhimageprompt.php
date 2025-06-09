<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_ai_xhimageprompt extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'ai_xhimageprompt';
        $this->_pk = 'id';
        parent::__construct();
    }

    public function insertData($setarr)
    {
        $setarr['name'] = getstr($setarr['name'],30);
        if(DB::result_first("select id from %t where name = %s and cate = %d",[$this->_table,$setarr['name'],$setarr['cate']])){
            return array('error'=>'该名称已存在');
        }else{
            return parent::insert($setarr,1);
        }

    }

    public function sortByIds($ids){
        if(!is_array($ids)) $ids = array($ids);
        foreach($ids as $k=>$id){
            parent::update($id,array('disp'=>$k),1);
        }
    }

    public function  setStatusById($id,$status){
        return parent::update($id,array('status'=>$status),1);
    }
    public function setDefaultByIdandCate($id,$cate){
        if(DB::update($this->_table,array('isdefault'=>0),DB::field('cate',$cate).' AND '.DB::field('isdefault',1))){
            return parent::update($id,array('isdefault'=>1),1);
        }
    }
    public function editById($id,$setarr){
        $setarr['name'] = getstr($setarr['name'],30);
        if(DB::result_first("select id from %t where name = %s and id != %d",[$this->_table,$setarr['name'],$id])){
            return array('error'=>'该名称已存在');
        }else{
            return parent::update($id,$setarr,1);
        }
    }

    public function fetchPromptByStatus(){
        return DB::fetch_all("select * from %t where status = 1 order by disp asc ",[$this->_table]);
    }
    public function fetchPromptByCate($cate){
        return DB::fetch_all("select * from %t where cate = %d order by  disp asc ",[$this->_table,$cate]);
    }

    public function deleteById($id){

        return parent::delete($id);
    }


}