<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if (!defined('IN_OAOOA')) {
	exit('Access Denied');
}

if(!isset($_GET['src']) && !$rid = dzzdecode($_GET['path'],'',0)){
    exit('Access Denied');
}
if($_GET['src']){
	$str = file_get_contents(urldecode($_GET['src']));
}else{
    $rid = dzzdecode($_GET['path'],'',0);
    $fileurl = getglobal('siteurl') . 'index.php?mod=io&op=getImg&path=' . dzzencode($rid.'_3', '', 7200, 0);
	$str = file_get_contents($fileurl);

}

$themecolor = C::t('user_setting')->fetch_by_skey('pichomeusertheme',$_G['uid']);
if($themecolor){
	$theme = $themecolor;
}elseif($_G['setting']['pichomepagesetting']['theme']){
	$theme = $_G['setting']['pichomepagesetting']['theme'];
}else{
	$theme = 'white';
}

require_once DZZ_ROOT . './dzz/class/class_encode.php';
$p = new Encode_Core();
$code = $p -> get_encoding($str);
if ($code)$str = diconv($str, $code, CHARSET);
$str = htmlspecialchars($str);
$str = nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $str));
include  template('textviewer');
?>
