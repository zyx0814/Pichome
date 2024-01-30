<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$limit=10;//强制生成缩略图并发数量
//runlog('aaaauploadafter',time());
//
//error_reporting(E_ALL);
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
$ridarr = isset($_GET['rids']) ? $_GET['rids']:[];
if(!$ridarr) exit(json_encode(['success'=>true,'data'=>false]));

$data = [];
$needcreate = [];
$hasrecordrids = [];
$thumbrecorddata = [];
foreach(DB::fetch_all("select * from %t where rid in(%n) order by stimes",array('thumb_record',$ridarr)) as $v){
    if($v['sstatus']){
        $data[$v['rid']][] = getglobal('siteurl').IO::getFileUri($v['spath']);
    }else{
        $needcreate[] = $v['rid'];
    }
    $hasrecordrids[] = $v['rid'];
    $thumbrecorddata[$v['rid']] = $v;
}
$diff = array_diff($ridarr,$hasrecordrids);
if($diff) $needcreate = array_merge($diff,$needcreate);
if(count($needcreate)>=$limit) $needcreate=array_splice($needcreate,$limit);
foreach($needcreate as $v){
    $data[$v] = false;
}

/*$locked = true;
$processnum = getglobal('config/thumbprocessnum') ? getglobal('config/thumbprocessnum'):1;
for($i=0;$i<$processnum;$i++){
    $processname = 'DZZ_LOCK_PICHOMECREATTHUMB'.$i;
    if (!dzz_process::islocked($processname, 60*15)) {
        $locked=false;
        break;
    }
}
$limit = 1;
$start=$i*$limit;
//dzz_process::unlock($processname);
if ($locked) {
    if(!$ridarr) exit(json_encode(['success'=>true,'data'=>$data]));
}*/
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
if(!$thumbstate) if(!$ridarr) exit(json_encode(['success'=>true,'data'=>$data]));
foreach($needcreate as $v){
        $processname1 = 'PICHOMEGETTHUMB_'.$v;
        //如果当前数据是锁定状态则跳过
        if (dzz_process::islocked($processname1, 60*5)) {
            continue;
        }
        //更新当前数据获取缩略图执行次数和时间
         C::t('thumb_record')->update($v,array('stimes'=>($thumbrecorddata[$v]['stimes'] ? $thumbrecorddata[$v]['stimes']:0)+1,'sdateline'=>TIMESTAMP));
        $thumbsign = 'small';
        //调用系统获取缩略图,暂时改为不直接触发生成
        $returnurl = IO::getThumb($v,$thumbsign,0,1,1);
        dzz_process::unlock($processname1);

}
 exit(json_encode(['success'=>true,'data'=>$data]));
