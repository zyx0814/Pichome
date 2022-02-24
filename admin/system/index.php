<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if (!defined('IN_OAOOA') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
require_once(__DIR__.'/dist/index.html');
exit();
$operation = $_GET['operation'] ? $_GET['operation'] : 'updatecache';
$url=getglobal('siteurl'). BASESCRIPT . '?mod=system&op=' . $operation;
$url = outputurl($url); 
@header("location: $url");
?>
