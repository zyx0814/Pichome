<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$overt = getglobal('setting/overt');
if(!$overt && !$overt = C::t('setting')->fetch('overt')){
    Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'gettagdata') {//获取标签位文件列表数据

} elseif ($do == 'getdata') {

}else{
    include template('fashion/page/index');
}


    
    