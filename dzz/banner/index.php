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
    $bannerdata = C::t('pichome_banner')->getbannerlist(0,1);
    $bannerdata = json_encode($bannerdata);
    // include template('fashion/page/index');
	$params=array();
    $collectlis = Hook::listen('collectlist');
    $collectdata = [];
    if(isset($collectlis[0])){
		if(isset($collectlis[0]['x']) && $collectlis[0]['x']){
			foreach($collectlis[0]['x'] as $value){
				$collectdata[] =['name'=>$value['title'],'url'=>'index.php?mod=fileCollect&op=upload&cid='.$value['cid']];
			}
			// $collectdata[] = ['name'=>'我提交的','url'=>'index.php?mod=fileCollect&type=1'];
		}
		// if(isset($collectlis[0]['m']) && $collectlis[0]['m']){
		// 	$collectdata[] = ['name'=>'我审核的','url'=>'index.php?mod=fileCollect&type=2'];
		// }
    }
    include template('fashion/pc/page/index');
}

    