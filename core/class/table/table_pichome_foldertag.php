<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_foldertag extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_foldertag';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_foldertag';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }

    public function insert($setarr)
    {
        if ($id = DB::result_first("select id from %t where tid = %d and fid = %s", array($this->_table, $setarr['tid'],$setarr['fid']))) {
            parent::update($id, $setarr);
        } else {
            if($id=parent::insert($setarr,1)){

                C::t('pichome_folder_tag')->add_hots_by_tid_appid($setarr['tid'],$setarr['appid']);
            }

        }
        return $id;
    }

    public function fetch_tag_by_fid($fid){
        $tagdata = [];
        foreach(DB::fetch_all("select t.tid,t.tagname from %t ft left join %t  t on ft.tid = t.tid where ft.fid = %s",
            array($this->_table,'pichome_tag',$fid)) as $v){
            $tagdata[$v['tid']] = $v['tagname'];
        }
        return $tagdata;
    }
    public function fetch_id_by_fid($fid)
    {
        $ids = [];
        foreach(DB::fetch_all("select id from %t where fid = %s",array($this->_table,$fid)) as $v){
            $ids[] = $v['id'];

        }
        return $ids;
    }
    public  function delete_by_fids_tids($fids,$tids){
        if(!is_array($fids)) $fids = (array)$fids;
        if(!is_array($tids)) $fids = (array)$tids;
        foreach(DB::fetch_all("select id,tid,appid from %t where fid in(%n) and tid in (%n)",array($this->_table,$fids,$tids)) as $v){
            $dids[] = $v['id'];
            C::t('pichome_tag')->delete_by_tid($v['tid']);
            C::t('pichome_folder_tag')->delete_by_tid_appid($v['tid'],$v['appid']);
        }
        return parent::delete($dids);
    }
    //根据appid删除数据
    public function delete_by_appid($appid)
    {
        $dids = [];
        foreach (DB::fetch_all("select id,tid,appid from %t where appid = %s", array($this->_table, $appid)) as $v) {
            $dids[] = $v['id'];
            C::t('pichome_tag')->delete_by_tid($v['tid']);
            C::t('pichome_folder_tag')->delete_by_tid_appid($v['tid'],$v['appid']);
        }
        return parent::delete($dids);
    }
    public function delete_by_fid($fid)
    {
        if(!is_array($fid)) $fid = (array)$fid;
        $delids = [];
        foreach (DB::fetch_all("select id,tid,appid from %t where fid in(%n)", array($this->_table, $fid)) as $v) {
            $delids[] = $v['id'];
            C::t('pichome_tag')->delete_by_tid($v['tid']);
        }
        return parent::delete($delids);
    }


    public function fetch_fids_by_tids($tids,$appid,$limit=6,$fid=''){
        if(!is_array($tids)) $tids = (array) $tids;
        $fids = [];
        foreach(DB::fetch_all("select  distinct  fid from %t where tid in(%n)  and fid != %s  and appid = %s limit 0,$limit",array($this->_table,$tids,$fid,$appid)) as $v){
            $fids[] = $v['fid'];
        }
        return $fids;
    }
    public function fetch_all_tag_by_fids($fids){
        $data = [];
        foreach(DB::fetch_all("select ft.fid,ft.tid,t.tagname from %t ft
            left join %t t on ft.tid = t.tid where ft.fid in(%n)",array($this->_table,'pichome_tag',$fids)) as $v){
            $data[$v['fid']][]= $v['tagname'];
        }
        return $data;
    }

    public function delete_by_fidtid($fid,$tids){
        if(!is_array($tids)) $tids = (array) $tids;
        $ids = [];
        foreach(DB::fetch_all("select id,tid from %t where fid = %s and tid in(%n)",array($this->_table,$fid,$tids)) as $v){
            $ids[] = $v['id'];
            C::t('pichome_tag')->delete_by_tid($v['tid']);
            C::t('pichome_folder_tag')->delete_by_tid_appid($v['tid'],$v['appid']);
        }
        return parent::delete($ids);
    }

    public function delete_by_appid_tid($appid,$tids){
        if(!is_array($tids)) $tids = (array) $tids;
        $fids = [];
        $ids = [];
        foreach(DB::fetch_all("select id,tid,fid from %t where appid = %s and tid in(%n)",array($this->_table,$appid,$tids)) as $v){
            $ids[] = $v['id'];
            $fids[] = $v['fid'];
            C::t('pichome_tag')->delete_by_tid($v['tid']);
            C::t('pichome_folder_tag')->delete_by_tid_appid($v['tid'],$v['appid']);
        }
        $solrdata = ['appid'=>$appid,'fids'=>$fids];
        Hook::listen('updatedataafter',$solrdata);
        return parent::delete($ids);
    }
}
