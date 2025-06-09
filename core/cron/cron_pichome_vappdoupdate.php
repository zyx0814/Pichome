<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
//获取正在执行导入任务的库
foreach(DB::fetch_all("select * from %t where isdelete = 0 and `type` != %d and state != %d and state != %d ",array('pichome_vapp',3,0,4)) as $v){
    $appid = $v['appid'];
    //固定频率时
    dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=exportfile&appid=' . $appid, 0, '', '', false, '', 1);
}