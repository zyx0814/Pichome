<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
global $_G;
$lang = '';
Hook::listen('lang_parse', $lang, ['checklang']);

if (!$lang) exit('success');
$locked = true;
$processname = 'DZZ_LOCK_UPDATEFILESEARCHVAL';
//dzz_process::unlock($processname);
if (!dzz_process::islocked($processname, 60 * 5)) {
    $locked = false;
}
if ($locked) {
    exit(json_encode(array('error' => '进程已被锁定请稍后再试')));
}


$limit = 10;

foreach (DB::fetch_all("select *from %t where 1 limit $limit", ['lang_file']) as $v) {
    $processname1 = 'DZZ_LOCK_UPDATEFILESEARCHVAL' . $v['rid'] . '_' . $v['lang'];
    //dzz_process::unlock($processname1);
    if (dzz_process::islocked($processname1, 60 * 5)) {
        continue;
    }
    $dateline = DB::result_first("select dateline from %t where idtype = %d and idvalue = %s and lang = %s ", ['lang_search', 0, $v['rid'], $v['lang']]);
    if (!$dateline || $dateline < $v['dateline']) {
        $clang = strtolower(str_replace('-', '_', $v['lang']));
        C::t('#lang#lang')->updateSearchvalById($clang, 0, $v['rid']);
    }
    C::t('#lang#lang_file')->delete($v['id']);
    dzz_process::unlock($processname1);
}


dzz_process::unlock($processname);
$num = 0;
$num = DB::result_first("select count(*) from %t where 1", ['lang_file']);
if ($num) {
    dfsockopen(getglobal('localurl') . 'misc.php?mod=updateFileSearchval', 0, '', '', false, '', 0.1);
} else {
    exit('success');
}

