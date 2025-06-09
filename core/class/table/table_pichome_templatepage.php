<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_templatepage extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_templatepage';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_templatepage';
        //$this->_cache_ttl = 3600;
        parent::__construct();
    }
    //新建或修改单页
    public function insertdata($setarr){

        if($setarr['id']){
            Hook::listen('lang_parse',$setarr,['setAlonepageLangData']);
            parent::update($setarr['id'],$setarr);
            return $setarr['id'];
        }else{
            if($id = parent::insert($setarr,1)){
                $setarr['id'] = $id;
                Hook::listen('lang_parse',$setarr,['setAlonepageLangData']);
                return $setarr['id'];
            }
        }
    }
    public function delete_by_id($id){
        if(!$id) return true;
        if(C::t('pichome_templatetag')->delete_by_pageid($id)){
            C::t('pichome_route')->delete_by_abid($id,0);
            if(parent::delete($id)){
                HOOK::listen('lang_parse',$id,['delAlonepageLangData']);
                return  true;
            }
        }else{
            return false;
        }

    }

    public function fetch_data_by_id($id){
        $pagedata = parent::fetch($id);
        Hook::listen('lang_parse',$pagedata,['getAlonepageLangData']);
        if(!$pagedata) return [];
        $pagedata['data'] = C::t('pichome_templatetag')->fetch_by_pageid($id);
        return $pagedata;
    }
    //获取单页数据及其标签位
    public function fetch_pagedata_by_id($id){
        $id = intval($id);
        if(!$pagedata = parent::fetch($id)) return [];
        Hook::listen('lang_parse',$pagedata,['getAlonepageLangData']);
        $pagedata['tags'] = C::t('pichome_templatetag')->fetch_tag_by_pageid($id);
        return $pagedata;
    }

}