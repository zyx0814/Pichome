<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
global $_G;
$path = isset($_GET['path']) ? trim($_GET['path']):'';
$size = isset($_GET['size']) ? trim($_GET['size']):'';
if(!$path = dzzdecode($path,'',0)){
    @header('HTTP/1.1 404 Not Found');
    @header('Status: 404 Not Found');
}
$width = $_GET['width'] ? intval($_GET['width']):0;
$height = $_GET['height'] ? intval($_GET['height']):0;
$size=in_array($size,array_keys($_G['setting']['thumbsize']))?$size:'large';
$original=intval($_GET['original']);
if(!$width) $width=$_G['setting']['thumbsize'][$size]['width'];
if(!$height) $height=$_G['setting']['thumbsize'][$size]['height'];
$returnurl  = $_GET['returnurl'] ? intval($_GET['returnurl']):0;
$thumbtype  = $_GET['thumbtype'] ? intval($_GET['thumbtype']):1;
IO::getThumb($path, $width,$height,$returnurl, $thumbtype);