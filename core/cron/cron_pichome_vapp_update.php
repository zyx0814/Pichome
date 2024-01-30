<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
//获取需要执行计划任务的库
foreach(DB::fetch_all("select * from %t where isdelete = 0 and `type` != %d and (state = %d or state = %d) and cron = %d and isdelete < 1",array('pichome_vapp',3,0,4,1)) as $v){
    $appid = $v['appid'];
    //固定频率时
    if($v['crontype']){
        $corntime = $v['crontime']*60*60;

        if($corntime+$v['dateline'] <= TIMESTAMP ){
            dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=initexport&appid=' . $appid, 0, '', '', false, '', 1);
        }
    }else{

        preg_match_all('/\d{2}/', $v['crontime'], $matches);

        $corntimes = $matches[0];

        //获取上次执行的小时数
        $hour = date('H', $v['dateline']);
        // 获取当前小时数
        $currentHour = date('H');

        if (in_array($hour, $corntimes)) {
            $index = array_search($hour, $corntimes);
            array_splice($corntimes, 0, $index + 1);
        } else {
            $corntimes = array();
        }
        if(in_array($currentHour, $corntimes)){
            dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=initexport&appid=' . $appid, 0, '', '', false, '', 1);
        }

    }
}