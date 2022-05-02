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

class table_organization_admin extends dzz_table
{
	public function __construct() {

		$this->_table = 'organization_admin';
		$this->_pk    = 'id';
		$this->_pre_cache_key = 'organization_admin_';
        $this->_cache_ttl = 0;
		parent::__construct();
	}
	// $ou > 0 时 同时插入用户表
	public function insert_by_orgid($orgid,$uids,$ou=false){
		if(!is_array($uids)) $uids=array($uids);
		$ret=0;
		foreach($uids as $uid){
			$setarr=array("orgid"=>$orgid,
						  'uid'=>$uid,
						  'opuid'=>getglobal('uid'),
						  'dateline'=>TIMESTAMP,
						  'admintype'=>1
			);
			if(parent::insert($setarr,1,1)){
				$ret++;
			}
		}
		return $ret;
	}
	public function insert($uid, $orgid,$admintype = 1) {
		if(!$uid || !$orgid) return 0;
		if(!C::t('organization_user')->fetch_num_by_orgid_uid($orgid,$uid)){
            $ret = C::t('organization_user')->insert_by_orgid($orgid,$uid);
		}
		if($id=DB::result_first('select id from %t where uid=%d and orgid=%d',array($this->_table,$uid,$orgid))){
			parent::update($id,array("orgid"=>$orgid,'uid'=>$uid,'opuid'=>getglobal('uid'),'dateline'=>TIMESTAMP,'admintype'=>$admintype));
		}else{
			$id=parent::insert(array("orgid"=>$orgid,'uid'=>$uid,'opuid'=>getglobal('uid'),'dateline'=>TIMESTAMP,'admintype'=>$admintype),1);
		}
		return $id;
	}
	public function update_perm($uid,$orgid,$admintype){
		if(strpos($uid,'g_')===0){
			$guid=str_replace('g_','',$uid);
			return C::t('organization_guser')->update_perm($guid,$orgid,$admintype);
		}
		if($id=DB::result_first("select id from %t where orgid = %d and uid = %d",array($this->_table,$orgid,$uid))){
			if($admintype == 0){
				return parent::update($id,array('orgid'=>$orgid,'uid'=>$uid));
			}else{
				return parent::update($id,array('orgid'=>$orgid,'uid'=>$uid,'admintype'=>$admintype));
			}
		}else{
			return self::insert($uid,$orgid,$admintype);
		}

	}
	public function delete_by_id($id,$force=false){
		$data=self::fetch($id);
        if(!$force && $data['admintype'] == 2) return false;
		if($return=parent::delete($id)){
            self::update_groupid_by_uid($data['uid']);
		}
		return $return;
	}
	//判断是否具有当前部门或机构管理员权限
	public function is_admin_by_orgid($orgid,$uid){
		$cachekey='isadmin_'.$orgid.'_'.$uid;
		if ($data = $this->fetch_cache($cachekey)) {
			if($data=='is') return true;
			else return false;
		}
		$org=C::t('organization')->fetch($orgid);
		$currentpathkey = $org['pathkey'];
		$orgids = explode('-',str_replace('_','',$currentpathkey));
		if(DB::result_first("select max(admintype) from %t where orgid in (%n) and uid = %d",array($this->_table,$orgids,$uid)) > 0){
			$this->store_cache($cachekey, 'is');
			return true;
		}
		if($myorgids=C::t('organization_user')->fetch_orgids_by_uid($uid)){
			if($gids=C::t('organization')->fetch_parent_by_orgid($myorgids)){
				if(DB::result_first("select id from %t where orgid in (%n) and guid IN(%n) and admintype>0",array('organization_guser',$orgids,$gids)) > 0){
					$this->store_cache($cachekey, 'is');
					return true;
				}
			}
		}else{//无机构人员
			if(DB::result_first("select id from %t where orgid in (%n) and guid ='0' and admintype>0",array('organization_guser',$orgids)) > 0){
				$this->store_cache($cachekey, 'is');
				return true;
			}
		}
		$this->store_cache($cachekey, 'no');
		return false;
	}
	
