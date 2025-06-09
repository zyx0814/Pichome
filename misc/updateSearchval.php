<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
global $_G;
$lang = '';
Hook::listen('lang_parse',$lang,['checklang']);

if(!$lang) exit('success');
$locked = true;
$processname = 'DZZ_LOCK_UPDATESEARCHVAL';
//dzz_process::unlock($processname);
if (!dzz_process::islocked($processname, 60*5)) {
    $locked=false;
}
if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}

$langlist = $_G['language_list'];
$limit = 5;
foreach($langlist as $k=>$kv){
    $clang = strtolower(str_replace('-','_',$k));
    foreach(DB::fetch_all("select * from %t where (chkdate < dateline or deldate > 0) and (idtype = %d or idtype = %d)",['lang_'.$clang,3,8]) as $v){
        $processname1 = 'DZZ_LOCK_UPDATESEARCHVAL_'.$v['idtype'].'_'.$v['idvalue'].'_'.$clang;
        //dzz_process::unlock($processname1);
        if (dzz_process::islocked($processname1, 60*5)) {
            continue;
        }
        if($v['idtype'] == 3){
            $dateline = DB::result_first("select dateline from %t where idtype = %d and idvalue = %d ",['lang_search',3,$v['idvalue']]);
            if(!$dateline || $dateline < $v['dateline']){
                C::t('#lang#lang')->updateSearchvalById($clang,$v['idtype'],$v['idvalue']);
            }
            $tmparr = ['skey'=>$v['skey'],'chkdate'=>TIMESTAMP];
            C::t('#lang#lang')->updateData($clang,$tmparr);
        }elseif($v['idtype'] == 8){
            $rids = [];
            foreach(DB::fetch_all("select rid from %t  where find_in_set(%d,tag)",['pichome_resources_attr',$v['idvalue']]) as $val){
                C::t('#lang#lang_file')->insertData($val['rid'],$k,$v['dateline']);
            }
            $tmparr = ['skey'=>$v['skey'],'chkdate'=>TIMESTAMP];
            C::t('#lang#lang')->updateData($clang,$tmparr);
        }else{
            $tmparr = ['skey'=>$v['skey'],'chkdate'=>TIMESTAMP];
            C::t('#lang#lang')->updateData($clang,$tmparr);
        }

    }
}


dzz_process::unlock($processname);
$num = 0;
foreach($langlist as $k=>$v){
    $clang = str_replace('-','_',$k);
    $cnum = DB::fetch_all("select * from %t where (chkdate < dateline or deldate > 0) and (idtype = %d or idtype = %d)",['lang_'.$clang,3,8]);
    $num += intval($cnum);
}
if($num){
    dfsockopen(getglobal('localurl') . 'misc.php?mod=updateSearchval', 0, '', '', false, '', 0.1);
}else{
    exit('success');
}

