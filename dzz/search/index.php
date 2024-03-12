<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 */
if(!defined('IN_OAOOA') ) {
    exit('Access Denied');
}
$overt = getglobal('setting/overt');
if(!$overt && !$overt = C::t('setting')->fetch('overt')){
    Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
}
$templatedata= C::t('search_template')->fetch_all_template();

$templatedata = json_encode($templatedata);

include template('view/pc/page/index');
exit();