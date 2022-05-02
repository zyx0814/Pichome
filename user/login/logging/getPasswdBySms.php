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
if($_GET['operation']=='resetPasswd'){
	if($_SESSION['sms_code_'.$uid]['pass']<1){
		header("Loction:user.php?mod=login&op=logging&action=getPasswdBySms&uid=".$uid);
		exit();
	}
	 if($_GET['newpasswd1'] != addslashes($_GET['newpasswd1'])) {
            showmessage(lang('profile_passwd_illegal'));
        }
        if($_G['setting']['pwlength']) {
            if(strlen($_GET['newpasswd1']) < $_G['setting']['pwlength']) {
                showmessage(lang('profile_password_tooshort', array('pwlength' => $_G['setting']['pwlength'])));
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
                showmessage(lang('password_weak').implode(',', $strongpw_str));
            }
        }
        $salt=substr(uniqid(rand()), -6);

        $password = md5(md5($_GET['newpasswd1']).$salt);
       
        C::t('user')->update($_GET['uid'], array('password' => $password,'authstr' => '','salt'=>$salt));
		$_SESSION['sms_code_'.$uid]=array();
        showmessage(lang('getpasswd_succeed'), 'index.php', array());
}elseif($_GET['operation']=='sms_send'){
   $phone=$member['phone'];
   $re=sms('sms_login_code',$phone);
   if($re['error']){
	   exit(json_encode($re));
   }else{
	   $_SESSION['sms_code_'.$uid]=$re;
	   exit(json_encode(array('msg'=>'success')));
   }
}elseif(!submitcheck('getpwsubmit')) {
	$hashid = $_GET['id'];
	include template('getpasswd_sms');
} else {
	 $codearr=$_SESSION['sms_code_'.$uid];
	if($codearr['startdate']<(TIMESTAMP-$codearr['expire']*60)) {
		showmessage(lang('sms_code_timeout'));
	}
	 if($_GET['code'] != $codearr['code']) {
		showmessage(lang('sms_code_illegal'));
	}
	$_SESSION['sms_code_'.$uid]['pass']=1;
	include template('getpasswd_sms_success');
   exit();
}

