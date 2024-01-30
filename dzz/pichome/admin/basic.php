<?php
if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}

$operation = isset($_GET['operation']) ? trim($_GET['operation']) : 'basic';
$themeid = isset($_G['setting']['pichometheme']) ? intval($_G['setting']['pichometheme']):1;
include libfile('function/cache');
global $_G;
$navtitle = '基本设置';
if (isset($_G['setting'])) $setting = $_G['setting'];
else  $setting = C::t('setting')->fetch_all();
$themedata = getthemedata($themeid);
$lefsetdata = $themedata['singlepage'];
if ($operation == 'basic') {
    if (submitcheck('settingsubmit')) {
        $settingnew = $_GET['settingnew'];
        updatesetting($setting, $settingnew);
        dexit(json_encode(array('success' => true)));
    } else {
		$type = isset($_GET['type']) ? trim($_GET['type']) : '';
        if($type=='getdata'){
            exit(json_encode(array('data' => $setting)));
        }else{
            $waterfilepath = $_G['setting']['attachurl'] . 'sitelogo/sitelogo.png';
            if (!file_exists($waterfilepath)) {
                $waterfilepath = '';
            }
            include template('admin/pc/page/basic');
        }

    }
}
elseif ($operation == 'authorize') {//授权信息
	
    include_once libfile( 'function/cache' );
    $operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
    if ($operation) {

        if(isset($_FILES['file'])){
            $files = $_FILES['file'];
            $licdata = file_get_contents($files['tmp_name']);
            if(!$licdata) exit(json_encode(array('error'=>true)));
            C::t('setting')->update('sitelicensedata',$licdata);
            updatecache( 'setting' );
		
            dexit(json_encode(array('success' => true)));
        }else{
            dexit(json_encode(array('error'=>true)));
        }
    } else {
        $version = defined('LICENSE_VERSION') ? lang(LICENSE_VERSION) : lang('Home');
        $limitusernum = defined('LICENSE_LIMIT') ? LICENSE_LIMIT : 1;
        if (defined('NOLIMITUSER')) $limitusernum = '无限';
        $authdate = defined('LICENSE_CTIME') ? dgmdate(LICENSE_CTIME, 'Y-m-d H:i:s') : '';
        include template('admin/pc/page/site/authorize');

    }
}
elseif($operation == 'updateauth'){
    include_once libfile( 'function/cache' );
    $username = isset($_GET['username']) ? trim($_GET['username']):'';
    $password = isset($_GET['password']) ? trim($_GET['password']):'';
    $mcode = getglobal('setting/machinecode');
    $datastr = $username."\t".$password."\t".$mcode;
    $data = dzzencode($datastr,$mcode);
    $authurl = APP_CHECK_URL.'authlicense/getauth/'.$mcode.'/'.$data.'/'. TIMESTAMP;
    $response = json_decode(dfsockopen($authurl,0, '', '', FALSE, '',3),true);
    if(isset($response['authcode'])){
        C::t('setting')->update('sitelicensedata',$response['authcode']);
        updatecache( 'setting' );

    }
    if(isset($response['error']))exit(json_encode(array('error'=>$response['error'])));
    else exit(json_encode(array('success'=>true)));

}
elseif ($operation == 'uploadlogo') {//上传logo
    global $_G;
    $files = $_FILES['file'];
    if ($files["type"] != 'image/png' || $files['size'] >= 1024 * 1024 * 2) {
        exit(json_encode(array('error' => 'file is not invalite')));
    }
    $waterfilepath = $_G['setting']['attachurl'] . 'sitelogo/sitelogo.png';
    $return = move_uploaded_file($files["tmp_name"], $waterfilepath);
    exit(json_encode(array('success' => true)));
}
elseif ($operation == 'pagesetting') {//界面设置
    if (submitcheck('settingsubmit')) {
        $settingnew = $_GET['settingnew'];
        updatesetting($setting, $settingnew);
        exit(json_encode(array('success' => true)));
    } else {
        $waterfilepath = $_G['setting']['attachurl'] . 'sitelogo/sitelogo.png';
        include template('admin/pc/page/site/page');
    }
}
elseif($operation == 'importsetting'){
    if (submitcheck('settingsubmit')) {
        $settingnew = $_GET['settingnew'];
        updatesetting($setting, $settingnew);
        exit(json_encode(array('success' => true)));
    } else {
        $Defaultnotallowdir = json_encode($Defaultnotallowdir);
        include template('admin/pc/page/site/import');
    }
}elseif ($operation == 'loginpage') {//登录页设置

}
elseif ($operation == 'fileterset') {//筛选项设置
    $applist = DB::fetch_all("select appid,appname from %t where isdelete < 1",array('pichome_vapp'));
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    if (submitcheck('settingsubmit')) {
        $settingnew = $_GET['settingnew'] ? $_GET['settingnew'] : array();
        if($appid!='all'){
            $setarr = ['filter' => isset($settingnew['pichomefilterfileds']) ? serialize($settingnew['pichomefilterfileds']):''];
            C::t('pichome_vapp')->update($appid, $setarr);
        }else{
            $settingnew['pichomefilterfileds'] = isset($settingnew['pichomefilterfileds']) ? serialize($settingnew['pichomefilterfileds']):'';
            updatesetting($setting, $settingnew);
        }

        exit(json_encode(array('success' => true)));
    } else {
        if($appid){
            if($appid == 'all'){
                $catdata =  array();
                $data = isset($setting['pichomefilterfileds']) ? $setting['pichomefilterfileds']:[];
            }else{
                $appdata = C::t('pichome_vapp')->fetch($appid);
                $catdata = C::t('pichome_taggroup')->fetch_by_appid($appid);
                array_push($catdata,['cid'=>-1,'catname'=>'未分组']);
                $data = $appdata['filter'] ? unserialize($appdata['filter']):[];

            }
            exit(json_encode(array('data'=>$data,'catdata'=>$catdata)));
        }else{
            include template('admin/pc/page/site/fileterset');
        }
    }
}elseif ($operation == 'onlyofficesetting') {//onlyoffice
    if (submitcheck('settingsubmit')) {
        $settingnew = $_GET['settingnew'];
        updatesetting($setting, $settingnew);
        exit(json_encode(array('success' => true)));
    } else {
        $settingnew['onlyofficeurl'] = isset($setting['onlyofficeurl']) ? $setting['onlyofficeurl']:'';
        $settingnew['onlyofficethumbext'] = isset($setting['onlyofficethumbext']) ? $setting['onlyofficethumbext']:'pdf,doc,docx,rtf,odt,htm,html,mht,txt,ppt,pptx,pps,ppsx,odp,xls,xlsx,ods,csv';
        $settingnew = json_encode($settingnew);
        include template('pc/page/adminOnlyOffice');
    }
}
function updatesetting($setting, $settingnew){
    $updatecache = false;
    $settings = array();
    $updatethumb = false;
    $updateroute = false;
    foreach ($settingnew as $key => $val) {
        if ($setting[$key] != $val) {
            $updatecache = TRUE;
            if (in_array($key, array('timeoffset', 'regstatus', 'oltimespan', 'seccodestatus'))) {
                $val = (float)$val;
            }elseif($key == 'pathinfo'){
                $updateroute = true;
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
    if($updateroute){
        C::t('pichome_route')->update_route();
    }

    return true;
}

exit();