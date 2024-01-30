<?php
/*
 * 应用卸载程序示例
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */ 
if (!defined('IN_OAOOA') && !defined('IN_ADMIN')) {
	exit('Access Denied');
}
$op="admin"; 
Hook::listen('adminlogin');
$app=C::t('app_market')->fetch_by_identifier('onlyoffice_view','dzz');
$app['extra'] && $app['extra']=unserialize($app['extra']); 
if (!submitcheck('confirmsubmit')) {
	include template('admin');
} else {
	if ( $_GET['app_key'] ) { 
		$extra =$app['extra'];
		$extra["DocumentUrl"]=$_GET['app_key'];
		$extra["FileUrl"]=$_GET['fileurl']?trim($_GET['fileurl']):'';
		$extra["exts"]=$_GET['exts']?trim($_GET['exts']):'';
		$extra["secret"]=$_GET['secret']?trim($_GET['secret']):'';

		C::t("app_market")->update($app['appid'],array("extra"=> serialize($extra))); 
		showmessage('save_success', $_GET['refer']?$_GET['refer']:dreferer(), array(), array('alert' => 'right'));
	} else {
		showmessage('onlyoffice_url_setfailed');
	}
	exit();
}
