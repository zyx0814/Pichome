<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
//获取需要执行计划任务的库
foreach(DB::fetch_all("select * from %t where isdelete = 0 ",array('pichome_tab')) as $v){
   $gid = $v['gid'];
    dfsockopen(getglobal('localurl').'misc.php?mod=updatetabStatsData&gid='.$gid,0,'','',false,'',1);

}