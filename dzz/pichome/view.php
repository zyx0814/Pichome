<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
updatesession();
$navtitle="我的库";
global $_G;

if (isset($_G['setting'])) $setting = $_G['setting'];
else  $setting = C::t('setting')->fetch_all();
//当前用户id
$uid = $_G['uid'];
$themeid = isset($_G['setting']['pichometheme']) ? intval($_G['setting']['pichometheme']) : 1;
$themedata = getthemedata($themeid);

$data = array();
require_once(DZZ_ROOT . './dzz/class/class_encode.php');
if ($_G['adminid'] == 1) {
    $vappdatas = DB::fetch_all("select * from %t  where isdelete = 0 order by `disp` asc,dateline desc", array('pichome_vapp'));
} else {
    $vappdatas = DB::fetch_all("select v.* from %t vm left join %t v on v.appid = vm.appid where vm.uid = %d and v.isdelete = 0 order by v.disp",
        array('pichome_vappmember', 'pichome_vapp', $uid));
}


foreach ($vappdatas as $val) {
    $val['connect'] = IO::checkfileexists($val['path'], 1) ? 1 : 0;
    $arr = explode(':', $val['path']);

    //获取最新图片
    $resourcesdata = DB::fetch_first("select r.*,ra.path from %t r left join %t ra on r.rid = ra.rid 
    where r.isdelete = 0 and r.appid = %s order by r.dateline desc ", ['pichome_resources', 'pichome_resources_attr', $val['appid']]);
    
    $icondata = C::t('pichome_resources')->getfileimageurl($resourcesdata, $val['path'], $val['type'], 1);
    $val['icon'] = $icondata['icondata'];



    if ($arr[1] && is_numeric($arr[1])) {
        $pathpre = DB::result_first("select cloudname from %t where id = %d", array('connect_storage', $arr[1]));
        $arr1 = explode('/', $arr[2]);
        unset($arr1[0]);
        $object = implode('/', $arr1);
        $val['path'] = $pathpre . '/' . $object;
    } else {
        $p = new Encode_Core();
        $charset = $p->get_encoding($val['path']);
        if ($val['charset'] != CHARSET) {
            $val['path'] = diconv($val['path'], $charset, CHARSET);
        }
    }
    $val['path'] = str_replace('dzz::', '', $val['path']);
    $url = 'index.php?mod=pichome&op=fileview#appid=' . $val['appid'];
    if ($setting['pathinfo']) $path = C::t('pichome_route')->feth_path_by_url($url);
    else $path = '';
    if ($path) {
        $val['url'] = $path;
    } else {
        $val['url'] = $url;
    }

    $data[] = $val;
}
// $theme = GetThemeColor();
\Hook::listen('lang_parse',$data,['getVappLangData',1]);
$ismobile = helper_browser::ismobile();
if ($ismobile) {
    include template('storehouseview/mobile/page/index');
} else {
    include template('storehouseview/pc/page/index');
    
}