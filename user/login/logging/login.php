<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/3/1
 * Time: 18:53
 */
if (!defined('IN_OAOOA')) {

    exit('Access Denied');
}
session_start();
global $_G;
$_G['setting']['reglinkname']=lang($_G['setting']['reglinkname']);
if($_G['uid']>0){
	  $param = array(
            'username' => $_G['username'],
            'usergroup' => $_G['group']['grouptitle'],
            'uid' => $_G['uid'],
            'groupid' => $_G['groupid'],
            'syn' =>  0
        );
        $loginmessage =  'login_succeed';

        $location = dreferer();//待修改
        
        $href = str_replace("'", "\'", $location);
        $href = preg_replace("/user\.php\?mod\=login.*?$/i", "", $location);
	
        $messageText = lang($loginmessage, $param);
        writelog('loginlog', lang('login_success'));
        $isadmin = (getglobal('adminid')) ? 1 : 0;
       addLoginStats(lang('login_success'),$isadmin);
	if($_GET['returnType']=='json'){
		exit(json_encode(array('success'=>array('message'=>$messageText,'url_forward'=>$href))));
	}
	showmessage($messageText,$href);
}

$setting = isset($_G['setting']) ? $_G['setting']:'';

if(empty($setting)){

	$setting= C::t('setting')->fetch_all(array(),true);
}

//Hook::listen('login_check');//检查登录状态

if(!isset($_GET['loginsubmit'])) {//是否提交

    $username = !empty($_G['cookie']['loginuser']) ? dhtmlspecialchars($_G['cookie']['loginuser']) : '';

    $cookietimecheck = !empty($_G['cookie']['cookietime']) || !empty($_GET['cookietime']) ? 'checked="checked"' : '';
    $referer = (isset($_GET['referer'])) ? ($_GET['referer']):dreferer();
    $navtitle = lang('title_login');
	
	include template('pc/page/login_single'.($_GET['template']?$_GET['template']:(isset($setting['loginset']['template']) ? $setting['loginset']['template'] : 1)));
	
} else {
    $type = isset($_GET['returnType']) ?  $_GET['returnType']: 'json';//返回值方式

    Hook::listen('login_valchk',$_GET);//验证登录输入值及登录失败次数

    //登录
    $result = userlogin($_GET['email'], $_GET['password'], $_GET['questionid'], $_GET['answer'],'auto', $_G['clientip']);
   
    if($result['status']== -2){

        showTips(array('error'=>lang('user_stopped_please_admin')),$type);


    }elseif($_G['setting']['bbclosed']>0 && $result['member']['adminid']!=1){

        showTips(array('error'=>lang('site_closed_please_admin')),$type);
    }

    if($result['status'] > 0) {
		$lastLoginTime=getCookie('logintime');
		
		if(!defined('PICHOME_LIENCE') && !C::t('user')->checkfounder($result['member'])){
            showTips(array('error'=>lang('personversion_no_create_unablelogin')),$type);
        }
        //设置登录
        setloginstatus($result['member'], $_GET['cookietime'] ?  2592000:0 );

        if($_G['member']['lastip'] && $_G['member']['lastvisit']) {

            dsetcookie('lip', $_G['member']['lastip'].','.$_G['member']['lastvisit']);
        }

        //记录登录
        C::t('user_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'lastvisit' =>TIMESTAMP, 'lastactivity' => TIMESTAMP));

        //登录成功提示信息
        $param = array(
            'username' => $result['ucresult']['username'],
            'usergroup' => $_G['group']['grouptitle'],
            'uid' => $_G['member']['uid'],
            'groupid' => $_G['groupid'],
            'syn' =>  0
        );
        $loginmessage = 'login_succeed';
        $location = dreferer();//待修改
        $href = str_replace("'", "\'", $location);
        $href = preg_replace("/user\.php\?mod\=login.*?$/i", "", $location);

        $messageText = lang($loginmessage, $param);
        writelog('loginlog', lang('login_success'));
        $isadmin = (getglobal('adminid')) ? 1 : 0;
        addLoginStats(lang('login_success'),$isadmin);
        showTips(array('success'=>array('message'=>$messageText,'url_forward'=>$href,'formhash'=>formhash())),$type);


    } else {//登录失败记录日志 
        //写入日志
        $errorlog=lang('user').($result['ucresult']['email'] ? $result['ucresult']['email'] : $_GET['email']).lang('try_log')."[".$_GET['password']."]"."\t".lang('error');
        writelog('loginlog', $errorlog);
        $isadmin = (getglobal('adminid')) ? 1 : 0;
        addLoginStats(lang('login_success'),$isadmin);
        loginfailed($_GET['email']);//更新登录失败记录

        if($_G['member_loginperm'] > 1) {

            showTips(array('error'=>lang('login_invalid', array('loginperm' => $_G['member_loginperm'] - 1))),$type);

        } elseif($_G['member_loginperm'] == -1) {

            showTips(array('error'=>lang('login_password_invalid')),$type);

        } else {

            showTips(array('error'=>lang('login_strike')),$type);
        }
    }


}
