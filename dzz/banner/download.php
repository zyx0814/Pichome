<?php
if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}

$patharr = Pdecode($_GET['dpath']);

$rid = $patharr['path'];
if(!$rid) exit(json_encode(array('error'=>'dpath is must')));
$resourcesdata = C::t('pichome_resources')->fetch($rid);
Hook::listen('lang_parse',$resourcesdata,['getResourcesLangData']);
$ulevel = getglobal('pichomelevel') ? getglobal('pichomelevel'):0;
$appdata = C::t('pichome_vapp')->fetch($resourcesdata['appid']);
/*if(!C::t('pichome_vapp')->getpermbypermdata($appdata['download'],$resourcesdata['appid'],'download')){
    exit(lang('no_perm'));
}*/
$extension =  substr($resourcesdata['name'], strrpos($resourcesdata['name'], '.') + 1);
if($extension != $resourcesdata['ext']){
    $resourcesdata['name'] = $resourcesdata['name'].'.'.$resourcesdata['ext'];
}
$resourcesdata['name'] = '"' . (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($resourcesdata['name']) : ($resourcesdata['name'])) . '"';
if($patharr['fpath'] && strpos($patharr['fpath'], 'attach::') === 0){
    $attachpath = $patharr['fpath'];
    $aid = intval(str_replace('attach::','',$patharr['fpath']));
    $attachment = C::t('attachment')->fetch($aid);
    $resourcesdata['size'] = $attachment['filesize'];
}else{
    $attach = DB::fetch_first("select path,appid from %t where rid = %s",array('pichome_resources_attr',$rid));

    if(is_numeric($attach['path'])){
        $attachpath = 'attach::'.$attach['path'];

    }else{
        $attachpath = $appdata['path'].BS.$attach['path'];
    }
}


addFiledownloadStats($rid,0);
$attachurl = IO::getStream($attachpath);
$attachurl= str_replace('#','%23',$attachurl);
if (isset($headers['Content-Length'])) {
    $fileSize = $headers['Content-Length'];
} else {
    $fileSize = $resourcesdata['size'];
}
$d = new FileDownload();
$d->download($attachurl, $resourcesdata['name'], $fileSize, 0, true);
exit();