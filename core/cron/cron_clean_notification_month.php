<?php
/*
 * 计划任务脚本 清空一个月以上的通知
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

if(!defined('IN_OAOOA')) {
	exit('Access Denied');
}

C::t('notification')->delete_clear(0, 30);
C::t('notification')->delete_clear(1, 30);
C::t('notification')->optimize();

?>
