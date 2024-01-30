<?php
    if(!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    class table_pichome_share extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_share';
            $this->_pk = 'id';
            $this->_pre_cache_key = 'pichome_share';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        
        public function add_share($rid,$stype =0){
            if(!$stype && !$data = C::t('pichome_resources')->fetch($rid)) return false;
            if($stype == 1 && !$data=C::t('pichome_collectlist')->fetch($rid)) return false;

            if($stype == 2 && !$data =C::t('pichome_collect')->fetch($rid)) return false;
            $setarr = [
                'title'=>$data['name'],
                'filepath'=>$rid,
                'appid'=>($stype < 2) ? $data['appid']:'',
                'clid'=>isset($data['clid']) ? $data['clid']:0,
                'dateline'=>TIMESTAMP,
                'uid'=>getglobal('uid') ? getglobal('uid'):0,
                'stype'=>$stype
            ];
            if($id = DB::result_first("select id from %t where filepath = %s and uid = %d and stype = %d ",array($this->_table,$setarr['filepath'],$setarr['uid'],$setarr['stype']))){

            }else{
                $id = parent::insert($setarr,1);

            }
            if($stype == 2){
                $shareurl = C::t('shorturl')->getShortUrl(getglobal('siteurl').'index.php?mod=collection&op=share&sid='.dzzencode($id,'',0,0));

            }else{
                $shareurl = C::t('shorturl')->getShortUrl(getglobal('siteurl').'index.php?mod=pichome&op=share&sid='.dzzencode($id,'',0,0));
            }
            return $shareurl;

        }
        public function delete_by_riduid($rid){
            $uid = getglobal('uid') ? getglobal('uid'):0;
            $id = DB::result_first("select id from %t where filepath = %s and uid = %d",array($this->_table,$rid,$uid));
            if($id) parent::delete($id);
            return true;
        }
        public function delete_by_rid($rid){
            if(!is_array($rid)) $rid = (array) $rid;
            $uid = getglobal('uid') ? getglobal('uid'):0;
            $ids = [];
            foreach(DB::fetch_all("select id from %t where filepath in(%n) ",array($this->_table,$rid,$uid)) as $v){
                $ids[] = $v['id'];
            }
            if(!empty($ids)) parent::delete($ids);
            return true;
        }
        public function fetch_by_idandtype($id,$stype=0){
            if($stype != 2){
                $data =parent::fetch($id);
            }else{
                $data = DB::fetch_first("select * from %t where id = %d and stype = %d",array($this->_table,$id,$stype));
                return $data;
            }
            if(!$data) return false;
            if($data['stype'] == 0){

                $resourcesdata  = C::t('pichome_resources')->fetch_by_rid($data['filepath'],1);
            }elseif($data['stype'] == 1){
                $rid = DB::result_first("select rid from %t where id = %d",array('pichome_collectlist',$data['filepath']));
                $resourcesdata  = C::t('pichome_resources')->fetch_by_rid($rid);
            }

            if(empty($resourcesdata)){
                parent::delete($id);
                return false;
            }else{
                $data['resourcesdata'] = $resourcesdata;
            }
            return $data;
        }
  
    }