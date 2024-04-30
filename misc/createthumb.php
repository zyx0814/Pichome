<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$limit = 10;//强制生成缩略图并发数量
//runlog('aaaauploadafter',time());
//
//error_reporting(E_ALL);
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
$ridarr = isset($_GET['rids']) ? $_GET['rids'] : [];
if (!$ridarr) exit(json_encode(['success' => true, 'data' => false]));


$data = [];
$needcreate = [];
$hasrecordrids = [];
$thumbrecorddata = [];
foreach (DB::fetch_all("select * from %t where rid in(%n) order by stimes", array('thumb_record', $ridarr)) as $v) {
    if ($v['sstatus']) {
        $data[$v['rid']][] = IO::getFileUri($v['spath']);
    } else {
        $needcreate[] = $v['rid'];
    }
    $hasrecordrids[] = $v['rid'];
    $thumbrecorddata[$v['rid']] = $v;
}

$diff = array_diff($ridarr, $hasrecordrids);
if ($diff) $needcreate = array_merge($diff, $needcreate);
if (count($needcreate) >= $limit) $needcreate = array_splice($needcreate, 0, $limit);
foreach ($needcreate as $v) {
    $data[$v] = false;
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
if (!$thumbState)  exit(json_encode(['success' => true, 'data' => $data]));
foreach ($needcreate as $v) {
    $processname1 = 'PICHOMEGETTHUMB_' . $v;
    //如果当前数据是锁定状态则跳过
    if (dzz_process::islocked($processname1, 60 * 5)) {
        continue;
    }
    //更新当前数据获取缩略图执行次数和时间
    C::t('thumb_record')->update($v, array('stimes' => ($thumbrecorddata[$v]['stimes'] ? $thumbrecorddata[$v]['stimes'] : 0) + 1, 'sdateline' => TIMESTAMP));
    $thumbsign = 'small';
    $metadata = IO::getMeta($v);
    $ext = $metadata['ext'];
    $dzztype = getDzzExt($ext);
    //如果是本地存储位置文件
    if ($metadata['bz'] == 'dzz::') {
        //判断是否符合本地存储生成规则后缀
        if (!$status[$dzztype][$metadata['bz']]) {
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
    //调用系统获取缩略图,暂时改为不直接触发生成
    $returnurl = IO::getThumb($v, $thumbsign, 0, 1, 1);
    dzz_process::unlock($processname1);

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

exit(json_encode(['success' => true, 'data' => $data]));
