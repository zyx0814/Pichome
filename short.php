<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
require __DIR__.'/core/coreBase.php';
$dzz = C::app();
$dzz->init_session = false;
$dzz->init_setting=false;
$dzz->init_user=false;
$dzz->init_misc=false;
$dzz->init();
$sid=$_GET['sid'];
$short=C::t('shorturl')->fetch($sid);
C::t('shorturl')->addview($sid);
@header("Location: ". outputurl($short['url']));
exit();
?>