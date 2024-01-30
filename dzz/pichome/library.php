<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
//检查登录
Hook::listen('check_login');
global $_G;
$uid = $_G['uid'];
if(!C::t('pichome_vappmember')->checkuserperm_by_uid($uid)){
    exit(json_encode(array('success'=>false,'msg'=>"对不起，您没有访问权限")));
}
@include Hook::listen('mod_start',$_GET,null,true);//模块路由
exit();