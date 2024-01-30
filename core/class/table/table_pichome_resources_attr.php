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
      			if(parent::update($rid,$setarr)){
                    $hookindex = ['rids'=>$setarr['rid'],'appid'=>$attrdata['appid']];
                    Hook::listen('updatedataafter',$hookindex);
                }
      		}else{
      			 if(parent::insert($setarr)){
                     $hookindex = ['rids'=>$setarr['rid'],'appid'=>$setarr['appid']];
                     Hook::listen('updatedataafter',$hookindex);
                 }
      		}
      		return true;
      	}
      	//删除文件属性数据
      	public function delete_by_rid($rids){
            if(!is_array($rids)) $rids = (array)$rids;
            $aids = [];
            $tids = [];
            foreach(DB::fetch_all("select * from %t where rid in(%n) ",array($this->_table,$rids)) as $v){
                $tids= array_merge($tids,explode(',',$v['tags']));
                if(is_numeric($v['path'])){
                    C::t('attachment')->delete_by_aid($v['path']);
                    C::t('pichome_vapp')->addcopy_by_appid($v['appid'],-1);
                }
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
        public function update_by_rid($appid,$rid,$setarr){
            if(parent::update($rid,$setarr)){
                $this->update_searchval_by_rid($rid);
                $hookindex = ['rids'=>[$rid],'appid'=>$appid];
                Hook::listen('updatedataafter',$hookindex);
            }
            return true;
        }
        public function update_by_rids($appid,$rids,$setarr){
            if(!is_array($rids)) $rids = (array)$rids;
            foreach($rids as $v){
                $this->update_by_rid($appid,$v,$setarr);
            }
        }
        public function update_searchval_by_rid($rid){
            $searchval = '';
            $resourcesdata = C::t('pichome_resources')->fetch($rid);
            $searchval .= $resourcesdata['name'];
            $attrdata = parent::fetch($rid);
            $searchval .= $attrdata['link'].$attrdata['desc'];
            $tids = explode(',',$attrdata['tag']);
            foreach (DB::fetch_all("select tagname from %t where tid in(%n)",array('pichome_tag',$tids)) as $v){
                $searchval .= $v['tagname'];
            }
            foreach (DB::fetch_all("select annotation from %t where rid =%s",array('pichome_comments',$tids)) as $v){
                $searchval .= $v['annotation'];
            }
            parent::update($rid,array('searchval'=>$searchval));

        }
    }