	public function fetch_group_creater($orgid){
		if(!$orgid) return false;
		$uid = DB::result_first("select uid from %t where orgid = %d and admintype = %d",array($this->_table,$orgid,2));
		$username = DB::result_first("select username from %t where uid = %d ",array('user',$uid));
		return $username;
	}
	public function update_groupid_by_uid($uid){
	    return true;
		$user=getuserbyuid($uid);
		if($user['groupid']==1) return ;
		//判断当前用户是否仍为机构和部门管理员
		if(DB::result_first("select COUNT(*) from %t a left join %t o on o.orgid = a.orgid where a.uid=%d and o.type = 0 ",array($this->_table,'organization',$uid))){
			$groupid=2;
		}else{
			$groupid=9;
		}
		return C::t('user')->update($uid,array('groupid'=>$groupid));
	}
	public function delete_by_uid($uids) {
		if(!is_array($uids)) $uids=array($uids);
		$uuids=$guids=array();
		foreach($uids as $key =>$uid){
			if(strpos($uid,'g_')===0){
				$guids[]=intval(str_replace('g_','',$uid));
			}else{
				$uuids[]=$uid;
			}
		}
		$ret=0;
		if($uuids){
			$ids=array();
			foreach(DB::fetch_all("select id from %t where uid IN (%n)",array($this->_table,$uids)) as $value){
				$ids[]=$value['id'];
			}
			$ret+=parent::delete($ids);
		}
		if($guids){
			$ret+=C::t('organization_guser')->delete_by_guid($guids);
		}
		
		return  $ret;
	}
	public function delete_by_orgid($orgids) {
		$orgids=(array)$orgids;
		$ids=array();
		$ret=0;
		foreach(DB::fetch_all("select id from %t where orgid IN (%n) ",array($this->_table,$orgids)) as $value){
			$ids[]=$value['id'];
		}
		$ret+=parent::delete($ids);
		$ret+=C::t('organization_guser')->delete_by_orgid($orgids);
		return $ret;
	}
	public function delete_by_uid_orgid($uids,$orgid) {
		if(!is_array($uids)) $uids=array($uids);
		$uuids=$guids=array();
		foreach($uids as $key =>$uid){
			if(strpos($uid,'g_')===0){
				$guids[]=intval(str_replace('g_','',$uid));
			}else{
				$uuids[]=$uid;
			}
		}
		$ids=array();
		foreach(DB::fetch_all("select id from %t where orgid=%d and uid IN (%n)",array($this->_table,$orgid,$uuids)) as $value){
			$ids[]=$value['id'];
		}
		$ret=0;
		if($ids) $ret+=parent::delete($ids);
		if($guids)	$ret+=C::t('organization_guser')->delete_by_uid_orgid($guids,$orgid);
		return $ret;
	}
	
	public function fetch_uids_by_orgid($orgids,$hasguser=0){//hasguser>0:包含用户组
		$uids=array();
		if(!is_array($orgids)) $orgids=array($orgids);
		$query=DB::query("select uid from %t where orgid IN(%n)",array($this->_table,$orgids));
		while($value=DB::fetch($query)){
			$uids[]=$value['uid'];
		}
		if($hasguser){
			foreach(DB::query("select guid from %t where orgid IN(%n) and admintype>0",array('organization_guser',$orgids)) as $value){
				$uids[]='g_'.$value['guid'];
			}
		}
		return $uids;
	}
	
	public function fetch_moderators_by_orgid($orgids,$count=false,$hasguser=0){
		if(!is_array($orgids)) $orgids=array($orgids);
		if($count) return DB::result_first("select COUNT(*) from %t where orgid IN (%n)",array($this->_table,$orgids));
		return DB::fetch_all("select o.* ,u.username,u.email,u.uid from ".DB::table($this->_table). " o LEFT JOIN ".DB::table('user')." u ON o.uid=u.uid where o.orgid IN(".dimplode($orgids).") order by o.dateline DESC");
	}

	public function fetch_orgids_by_uid($uids,$orgtype = 0){
		$uids=(array)$uids;
		$orgids=array();
	   	$guids=array();
		foreach($uids as $key => $uid){
			if(strpos($uid,'g_')===0){
				$gid=str_replace('g_','',$uid);
				$guids[]=$gid;
				unset($uids[$key]);
			}
		}
	   if($uids){
			$param=array($this->_table);
			if($orgtype>-1){
				$sql = "select u.orgid from %t u LEFT JOIN %t o ON u.orgid=o.orgid where u.uid IN(%n) and o.type=%d";
				$param[]='organization';
				$param[]=$uids;
				$param[]=$orgtype;
			}else{
				$sql = "select orgid from %t where uid IN(%n)";
				$param[]=$uids;
			}
			foreach(DB::fetch_all($sql,$param) as $value){
				$orgids[$value['orgid']]=$value['orgid'];
			}
	   }
	   if($guids){
			if($orgids1=C::t('organization_guser')->fetch_orgids_by_guid($guids)){
				$orgids=array($orgids,$orgids1);
			}
		}
		return $orgids;
	}
	
