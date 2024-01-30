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
    public function insert($setarr)
    {
        if($id = DB::result_first("select id from %t where appid = %s and tid = %d ",array($this->_table,$setarr['appid'],$setarr['tid']))){
            return $id;
        }else{
           if(parent::insert($setarr)){
               C::t('pichome_folder_tag')->insert($setarr);
               C::t('pichome_resources_tag')->insert($setarr);
           }
        }

    }


    public function add_hots_by_tid_appid($tid,$appid){
        $tagappdata =DB::fetch_first("select * from %t where tid = %d and appid = %s",array($this->_table,$tid,$appid));
        if(!$tagappdata){
            $setarr = ['appid'=>$appid,'tid'=>$tid,'hots'=>1];
            if(parent::insert($setarr)){
                C::t('pichome_tag')->add_hots_by_tid($tid);
            }
        }else{
            $setarr['hots'] = intval($tagappdata['hots']) +1;
            if(parent::update($tagappdata['id'],$setarr)){
                C::t('pichome_tag')->add_hots_by_tid($tid);
            }
        }
        return true;
    }

    public function delete_by_tid_appid($tid,$appid){
        $tagappdata =DB::fetch_first("select * from %t where tid = %d and appid = %s",array($this->_table,$tid,$appid));
        if(!$tagappdata) return false;
        //次数为0时不删除标签
        if($tagappdata['hots'] >= 1){
            if(parent::update($tagappdata['id'],array('hots'=>$tagappdata['hots']-1))){
                C::t('pichome_tag')->delete_by_tid($tid);
            }
        }
        /*if($tagappdata['hots'] > 1){
            if(parent::update($tagappdata['id'],array('hots'=>$tagappdata['hots']-1))){
                C::t('pichome_tag')->delete_by_tid($tid);
            }
        }else{
            if(parent::delete($tagappdata['id'])){
                C::t('pichome_tag')->delete_by_tid($tid);
            }
        }*/
        return true;
    }

    public function delete_tag_by_tid_appid($tid,$appid){
        //删除标签目录和文件关系，此处自动处理库标签数字和删除
        C::t('pichome_resources_tag')->delete_tag_by_tid_appid($tid,$appid);
        C::t('pichome_folder_tag')->delete_tag_by_tid_appid($tid,$appid);
        //删除标签分类关系
        C::t('pichome_tagrelation')->delete_by_tpid($tid,$appid);
        //删除当前标签
        if($id = DB::result_first("select id from %t where appid = %s and tid = %d ",array($this->_table,$appid,$tid))){
            if(parent::delete($id)){
                C::t('pichome_tag')->delete_by_tid($tid,0);
            }
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
