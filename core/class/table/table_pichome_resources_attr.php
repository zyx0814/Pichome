<?php
    if(!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    class table_pichome_resources_attr extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_resources_attr';
            $this->_pk = 'rid';
            $this->_pre_cache_key = 'pichome_resourcesattr';
			$this->_cache_ttl = 3600;
            parent::__construct();
        }
      	
      	public function insert($setarr){
      		$rid = trim($setarr['rid']);
      		if($attrdata = parent::fetch($rid)){
      			unset($setarr['rid']);
      			return parent::update($rid,$setarr);
      		}else{
      			return parent::insert($setarr);
      		}
      	}
      	//删除文件属性数据
      	public function delete_by_rid($rids){
            if(!is_array($rids)) $rids = (array)$rids;
            $aids = [];
            $tids = [];
            foreach(DB::fetch_all("select * from %t where rid in(%n) ",array($this->_table,$rids)) as $v){
                $tids= array_merge($tids,explode(',',$v['tags']));
            }
            $tids = array_unique($tids);
            foreach ($tids as $tid){
                C::t('pichome_tag')->delete_by_tid($tid);
            }
            return parent::delete($rids);
        }
        
        public function fetch_by_rids($rids){
            if(!is_array($rids)) $rids = (array)$rids;
            $data = [];
            foreach(parent::fetch_all($rids) as $v){
                $data[$v['rid']] = $v;
            }
            return $v;
        }
        public function fetch_rids_by_link($link,$appid,$limit=6,$rid=''){
            $rids = [];
            foreach(DB::fetch_all("select distinct rid from %t where link =%s and rid !=%s  and appid = %s limit 0,$limit",array($this->_table,$link,$rid,$appid)) as $v){
                $rids[] = $v['rid'];
            }
            return $rids;
        }
        public function fetch_like_words($keyword,$limit=10){
            $likewords = [];
            $presql = " case when 'desc' like %s then 3 when 'desc' like %s then 2 when 'desc' like %s then 1 end as rn";
            $wheresql = " 'desc' like %s";
            $params =    [$keyword . '%', '%' . $keyword,'%'.$keyword.'%',$this->_table,'%'.$keyword.'%'];
            foreach(DB::fetch_all("select 'desc',$presql from %t where $wheresql order by rn desc  limit 0,$limit",$params) as $v){
                $likewords[] = $v['desc'];
            }
            return $likewords;
        }
    }