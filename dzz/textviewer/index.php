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
    global $_G;
    if(strpos($rid, 'attach::') === 0){
        $resourcesdata = C::t('attachment')->fetch(intval(str_replace('attach::', '', $path)));

    }else{
        $resourcesdata = C::t('pichome_resources')->fetch_data_by_rid($rid);
    }
    $fileurl = IO::getFileUri($resourcesdata['path']);
    $str = file_get_contents($fileurl);
}

require_once DZZ_ROOT . './dzz/class/class_encode.php';
$p = new Encode_Core();
$code = $p -> get_encoding($str);
if ($code)$str = diconv($str, $code, CHARSET);
$str = htmlspecialchars($str);
$str = nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $str));
include  template('textviewer');
?>
