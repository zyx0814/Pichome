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

class table_user_wechat extends dzz_table
{
	public function __construct() {

		$this->_table = 'user_wechat';
		$this->_pk    = 'uid';

		parent::__construct();
	}
	function fetch_by_openid($openid,$appid){
		return DB::fetch_first("select * from %t where openid=%s and appid=%s",array($this->_table,$openid,$appid));
	}
}
?>
