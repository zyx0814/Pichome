<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
$locked = true;
$processname = 'DZZ_TABGROUPDEL_LOCK';
//dzz_process::unlock($processname);
if (!dzz_process::islocked($processname, 10 * 60)) {
    foreach(DB::fetch_first("select gid from %t where  issystem = 0 and isdelete = 1",array('tab_group')) as $v){
        $processname1 = 'DZZ_TABGROUPDEL_LOCK_'.$v;
        if (!dzz_process::islocked($processname1, 5 * 60)) {
            C::t('#tab#tab_group')->delete_data_by_gid($v);
            dzz_process::unlock($processname1);
        }
    }
    dzz_process::unlock($processname);
}
exit('success');

