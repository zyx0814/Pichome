<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
$locked = true;
$processname = 'DZZ_LOCK_PICHOMEFILEDELETE';
if (!dzz_process::islocked($processname, 60*5)) {
    $locked=false;
}
$limit = 100;
if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}
foreach(DB::fetch_all("select rid from %t where isdelete =2 limit 0,$limit",array('pichome_resources')) as $v){
    C::t('pichome_resources')->delete_by_rid($v['rid']);
}
dzz_process::unlock($processname);
if(DB::result_first("select count(rid) from %t where isdelete =2 ",array('pichome_resources'))){
    dfsockopen(getglobal('localurl') . 'misc.php?mod=finalydelfile', 0, '', '', false, '', 0.1);
}else{
    exit('success');
}
