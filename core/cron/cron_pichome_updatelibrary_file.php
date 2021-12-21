<?php
    if(!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    ignore_user_abort(true);
    @set_time_limit(0);
    $appdata = DB::fetch_all("select  * from %t  where isdelete = 0 ",array('pichome_vapp'));
    foreach($appdata as $v){
        if($v['state'] == 2){
            dfsockopen(getglobal('localurl'). 'index.php?mod=pichome&op=exportfile&appid='.$v['appid'],0, '', '', false, '', 1);
        }elseif($v['state'] == 3){
            dfsockopen(getglobal('localurl'). 'index.php?mod=pichome&op=exportfilecheck&appid='.$v['appid'],0, '', '', false, '', 1);
        }

    }