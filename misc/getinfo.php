<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
$appids = [''];
$appdata = [];
foreach (DB::fetch_all("select appid,path,`type` from %t where (`type` = %d or type = %d)and isdelete < 1", array('pichome_vapp', 1, 3)) as $v) {
    if ($v['type'] == 3 || IO::checkfileexists($v['path'], 1)) {
        $appids[] = $v['appid'];
        $appdata[$v['appid']] = $v;
    }

}
if (empty($appids)) {
    exit('success');
}

$locked = true;
/*$i = 0;
$processname = 'DZZ_LOCK_PICHOMEGETINFO'.$i;*/
$processnum = getglobal('config/infoprocessnum') ? getglobal('config/infoprocessnum') : 1;
for ($i = 0; $i < $processnum; $i++) {
    $processname = 'DZZ_LOCK_PICHOMEGETINFO' . $i;
    if (!dzz_process::islocked($processname, 60 * 15)) {
        $locked = false;
        break;
    }
}
$limit = 100;
$start = $i * $limit;
/*if (!dzz_process::islocked($processname, 60*15)) {
    $locked=false;
}*/
//dzz_process::unlock($processname);
if ($locked) {
    exit(json_encode(array('error' => '进程已被锁定请稍后再试')));
}


//查询符合执行条件的数据
$datas = DB::fetch_all("select ra.rid,ra.getdonum from %t ra left join %t v on ra.appid=v.appid
where ra.isget < 1 and ra.appid in(%n) and ra.getinfotime < v.dateline
 order by ra.getdonum asc limit $start,$limit", array('pichome_resources_attr', 'pichome_vapp', $appids));

if ($datas) {
    foreach ($datas as $v) {

        $processname1 = 'PICHOMEGETINFO_' . $v['rid'];
        //dzz_process::unlock($processname1);
        //如果当前数据是锁定状态则跳过
        if (dzz_process::islocked($processname1, 60 * 5)) {
            continue;
        }
        //更新当前数据获取缩略图执行次数和时间
        C::t('pichome_resources_attr')->update($v['rid'],array('getdonum'=>intval($v['getdonum'])+1,'getinfotime'=>TIMESTAMP));
        $resourcesdata = C::t('pichome_resources')->fetch_data_by_rid($v['rid']);
        Hook::listen('pichomegetfileinfo', $resourcesdata);
        dzz_process::unlock($processname1);

    }
    dzz_process::unlock($processname);
    if (DB::fetch_all("select count(ra.rid) from %t ra left join %t v on ra.appid=v.appid where ra.isget=0 and ra.appid in(%n) and ra.getinfotime < v.dateline
", array('pichome_resources_attr', 'pichome_vapp', $appids))) {
        sleep(2);
        dfsockopen(getglobal('localurl') . 'misc.php?mod=getinfo', 0, '', '', false, '', 1);
    }
} else {
    dzz_process::unlock($processname);
}

exit('success' . $i);