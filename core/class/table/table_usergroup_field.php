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

class table_usergroup_field extends dzz_table
{
	public function __construct() {

		$this->_table = 'usergroup_field';
		$this->_pk    = 'groupid';

		parent::__construct();
	}

	public function fetch_all() {
		return DB::fetch_all("SELECT * FROM %t where 1", array($this->_table),$this->_pk);
	}

}

?>
