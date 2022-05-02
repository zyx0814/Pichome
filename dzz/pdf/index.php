<?php
if(!defined('IN_OAOOA')) {
	exit('Access Denied');
}
if(!isset($_GET['src']) && !$rid = dzzdecode($_GET['path'],'',0)){
	exit('Access Denied');
}

if($_GET['src']){
    //$file = str_replace('+', ' ', urlencode($_GET['src']));
    $file = urldecode($_GET['src']);
}else{
   /* if(!Decode(rawurldecode($_GET['path']),'download')){
        $perm_download=0;
        $perm_print=0;
    }else{
        $perm_download=1;
        $perm_print=1;
    }*/

    $resourcesdata = C::t('pichome_resources')->fetch($rid);
    $appdata = C::t('pichome_vapp')->fetch($resourcesdata['appid']);
    if($appdata['download']){
        $perm_download=1;
        $perm_print=1;
    }else{
        $perm_download=0;
        $perm_print=0;
    }
    $file = getglobal('siteurl') . 'index.php?mod=io&op=getImg&path=' . dzzencode($rid.'_3', '', 0, 0);
   // $file=IO::getFileUri($path);
}

include template('viewer');
exit();