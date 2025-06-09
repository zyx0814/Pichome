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

class table_search_template extends dzz_table
{
	public function __construct() {

		$this->_table = 'search_template';
		$this->_pk    = 'tid';
		/*$this->_pre_cache_key = 'search_template_';
		$this->_cache_ttl = 0;*/

		parent::__construct();
	}
	public function fetch_all_template(){
		$data=array();
		foreach(DB::fetch_all("select * from %t where 1 order by disp asc,dateline desc",array($this->_table)) as $value){
			if($value['screen']){
				$value['screen']=json_decode($value['screen'],true);
			}else{
				$value['screen']=array();
			}
			if($value['pagesetting']){
				$value['pagesetting']=json_decode($value['pagesetting'],true);
				if($value['pagesetting']['layout']) $value['layout']=$value['pagesetting']['layout'];
				else{
					$value['layout']='waterFall';
				}
			}else{
				$value['pagesetting']=array();
				$value['layout']='waterFall';
			}
			if($value['searchRange']){
				$appids=explode(',',$value['searchRange']);
				$appnames=array();
				foreach($appids as $appid){
					if(isset($apps[$appid])){
						$appnames[]=$apps[$appid]['appname'];
					}
				}
				$value['searchRange_names']=implode(',',$appnames);

			}else{
				$value['searchRange']=array();
				$value['searchRange_names']=lang('all_library');
			}
			$data[$value['tid']]=$value;
		}
        Hook::listen('lang_parse',$data,['getSearchtemplateLangData',1]);
		return $data;
	}
	
}
