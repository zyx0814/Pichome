<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_templatetagtheme extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_templatetagtheme';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_templatetagtheme';
        //$this->_cache_ttl = 3600;
        parent::__construct();
    }
    //新建或修改单页
    public function insertdata($setarr){
        $id = DB::result_first("select id from %t where themeid = %d and tid = %d",[$this->_table,$setarr['themeid'],$setarr['tid']]);
       if($setarr['style']){
           if($id){
              return parent::update($id,$setarr);
           }else{
               return  parent::insert($setarr);
           }
       }else{
           if($id){
               return parent::delete($id);
           }else{
               return true;
           }
       }
    }

    public function delete_by_tid($tid){
        $ids = [];
        foreach(DB::fetch_all("select id from %t where tid = %d",[$this->_table,$tid]) as $v){
            $ids[] = $v['id'];
        }
        return parent::delete($ids);
    }
}