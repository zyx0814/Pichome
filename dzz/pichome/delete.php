<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
foreach(DB::fetch_all("select appid from %t where isdelete =1",array('pichome_vapp')) as $v){
    C::t('pichome_vapp')->delete_vapp_by_appid($v['appid']);
}