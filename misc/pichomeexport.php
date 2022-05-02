<?php
    ignore_user_abort(true);
    @set_time_limit(0);
    $appdata = DB::fetch_all("select  * from %t  where 1 ",array('pichome_vapp'));
   foreach($appdata as $v){
       $jsonfile = getglobal('setting/attachdir') . './' . $v['path'] . '/mtime.json';
       if (!file_exists($jsonfile)) {
           //删除库
           C::t('#pichome#pichome_record')->delete_vapp_by_appid($v['appid']);
       }else{
           dfsockopen(getglobal('localurl'). 'index.php?mod=pichome&op=exportfile&appid='.$v['appid'],0, '', '', false, '', 1);
       }
     
   }