<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
$locked = true;
$flags = isset($_GET['flag']) ? trim($_GET['flag']):'';
if($flags) $processname = 'DZZ_LOCK_DELETEFILED_'.$flags;
else $processname = 'DZZ_LOCK_DELETEFILED';
if (!dzz_process::islocked($processname, 60*5)) {
    $locked=false;
}

//dzz_process::unlock($processname);
if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}

if($flags){
    $flagarr = explode(',',$flags);
    foreach(DB::fetch_all("select * from %t where flag in(%n)",['form_setting',$flags]) as $fileddata){
        $processname1 = 'DZZ_LOCK_DELETEFILED_'.$fileddata['flag'];
        if (dzz_process::islocked($processname1, 60*5)) {
            continue;
        }
        if(!$fileddata['isdel']) {
            dzz_process::unlock($processname1);
            continue;
        }
        else{
            if($fileddata['tabgroupid']) {
                Hook::listen('lang_parse',$flag,['delTabFiledLangData']);
                C::t('#tab#tab_attr')->delete_by_skey($flag);
                dfsockopen(getglobal('localurl') . 'misc.php?mod=updateSearchval', 0, '', '', false, '', 0.1);
            }else{
                C::t('form_setting')->delete_by_flag($flag);
            }

        }
        dzz_process::unlock($processname1);
    }

}else{

    $limit = 5;
    foreach(DB::fetch_all("select * from %t where isdel = %d limit 0,$limit",['form_setting',1]) as $v){
        $processname1 = 'DZZ_LOCK_DELETEFILED_'.$v['flag'];
        if (dzz_process::islocked($processname1, 60*5)) {
            continue;
        }
        if($v['tabgroupid']){
            Hook::listen('lang_parse',$v['flag'],['delTabFiledLangData']);
            C::t('#tab#tab_attr')->delete_by_skey($v['flag']);
            dfsockopen(getglobal('localurl') . 'misc.php?mod=updateSearchval', 0, '', '', false, '', 0.1);

        }else{
             C::t('form_setting')->delete_by_flag($v['flag']);
        }
        dzz_process::unlock($processname1);
    }
}
dzz_process::unlock($processname);
if(DB::result_first("select count(*) from %t where isdel = %d ",['form_setting',1])){
    dfsockopen(getglobal('localurl') . 'misc.php?mod=delfiled', 0, '', '', false, '', 0.1);
}else{
    exit('success');
}

