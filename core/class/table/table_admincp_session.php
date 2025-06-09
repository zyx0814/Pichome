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

class table_admincp_session extends dzz_table
{
	public function __construct() {

		$this->_table = 'admincp_session';
		$this->_pk    = 'uid';

		parent::__construct();
	}

	public function fetch($uid, $panel=1) {
		$sql = 'SELECT * FROM %t WHERE uid=%d AND panel=%d';
		return DB::fetch_first($sql, array($this->_table, $uid, $panel));
	}

	public function fetch_all_by_panel($panel) {
		return DB::fetch_all('SELECT * FROM %t WHERE panel=%d', array($this->_table, $panel), 'uid');
	}

	public function delete_by_uid($uid, $panel, $ttl = 3600) {


		$sql = 'DELETE FROM %t WHERE (uid=%d AND panel=%d) OR dateline<%d';
		DB::query($sql, array($this->_table, $uid, $panel, TIMESTAMP-intval($ttl)));

	}

	public function update_by_uid($uid, $panel, $data) {
		if(!empty($data) && is_array($data)) {
			if(!self::fetch($uid,$panel)){
				$data=array_merge($data,array('uid'=>$uid, 'panel'=>$panel));
				DB::insert($this->_table,$data);
			}else{
				DB::update($this->_table, $data, array('uid'=>$uid, 'panel'=>$panel));
			}
		}
	}

}

?>
