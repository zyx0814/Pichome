<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
define('NOROBOT', TRUE);
if(empty($_GET['uid'])) showmessage(lang('parameters_error'));
session_start();
$uid = $_GET['uid'];
$dzz_action = 141;
$member = C::t('user')->get_user_by_uid($uid);
if($_GET['operation']=='sms_send'){
   $phone=$member['phone'];
   $re=sms('sms_login_code',$phone);
   if($re['error']){
	   exit(json_encode($re));
   }else{
	   $_SESSION['sms_code_'.$uid]=$re;
	   exit(json_encode(array('msg'=>'success')));
   }
}elseif(!submitcheck('smsAuthSubmit')) {
	$referer = $_GET['referer'];
	include template('login_sms_auth');
} else {
	 $codearr=$_SESSION['sms_code_'.$uid];
	if($codearr['startdate']<(TIMESTAMP-$codearr['expire']*60)) {
		showmessage(lang('sms_code_timeout'));
	}
	 if($_GET['code'] != $codearr['code']) {
		showmessage(lang('sms_code_illegal'));
	}
	//设置登录
	setloginstatus($member, $_GET['cookietime'] ? 2592000 : 0);

	if($_G['member']['lastip'] && $_G['member']['lastvisit']) {

		dsetcookie('lip', $_G['member']['lastip'].','.$_G['member']['lastvisit']);
	}

	//记录登录
	C::t('user_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'lastvisit' =>TIMESTAMP, 'lastactivity' => TIMESTAMP));


	//登录成功提示信息
	$param = array(
		'username' => $member['username'],
		'usergroup' => $_G['group']['grouptitle'],
		'uid' => $_G['member']['uid'],
		'groupid' => $_G['groupid'],
		'syn' =>  0
	);
	$loginmessage = /*$_G['groupid'] == 8 ? 'login_succeed_inactive_member' :*/ 'login_succeed';

	$location = ($_GET['referer']) ? ($_GET['referer']):$_G['siteurl'];

	$href = str_replace("'", "\'", $location);
	$href = preg_replace("/user\.php\?mod\=login.*?$/i", "", $location);

	$messageText = lang($loginmessage, $param);
	writelog('loginlog', lang('login_success'));

	$_SESSION['sms_code_'.$uid]=array();
	showmessage($messageText, $href, array());
}

