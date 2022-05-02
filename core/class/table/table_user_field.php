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

class table_user_field extends dzz_table
{
	public function __construct() {

		$this->_table = 'user_field';
		$this->_pk    = 'uid';
		$this->_pre_cache_key = 'user_field_';
		$this->_cache_ttl = 60*60;
		
		parent::__construct();
	}
}
