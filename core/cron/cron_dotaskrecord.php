<?php

if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
//获取需要执行计划任务
foreach(DB::fetch_all("select * from %t where donum < totalnum limit 0,1", array('task_record')) as $v){
    $id = $v['id'];
    dfsockopen(getglobal('localurl').'misc.php?mod=dotaskrecord&id='.$id,0,'','',false,'',1);

}