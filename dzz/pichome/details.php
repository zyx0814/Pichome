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
updatesession();
global $_G;
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
$opentype = isset($_GET['opentype']) ? trim($_GET['opentype']) : '';
$overt = getglobal('setting/overt');
if($operation == 'fetch'){

    $path = $_GET['path'] ? dzzdecode($_GET['path'],'',0):'';

    if(!$path){
        exit(json_encode(array('status'=>2,'error'=>lang('no_perm'))));
    }else{
        $patharr = explode('_',$path);
        $rid = $patharr[0];
        $isshare = isset($patharr[1]) ? intval($patharr[1]):0;
    }
	
    if(!$isshare){
       
        if (!$overt && !getglobal('uid')) {
           Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
            // exit(json_encode(array('status'=>0,'error'=>lang('need_login'))));
        }
    }
    $ulevel = getglobal('pichomelevel') ? getglobal('pichomelevel') : 0;

    
   
	if (!$rid) {
        exit(json_encode(array('status'=>2,'error'=>lang('no_perm'))));
	}

	$resourcesdata = C::t('pichome_resources')->fetch_by_rid($rid,$isshare);
	$viewsdata = [
	    'rid'=>$rid,
        'filename'=>$resourcesdata['name'],
        'appid'=>$resourcesdata['appid'],
        'uid'=>$uid,
        'dateline'=>TIMESTAMP
    ];
	C::t('pichome_views')->insert($viewsdata);
	if($isshare || $ulevel >= $resourcesdata['level']){
        exit(json_encode(array('status'=>1,'resourcesdata' => $resourcesdata,'sitename'=>$_G['setting']['sitename'])));
    }else{
        exit(json_encode(array('status'=>2,'error'=>lang('no_perm'))));
    }


}else{
	$theme = GetThemeColor();
	$ismobile = helper_browser::ismobile();
	if ($ismobile) {
	    include template('mobile/page/details');
	} else {
		include template('pc/page/details');
	}
	
}

