<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/11/28
 * Time: 14:32
 */
if (!defined('IN_OAOOA') && !defined('IN_ADMIN')) {
    exit('Access Denied');
}
$op="admin";
Hook::listen('adminlogin');
include_once libfile('function/cache');
$app = C::t('app_market')->fetch_by_identifier('ffmpeg','dzz');

if(submitcheck('settingsubmit')){
    $newsetting = $_GET['settingnew'];
    foreach($newsetting as $k=>$v){
        $newsetting[$k] = !is_array($v)?getstr($v):$v;
    }
    if(C::t("app_market")->update($app['appid'],array("extra"=> serialize($newsetting)))){
        showmessage('do_success', dreferer());
    }else{
        showmessage('do_failed',dreferer());
    }
}else{
    $appextra = unserialize($app['extra']);
    include template('admin');
}

