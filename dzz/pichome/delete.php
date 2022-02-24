<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
$locked = true;
$processname = 'DZZ_LOCK_PICHOMEDELETE';
if (!dzz_process::islocked($processname, 60*60)) {
    $locked=false;
}
//dzz_process::unlock($processname);
if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}
foreach(DB::fetch_all("select appid from %t where isdelete =1",array('pichome_vapp')) as $v){
    C::t('pichome_vapp')->delete_vapp_by_appid($v['appid']);
}
dzz_process::unlock($processname);
if(DB::result_first("select count(appid) from %t where isdelete =1 ",array('pichome_vapp'))){
    dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=delete', 0, '', '', false, '', 0.1);
}
exit('success');