<?php
    if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
        exit('Access Denied');
    }
    $path = dzzdecode($_GET['dpath']);
    if(!$path) exit(json_encode(array('error'=>'dpath is must')));
    $resourcesdata = C::t('pichome_resources')->fetch($path);
    $resourcesdata['name'] = '"' . (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($resourcesdata['name']) : ($resourcesdata['name'])) . '"';
    $attachurl = DB::result_first("select path from %t where rid = %s",array('pichome_resources_attr',$path));
    $attachurl = 'library/' .$attachurl;
    $d = new FileDownload();
    $d->download($attachurl, $resourcesdata['name'], $resourcesdata['size'], 0, true);
    exit();