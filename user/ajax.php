<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if (!defined('IN_OAOOA')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);
if ($_GET['action'] == 'checkusername') {

	$username = trim($_GET['username']);
	$usernamelen = dstrlen($username);
	if ($usernamelen < 3) {
		showTips(array('error'=>lang( 'profile_nickname_tooshort')));
	} elseif ($usernamelen > 30) {
		showTips(array('error'=>lang( 'profile_nickname_toolong')));
	}

	require_once libfile('function/user');
	$ucresult = uc_user_checkname($username);
	if ($ucresult == -1) {
		showTips(array('error'=>lang( 'profile_nickname_illegal')));
	} elseif ($ucresult == -2) {
		showTips(array('error'=>lang( 'profile_nickname_protect')));
	} elseif ($ucresult == -3) {
		showTips(array('error'=>lang( 'register_check_found')));
	}

	$censorexp = '/^(' . str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')) . ')$/i';
	if ($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
		showTips(array('error'=>lang( 'profile_nickname_protect')));
	}

} elseif ($_GET['action'] == 'checkemail') {

	require_once libfile('function/user');
	checkemail($_GET['email']);
} elseif ($_GET['action'] == 'checkphone') {
	$phone=htmlspecialchars($_GET['phone']);
	if(!isphone($phone)){
		showTips(array('error'=>lang( 'user_phone_illegal')));
	}
	if(C::t('user')->fetch_by_phone($phone,1)){
		showTips(array('error'=>lang( 'user_phone_registered')));
	}

} elseif ($_GET['action'] == 'checkuserexists') {

	if (C::t('user') -> fetch_by_username(trim($_GET['username']))) {
		showmessage('<img src="' . $_G['style']['imgdir'] . '/check_right.gif" width="13" height="13">', '', array(), array('msgtype' => 3));
		
	} else {
		showmessage('username_nonexistence', '', array(), array('msgtype' => 3));
	}

}
