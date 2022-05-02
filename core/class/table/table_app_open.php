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

class table_app_open extends dzz_table
{
	public function __construct() {
		$this->_table = 'app_open';
		$this->_pk    = 'extid';
		$this->_pre_cache_key = 'app_open_';
		$this->_cache_ttl =0;
		parent::__construct();
	}
	public function setDefault($extid){
		$data=self::fetch($extid);
		DB::update($this->_table,array('isdefault'=>0),"ext='{$data[ext]}'");
		$this->clear_cache('ext_all');
		$this->clear_cache('all');
		return self::update($extid,array('isdefault'=>1)); 
	}
	public function setOrders($extid){ 
		foreach($extid as $k=>$v ){
			$result = self::update($v,array('disp'=>$k)); 
		} 
		$this->clear_cache('ext_all');
		$this->clear_cache('all');
		return true;
	}
	public function delete_by_appid($appid){
		if(!$appid) return false;
		$query=DB::query("SELECT * FROM %t WHERE appid=%d ",array($this->_table,$appid));
		while($value=DB::fetch($query)){
			if($value['extid']){
				$result=C::t('app_open_default')->delete_by_extid($value['extid']); 
			} 
		} 
		$this->clear_cache('ext_all');
		$this->clear_cache('all');
		return DB::delete($this->_table," appid='{$appid}'");
	}
	public function insert_by_exts($appid,$exts,$isall=1){
		if(!$appid) return false;
		if(!is_array($exts)) $exts=$exts?explode(',',$exts):array();
		//删除原来的ext
		
		$oexts=array();
		$delids=array();
		$oextarr=DB::fetch_all("select * from ".DB::table('app_open')." where appid='{$appid}'");
		foreach($oextarr as $value){
			$oexts[]=$value['ext'];
			if(!in_array($value['ext'],$exts)) $delids[]=$value['extid'];
		}
		
		if($isall && $delids) {
			self::delete($delids);
		}
		foreach($exts as $ext){
			if($ext && !in_array($ext,$oexts))	parent::insert(array('ext'=>$ext,'appid'=>$appid));
		}
		$this->clear_cache('ext_all');
		$this->clear_cache('all');
		return true;
	}
	public function delete_by_exts($appid,$exts){
		$ret=0;
		foreach(DB::fetch_all("select * from %t where appid=%d and ext IN (%n)",array('app_open',$appid,$exts)) as $value){
			if(parent::delete($value['id'])) $ret++;
		}
		return $ret;
	}
	public function fetch_all_ext(){
		$data = array();
		if(($data = $this->fetch_cache('all')) === false) {
			$data = array();
			$query=DB::query("SELECT * FROM %t WHERE 1 ",array($this->_table));
			while($value=DB::fetch($query)){
				if($value['appid']){
					 if($app=C::t('app_market')->fetch_by_appid($value['appid'],false)){
						 if($app['available']<1) continue;
						 if(!$value['icon']) $value['icon']=$app['appico'];
						 if(!$value['name']) $value['name']=$app['appname'];
						 if(!$value['url'])  $value['url']=$app['appurl'];
						 if(!$value['nodup']) $value['nodup']=$app['nodup'];
						 if(!$value['feature']) $value['feature']=$app['feature'];
						 $value['canedit']=intval($app['haveflash']);
					 }else{
						continue; 
					 }
				}
				$value['url']=replace_canshu($value['url']);
				$data[$value['extid']]=$value;
			}
			if(!empty($data)) $this->store_cache('all', $data);
		}
		return $data;
	}
	public function fetch_all_orderby_ext($uid,$ext_all=array()){
		$data = array();
		if($config = C::t('user_field')->fetch($uid)){
			if($config['applist']){
				$appids=explode(',',$config['applist']);
			}else{
				$appids=array();
			}
		}
		if(!$ext_all) $ext_all=self::fetch_all_ext();
		foreach($ext_all as $value){
			if($value['appid'] && !in_array($value['appid'],$appids)){
				continue;
			}
			$data[$value['ext']][]=$value['extid'];
		}
		return $data;
		
	}
	
}
?>
