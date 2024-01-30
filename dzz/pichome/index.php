<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
//检查登录
Hook::listen('check_login');
global $_G;
$uid = $_G['uid'];
if(!C::t('pichome_vappmember')->checkuserperm_by_uid($uid)){
    exit(json_encode(array('success'=>false,'msg'=>lang('no_perm'))));
}
@include Hook::listen('mod_start',$_GET,null,true);//模块路由
exit();