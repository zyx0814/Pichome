<?php
if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}

$path = dzzdecode($_GET['path'],'',0);

$patharr = explode('_',$path);
$path = $patharr[0];
$filename = isset($_GET['filename']) ? $_GET['filename'] : '';

if(strpos($path,'attach::')>-1){
	$aid=str_replace('attach::','',$path);
    if(!$attach=C::t('attachment')->fetch($aid)){
		@header( 'HTTP/1.1 403 Not Found' );
		@header( 'Status: 403 Not Found' );
		exit( lang( 'attachment_nonexistence' ) );
	}
	$size=intval($attach['filesize']);
	if(empty($filename)) $filename=$attach['filename'];
}else{
	$rid=$path;
	if(!$resource=C::t('pichome_resources')->fetch($rid)){
		@header( 'HTTP/1.1 403 Not Found' );
		@header( 'Status: 403 Not Found' );
		exit( lang( 'attachment_nonexistence' ) );
	}
	$attach = DB::fetch_first("select path,appid from %t where rid = %s",array('pichome_resources_attr',$rid));
	
	if(is_numeric($attach['path'])){
		$path = 'attach::'.$attach['path'];
	}else{
		$appdata = C::t('pichome_vapp')->fetch($attach['appid']);
		$path = $appdata['path'].BS.$attach['path'];
	}
	if(empty($filename)) $filename=$resource['name'];
	$filesize=$resource['size'];
}

$attachurl = IO::getStream($path);
$attachurl= str_replace('#','%23',$attachurl);

$filename = '"' . (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($filename) : ($filename)) . '"';

$d = new FileDownload();
$d->download($attachurl, $filename, $filesize, 0, true);
exit();