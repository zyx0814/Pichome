<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

if (!defined('IN_OAOOA') ) {
    exit('Access Denied');
}

  html_login_header();

if ($admincp -> cpaccess == -1 || $admincp -> cpaccess == -4) {
    $ltime = $this -> sessionlife - (TIMESTAMP - $this -> adminsession['dateline']);
    echo '<p class="logintips">' . lang('login_cplock', array('ltime' => $ltime)) . '</p>';

} else {
    html_login_form();
}

html_login_footer();

function html_login_header($form = true) {
    global $_G;
    $uid = getglobal('uid');
    $charset = CHARSET;
    $title = lang('title_admincp');

    echo <<<EOT
<!DOCTYPE>
<html>
<head>
<title>$title</title>
<base href="{$_G['siteurl']}">
<meta http-equiv="Content-Type" content="text/html;charset=$charset" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<link rel="stylesheet" href="admin/login/images/adminlogin.css" type="text/css" media="all" />
<script type="text/javascript" src="static/js/md5.js"></script> 
</head>
<body>
EOT;

    if ($form) {
        $loginset_img=$_G['setting']['loginset']['img']?$_G['setting']['loginset']['img']:'admin/login/images/bg.jpg';
        $loginset_bcolor=$_G['setting']['loginset']['bcolor']?$_G['setting']['loginset']['bcolor']:'#76838f';
        echo <<<EOT
<div id="wrapper_div" style="width: 100%;height:100%;  position: absolute; top: 0px; left: 0px; margin: 0px; padding: 0px; overflow: hidden;z-index:0;  font-size: 0px; background:$loginset_bcolor;"> 
	
	<img src="$loginset_img" name="imgbg" id="imgbg" style="right: 0px; bottom: 0px; top: 0px; left: 0px; z-index:1;margin:0;padding:0;overflow:hidden; position: absolute;width:100%;height:100%" height="100%" width="100%">
</div>
<div class="mainContainer">
<table class="loginContainer" width="100%" height="100%" style="layout:f">
<tr><td align="center" valign="middle">
EOT;
    }
}

function html_login_footer($halt = true) {
    $version = CORE_VERSION;
    $release = CORE_RELEASE;
    echo <<<EOT
</td>
</tr>
</table>
</div>
</body>
</html>

EOT;
    $halt && exit();
}

function html_login_form() {
    global $_G;
    $uid = getglobal('uid');
    $year=dgmdate(TIMESTAMP,'Y');
    $maintitle=lang('title_admincp');
	$placeholder_email=lang('login_email_username');
    $loginuser = empty($uid) ? '<input class="form-control" name="admin_email"  type="text" placeholder="'.$placeholder_email.'"  autocomplete="off" />' : '<div class="username">' . $_G['member']['username'] . '</div><div class="email">' . $_G['member']['email'] . '</div>';
    $sid = getglobal('sid');
    if(!$uid ){
        $avastar ='<img src="data/attachment/sitelogo/sitelogo.png?'.VERHASH.'" />';
    }else{
        $avastar = avatar_block($uid);
    }
	$_GET['referer'] = dhtmlspecialchars($_GET['referer'], ENT_QUOTES);
    $referer = str_replace('&amp;', '&', $_GET['referer']);
    $avastar.='<div class="maintitle">'.$maintitle.'</div>';
    $extra = BASESCRIPT . '?' . $_SERVER['QUERY_STRING'];
	$placeholder_password=lang('password');
	$placeholder_login=lang('login');
    echo <<<EOT
    	
		<form method="post" name="login" id="loginform" action="$extra" onsubmit="pwmd5('admin_password')">
            <input type="hidden" name="sid" value="$sid">
            <input type="hidden" name="referer" value="$referer">
            <div class="loginformContainer">       
                <div class="avatarContainer">$avastar</div>
				
                $loginuser
                <div id="admin_password_Container">
						<input  name="admin_password"  id="admin_password"  type="password" class="form-control"  value="" autocomplete="off" placeholder="$placeholder_password" />

                </div>
                <button name="submit"  type="submit" class="btn btn-primary btn-block btn-lg" >$placeholder_login</button>
                
             </div>
             
		 </form>
		<div class="copyright">Powered by <a href="https://www.oaooa.com/" target="_blank">Pichome</a> &copy; 2012-$year</div>
EOT;
}
?>