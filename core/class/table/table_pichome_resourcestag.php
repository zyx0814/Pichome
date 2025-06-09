<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    
    class table_pichome_resourcestag extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_resourcestag';
            $this->_pk = 'id';
            $this->_pre_cache_key = 'pichome_resourcestag';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        
        public function insert($setarr)
        {
            if ($id = DB::result_first("select id from %t where tid = %d and rid = %s", array($this->_table, $setarr['tid'],$setarr['rid']))) {
                parent::update($id, $setarr);
            } else {
               $id=parent::insert($setarr,1);
                //C::t('pichome_tag')->add_hots_by_tid($setarr['tid']);
                C::t('pichome_resources_tag')->add_hots_by_tid_appid($setarr['tid'],$setarr['appid']);
                $indexarr = ['rid'=>$setarr['rid'],'tid'=>$setarr['tid'],'type'=>0];
                Hook::listen('changefiletagafter',$indexarr);
            }
            return $id;
        }
        
        public function fetch_tag_by_rid($rid){
            $tagdata = [];
            foreach(DB::fetch_all("select t.tid,t.tagname from %t rt left join %t  t on rt.tid = t.tid where rt.rid = %s",array($this->_table,'pichome_tag',$rid)) as $v){
                Hook::listen('lang_parse',$v,['getTagLangData']);
                $tagdata[$v['tid']] = $v['tagname'];
            }
            return $tagdata;
        }
        public function fetch_id_by_rid($rid)
        {
            $ids = [];
            foreach(DB::fetch_all("select id from %t where rid = %s",array($this->_table,$rid)) as $v){
                $ids[] = $v['id'];

            }
            return $ids;
        }
        public  function delete_by_rids_tids($rids,$tids){
            if(!is_array($rids)) $rids = (array)$rids;
            if(!is_array($tids)) $rids = (array)$tids;
            foreach(DB::fetch_all("select id,tid,appid,rid from %t where rid in(%n) and tid in (%n)",array($this->_table,$rids,$tids)) as $v){
                $dids[] = $v['id'];
                C::t('pichome_resources_tag')->delete_by_tid_appid($v['tid'],$v['appid']);
                $indexarr = ['tid'=>$v['tid'],'rid'=>$v['rid'],'type'=>1];
                Hook::listen('changefiletagafter',$indexarr);
            }
            return parent::delete($dids);
        }
        //根据appid删除数据
        public function delete_by_appid($appid)
        {
            $dids = [];
            foreach (DB::fetch_all("select id,tid,appid from %t where appid = %s", array($this->_table, $appid)) as $v) {
                $dids[] = $v['id'];
               // C::t('pichome_tag')->delete_by_tid($v['tid']);
                C::t('pichome_resources_tag')->delete_by_tid_appid($v['tid'],$v['appid']);
            }
            return parent::delete($dids);
        }
        public function delete_by_rid($rid)
        {
            if(!is_array($rid)) $rid = (array)$rid;
            $delids = [];
            foreach (DB::fetch_all("select id,tid,appid from %t where rid in(%n)", array($this->_table, $rid)) as $v) {
                $delids[] = $v['id'];
                //C::t('pichome_tag')->delete_by_tid($v['tid']);
                C::t('pichome_resources_tag')->delete_by_tid_appid($v['tid'],$v['appid']);
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
        public function fetch_all_tag_by_rids($rids){
            $data = [];
            foreach(DB::fetch_all("select rt.rid,rt.tid,t.tagname from %t rt
            left join %t t on rt.tid = t.tid where rt.rid in(%n)",array($this->_table,'pichome_tag',$rids)) as $v){
                $data[$v['rid']][]= $v['tagname'];
            }
            return $data;
        }
        
        public function delete_by_ridtid($rid,$tids){
            if(!is_array($tids)) $tids = (array) $tids;
            $ids = [];
            foreach(DB::fetch_all("select id,tid,appid from %t where rid = %s and tid in(%n)",array($this->_table,$rid,$tids)) as $v){
                $ids[] = $v['id'];
                C::t('pichome_tag')->delete_by_tid($v['tid']);
                C::t('pichome_resources_tag')->delete_by_tid_appid($v['tid'],$v['appid']);
            }
            return parent::delete($ids);
        }

        public function delete_by_appid_tid($appid,$tids){
            if(!is_array($tids)) $tids = (array) $tids;
            $ids = [];
            foreach(DB::fetch_all("select id,tid,rid from %t where appid = %s and tid in(%n)",array($this->_table,$appid,$tids)) as $v){
                $ids[] = $v['id'];
                $rids[] = $v['rid'];
                C::t('pichome_tag')->delete_by_tid($v['tid']);
                C::t('pichome_resources_tag')->delete_by_tid_appid($v['tid'],$v['appid']);
            }
            $solrdata = ['appid'=>$appid,'rids'=>$rids];
            Hook::listen('updatedataafter',$solrdata);
            return parent::delete($ids);
        }
    }
