<?php
    
    if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
        exit('Access Denied');
    }
//管理权限进入
Hook::listen('adminlogin');
$navtitle=lang('manage_center');
@include Hook::listen('mod_start',$_GET,null,true);//模块路由
exit();
