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

/*if(!($_G['adminid'] == 1 && $_GET['formhash'] == formhash()) && $_G['setting']) {
	exit('Access Denied');
}*/

require_once libfile('function/cache');
updatecache();
cleartemplatecache();
?>