<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

//runlog('aaaauploadafter',time());
//
//error_reporting(E_ALL);
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);

$locked = true;

$processnum = getglobal('config/thumbprocessnum') ? getglobal('config/thumbprocessnum'):1;
for($i=0;$i<$processnum;$i++){

    $processname = 'DZZ_LOCK_PICHOMEGETTPREVIEWHUMB'.$i;
    //dzz_process::unlock($processname);
    if (!dzz_process::islocked($processname, 60*15)) {
        $locked=false;
        break;
    }
}
$limit = 100;
$start=$i*$limit;

if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}

$cachename = 'PICHOMETHUMBSTATUS';

$thumbstatus = C::t('cache')->fetch_cachedata_by_cachename($cachename);

if (!$thumbstatus) {
    $thunbdata = [];
    foreach(DB::fetch_all("select id,bz,imagestatus from %t where 1",array('connect_storage')) as $v){
        if($v['bz'] == 'dzz'){
            $key  = $v['bz'].'::';
        }else{
            $key = $v['bz'].':'.$v['id'].':';
        }
        $thunbdata[$key] = intval($v['imagestatus']);
    }
    $setarr = ['cachekey' => $cachename, 'cachevalue' => serialize($thunbdata), 'dateline' => time()];
    C::t('cache')->insert_cachedata_by_cachename($setarr);
} else {
    $thunbdata = $thumbstatus;
}

$thumbstate = 0;
foreach($thunbdata as $v){
    if($v) $thumbstate = 1;
}
if(!$thumbstate) exit(json_encode( array('error'=>'未开启缩略图转换')));
$appids = [''];
$appdata = [];
foreach(DB::fetch_all("select appid,path,`type` from %t where (`type` = %d or `type` = %d ) and isdelete < 1",array('pichome_vapp',1,3,1)) as $v){
    if($v['type'] == 3 || IO::checkfileexists($v['path'],1)){
        $appids[] = $v['appid'];
        $appdata[$v['appid']] = $v;
    }
}

if(empty($appids)){
    exit('success');
}


$datas = DB::fetch_all("select r.rid,r.appid,t.*,least(t.ltimes,t.stimes) as mintimes
from %t t left join %t r on t.rid = r.rid   
where (t.sstatus < 1 or t.lstatus < 1) and  ((t.ltimes+t.stimes) < %d)  and r.isdelete = 0 and r.appid in(%n)
order by mintimes asc,r.dateline asc limit $start,$limit",array('thumb_preview','pichome_resources',6,$appids));

if($datas){
    foreach($datas as $v){
        $processname1 = 'PICHOMEGETPREVIEWTHUMB_'.$v['id'];
        //dzz_process::unlock($processname1);
        //如果当前数据是锁定状态则跳过
        if (dzz_process::islocked($processname1, 60*5)) {
            continue;
        }
        $thumbsign = '';
        //更新当前数据获取缩略图执行次数和时间
        if(!$v['sstatus']){
            C::t('thumb_preview')->update($v['id'],array('stimes'=>intval($v['stimes'])+1,'sdateline'=>TIMESTAMP));
            $thumbsign = 'small';
        }elseif(!$v['lstatus']){
            C::t('thumb_preview')->update($v['id'],array('ltimes'=>intval($v['ltimes'])+1,'ldateline'=>TIMESTAMP));
            $thumbsign = 'large';
        }else{
            dzz_process::unlock($processname1);
            continue;
        }
        $metadata = IO::getMeta($v['aid']);
        if (!$thunbdata[$metadata['bz']]) {
            dzz_process::unlock($processname1);
            continue;
        }
        //调用系统获取缩略图
        $returnurl = IO::getPreviewThumb($v,$thumbsign,0,1,1);
        dzz_process::unlock($processname1);
        //exit('aaaa');

    }
    dzz_process::unlock($processname);
    if(DB::result_first("select count(r.rid)
from %t t left join %t r on t.rid = r.rid  
where (t.sstatus < 1 or t.lstatus < 1) and  ((t.ltimes+t.stimes) < %d) and r.isdelete = 0 and r.appid in(%n)",
        array('thumb_preview','pichome_resources',6,$appids))){
        sleep(2);
        dfsockopen(getglobal('localurl') . 'misc.php?mod=getPreviewThumb', 0, '', '', false, '',1);
    }
}else{
    dzz_process::unlock($processname);
}

exit('success'.$i);