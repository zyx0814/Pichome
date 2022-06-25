<?php
/*
 * 应用卸载程序示例
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

if(!defined('IN_OAOOA')) {
	exit('Access Denied');
}
$appid=intval($_GET['appid']);
if(!submitcheck('confirmsubmit')){
	
	include template('appkey');
}else{
	if($_GET['app_key']){
		$url=$_GET['adminurl'].'&op=cp&app_key='.$_GET['app_key'].'&do=install&dir=onlyoffice_view';
		@header("Location: $url");
	}else{
		showmessage('文档服务器地址不合法，无法完成安装',$_GET['adminurl']);
	}
	exit();
}
