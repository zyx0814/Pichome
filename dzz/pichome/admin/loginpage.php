<?php
include libfile('function/cache');
global $_G;
$navtitle="登录页设置";
if (isset($_G['setting'])) $setting = $_G['setting'];
else  $setting = C::t('setting')->fetch_all();
$themeid = isset($_G['setting']['pichometheme']) ? intval($_G['setting']['pichometheme']):1;
$themedata = getthemedata($themeid);
$lefsetdata = $themedata['singlepage'];
$navtitle = '登录页设置';
if (submitcheck('settingsubmit')) {
    $settingnew = $_GET['settingnew'];
    if ($back = trim($settingnew['loginset']['background'])) {
        if (strpos($back, '#') === 0) {
            $settingnew['loginset']['bcolor'] = $back;
        } else {
            $arr = explode('.', $back);
            $ext = array_pop($arr);
            if ($ext && in_array(strtolower($ext), array('jpg', 'jpeg', 'gif', 'png'))) {
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
    updatesetting($setting, $settingnew);
    exit(json_encode(array('success' => true)));
} else {
    include template('admin/pc/page/loginpage');

}
