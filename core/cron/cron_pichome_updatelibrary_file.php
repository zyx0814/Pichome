<?php
    if(!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    //ignore_user_abort(true);
    @set_time_limit(0);
    $appdata = DB::fetch_all("select  * from %t  where 1 ",array('pichome_vapp'));
    foreach($appdata as $v){
        $jsonfile = DZZ_ROOT.'library/' . $v['path'] . '/mtime.json';
        if (!file_exists($jsonfile)) {
            //删除库
            C::t('#pichome#pichome_vapp')->delete_vapp_by_appid($v['appid']);
        }else{
            if($v['state'] == 1){
                dfsockopen(getglobal('localurl'). 'index.php?mod=pichome&op=exportfile&appid='.$v['appid'],0, '', '', false, '', 1);
            }elseif($v['state'] == 2){
                dfsockopen(getglobal('localurl'). 'index.php?mod=pichome&op=exportfilecheck&appid='.$v['appid'],0, '', '', false, '', 1);
            }
         
        }
        
    }