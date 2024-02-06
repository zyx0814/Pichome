<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/3
 * Time: 17:27
 */
if (!defined('IN_OAOOA') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}
if(!defined('IS_API')) define('IS_API',1);
Hook::listen('adminlogin');
include_once libfile('function/cache');
include_once libfile('function/organization');
$operation = empty($_GET['operation']) ? 'basic' : trim($_GET['operation']);
$setting = C::t('setting')->fetch_all(null);
$checkLanguage = checkLanguage();
//基本设置
if ($operation == 'basic') {
    $serverspace = C::t('local_storage')->fetch_all_orderby_disp();
    if (!submitcheck('settingsubmit')) {
        $navtitle = lang('members_verify_base');
        $spacesize = DB::result_first("select maxspacesize from " . DB::table('usergroup_field') . " where groupid='9'");
        include_once libfile('function/organization');

        if ($setting['defaultdepartment']) {
            $patharr = getPathByOrgid($setting['defaultdepartment']);
            $defaultdepartment = implode(' - ', ($patharr));

        }
        if (empty($defaultdepartment)) {
            $defaultdepartment = lang('no_join_agency_department');
            $setting['defaultdepartment'] = 'other';
        }
        $setting['defaultsettingid'] = $setting['defaultspacesetting']['remoteid'];
        $applist = DB::fetch_all("select appname,identifier from %t where isshow>0 and `available`>0 and position>0 and app_path='dzz' ORDER BY disp", array('app_market'));
        $params = array();
        $vapps = Hook::listen('vapplist', $params, null, true);
        foreach ($vapps as $key => $value) {
            $params = array('name' => 'vapp/' . $value['identifier'], 'perm' => 1, 'return_type' => 'bool');
            if (Hook::listen('rolecheck', $params, null, true) === false) continue;
            $applist[] = array('appname' => $value['appname'], 'identifier' => 'vapp_' . $value['identify']);
        }

        $setting['sitelogoPath'] =getglobal('setting/attachurl').'sitelogo/sitelogo.png?'.VERHASH;

        exit(json_encode(array('settingdata' => $setting, 'appdata' => $applist,'serverspace'=>$serverspace)));
    } else {
        $settingnew = $_GET['settingnew'];
        $settingnew['bbname'] = $settingnew['sitename'];
        if(isset($settingnew['pathinfo'])){
            $data = ['pathinfo'=>$settingnew['pathinfo']];
            $routefile = CACHE_DIR . BS . 'route' . EXT;
            foreach(DB::fetch_all("SELECT * FROM %t where 1",array('pichome_route')) as $value) {
                $data[$value['url']]=$value['path'];
            }
            //写入缓存文件
            @file_put_contents($routefile,"<?php \t\n return ".var_export($data,true).";");
        }
        if($settingnew['defaultsettingid']){
            $remoteid = $settingnew['defaultsettingid'];
            $storagedata = C::t('local_storage')->fetch_by_remoteid($remoteid);
            $hostdataarr = explode(':',$storagedata['hostname']);
            $defaultspacesettingdata = [
                'bucket'=>$storagedata['bucket'],
                'bz'=>$storagedata['bz'],
                'remoteid'=>$remoteid,
                'region'=> ($storagedata['bz'] == 'ALIOSS') ? $storagedata['hostname']:$hostdataarr[1],
                'did'=>$storagedata['did'],
                'host'=>$storagedata['host'],
            ];
            unset($settingnew['defaultsettingid']);
            if( DB::update('local_storage',array('isdefault'=>0),'1') &&DB::update('local_storage',array('isdefault'=>1),array('remoteid'=>$remoteid))){
                $settingnew['defaultspacesetting'] = $defaultspacesettingdata;
            }
            
            
        }
        foreach ($settingnew['thumbsize'] as $key => $value) {
            $value['width'] = intval($value['width']);
            if (!$value['width']) {
                $value['width'] = ($key == 'samll' ? 256 : ($key == 'middle' ? 800 : 1440));
            }
            $value['height'] = intval($value['height']);
            if (!$value['height']) {
                $value['height'] = ($key == 'samll' ? 256 : ($key == 'middle' ? 600 : 900));
            }
            $settingnew['thumbsize'][$key] = $value;
        }

        //设置默认应用
        if (!$settingnew["default_mod"] || $settingnew["default_mod"] != $_GET["old_default_mod"]) {
            $configfile = DZZ_ROOT . 'data/cache/default_mod.php';
            $configarr = array();
            //if (strpos($settingnew["default_mod"], 'vapp_') !== 0) $settingnew["default_mod"] = 'index';
            //$configarr['default_mod'] = $settingnew["default_mod"];
            $configarr['default_mod'] = 'banner';
            @file_put_contents($configfile, "<?php \t\n return " . var_export($configarr, true) . ";");
        }


      /*  if ($settingnew['sitelogo'] && $settingnew['sitelogo'] != $setting['sitelogo']) {
            if ($setting['sitelogo']) C::t('attachment')->delete_by_aid($setting['sitelogo']);
            C::t('attachment')->addcopy_by_aid($settingnew['sitelogo'], 1);
        }*/
        updatesetting($setting,$settingnew);
        exit(json_encode(array('success'=>true)));
    }
}elseif($operation == 'upload'){//上传设置
    if(!submitcheck('settingsubmit')){
        $setting['maxChunkSize'] = round($setting['maxChunkSize'] / (1024 * 1024), 2);
        $navtitle = lang('upload_set');
        $setting['unRunExts'] = implode(',', $setting['unRunExts']);
        $usergroups = DB::fetch_all("select f.*,g.grouptitle from %t f LEFT JOIN %t g ON g.groupid=f.groupid where f.groupid NOT IN ('2','3','4','5','6','7','8') order by groupid DESC", array('usergroup_field', 'usergroup'));
        exit(json_encode(array('setting'=>$setting)));
    }else{
		$settingnew = $_GET['settingnew'];
        if ($settingnew['unRunExts'])
            $settingnew['unRunExts'] = explode(',', trim($settingnew['unRunExts'], ','));
        else
            $settingnew['unRunExts'] = array();
        if (!in_array('php', $settingnew['unRunExts']))
            $settingnew['unRunExts'][] = 'php';
        $settingnew['maxChunkSize'] = intval($settingnew['maxChunkSize'] * 1024 * 1024);
        $group = $_GET['group'];
        foreach ($group as $key => $value) {
            C::t('usergroup_field') -> update(intval($key), array('maxspacesize' => intval($value['maxspacesize']), 'maxattachsize' => intval($value['maxattachsize']), 'attachextensions' => trim($value['attachextensions'])));
        }
        updatesetting($setting,$settingnew);
        include_once libfile('function/cache');
        updatecache('usergroups');
        exit(json_encode(array('success'=>true)));
    }
}elseif($operation == 'loginset'){//登录页设置
    if(!submitcheck('settingsubmit')) {
        exit(json_encode(array('setting'=>$setting)));
    }else{
		$settingnew = $_GET['settingnew'];
        if ($back = trim($settingnew['loginset']['background'])) {
            if (strpos($back, '#') === 0) {
                $settingnew['loginset']['bcolor'] = $back;
            } else {
                $arr = explode('.', $back);
                $ext = array_pop($arr);
                if ($ext && in_array(strtolower($ext), array('jpg', 'jpeg', 'gif', 'png','webp'))) {
                    $settingnew['loginset']['img'] = $back;
                    $settingnew['loginset']['bcolor'] = '';
                } else {
                    $settingnew['loginset']['url'] = $back;
                    $settingnew['loginset']['bcolor'] = '';
                }
            }
        } else {
            $settingnew['loginset']['bcolor'] = '';
        }
        updatesetting($setting,$settingnew);
        exit(json_encode(array('success'=>true)));
    }

}elseif($operation == 'access'){//登录设置
    if(!submitcheck('settingsubmit')) {
        exit(json_encode(array('setting'=>$setting)));
    }else{
		$settingnew = $_GET['settingnew'];
        isset($settingnew['reglinkname']) && empty($settingnew['reglinkname']) && $settingnew['reglinkname'] = lang('register_immediately');
        $settingnew['pwlength'] = intval($settingnew['pwlength']);
        $settingnew['regstatus'] = intval($settingnew['regstatus']);

        if (empty($settingnew['strongpw'])) {
            $settingnew['strongpw'] = array();
        }
        updatesetting($setting,$settingnew);
        exit(json_encode(array('success'=>true)));
    }
}elseif($operation == 'mail'){
    if(!submitcheck('settingsubmit')) {
        $passwordmask = $setting['mail']['auth_password'] ? $setting['mail']['auth_password']{0} . '********' . substr($setting['mail']['auth_password'], -2) : '';
        $smtps = array();
        foreach ($setting['mail']['smtp'] as $id => $smtp) {
            $smtp['auth'] = $smtp['auth'] ? true : false;
            $smtp['auth_password'] = $smtp['auth_password'] ? $smtp['auth_password']{0} . '********' . substr($smtp['auth_password'], -2) : '';
			$smtp['id'] = $id;
            $smtps[] = $smtp;
        }
        exit(json_encode(array('setting'=>$setting,'smtps'=>$smtps)));
    }else{
		$settingnew = $_GET['settingnew'];
        $settingnew['mail'] = ($settingnew['mail']);
        $oldsmtp = array();
		if(intval($settingnew['mail']['mailsend']) == 3){
			$oldsmtp = $settingnew['mail']['smtp'];
		}elseif(intval($settingnew['mail']['mailsend']) == 2){
			$oldsmtp = $settingnew['mail']['esmtp'];
		}
        // $deletesmtp = $settingnew['mail']['mailsend'] != 1 ? ($settingnew['mail']['mailsend'] == 3 ? $settingnew['mail']['smtp']['delete'] : $settingnew['mail']['esmtp']['delete']) : array();
        $settingnew['mail']['smtp'] = array();
		
        foreach ($oldsmtp as $id => $value) {
            // if ((empty($deletesmtp) || !in_array($id, $deletesmtp)) && !empty($value['server']) && !empty($value['port'])) {
            //     $passwordmask = $setting['mail']['smtp'][$id]['auth_password'] ? $setting['mail']['smtp'][$id]['auth_password']{0} . '********' . substr($setting['mail']['smtp'][$id]['auth_password'], -2) : '';
            //     $value['auth_password'] = $value['auth_password'] == $passwordmask ? $setting['mail']['smtp'][$id]['auth_password'] : $value['auth_password'];
            //     $settingnew['mail']['smtp'][] = $value;
            // }
			if ( !$value['delete'] && !empty($value['server']) && !empty($value['port'])) {
			    $passwordmask = $setting['mail']['smtp'][$id]['auth_password'] ? $setting['mail']['smtp'][$id]['auth_password']{0} . '********' . substr($setting['mail']['smtp'][$id]['auth_password'], -2) : '';
			    $value['auth_password'] = $value['auth_password'] == $passwordmask ? $setting['mail']['smtp'][$id]['auth_password'] : $value['auth_password'];
			    $settingnew['mail']['smtp'][] = $value;
			}
        }

        // if (!empty($_GET['newsmtp'])) {
        //     foreach ($_GET['newsmtp']['server'] as $id => $server) {
        //         if (!empty($server) && !empty($_GET['newsmtp']['port'][$id])) {
        //             $settingnew['mail']['smtp'][] = array('server' => $server, 'port' => $_GET['newsmtp']['port'][$id] ? intval($_GET['newsmtp']['port'][$id]) : 25, 'auth' => $_GET['newsmtp']['auth'][$id] ? 1 : 0, 'from' => $_GET['newsmtp']['from'][$id], 'auth_username' => $_GET['newsmtp']['auth_username'][$id], 'auth_password' => $_GET['newsmtp']['auth_password'][$id]);
        //         }
        //     }
        // }
        updatesetting($setting,$settingnew);
        exit(json_encode(array('success'=>true)));
    }
}elseif($operation == 'uploadsitelogo'){
    global $_G;
    $files = $_FILES['files'];

    if($files["type"] != 'image/png' || $files['size'] >= 1024*1024*2){
        exit(json_encode(array('error'=>'file is not invalite')));
    }

    $logopath = 'sitelogo/sitelogo.png';
    $logofilepath =$_G['setting']['attachdir'] .$logopath;
    $logodir = dirname($logofilepath);
    dmkdir($logodir);
    //获取md5
    $logomd5 = md5_file($files["tmp_name"]);
    $return = move_uploaded_file($files["tmp_name"],$logofilepath);
    if($return){
        updatecache('setting');
        exit(json_encode(array('success'=>true)));
    }else{
        exit(json_encode(array('success'=>false)));
    }


}
elseif($operation == 'mailcheck'){//邮件检测
    global $_G;
	if(!submitcheck('settingsubmit')) {
		$op = $_GET['op']?$_GET['op']:' ';
		$navtitle=lang('email_send_test');
	}else{
		if(!is_array($_G['setting']['mail'])) {
			$_G['setting']['mail'] = dunserialize($_G['setting']['mail']);
		}
		
		$test_to =  $_GET['settingnew']['test_to'];
		$test_from =  $_GET['settingnew']['test_from'];
		$date = date('Y-m-d H:i:s');
		$alertmsg = '';
	
		$title = lang('setting_mail_check_title_'.$_G['setting']['mail']['mailsend']);
		$message = lang('setting_mail_check_message_'.$_G['setting']['mail']['mailsend']).' '.$test_from.lang('setting_mail_check_date').' '.$date;
	
		$_G['setting']['bbname'] = lang('setting_mail_check_method_1');
		include libfile('function/mail');
		$succeed = sendmail($test_to, $title.' @ '.$date, $_G['setting']['bbname']."\n\n\n$message", $test_from);
		$_G['setting']['bbname'] = lang('setting_mail_check_method_2');
		$succeed = sendmail($test_to, $title.' @ '.$date, $_G['setting']['bbname']."\n\n\n$message", $test_from);
		if($succeed) {
			$alertmsg = lang('setting_mail_check_success_1')."$title @ $date".lang('setting_mail_check_success_2');
			exit(json_encode(array('success'=>$alertmsg)));
		} else {
			$alertmsg = lang('setting_mail_check_error').$alertmsg;
			exit(json_encode(array('error'=>$alertmsg)));
		}
		
	}
}elseif($operation == 'image'){//缩略图设置
    if(!submitcheck('settingsubmit')) {
        $setting['thumbsize'] = ($setting['thumbsize']);
        foreach ($setting['thumbsize'] as $key => $value) {
            $value['width'] = intval($value['width']);
            if (!$value['width']) {
                $value['width'] = ($key == 'samll' ? 256 : ($key == 'middle' ? 800 : 1440));
            }
            $value['height'] = intval($value['height']);
            if (!$value['height']) {
                $value['height'] = ($key == 'samll' ? 256 : ($key == 'middle' ? 600 : 900));
            }
            $setting['thumbsize'][$key] = $value;
        }
        $setting['waterimg'] = isset($setting['waterimg']) ? $setting['waterimg']:'';;
        exit(json_encode(array('setting'=>$setting)));
    }else{
		$settingnew = $_GET['settingnew'];
        foreach ($settingnew['thumbsize'] as $key => $value) {
            $value['width'] = intval($value['width']);
            if (!$value['width']) {
                $value['width'] = ($key == 'samll' ? 256 : ($key == 'middle' ? 800 : 1440));
            }
            $value['height'] = intval($value['height']);
            if (!$value['height']) {
                $value['height'] = ($key == 'samll' ? 256 : ($key == 'middle' ? 600 : 900));
            }
            $settingnew['thumbsize'][$key] = $value;
        }
        updatesetting($setting,$settingnew);
        exit(json_encode(array('success'=>true)));
    }
}elseif($operation == 'waterimg'){
    global $_G;
    $files = $_FILES['file'];

    if($files["type"] != 'image/png' || $files['size'] >= 1024*1024*2){
        exit(json_encode(array('error'=>'file is not invalite')));
    }

    $waterpath = 'waterimg/water.png';
    $waterfilepath =$_G['setting']['attachdir'] .$waterpath;
    $waterdir = dirname($waterfilepath);
    dmkdir($waterdir);
    //获取缩略图的md5
    $watermd5 = md5_file($files["tmp_name"]);
    $return = move_uploaded_file($files["tmp_name"],$waterfilepath);

    if($return){
        //rename($waterpath.$files['name'],$waterpath.'water.png');
        //@unlink($_G['setting']['waterimg']);
        //C::t('setting')->update('waterimg',$_G['setting']['attachurl'] .$waterpath);
        C::t('setting')->update('watermd5',$watermd5);
        updatecache('setting');
        if(!isset($_G['setting']['defaultspacesetting'])){
            //如果未查询到默认存储位置设置
            $settingdata = C::t('setting')->fetch_all('defaultspacesetting',true);
            if($settingdata){
                $defaultspacesetting = $settingdata['defaultspacesetting'];
            }else{
                //获取默认存储位置
                $space = C::t('connect_storage')->fetch_default_space();
                $hostdataarr = explode(':',$space['hostname']);
                $defaultspacesettingdata = [
                    'bucket'=>$space['bucket'],
                    'bz'=>$space['bz'],
                    'remoteid'=>$space['id'],
                    'region'=> ($space['bz'] == 'ALIOSS') ? $space['hostname']:$hostdataarr[1],
                    'did'=>$space['id'],
                    'host'=>$space['host'],
                ];
                $defaultspacesetting = $defaultspacesettingdata;
            }
        }else{
            $defaultspacesetting = $_G['setting']['defaultspacesetting'];
        }

        if($defaultspacesetting['bz'] == 'ALIOSS'){
            $did = $defaultspacesetting['did'];
            include_once DZZ_ROOT . './core/api/oss_sdk/autoload.php';
            $ossconfig = C::t('connect_storage')->fetch($did);
            $access_id = $ossconfig['access_id'];
            $access_key = dzzdecode($ossconfig['access_key'], 'ALIOSS');
            $osswaterpath = 'static/waterimg/water.png';
           $oss =  new OSS\OssClient($access_id, $access_key,  $ossconfig['hostname']);
           try{
               $return = $oss->putObject($ossconfig['bucket'],$osswaterpath, file_get_contents($waterfilepath));
           }catch (\Exception $e){
               echo $e->getMessage();
               exit(json_encode(array('error'=>'oss create error')));
           }
        }
        elseif($defaultspacesetting['bz'] =='QCOS' ){
            $did = $defaultspacesetting['did'];
            $qcosconfig = C::t('connect_storage')->fetch($did);
            $qcoswaterpath = '/static/waterimg/water.png';
                include_once DZZ_ROOT . './core/api/Qcos/vendor/autoload.php';
                $hostnamearr = explode(':', $qcosconfig['hostname']);
                $schema = isset($hostnamearr[0]) ? $hostnamearr[0] : 'http';
                $region = isset($hostnamearr[1]) ? $hostnamearr[1] : 'ap-beijing';
                $qcos_config = [
                    'credentials' => array(
                        'secretId' => $qcosconfig['access_id'],
                        'secretKey' => dzzdecode($qcosconfig['access_key'], 'QCOS')),
                    'region' =>$region,
                    'schema' => $schema,
                    'bucket'=>$qcosconfig['bucket']
                ];
                $qcos = new Qcloud\Cos\Client($qcos_config);
                try {
                    $save_path = $qcos->putObject(array('Bucket' => $qcos_config['bucket'], 'Key' => $qcoswaterpath, 'Body' =>fopen($waterfilepath,'rb')));
                    //C::t('setting')->update('qcoswaterimg','QCOS:'.$did.':'.$qcos_config['bucket'].$qcoswaterpath);
                    //updatecache('setting');
                } catch (\Exception $e) {
                    exit(json_encode(array('error'=>'qcos create error')));
                }
       
        exit(json_encode(array('path'=>$_G['setting']['attachurl'] .$waterpath)));
    }
        exit(json_encode(array('path'=>getglobal('setting/attachurl').$waterpath.'?'.VERHASH)));
    }else{
        exit(json_encode(array('error'=>'upload failer')));
    }
}elseif($operation == 'watermark'){//水印设置
    if(!submitcheck('settingsubmit')) {
        $fontlist = array();
        $dir = opendir(DZZ_ROOT.'./static/image/seccode/font/en');
        while($entry = readdir($dir)) {
            if(in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
                $fontlist[]=$entry;
            }
        }
        $dir = opendir(DZZ_ROOT.'./static/image/seccode/font/ch');
        while($entry = readdir($dir)) {
            if(in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
                $fontlist[]=$entry;
            }
        }
        exit(json_encode(array('setting'=>$setting,'fontlist'=>$fontlist)));
    }else{
		$settingnew = $_GET['settingnew'];
		//更新水印
		if(isset($settingnew['updatethumbwater']) && $settingnew['updatethumbwater']){
            C::t('thumb_record')->update_waterstatus();
        }
		unset($settingnew['updatethumbwater']);
        updatesetting($setting,$settingnew);
        exit(json_encode(array('success'=>true)));
    }
}

function dateformat($string, $operation = 'formalise')
{
    $string = dhtmlspecialchars(trim($string));
    $replace = $operation == 'formalise' ? array(array('n', 'j', 'y', 'Y'), array('mm', 'dd', 'yy', 'yyyy')) : array(array('mm', 'dd', 'yyyy', 'yy'), array('n', 'j', 'Y', 'y'));
    return str_replace($replace[0], $replace[1], $string);
}
//更新设置函数
function updatesetting($setting, $settingnew)
{
    $updatecache = false;
    $settings = array();
    $updatethumb = false;
    foreach ($settingnew as $key => $val) {
        if ($setting[$key] != $val) {
            $updatecache = TRUE;
            if (in_array($key, array('timeoffset', 'regstatus', 'oltimespan', 'seccodestatus'))) {
                $val = (float)$val;
            }
            $settings[$key] = $val;
        }
    }
    if ($settings) {
        C::t('setting')->update_batch($settings);
    }
    if ($updatecache) {
        updatecache('setting');
    }
    return true;
}
