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
$about=array();
$identify=$_GET['modname'];
$ismobile=helper_browser::ismobile();
$appConfig=DZZ_ROOT.'./dzz/'.$identify.'/config/config.php';
if($identify && file_exists($appConfig)){
	$config=include($appConfig);
	if(isset($config['about'])){
		$about=$config['about'];
		$about['sitelogo']=$_G['setting']['sitelogo']?\IO::getFileUri('attach::'.$_G['setting']['sitelogo']):'static/image/common/logo.png';
		$appinfo=C::t('app_market')->fetch_by_identifier($identify);
		if(empty($about['logo'])){
			$about['logo']=$_G['setting']['attachurl'].$appinfo['appico'];
		}
		if(empty($about['version'])) $about['version']=$appinfo['version'];
	}
}
if(empty($about['name_en'])){
	$about['sitelogo']='';
	$about['name_zh']=getglobal('setting/sitename');//中文名称,留空不显示
	$about['name_en']='';//英文名称，注意前面的dzz去掉，留空不显示
	$about['version']=substr(CORE_VERSION,strpos(CORE_VERSION,'.')+1);//版本信息，留空不显示
	//中间大图
	$about['logo']=$_G['setting']['sitelogo']?\IO::getFileUri('attach::'.$_G['setting']['sitelogo']):'static/image/common/logo.png';
}

//站点logo,留空不显示
//
$license=array('license_limit'=>LICENSE_LIMIT,'license_version'=>LICENSE_VERSION,'license_company'=>LICENSE_COMPANY);
$ucount=DB::result_first("select COUNT(*) from %t where 1",array('user'));
if ($ismobile && !$_GET['inajax']) {
	include template('mobile_about');
} else {
	include template('about');
}
exit();