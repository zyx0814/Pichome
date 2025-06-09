<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_folder_tag extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_folder_tag';
        $this->_pk = 'id';
        parent::__construct();
    }
    public function insert($setarr)
    {
        if($id = DB::result_first("select id from %t where appid = %s and tid = %d ",array($this->_table,$setarr['appid'],$setarr['tid']))){
            return $id;
        }else{
            $id = parent::insert($setarr,1);
        }
        return $id;
    }


    public function add_hots_by_tid_appid($tid,$appid){
        $tagappdata =DB::fetch_first("select * from %t where tid = %d and appid = %s",array($this->_table,$tid,$appid));
        $return = false;
        if(!$tagappdata){
            $setarr = ['appid'=>$appid,'tid'=>$tid,'hots'=>1];
             $return = parent::insert($setarr);
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
            $return =  parent::delete($tagappdata['id']);
        }
        if($return) C::t('pichome_vapp_tag')->delete_by_tid_appid($tid,$appid);
        return true;
    }

    public function delete_tag_by_tid_appid($tid,$appid){
        //删除标签目录关系
        C::t('pichome_foldertag')->delete_by_appid_tid($appid,$tid);
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
