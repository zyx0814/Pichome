<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
ignore_user_abort(1);
if(!defined('IN_OAOOA')) {
	exit('Access Denied');
}

$setarr=array('ip'=>$_G['clientip'],
			  'agent'=>$_SERVER['HTTP_USER_AGENT'],
			  'os'=>$_SERVER['OS'],
			  'dateline'=>$_G['timestamp']
			  );
DB::insert('count_down',$setarr);
exit();
?>
