<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}

define('NOROBOT', TRUE);
global $_G;
if(isset($_GET['lostpwsubmit'])) {
    $_GET['email'] = (trim($_GET['email']));
    $type = $_GET['returnType'];
	if(isemail($_GET['email'])){
        $member = C::t('user')->fetch_by_email($_GET['email'], 1);
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

		if(!sendmail($member['username']." <".$tmp['email'].">", $get_passwd_subject, $get_passwd_message)) {
			runlog('sendmail', "$tmp[email] sendmail failed.");
			showTips(array('error'=>"Failed to send email to  \"$tmp[email]\", please contact the administrator"),$type);
		}
		showTips(array('success'=>array('msg'=>lang('password_has_been_sent_email',array('email'=>$tmp['email'])).lang('please_tree_edit_password'),'url'=>$_G['siteurl'], 'email'=>$tmp['email']),$type));
		
	

}else{
	$referer=$_GET['referer'];
    include template('pc/page/lostpasswd');
}