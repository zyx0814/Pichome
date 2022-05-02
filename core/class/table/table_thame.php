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

class table_thame extends dzz_table
{
	public function __construct() {

		$this->_table = 'thame';
		$this->_pk    = 'id';

		parent::__construct();
	}
}

?>
