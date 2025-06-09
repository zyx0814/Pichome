<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_resources_tag extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_resources_tag';
        $this->_pk = 'id';
        // $this->_pre_cache_key = 'pichome_vapp_tag';
        //$this->_cache_ttl = 3600;
        parent::__construct();
    }
    public function insert($setarr)
    {
        if($id = DB::result_first("select id from %t where appid = %s and tid = %d ",array($this->_table,$setarr['appid'],$setarr['tid']))){
            return $id;
        }else{
          if(parent::insert($setarr,1)){
              C::t('pichome_vapp_tag')->insert($setarr);
          }
        }
        return true;
    }


    public function add_hots_by_tid_appid($tid,$appid){
        $tagappdata =DB::fetch_first("select * from %t where tid = %d and appid = %s",array($this->_table,$tid,$appid));
        $return = false;
        if(!$tagappdata){
            $setarr = ['appid'=>$appid,'tid'=>$tid,'hots'=>1];
            $return =  parent::insert($setarr);
        }else{
            $setarr['hots'] = intval($tagappdata['hots']) +1;
            $return =  parent::update($tagappdata['id'],$setarr);
        }
        if($return) C::t('pichome_vapp_tag')->add_hots_by_tid_appid($tid,$appid);
        return true;
    }

    public function delete_by_tid_appid($tid,$appid){
        $tagappdata =DB::fetch_first("select * from %t where tid = %d and appid = %s",array($this->_table,$tid,$appid));
        if(!$tagappdata) return false;
        $return = false;
        if($tagappdata['hots'] > 1){
            $return =  parent::update($tagappdata['id'],array('hots'=>$tagappdata['hots']-1));
        }else{
            //当该标签使用数为0时不删除当前标签
            $return =  parent::delete($tagappdata['id']);
            //parent::update($tagappdata['id'],array('hots'=>0));
        }
        if($return) C::t('pichome_vapp_tag')->delete_by_tid_appid($tid,$appid);
        return true;
    }

    public function delete_tag_by_tid_appid($tid,$appid){
        //删除标签文件关系
        C::t('pichome_resourcestag')->delete_by_appid_tid($appid,$tid);
        //删除标签分类关系
        //C::t('pichome_tagrelation')->delete_by_tpid($tid,$appid);
        //删除当前标签
        if($id = DB::result_first("select id from %t where appid = %s and tid = %d ",array($this->_table,$appid,$tid))){
            return parent::delete($id);
        }
        return true;
    }
    public function delete_by_appid($appid){

        foreach(DB::fetch_all("select id from %t where appid = %s",array($this->_table,$appid)) as $v){
             parent::delete($v['id']);
        }
        return true;
    }

}
