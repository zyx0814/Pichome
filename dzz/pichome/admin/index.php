<?php
if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}
global $_G;
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
$navtitle = '主题设置';
if($operation == 'signlepageset'){
    //标签所属主题
    $themeid = isset($_GET['themeid']) ? intval($_GET['themeid']):0;
    //标签标志
    $tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
    //获取到标签数据
    $dataval = $_GET['dataval'];

}elseif($operation == 'settheme'){
    include libfile('function/cache');
    $themeid = isset($_GET['themeid']) ? intval($_GET['themeid']):0;
    C::t('setting')->update('pichometheme',$themeid);
    updatecache('setting');
    dexit(json_encode(array('success'=>true)));
}else{

	$version = defined('LICENSE_VERSION') ? lang(LICENSE_VERSION):lang('Home');
	$limitusernum = defined('LICENSE_LIMIT') ? LICENSE_LIMIT:1;
	if(defined('NOLIMITUSER')) $limitusernum = '无限';
	$authdate = defined('LICENSE_CTIME') ? dgmdate(LICENSE_CTIME,'Y-m-d H:i:s'):'';
	
	
    $defaulttheme = C::t('setting')->fetch('pichometheme');
    $themelist = $_G['setting']['pichomethemedata'];
    include template('admin/pc/page/theme');
}

    