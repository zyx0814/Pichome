<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_vapp_tag extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_vapp_tag';
        $this->_pk = 'id';
       // $this->_pre_cache_key = 'pichome_vapp_tag';
        //$this->_cache_ttl = 3600;
        parent::__construct();
    }
    public function add_hots_by_tid_appid($tid,$appid){
        $tagappdata =DB::fetch_first("select * from %t where tid = %d and appid = %s",array($this->_table,$tid,$appid));
        if(!$tagappdata){
            $setarr = ['appid'=>$appid,'tid'=>$tid,'hots'=>1];
            return parent::insert($setarr);
        }else{
            $setarr['hots'] = intval($tagappdata['hots']) +1;
            return parent::update($tagappdata['id'],$setarr);
        }

    }
    public function insert($setarr){
        if($id = DB::result_first("select id from %t where appid = %s and tid = %d",array($this->_table,$setarr['appid'],$setarr['tid']))){
            parent::update($id,$setarr);
        }else{
            parent::insert($setarr);
        }
    }
    public function delete_by_tid_appid($tid,$appid){
        $tagappdata =DB::fetch_first("select * from %t where tid = %d and appid = %s",array($this->_table,$tid,$appid));
        if(!$tagappdata) return false;
        if($tagappdata['hots'] > 1){
            return parent::update($tagappdata['id'],array('hots'=>$tagappdata['hots']-1));
        }else{
            return parent::delete($tagappdata['id']);
        }
    }

}
