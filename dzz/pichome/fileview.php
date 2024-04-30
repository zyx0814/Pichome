<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$overt = getglobal('setting/overt');
if (!$overt && !$overt = C::t('setting')->fetch('overt')) {
    Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
}
$ismobile = helper_browser::ismobile();
if ($ismobile) {
    include template('libraryview/mobile/page/index');
} else {
    include template('libraryview/pc/page/index');
}