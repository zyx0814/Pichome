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
global $_G;
$locked = true;
$processnum = getglobal('config/thumbprocessnum') ? getglobal('config/thumbprocessnum') : 1;
for ($i = 0; $i < $processnum; $i++) {
    $processname = 'DZZ_LOCK_PICHOMEGETTHUMB' . $i;
    if (!dzz_process::islocked($processname, 60 * 15)) {
        $locked = false;
        break;
    }
}
$limit = 100;
$start = $i * $limit;
//dzz_process::unlock($processname);
if ($locked) {
    exit(json_encode(array('error' => '进程已被锁定请稍后再试')));
}
$imageCacheName = 'PICHOMETHUMBSTATUS';
$docCacheName = 'PICHOMEDOCSTATUS';
$mediaCacheName = 'PICHOMECONVERTSTATUS';


$cacheStatuses = [
    $imageCacheName => 'imagestatus',
    $docCacheName => 'mediastatus',
    $mediaCacheName =>'docstatus',
];
$thumbStatus = [];
foreach ($cacheStatuses as $cacheName => $status) {
    $thumbStatus[$status] = C::t('cache')->fetch_cachedata_by_cachename($cacheName);
    if (!$thumbStatus[$status]) {
        // 缓存未命中时，查询数据库并更新缓存
        $filed = $status;
        $storageStatus = DB::fetch_all("select $filed,bz,id from %t where 1", array('connect_storage'));
        foreach ($storageStatus as $v) {
            $key = ($v['bz'] == 'dzz') ? $v['bz'] . '::' : $v['bz'] . ':' . $v['id'] . ':';
            $thumbStatus[$status] [$key]= intval($v[$status]);

        }
        $setArr = [
            'cachekey' => $cacheName,
            'cachevalue' => serialize($thumbStatus[$status]),
            'dateline' => time(),
        ];
        C::t('cache')->insert_cachedata_by_cachename($setArr);

    }
}

$thumbState =  0;
foreach ($thumbStatus as $v) {
    foreach($v as $val){
        if ($val) {
            $thumbState = 1;
            break;
        }
    }
}
if (!$thumbState) exit(json_encode(array('error' => '未开启缩略图转换')));
$appids = [''];
$appdata = [];
foreach (DB::fetch_all("select appid,path,`type` from %t where (`type` = %d or `type` = %d ) and isdelete < 1", array('pichome_vapp', 1, 3, 1)) as $v) {
    if ($v['type'] == 3 || IO::checkfileexists($v['path'], 1)) {
        $appids[] = $v['appid'];
        $appdata[$v['appid']] = $v;
    }
}

if (empty($appids)) {
    dzz_process::unlock($processname);
    exit('success');
}

$datas = DB::fetch_all("select r.rid,r.appid,t.rid,t.sstatus,t.lstatus,t.ltimes,t.stimes,t.ltimes+t.stimes as mintimes
from %t t left join %t r on t.rid = r.rid   
where (t.sstatus < 1 or t.lstatus < 1) and  ((t.ltimes+t.stimes) < %d)  and r.isdelete = 0 and r.appid in(%n) 
order by mintimes asc,r.dateline asc limit $start,$limit", array('thumb_record', 'pichome_resources', 6, $appids));

if ($datas) {
    foreach ($datas as $v) {

        $processname1 = 'PICHOMEGETTHUMB_' . $v['rid'];
        //dzz_process::unlock($processname1);
        //如果当前数据是锁定状态则跳过
        if (dzz_process::islocked($processname1, 60 * 5)) {
            continue;
        }
        $metadata = IO::getMeta($v['rid']);
        $ext = $metadata['ext'];
        $dzztype = getDzzExt($ext);
        //如果是本地存储位置文件
        if ($metadata['bz'] == 'dzz::') {
            //判断是否符合本地存储生成规则后缀
            if (!$thumbStatus[$dzztype][$metadata['bz']]) {
                dzz_process::unlock($processname1);
                continue;
            }
        } else {
            $type = getQcosExt($ext);
            if (!$status[$type][$metadata['bz']] && !$status[$dzztype]['dzz::']) {
                dzz_process::unlock($processname1);
                continue;
            }
        }
        $thumbsign = '';
        //更新当前数据获取缩略图执行次数和时间
        if (!$v['sstatus']) {
            C::t('thumb_record')->update($v['rid'], array('stimes' => intval($v['stimes']) + 1, 'sdateline' => TIMESTAMP));
            $thumbsign = 'small';
        } elseif (!$v['lstatus']) {
            C::t('thumb_record')->update($v['rid'], array('ltimes' => intval($v['ltimes']) + 1, 'ldateline' => TIMESTAMP));
            $thumbsign = 'large';
        } else {
            dzz_process::unlock($processname1);
            continue;
        }
        try{
            //调用系统获取缩略图
            $returnurl = IO::getThumb($v['rid'], $thumbsign, 0, 1, 1);
            dzz_process::unlock($processname1);
        }catch (Exception $e){
            runlog('createThumbError',$e->getMessage()."\t".$v['rid']);
            dzz_process::unlock($processname1);
        }
        //exit('aaaa');

    }
    dzz_process::unlock($processname);
    if (DB::result_first("select count(r.rid)
from %t t left join %t r on t.rid = r.rid  
where (t.sstatus < 1 or t.lstatus < 1) and  ((t.ltimes+t.stimes) < %d) and r.isdelete = 0 and r.appid in(%n)",
        array('thumb_record', 'pichome_resources', 6, $appids))) {
        sleep(2);
        dfsockopen(getglobal('localurl') . 'misc.php?mod=getthumb', 0, '', '', false, '', 1);
    }
} else {
    dzz_process::unlock($processname);
}
function getDzzExt($ext){
    global $_G;
    $type = '';
    $app = C::t('app_market')->fetch_by_identifier('onlyoffice_view', 'dzz');
    $onlyofficedata = unserialize($app['extra']);
    $docext = explode(',', $onlyofficedata['exts']);
    $gdlimitext = explode(',',$_G['config']['gdgetcolorextlimit']);
    $imgicklimitext = explode(',',$_G['config']['imageickallowextlimit']);
    if(getglobal('setting/imagelib')){
        $imageext = array_merge($gdlimitext,$imgicklimitext);
    }else{
        $imageext = $gdlimitext;
    }
    $imageext[] = 'webp';
    $mediaext = explode(',',$_G['config']['pichomeconvertext']);
    if(in_array($ext,$docext)){
        $type = 'docstatus';
    }elseif(in_array($ext,$imageext)){
        $type = 'imagestatus';
    }elseif(in_array($ext,$mediaext)){
        $type = 'mediastatus';
    }
    return $type;

}
function getQcosExt($ext){
    global $_G;
    $type = '';
    $docext = explode(',', $_G['config']['qcosoffice']);
    $imageext = explode(',',$_G['config']['qcosimage']);
    $mediaext = explode(',',$_G['config']['qcosmedia']);
    if(in_array($ext,$docext)){
        $type = 'docstatus';
    }elseif(in_array($ext,$imageext)){
        $type = 'imagestatus';
    }elseif(in_array($ext,$mediaext)){
        $type = 'mediastatus';
    }
    return $type;

}

exit('success' . $i);