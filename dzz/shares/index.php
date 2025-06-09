<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$sid = dzzdecode($_GET['sid'],'',0);
$sharedata=C::t('pichome_share')->fetch_by_sid($sid);
if(!$sharedata){
    showmessage('share_file_iscancled');
}

$ret=checkShare($sharedata);
if(!$ret['success']){
    showmessage($ret['msg']);
}
//验证提取码
if ($sharedata['password'] && ($sharedata['password'] != authcode($_G['cookie']['share_pass_' . $sid]))) {
    include template('pc/page/password');
    exit();
}
$viewurl=C::t('pichome_share')->getViewUrl($sharedata);
C::t('pichome_share')->add_views_by_id($sid);
header('Location:'.$viewurl);
exit();






