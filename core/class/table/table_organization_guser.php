<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if(!defined('IN_OAOOA')) {
	exit('Access Denied');
}

class table_organization_guser extends dzz_table
{
	public function __construct() {

		$this->_table = 'organization_guser';
		$this->_pk    = 'id';
		$this->_pre_cache_key = 'organization_guser_';
        $this->_cache_ttl = 0;
		parent::__construct();
	}
	// $ou > 0 时 同时插入用户表
	public function insert_by_orgid($orgid,$uids,$admintype=0){
		if(!is_array($uids)) $uids=array($uids);
		$ret=array();
		foreach($uids as $uid){
			$setarr=array("orgid"=>$orgid,
						  'guid'=>$uid,
						  'opuid'=>getglobal('uid'),
						  'dateline'=>TIMESTAMP,
						  'admintype'=>$admintype
			);
			if(parent::insert($setarr,1,1)){
				$ret[]='g_'.$uid;
			}
		}
		return $ret;
	}
	public function insert($guid, $orgid,$admintype = 0) {
		if(!$guid || !$orgid) return 0;
		
		if($id=DB::result_first('select id from %t where orgid=%d and guid=%d',array($this->_table,$orgid,$guid))){
			parent::update($id,array("orgid"=>$orgid,'guid'=>$guid,'opuid'=>getglobal('uid'),'dateline'=>TIMESTAMP,'admintype'=>$admintype));
		}else{
			$id=parent::insert(array("orgid"=>$orgid,'guid'=>$guid,'opuid'=>getglobal('uid'),'dateline'=>TIMESTAMP,'admintype'=>$admintype),1);
		}
		return $id;
	}
	public function fetch_orgids_by_guid($guids){
		if(!is_array($guids)) $guids=array($guids);
		$orgids=array();
		foreach(DB::fetch_all("select orgid from %t where guid IN(%n)",array($this->_table,$guids)) as $value){
			$orgids[$value['orgid']]=$value['orgid'];
		}
		return array_values($orgids);
	}
	public function bind_guid_and_orgid($guid,$orgids,$admintype=0){
		if(!$guid || !$orgids) return 0;
		if(!is_array($orgids)) $orgids=array($orgids);
		$ret=0;
		foreach($orgids as $orgid){
			if(self::insert($guid,$orgid,$admintype)){
				$ret++;
			}
		}
		return $ret;
		
	}
	public function delete_by_guid($guids) {
		if(!is_array($guids)) $guids=array($guids);
		$ids=array();
		foreach(DB::fetch_all("select id from %t where guid IN (%n)",array($this->_table,$guids)) as $value){
			$ids[]=$value['id'];
		}
		return  parent::delete($ids);
	}
	public function update_perm($guid,$orgid,$admintype){
		
		if($id=DB::result_first("select id from %t where orgid = %d and guid = %d",array($this->_table,$orgid,$guid))){
			return parent::update($id,array('orgid'=>$orgid,'guid'=>$guid,'admintype'=>$admintype));
		}else{
			return self::insert($guid,$orgid,$admintype);
		}

	}
	public function delete_by_id($id,$force=false){
		$data=self::fetch($id);
        if(!$force && $data['admintype'] == 2) return false;
		return parent::delete($id);
	}



	public function delete_by_orgid($orgids) {
		$orgids=(array)$orgids;
		$ids=array();
		foreach(DB::fetch_all("select id from %t where orgid IN (%n) ",array($this->_table,$orgids)) as $value){
			$ids[]=$value['id'];
		}
		return parent::delete($ids);
	}
	public function delete_by_uid_orgid($guids,$orgid) {
		if(!is_array($guids)) $guids=array($guids);
		$ids=array();
		foreach(DB::fetch_all("select id from %t where orgid=%d and guid IN (%n)",array($this->_table,$orgid,$guids)) as $value){
			$ids[]=$value['id'];
		}
		return parent::delete($ids);
	}
	
	public function fetch_guids_by_orgid($orgids){
		$guids=array();
		if(!is_array($orgids)) $orgids=array($orgids);
		$query=DB::query("select uid from %t where orgid IN(%n)",array($this->_table,$orgids));
		while($value=DB::fetch($query)){
			$guids[]=$value['guid'];
		}
		return $guids;
	}
	
	public function fetch_moderators_by_orgid($orgids,$count=false){
		if(!is_array($orgids)) $orgids=array($orgids);
		if($count) return DB::result_first("select COUNT(*) from %t where orgid IN (%n) and admintype>0",array($this->_table,$orgids));
		return DB::fetch_all("select u.*  from ".DB::table($this->_table). " o LEFT JOIN ".DB::table('organization')." u ON o.uid=u.uid where o.orgid IN(".dimplode($orgids).") and o.admintype>0 order by o.dateline DESC");
	}

	public function fetch_guser_by_orgid($orgids, $limit = 0, $count = false,$limitsql = '')
    {
        if (!is_array($orgids)) $orgids = array($orgids);
        if ($limit && !$limitsql) $limitsql = "limit $limit";

        if ($count) return DB::result_first("select COUNT(*) from  %t where orgid IN(%n)", array($this->_table, $orgids));
        return DB::fetch_all("select gu.guid,gu.admintype,o.orgname from %t gu LEFT JOIN %t o ON o.orgid=gu.guid where gu.orgid IN(%n) order by gu.dateline DESC $limitsql ",array($this->_table,'organization',$orgids));
    }




}
