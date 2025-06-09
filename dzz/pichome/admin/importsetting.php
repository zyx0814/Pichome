<?php
global $_G;
$navtitle="导入设置";
include libfile('function/cache');
if (isset($_G['setting'])) $setting = $_G['setting'];
else  $setting = C::t('setting')->fetch_all();
$themedata = getthemedata($themeid);
$lefsetdata = $themedata['singlepage'];
if (submitcheck('settingsubmit')) {
	$settingnew = $_GET['settingnew'];
	updatesetting($setting, $settingnew);
	exit(json_encode(array('success' => true)));
} else {
	$Defaultnotallowdir = json_encode($Defaultnotallowdir);
	include template('admin/pc/page/site/importsetting');
}
function updatesetting($setting, $settingnew){
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