<?php
    if(!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    class table_pichome_folderresources extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_folderresources';
            $this->_pk = 'id';
            $this->_pre_cache_key = 'pichome_folderresources';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        public function insert($setarr){

            if($id = DB::result_first("select id from %t where rid = %s and fid = %s and appid = %s",array($this->_table,$setarr['rid'],$setarr['fid'],$setarr['appid']))){
                $rid = $setarr['rid'];
                unset($setarr['rid']);
               return  parent::update($rid,$setarr);
            }
           if(parent::insert($setarr)){
               $ofids = DB::result_first("select fids from %t where rid = %s",array('pichome_resources',$setarr['rid']));
               $ofidarr = explode(',',$ofids);
               if(!in_array($setarr['fid'],$ofidarr)){
                   $ofidarr[] = $setarr['fid'];
               }
               $fids = implode(',',$ofidarr);
               C::t('pichome_resources')->update_by_rids($setarr['appid'],$setarr['rid'],['fids'=>$fids,'lastdate'=>TIMESTMP]);
               C::t('pichome_folder')->add_filenum_by_fid($setarr,1);
           }

        }
        public function delete_by_appid($appid){
            $rids=[];
            foreach(DB::fetch_all("select rid from %t where appid = %s",array($this->_table,$appid)) as $v){
                $rids[] = $v['rid'];
            }
            return parent::delete($rids);
        }

        public function delete_by_fids($fids){
            if(!is_array($fids)) $fids = (array)$fids;
            foreach(DB::fetch_all("select * from %t where fid in(%n)",array($this->_table,$fids)) as $v){
                $ofids = DB::result_first("select fids from %t where rid = %s",array('pichome_resources',$v['rid']));
                $ofidarr = explode(',',$ofids);
                $fidarr = array_diff($ofids,$fids);
                $fids = implode(',',$fidarr);
                C::t('pichome_resources')->update_by_rids($v['appid'],$v['rid'],['fids'=>$fids,'lastdate'=>TIMESTMP]);
                parent::delete($v['id']);
            }
        }
        public function delete_by_ridfid($rids,$fids){
            if(!is_array($rids)) $rids = (array)$rids;
            if(!is_array($fids)) $fids = (array)$fids;
            $dids = [];
            //print_r(DB::fetch_all("select * from %t where rid in(%n) and fid in(%n)",array($this->_table,$rids,$fids)));die;
            foreach(DB::fetch_all("select * from %t where rid in(%n) and fid in(%n)",array($this->_table,$rids,$fids)) as $v){
                $dids[] = $v['id'];
                $rdata = DB::fetch_first("select fids,isdelete from %t where rid = %s",array('pichome_resources',$v['rid']));
                $fidarr = explode(',',$rdata['fids']);
                $dindex = array_search($v['fid'],$fidarr);
                unset($fidarr[$dindex]);
                $fids = implode(',',$fidarr);
                C::t('pichome_resources')->update_by_rids($v['appid'],$v['rid'],['fids'=>$fids,'lastdate'=>TIMESTMP]);
                //减少目录文件数
                if(!$rdata['isdelete'])C::t('pichome_folder')->add_filenum_by_fid($v['fid'],-1);

            }
            return parent::delete($dids);
        }
        //移除文件目录关系
        public function delete_by_rid($rid){
            if(!is_array($rid)) $rid = (array) $rid;
            $dids = [];
            foreach(DB::fetch_all("select id from %t where rid in(%n)",array($this->_table,$rid)) as $v){
                $dids[] = $v['id'];
            }
            return parent::delete($dids);
        }
        
        public function fetch_id_by_rid($rid){
            $ids = [];
            foreach(DB::fetch_all("select id from %t where rid = %s",array($this->_table,$rid)) as $v){
                $ids[] = $v['id'];
            }
            return $ids;
        }
        public function get_sum_by_fid($fid){
            return DB::result_first("select count(rid) from %t where fid=%s",array($this->_table,$fid));
        }
        public function get_sum_by_fids($fids){
            if(!is_array($fids)) $fids = (array) $fids;
            $datas = [];
            foreach(DB::fetch_all("select fid,count(rid) as num  from %t where fid in(%n) group by fid",array($this->_table,$fids)) as $val){
                $datas[$val['fid']]= $val['num'];
            }
            return $datas;
        }
        public function get_foldername_by_rid($rid){
            $foldernames = [];
            foreach(DB::fetch_all("select f.fid,f.fname,pathkey from %t  fr left join %t f on f.fid=fr.fid and !isnull(fr.id) where rid = %s",array($this->_table,'pichome_folder',$rid)) as $v){
                if(isset($v['fname'])) $foldernames[$v['fid']] = ['fname'=>$v['fname'],'pathkey'=>$v['pathkey']];
            }
            return $foldernames;
        }
        public function fetch_rid_by_fids($fids,$limit = 6,$rid=''){
            if(!is_array($fids)) $fids = (array) $fids;
            $rids = [];
            foreach(DB::fetch_all("select distinct  rid from %t where fid in(%n) and rid != %s limit 0,$limit",array($this->_table,$fids,$rid)) as $v){
                $rids[] = $v['rid'];
            }
            return $rids;
        }
    }