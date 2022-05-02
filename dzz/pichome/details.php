<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 *
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

    
if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
	exit('Access Denied');
}
$overt = getglobal('setting/overt');
if (!$overt) {
	Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
}
global $_G;
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
$opentype = isset($_GET['opentype']) ? trim($_GET['opentype']) : '';
if($operation == 'fetch'){
	$rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
	if (!$rid) {
		exit(json_encode(array('error' => false)));
	}
	$resourcesdata = C::t('pichome_resources')->fetch_by_rid($rid);
	exit(json_encode(array('resourcesdata' => $resourcesdata,'sitename'=>$_G['setting']['sitename'])));

}else{
	$theme = GetThemeColor();
	$ismobile = helper_browser::ismobile();
	if ($ismobile) {
	    include template('mobile/page/details');
	} else {
		include template('pc/page/details');
	}
	
}

