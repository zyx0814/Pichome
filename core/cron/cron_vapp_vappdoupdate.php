<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
//获取正在执行导入任务的库
foreach(DB::fetch_all("select * from %t where isdelete = 0 and `type` != %d and state =%d ",array('pichome_vapp',3,2)) as $v){
    $appid = $v['appid'];
    //固定频率时
    dfsockopen(getglobal('localurl') . 'misc.php?mod=exportfile&appid=' . $appid, 0, '', '', false, '', 1);
}