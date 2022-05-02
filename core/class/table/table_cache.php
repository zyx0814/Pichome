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

class table_cache extends dzz_table
{
	public function __construct()
	{
		
		$this->_table = 'cache';
		$this->_pk = 'cachekey';
		
		parent::__construct();
	}
	
	
	public function insert($setarr)
	{
		if (parent::fetch($setarr['cachekey'])) {
			$cachekey = $setarr['cachekey'];
			unset($setarr['cachekey']);
			return parent::update($cachekey, $setarr);
		} else {
			return parent::insert($setarr);
		}
	}
	
}
