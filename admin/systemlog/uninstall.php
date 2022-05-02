<?php
/*
 * 应用卸载文件； 
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      qchlian
 */ 
if(!defined('IN_OAOOA') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}  
$sql = <<<EOF
DELETE FROM `dzz_setting` WHERE `dzz_setting`.`skey` = 'systemlog_setting';
DELETE FROM `dzz_setting` WHERE `dzz_setting`.`skey` = 'systemlog_open';
EOF;
runquery($sql);
$finish = true;
