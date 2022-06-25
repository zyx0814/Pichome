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
//所有用户应用
//uid=0 的表示为默认应用

class table_connect extends dzz_table
{ 
	public function __construct() {

		$this->_table = 'connect';
		$this->_pk    = 'bz';
		$this->_pre_cache_key = 'connect_';
		$this->_cache_ttl = 60*60;
		parent::__construct();
	}
	public function fetch_all_by_available(){
		$data=array();
		foreach(DB::fetch_all("select name,bz from %t where available > %d and bz != %s",array($this->_table,0,'dzz')) as $val){
            $data[]=$val;
        }
		return $data;
	}
	
	public function fetch_all_folderdata($uid){
		$data=self::fetch_all_by_available();
		$folderdata=array();
		foreach($data as $value){
			foreach(DB::fetch_all("select id from ".DB::table($value['dname'])." where uid>0 && uid='{$uid}'") as $value1){
				
				$arr=C::t($value['dname'])->fetch_by_id($value1['id']);
				$folderdata[$arr['fid']]=$arr;
			}
		}
		return $folderdata;
	}
	public function fetch_all_bz(){
		$data=array();
		foreach(DB::fetch_all("select bz from %t where 1",array($this->_table)) as $value){
			$data[]=$value['bz'];
		}
		return $data;
	}
	public function delete_by_bz($bz){
		if($bz=='dzz') return false; //dzz是内置，不能删除
		$data=self::fetch($bz);
		if(is_file(DZZ_ROOT.'./core/class/table/table_'.$data['dname'].'.php')){
			C::t($data['dname'])->delete_by_bz($bz);
		}
		return self::delete($bz);
	}
}
