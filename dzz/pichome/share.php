<?php
    if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
        exit('Access Denied');
    }
	
$sid = isset($_GET['sid']) ? dzzdecode($_GET['sid'],'',0):'';
$sharedata = C::t('pichome_share')->fetch_by_id($sid);
$resourcesdata = $sharedata['resourcesdata'];
$resourcesdata['share'] = 0;
$colors = array();
foreach($resourcesdata['colors'] as $cval){
	$colors[] = $cval;
}
$resourcesdata['colors'] = json_encode($colors);

$tag = array();
foreach($resourcesdata['tag'] as $tval){
	if($tval){
		$tag[] = $tval;
	}
}
$resourcesdata['tag'] = json_encode($tag);

$foldernames = array();
foreach($resourcesdata['foldernames'] as $fval){
	$foldernames[] = $fval;
}
$resourcesdata['foldernames'] = json_encode($foldernames);

$theme = GetThemeColor();
$ismobile = helper_browser::ismobile();

if (($ismobile)) {
	include template('mobile/page/share');
} else {
	include template('pc/page/share');
}