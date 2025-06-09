<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
$limit = 5;
//获取需要插入任务
foreach(DB::fetch_all("select id from %t where 1 order by dateline asc limit 0,$limit",array('ai_cron')) as $v){
    dfsockopen(getglobal('localurl') . 'misc.php?mod=aicrontotask&id=' .$v['id'], 0, '', '', false, '', 1);
}