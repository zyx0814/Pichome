<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
$locked = true;
$processnamepre = 'DZZ_LOCK_DOAITASK';
$processnum = 2;
for($i=0;$i<$processnum;$i++){
    $processname = $processnamepre.$i;
    if (!dzz_process::islocked($processname, 60*5)) {
        $locked=false;
        break;
    }
}
$limit = 100;
//dzz_process::unlock($processname);
if ($locked) {
    exit(json_encode(array('error' => '进程已被锁定请稍后再试')));
}
$limit = 100;
foreach (DB::fetch_all("select * from %t where 1 limit 0,%d", array('ai_task',$limit)) as $v) {
       $ret=array();
       Hook::listen('imageAiData',$ret,['rid'=>$v['rid'],'isforce'=>0,'tplid'=>$v['tplid'],'aiKey'=>$v['aikey']]);
       if($ret['error']){
           runlog('ai_task',$ret['error'].'输入参数：'.json_encode(['rid'=>$v['rid'],'isforce'=>0,'tplid'=>$v['tplid'],'aiKey'=>$v['aikey']]));
       }
       C::t('ai_task')->delete($v['id']);
}

dzz_process::unlock($processname);
if (DB::result_first("select count(id) from %t where 1 ", array('ai_task'))) {
    dfsockopen(getglobal('localurl') . 'misc.php?mod=doaitask', 0, '', '', false, '', 0.1);
} else {
    exit('success'.$i);
}