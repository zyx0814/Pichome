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
global $_G;
$about=array();

$ismobile=helper_browser::ismobile();

$about['sitename']=getglobal('setting/sitename');//中文名称,留空不显示
$about['logo']=$_G['setting']['attachurl'].'sitelogo/sitelogo.png';


//站点logo,留空不显示
//
$license=array('license_limit'=>LICENSE_LIMIT,'license_version'=>LICENSE_VERSION,'license_company'=>LICENSE_COMPANY);
$ucount=DB::result_first("select COUNT(*) from %t where 1",array('user'));
$versioncode = explode('.',CORE_VERSION);
unset($versioncode[0]);
$version = implode('.',$versioncode);
$version_name=lang(CORE_VERSION_LEVEL).''.$version;
$about['version'] = $version_name;
// $about['copyright'] = 'lang('Copy_right')';
$about['copyright'] = 'Powered By oaooa PicHome © 2020-2022 欧奥图文';
$about['home_page'] = 'https://oaooa.com';
$about['home'] = 'oaooa.com';
exit(json_encode(array('data'=>$about)));
// if ($ismobile && !$_GET['inajax']) {
// 	include template('mobile_about');
// } else {
// 	include template('about');
// }
exit();