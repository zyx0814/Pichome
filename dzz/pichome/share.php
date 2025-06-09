<?php
if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}
$_G['setting']['sitename']=addslashes($_G['setting']['sitename']);

if(!$patharr=Pdecode($_GET['path'])){
    showmessage('share not found or expired');
}

$perm=$patharr['perm'];
$sid=$patharr['sid'];
$rid=$patharr['path'];

$resourcesdata = C::t('pichome_resources')->fetch_by_rid($rid,0,0,$perm);

$resourcesdata['share'] = 0;

if(perm::check('download2',$perm)){
    $resourcesdata['download'] = 1;
}else{
    $resourcesdata['download'] = 0;
}

//if(getglobal('adminid') != 1)$resourcesdata['download'] = 0;
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

//处理多预览图
$previews=array();
if(!$resourcesdata['iniframe']){
	$previews = C::t('thumb_preview')->fetchPreviewByRid($rid,false,$perm);
	$resourcesCover = ['spath'=>$resourcesdata['icondata'],'lpath'=>$resourcesdata['originalimg']];
	if(is_array($previews)) array_unshift($previews,$resourcesCover);
}
$resourcesdata['preview'] = json_encode($previews);

$foldernames = array();
foreach($resourcesdata['foldernames'] as $fval){
	$foldernames[] = $fval;
}
$resourcesdata['foldernames'] = json_encode($foldernames);

$theme = GetThemeColor();
$ismobile = helper_browser::ismobile();
if (($ismobile)) {
	include template('share/mobile/page/index');
} else {
	include template('share/pc/page/index');
}
