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

class table_connect_storage extends dzz_table
{ 
	public function __construct() {

		$this->_table = 'connect_storage';
		$this->_pk    = 'id';
		/*$this->_pre_cache_key = 'connect_storage_';
		$this->_cache_ttl = 0;*/
		parent::__construct();
	}

	public function fetch_all_space(){
	    return DB::fetch_all("select * from %t where 1 order by disp asc ",array($this->_table));
    }
    public function getBzByRemoteid($remoteid){
        if(!$remoteid || $remoteid == 1) return 'dzz::';
        $bz = DB::result_first("select bz from %t where id = %d",array($this->_table,$remoteid));
        return $bz.':'.$remoteid.':';
    }
    //查询默认存储位置
    public function fetch_default_space(){
        $return = [];
        if(!$return = DB::fetch_first("select * from %t where isdefault = 1",array($this->_table))){
           $return = parent::fetch(1);
        }
        return $return;
    }
	public function fetch_by_id($id){
		
		$value=self::fetch($id);
		$cloud=DB::fetch_first("select * from ".DB::table('connect')." where bz='{$value['bz']}'");
		$value['access_id']=authcode($value['access_id'],'DECODE',$value['bz'])?authcode($value['access_id'],'DECODE',$value['bz']):$value['access_id'];
		if(!$value['cloudname']) $value['cloudname']=$cloud['name'].':'.($value['bucket']?$value['bucket']:cutstr($value['access_id'], 4, ''));
		if($value['bucket']) $value['bucket'].='/';
		$data=array(
				'id'=>$value['id'],
				'fid'=>md5($cloud['bz'].':'.$value['id'].':'.$value['bucket']),
				'pfid'=>0,
				'fname'=>$value['cloudname'],
				'ficon'=>'dzz/images/default/system/'.$cloud['bz'].'.png',
				'bz'=>$cloud['bz'].':'.$value['id'].':',
				'path'=>$cloud['bz'].':'.$value['id'].':'.$value['bucket'],
				'type'=>'storage',
				'fsperm'=>$value['bucket']?'0':perm_FolderSPerm::flagPower($cloud['bz'].'_root'),
				'perm'=>perm_binPerm::getGroupPower('all'),
				'flag'=>$cloud['bz'],
				'iconview'=>1,
				'disp'=>'0',
			);
		
		return $data;
	}
	public function fetch_all_by_id($ids){
		$data=array();
		foreach($ids as $id){
			if($value=self::fetch_by_id($id)) $data[$value['fid']]=$value;
		}
		return $data;
	}
	public function delete_by_id($id){	
		//删除此应用的快捷方式
		$return=array();
		$data=parent::fetch($id);
		$bzpath = $data['bz'].':'.$id.':';
		//查询是否有使用的库
        if(DB::result_first("select count(appid) from %t where path like %s",array('pichome_vapp',$bzpath.'%'))){
            $return['msg']='有使用此存储位置的库，请先删除库后再执行此操作';
            $return['error']=true;
        }
		if(parent::delete($id)){
			$return['msg']='success';
		}
		return $return;
	}
	public function delete_by_uid($uid){
		if(!$uid) return 0;
		foreach(DB::fetch_all("select id from %t where uid=%d",array($this->_table,$uid)) as $value){
			self::delete_by_id($value['id']);
		}
		return true;
	}
	public function delete_by_bz($bz){	
		foreach(DB::fetch_all("select id from %t where bz=%s",array($this->_table,$bz)) as $value){
			self::delete_by_id($value['id']);
		}
	}

    public function name_filter($cloudname)
    {
        return str_replace(array('/', '\\', ':', '*', '?', '<', '>', '|', '"', "\n"), '', $cloudname);
    }
	public function getcloudname($cloudname = ''){
        static $i = 0;
        if(!$cloudname) $cloudname = '存储名称';
        $cloudname = self::name_filter($cloudname);
        if (DB::result_first("select COUNT(*) from %t where cloudname=%s ", array($this->_table, $cloudname))) {
            $cloudname = preg_replace("/\(\d+\)/i", '', $cloudname) . '(' . ($i + 1) . ')';
            $i += 1;
            return self::getFolderName($cloudname);
        } else {
            return $cloudname;
        }
    }
}

