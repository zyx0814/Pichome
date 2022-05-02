<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}

define('NOROBOT', TRUE);
global $_G;
if(isset($_GET['lostpwsubmit'])) {
    $_GET['email'] = (trim($_GET['email']));
    $type = $_GET['returnType'];
	$lostpw_to=intval($_GET['lostpw_to']);
	if(isemail($_GET['email'])){
        $member = C::t('user')->fetch_by_email($_GET['email'], 1);
        
    }elseif(isphone($_GET['email'])){
		 $member = C::t('user')->fetch_by_phone($_GET['email'], 1);
	}
	if(!$member) {
		 $member = C::t('user')->fetch_by_nickname($_GET['email'],1);
	}
    if(!$member) {
        showTips(array('error'=>lang('apology_account_data_mismatching')),$type);
    } elseif($member['adminid'] == 1) {
        showTips(array('error'=>lang('administrator_account_not_allowed_find')),$type);
    }
/*

    if($member['username'] != $_GET['username']) {

        showTips(array('error'=>lang('apology_account_data_mismatching')),$type);
    }*/
	if($lostpw_to==1){
		if(empty($member['phone'])){
			showTips(array('error'=>"No mobile phone number is bound, please contact the administrator"),$type);
		}
		showTips(array('error'=>'redirect','url'=>'user.php?mod=login&op=logging&action=getPasswdBySms&uid='.$member['uid']));
		
	}else{
		$tmp['email'] = $member['email'];
		$idstring = random(6);
		C::t('user')->update($member['uid'], array('authstr' => "$_G[timestamp]\t1\t$idstring"));
		//require_once libfile('function/mail');
		$get_passwd_subject = lang('email', 'get_passwd_subject');
		$get_passwd_message = lang(

			'get_passwd_message',
			array(
				'username' => $member['username'],
				'sitename' => $_G['setting']['sitename'],
				'siteurl' => $_G['siteurl'],
				'uid' => $member['uid'],
				'idstring' => $idstring,
				'clientip' => $_G['clientip'],
			)
		);

		if(!sendmail("$_GET[username] <$tmp[email]>", $get_passwd_subject, $get_passwd_message)) {
			runlog('sendmail', "$tmp[email] sendmail failed.");
			showTips(array('error'=>"Failed to send email to  \"$tmp[email]\", please contact the administrator"),$type);
		}
		showTips(array('success'=>array('msg'=>lang('password_has_been_sent_email',array('email'=>$tmp['email'])).lang('please_tree_edit_password'),'url'=>$_G['siteurl'], 'email'=>$tmp['email']),$type));
		
	}

}else{

    include template('lostpasswd');
}