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
$navtitle=lang('getpassword');
define('NOROBOT', TRUE);
$type = $_GET['returnType'];
if($_GET['uid'] && $_GET['id']) {

    $dzz_action = 141;
    $member = C::t('user')->get_user_by_uid($_GET['uid']);
    $table_ext =  '';
    list($dateline, $operation, $idstring) = explode("\t", $member['authstr']);

    if($dateline < TIMESTAMP - 86400 * 3 || $operation != 1 || $idstring != $_GET['id']) {
		//showTips(array('error'=>lang('getpasswd_illegal')),'html');
		showmessage(lang('getpasswd_illegal'));
    }

    if(!submitcheck('getpwsubmit')) {
        $hashid = $_GET['id'];
        $uid = $_GET['uid'];
        include template('pc/page/getpasswd');
    } else {
        
		if($_GET['newpasswd'] != ($_GET['newpasswd1'])) {
             showTips(array('error'=>lang('password_not_match')),$type);
        }
        if($_G['setting']['pwlength']) {
            if(strlen($_GET['newpasswd1']) < $_G['setting']['pwlength']) {
				 showTips(array('error'=>lang('profile_password_tooshort', array('pwlength' => $_G['setting']['pwlength']))),$type);
            }
        }
        if($_G['setting']['strongpw']) {
            $strongpw_str = array();
            if(in_array(1, $_G['setting']['strongpw']) && !preg_match("/\d+/", $_GET['newpasswd1'])) {
                $strongpw_str[] = lang('strongpw_1');
            }
            if(in_array(2, $_G['setting']['strongpw']) && !preg_match("/[a-z]+/", $_GET['newpasswd1'])) {
                $strongpw_str[] = lang('strongpw_2');
            }
            if(in_array(3, $_G['setting']['strongpw']) && !preg_match("/[A-Z]+/", $_GET['newpasswd1'])) {
                $strongpw_str[] = lang('strongpw_3');
            }
            if(in_array(4, $_G['setting']['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $_GET['newpasswd1'])) {
                $strongpw_str[] = lang('strongpw_4');
            }
            if($strongpw_str) {
				 showTips(array('error'=>lang('password_weak').implode(',', $strongpw_str)),$type);
            }
        }
        $salt=substr(uniqid(rand()), -6);

        $password = md5(md5($_GET['newpasswd']).$salt);
        C::t('user')->update($_GET['uid'], array('password' => $password,'authstr' => '','salt'=>$salt));
		showTips(array('success'=>array('message'=>lang('getpasswd_succeed'),'url_forward'=>$_G['siteurl'])),$type);
    }

} else {
    showmessage(lang('parameters_error'));
}