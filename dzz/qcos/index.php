<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/11/28
 * Time: 14:32
 */
if (!defined('IN_OAOOA') && !defined('IN_ADMIN')) {
    exit('Access Denied');
}
$op="admin";
Hook::listen('adminlogin');
include_once libfile('function/cache');
$setting = C::t('setting')->fetch('qcosvideo_setting',true);
if(submitcheck('settingsubmit')){
    $newsetting = $_GET['settingnew'];
    foreach($newsetting as $k=>$v){
        $newsetting[$k] = !is_array($v)?getstr($v):$v;
    }

    if(C::t('setting')->update('qcosvideo_setting',$newsetting)){
		updatecache('setting');
        showmessage('do_success', dreferer());
    }else{
        showmessage('do_failed',dreferer());
    }
}else{
	$formlist=array();
	foreach(C::t('form_setting')->fetch_all() as $value){
		$formlist[$value['flag']]=$value;
	}
	ksort($formlist);
	$infos=array(
		'width'=>lang('width'),
		'height'=>lang('height'),
		'avg_frame_rate'=>lang('avg_frame_rate'),
		'bit_rate'=>lang('bit_rate'),
		'duration'=>lang('duration'),
		'format_name'=>lang('format_name'),
	);
}

include template('admin');