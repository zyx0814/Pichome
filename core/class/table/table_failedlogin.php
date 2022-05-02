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

class table_failedlogin extends dzz_table
{
	public function __construct() {

		$this->_table = 'failedlogin';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch_username($ip='', $username='') {
		return DB::fetch_first("SELECT * FROM %t WHERE ip=%s AND username=%s", array($this->_table, $ip, $username));
	}
	public function fetch_ip($ip='',$username='') {
		return DB::fetch_first("SELECT * FROM %t WHERE ip=%s AND username = %s", array($this->_table, $ip,$username));
	}

	public function delete_old($time) {
		DB::query("DELETE FROM %t WHERE lastupdate<%d", array($this->_table, TIMESTAMP - intval($time)), 'UNBUFFERED');
	}

	public function update_failed($ip='', $username='') {
		DB::query("UPDATE %t SET count=count+1, lastupdate=%d WHERE ip=%s AND username = %s", array($this->_table, TIMESTAMP, $ip,$username));
	}

}
