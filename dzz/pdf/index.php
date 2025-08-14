<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
if(!Decode(rawurldecode($_GET['path']),'read')) {
    exit('Access Denied');
}
if(!Decode(rawurldecode($_GET['path']),'download')){
    $perm_download=0;
    $perm_print=0;
}else{
    $perm_download=1;
    $perm_print=1;
}
$file = getglobal('siteurl') . 'index.php?mod=io&op=getStream&path=' . $_GET['path'];

include template('viewer');
exit();