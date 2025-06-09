<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/11/28
 * Time: 14:32
 */
if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}
$ismobile=helper_browser::ismobile();

$navtitle=lang('appname');
Hook::listen('adminlogin');
$do=$_GET['do'];
if ($do == 'authorize') {//授权信息
        include_once libfile( 'function/cache' );
		$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
	    if ($operation) {
	        if(isset($_FILES['file'])){
                $files = $_FILES['file'];
                $licdata = file_get_contents($files['tmp_name']);
                if(!$licdata) exit(json_encode(array('error'=>true)));
                C::t('setting')->update('sitelicensedata',$licdata);
                updatecache( 'setting' );
                exit(json_encode(array('success' => true)));
            }else{
                exit(json_encode(array('error'=>true)));
            }
	    }
    }
    elseif($do == 'updateauth'){
        include_once libfile( 'function/cache' );
        $username = isset($_GET['username']) ? trim($_GET['username']):'';
        $password = isset($_GET['password']) ? trim($_GET['password']):'';
        $mcode = getglobal('setting/machinecode');
        $datastr = $username."\t".$password."\t".$mcode;
        $data = dzzencode($datastr,$mcode,0,4);
        $authurl = APP_CHECK_URL.'authlicense/getauth/'.$mcode.'/'.$data.'/'. TIMESTAMP;
        $response = json_decode(dfsockopen($authurl,0, '', '', FALSE, '',3),true);
        if(isset($response['authcode'])){
            C::t('setting')->update('sitelicensedata',$response['authcode']);
            updatecache( 'setting' );

        }
        if(isset($response['error']))exit(json_encode(array('error'=>$response['error'])));
        else exit(json_encode(array('success'=>true)));

    }else{
		 $version = defined('LICENSE_VERSION') ? lang(LICENSE_VERSION):lang('Home');
		$limitusernum = defined('LICENSE_LIMIT') ? LICENSE_LIMIT:1;
		if(defined('NOLIMITUSER')) $limitusernum = lang('unlimited');
		$authdate = defined('LICENSE_CTIME') ? dgmdate(LICENSE_CTIME,'Y-m-d H:i:s'):'';
		
		include template('index');
	}
