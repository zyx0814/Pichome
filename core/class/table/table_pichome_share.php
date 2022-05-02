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
        
        public function add_share($rid){
            if(!$data = C::t('pichome_resources')->fetch($rid)) return false;
            $setarr = [
                'title'=>$data['name'],
                'filepath'=>$rid,
                'appid'=>$data['appid'],
                'dateline'=>TIMESTAMP,
                'uid'=>getglobal('uid') ? getglobal('uid'):0
            ];
            if($id = DB::result_first("select id from %t where filepath = %s and uid = %d",array($this->_table,$setarr['filepath'],$setarr['uid']))){
                return  C::t('shorturl')->getShortUrl(getglobal('siteurl').'index.php?mod=pichome&op=share&sid='.dzzencode($id,'',0,0));
            }else{
                if($id = parent::insert($setarr,1)){
                    return  C::t('shorturl')->getShortUrl(getglobal('siteurl').'index.php?mod=pichome&op=share&sid='.dzzencode($id,'',0,0));
                }
            }
            return false;
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
        public function fetch_by_id($id){
            if(!$data =parent::fetch($id)) return false;
            $resourcesdata  = C::t('pichome_resources')->fetch_by_rid($data['filepath']);
            if(empty($resourcesdata)){
                parent::delete($id);
                return false;
            }else{
                $data['resourcesdata'] = $resourcesdata;
            }
            return $data;
        }
  
    }