	public function ismoderator_by_uid_orgid($orgid,$uid,$up=1){
		global $_G;
		if($_G['adminid']==1) return true;
		if($up) $orgids=C::t('organization')->fetch_parent_by_orgid($orgid);
		else $orgids=array($orgid);
		
		if(DB::result_first("select COUNT(*) from %t where orgid IN (%n) and uid=%d ",array($this->_table,$orgids,$uid))){
			return true;
		}
		if($myorgids=C::t('organization_user')->fetch_orgids_by_uid($uid)){
			if($gids=C::t('organization')->fetch_parent_by_orgid($myorgids)){
				if(DB::result_first("select id from %t where orgid in (%n) and guid IN(%n) and admintype>0",array('organization_guser',$orgids,$gids)) > 0){
					return true;
				}
			}
		}else{//无机构人员
			if(DB::result_first("select id from %t where orgid in (%n) and guid ='0' and admintype>0",array('organization_guser',$orgids)) > 0){
				return true;
			}
		}
	}

	public function fetch_toporgids_by_uid($uid){
		$ret=array();
		$orgids=self::fetch_orgids_by_uid($uid);
		foreach(C::t('organization')->fetch_all($orgids) as $value){
			$topids=explode('-',$value['pathkey']);
			$topid=intval(str_replace('_','',$topids[0]));
			$ret[$topid]=$topid;
		}
		return $ret;
	}
	public function chk_memberperm($orgid,$uid = 0){
		global $_G;
		$gids=array();
        $perm = 0;
        if(!$org=C::t('organization')->fetch($orgid)) return $perm;
        if(!$uid) $uid = $_G['uid'];
		if($_G['adminid'] == 1 && $uid == $_G['uid']) {
            $perm = 4;
            return $perm;
        }
		if(strpos($uid,'g_')===0){
			$guid=intval(str_replace('g_','',$uid));
			if($org['forgid']){
				$orgids = C::t('organization')->fetch_parent_by_orgid($orgid);
				$key = array_search($orgid,$orgids);
				unset($orgids[$key]);
				if(DB::result_first("select count(*) from %t where orgid in(%n) and guid = %d and admintype>0",array('organization_guser',$orgids,$guid)) > 0){
					$perm = 3;
				  return $perm;
				}
			}
			foreach(DB::fetch_all("select admintype from %t where orgid = %d and guid =%d and admintype>0",array('organization_guser',$orgid,$guid)) as $value){
				if($value['admintype']>$perm) $perm = $value['admintype'];
			}
			
		}else{
		
			//判断是否有上级,如果有上级并且当前用户为上级管理员，则给予类似创始人权限
			if($org['forgid']){
				$orgids = C::t('organization')->fetch_parent_by_orgid($orgid);

				$key = array_search($orgid,$orgids);
				unset($orgids[$key]);
				if(DB::result_first("select count(*) from %t where orgid in(%n) and uid = %d",array($this->_table,$orgids,$uid)) > 0){
					$perm = 3;
				  return $perm;
				}
				//判断组

				if($myorgids=C::t('organization_user')->fetch_orgids_by_uid($uid)){
					if($gids=C::t('organization')->fetch_parent_by_orgid($myorgids)){
						if(DB::result_first("select id from %t where orgid in (%n) and guid IN(%n) and admintype>0",array('organization_guser',$orgids,$gids)) > 0){
							$perm = 3;
							return $perm;
						}
					}
				}else{//无机构人员判断
					if(DB::result_first("select id from %t where orgid in (%n) and guid='0' and admintype>0",array('organization_guser',$orgids)) > 0){
							$perm = 3;
							return $perm;
						}
				}
			}

			if($data=DB::fetch_first("select admintype from %t where orgid = %d and uid = %d",array($this->_table,$orgid,$uid))){
				$perm = $data['admintype'];
			}

			if($myorgids){
				foreach(DB::fetch_all("select admintype from %t where orgid = %d and guid IN(%n) and admintype>0",array('organization_guser',$orgid,$gids)) as $value){
					if($value['admintype']>$perm) $perm = $value['admintype'];
				}
			}else{//无机构人员
				foreach(DB::fetch_all("select admintype from %t where orgid = %d and guid ='0' and admintype>0",array('organization_guser',$orgid)) as $value){
					if($value['admintype']>$perm) $perm = $value['admintype'];
				}
			}
		}
		return $perm;
	}
	public function fetch_adminer_by_orgid($orgid){
	    $admindata = '';
	    foreach(DB::fetch_all("select u.username from %t a left join %t u on a.uid = u.uid where orgid = %d ",array($this->_table,'user',$orgid)) as $v){
            $admindata .= $v['username'].',';
        }
		/*foreach(DB::fetch_all("select o.orgname from %t gu left join %t o on gu.guid = o.orgid where gu.orgid = %d ",array('organization_guser','organization',$orgid)) as $v){
            $admindata .= $v['orgname'].',';
        }*/
        $admindata = substr($admindata,0,-1);
        return $admindata;
    }
}
