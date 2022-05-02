<?php
    if(!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    ignore_user_abort(true);
    @set_time_limit(0);
    $percachename = 'pichome_searchhot';
    $alldata = C::t('pichome_searchrecent')->fetch_hotkeyword_by_appid();
    $setarr = ['cachekey'=>$percachename,'cachevalue'=>serialize($alldata),'dateline'=>time()];
    C::t('cache')->insert($setarr);
    foreach(DB::fetch_all("select  appid  from %t  where 1 ",array('pichome_vapp')) as $v){
        $tmpkey = $percachename.$v['appid'];
        $tmpdata = C::t('pichome_searchrecent')->fetch_hotkeyword_by_appid($v['appid']);
        $setarr = ['cachekey'=>$tmpkey,'cachevalue'=>serialize($tmpdata),'dateline'=>time()];
        C::t('cache')->insert($setarr);
    }