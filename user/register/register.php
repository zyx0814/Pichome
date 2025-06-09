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
session_start();
$setting = $_G['setting'];

$showregisterform = 1;

Hook::listen('register_before');//注册预处理钩子

if($_G['uid']) {
			
	$url_forward = dreferer();
	if(strpos($url_forward, 'user.php') !== false) {
		$url_forward = 'index.php';
	}
	
	showmessage('login_succeed', $url_forward ? $url_forward : $_G['siteurl'], array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['uid']), array());
} elseif($setting['bbclosed']) {
	showmessage(lang('site_closed_please_admin'));
} elseif(!$setting['regclosed']) {	
	if($_GET['action'] == 'activation' || $_GET['activationauth']) {
		if(!$setting['ucactivation'] && !$setting['closedallowactivation']) {
			showmessage('register_disable_activation');
		}
	} elseif(!$setting['regstatus']) {
		showmessage(!$setting['regclosemessage'] ? 'register_disable' : str_replace(array("\r", "\n"), '', $setting['regclosemessage']));
	}
}
$seccodecheck = $setting['seccodestatus'] & 1;
$smsauth=0;
$smssetting=$setting['sms_setting'];
if($smssetting['enable']>0 && in_array('2',$smssetting['scope'])){
	$smsauth=1;
	$seccodecheck=0;
}

//判断是否提交
if(!submitcheck('regsubmit', 0, $seccodecheck)) {

        //应用注册页挂载点
        Hook::listen('appregister');
		$bbrules = $setting['bbrules'];
		
		$regname =$setting['regname'];
		
		$username = isset($_GET['username']) ? dhtmlspecialchars($_GET['username']) : '';
		
		$navtitle = $setting['reglinkname'];

		$dreferer = dreferer();

		include template('register');
		exit();
}else{
	
    Hook::listen('check_val',$_GET);//用户数据验证钩子,用户注册资料信息提交验证
	$result=$_GET;
	
    Hook::listen('register_common',$result);//用户注册钩子
    $type = isset($_GET['returnType']) ? $_GET['returnType']:'';
   
    //获取ip
    $ip = $_G['clientip'];
    //用户状态表数据
    $status = array(
        'uid' => $result['uid'],
        'regip' => (string)$ip,
        'lastip' => (string)$ip,
        'lastvisit' => TIMESTAMP,
        'lastactivity' => TIMESTAMP,
        'lastsendmail' => 0
    );
    //插入用户状态表
    \DB::insert('user_status',$status,1); 

    //新用户登录
    setloginstatus(array(
        'uid' => $result['uid'],
        'username' => $result['username'],
        'password' => $result['password'],
        'groupid' => $result['groupid'],
    ), 0);

    //设置显示提示文字
    $param = daddslashes(array('sitename' => $setting['sitename'], 'username' => $result['username'], 'usergroup' => $_G['cache']['usergroups'][$result['groupid']]['grouptitle'], 'uid' => $result['uid']));

    $messageText = lang('register_succeed', $param);

    //获取之前的链接
    $url_forward = (isset($_GET['referer'])) ? $_GET['referer']:dreferer();


    $url_forward = $url_forward ? $url_forward : $_G['siteurl'];
    if(strpos($url_forward, 'user.php') !== false) {
		$url_forward = 'index.php';
	}
    showTips(array('success'=>array('message'=>$messageText,'url_forward'=>$url_forward)),$type);

}

