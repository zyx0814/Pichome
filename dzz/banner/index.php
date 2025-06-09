<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$ismobile=helper_browser::ismobile();
$overt = getglobal('setting/overt');
if(!$overt && !$overt = C::t('setting')->fetch('overt')){
    Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
}
$id = isset($_GET['id']) ? trim($_GET['id']) : '';
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'gettagdata') {//获取标签位文件列表数据

} elseif ($do == 'getdata') {

}else{
    $bannerdata = C::t('pichome_banner')->getBannerTree($id);

    $tilebanner = json_encode($bannerdata['tilebanner']);
    $bannerdata = json_encode($bannerdata['bannerlist']);
    // include template('fashion/page/index');
	$params=array();
    $collectlis = Hook::listen('collectlist');
    $collectlisarr = [];    
    $tabgroupdatas = [];
    $tabarr = [];
    Hook::listen('gettabgroupdata',$tabgroupdatas,'edits');

    if(count($tabgroupdatas)){
        foreach($tabgroupdatas as $value){
            $tabarr[] =['name'=>lang('creation').$value['name'],'value'=>$value['gid'],'type'=>'tab'];
        }

    }
    if(isset($collectlis[0])){
		if(isset($collectlis[0]['x']) && $collectlis[0]['x']){
			foreach($collectlis[0]['x'] as $value){
				$collectlisarr[] =['name'=>$value['title'],'value'=>'index.php?mod=fileCollect&op=upload&cid='.$value['cid'],'type'=>'collect'];
			}
		}

    }
    $collectlisarr = json_encode($collectlisarr);
    $tabarr = json_encode($tabarr);
    $PICHOME_LIENCE = PICHOME_LIENCE;
    if($ismobile){
        include template('fashion/mobile/page/index');
    }else{
        include template('fashion/pc/page/index');
    }
    
}

    