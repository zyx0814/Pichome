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

class table_process extends dzz_table
{
	public function __construct() {

		$this->_table = 'process';
		$this->_pk    = 'processid';

		parent::__construct();
	}

	public function delete_process($name, $time) {
		$name = addslashes($name);
		return DB::delete('process', "processid='$name' OR expiry<".intval($time));
	}
}

?>
