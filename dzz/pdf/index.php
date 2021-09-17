<?php
if(!defined('IN_OAOOA')) {
	exit('Access Denied');
}
if(!isset($_GET['src']) && !$path=Decode($_GET['path'],'read')){
	exit('Access Denied');
}
if($_GET['src']){
    $file = urldecode($_GET['src']);
}else{
    if(!Decode(rawurldecode($_GET['path']),'download')){
        $perm_download=0;
        $perm_print=0;
    }else{
        $perm_download=1;
        $perm_print=1;
    }
    $file=IO::getFileUri($path);
}

/*header("Location: /dzz/pdf/web/viewer.html?file=".urlencode($file));
exit();*/
include template('viewer');
exit();