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

class table_mailqueue extends dzz_table
{
	public function __construct() {

		$this->_table = 'mailqueue';
		$this->_pk    = 'qid';

		parent::__construct();
	}

	public function fetch_all_by_cid($cids) {
		if(empty($cids)) {
			return array();
		}
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('cid', $cids), array($this->_table));
	}

	public function delete_by_cid($cids) {
		if(empty($cids)) {
			return false;
		}
		return DB::query('DELETE FROM %t WHERE '.DB::field('cid', $cids), array($this->_table));
	}
}

?>
