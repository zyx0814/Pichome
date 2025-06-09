<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
global $_G;
$_G['setting']['sitename']=addslashes($_G['setting']['sitename']);
$appid = isset($_GET['id']) ? trim($_GET['id']) : '';
$vappdata = C::t('pichome_vapp')->fetch($appid);
$viewperm = unserialize($vappdata['view']);
$overt = getglobal('setting/overt');

if ($viewperm !== '1' && !$overt && !$overt = C::t('setting')->fetch('overt')) {
    Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
}
$ismobile = helper_browser::ismobile();
if ($ismobile) {
    include template('libraryview/mobile/page/index');
} else {
    include template('libraryview/pc/page/index');
}