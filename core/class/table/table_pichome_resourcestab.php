<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_resourcestab extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_resourcestab';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_resourcestab';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }

    public function insert($setarr)
    {
        if ($id = DB::result_first("select id from %t where tid = %d and rid = %s", array($this->_table, $setarr['tid'],$setarr['rid']))) {
            parent::update($id, $setarr);
        } else {
            $id=parent::insert($setarr,1);
            if($id){
                $indexarr = ['rid'=>$setarr['rid'],'tid'=>$setarr['tid'],'gid'=>$setarr['gid'],'type'=>0];
                Hook::listen('changefiletabafter',$indexarr);
            }
        }
        return $id;
    }

    //获取文件所有的卡片信息
    public function fetch_tid_by_rid($rid){
        $tagdata = [];
        foreach(DB::fetch_all("select tid from %t  where rid = %s",array($this->_table,$rid)) as $v){
            $tagdata[] = $v['tid'];
        }
        return $tagdata;
    }
    public  function delete_by_rids_tids($rids,$tids){
        if(!is_array($rids)) $rids = (array)$rids;
        if(!is_array($tids)) $rids = (array)$tids;
        foreach(DB::fetch_all("select id,rid,tid,gid from %t where rid in(%n) and tid in (%n)",array($this->_table,$rids,$tids)) as $v){
            $dids[] = $v['id'];
            $indexarr = ['rid'=>$v['rid'],'tid'=>$v['tid'],'gid'=>$v['gid'],'type'=>1];
            Hook::listen('changefiletabafter',$indexarr);
        }
        return parent::delete($dids);
    }

    //根据appid删除数据
    public function delete_by_appid($appid)
    {
        $dids = [];
        foreach (DB::fetch_all("select id from %t where appid = %s", array($this->_table, $appid)) as $v) {
            $dids[] = $v['id'];
        }
        return parent::delete($dids);
    }
    public function delete_by_rid($rid)
    {
        if(!is_array($rid)) $rid = (array)$rid;
        $delids = [];
        foreach (DB::fetch_all("select id from %t where rid in(%n)", array($this->_table, $rid)) as $v) {
            $delids[] = $v['id'];
        }
        return parent::delete($delids);
    }


    public function fetch_rids_by_tids($tids,$appid,$limit=6,$rid=''){
        if(!is_array($tids)) $tids = (array) $tids;
        $rids = [];
        foreach(DB::fetch_all("select  distinct  rid from %t where tid in(%n)  and rid != %s  and appid = %s limit 0,$limit",array($this->_table,$tids,$rid,$appid)) as $v){
            $rids[] = $v['rid'];
        }
        return $rids;
    }

    public function delete_by_ridtid($rid,$tids){
        if(!is_array($tids)) $tids = (array) $tids;
        $ids = [];
        foreach(DB::fetch_all("select id from %t where rid = %s and tid in(%n)",array($this->_table,$rid,$tids)) as $v){
            $ids[] = $v['id'];
        }
        return parent::delete($ids);
    }

    public function delete_by_appid_tid($appid,$tids){
        if(!is_array($tids)) $tids = (array) $tids;
        $ids = [];
        foreach(DB::fetch_all("select id from %t where appid = %s and tid in(%n)",array($this->_table,$appid,$tids)) as $v){
            $ids[] = $v['id'];
        }
        return parent::delete($ids);
    }

    public function delete_by_gid($gid){
        $ids = [];
        foreach(DB::fetch_all("select id from %t where gid=%d",array($this->_table,$gid)) as $v){
            $ids[] = $v['id'];
        }
        return parent::delete($ids);
    }

    public function fetch_num_by_tid($tid){
        return DB::result_first("select count(distinct rid) from %t where tid =%d",array($this->_table));
    }
}
