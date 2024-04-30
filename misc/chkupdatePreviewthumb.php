<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
global $_G;
$thumbsize = $_G['setting']['thumbsize'];
$wp = $_G['setting']['IsWatermarkstatus'] ? intval($_G['setting']['watermarkstatus']):0;//水印位置
$wt = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarktype']:0;//水印类型
$wcontent = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['waterimg']:'';//水印内容
$appids = [''];
$appdata = [];
foreach(DB::fetch_all("select appid,path,`type` from %t where (`type` = %d or `type` = %d )and isdelete < 1",array('pichome_vapp',1,3,1)) as $v){
    if($v['type'] == 3 || IO::checkfileexists($v['path'],1)){
        $appids[] = $v['appid'];
        $appdata[$v['appid']] = $v;
    }
}

if(empty($appids)){
    exit('success');
}
$locked = true;
$processname = 'DZZ_LOCK_PICHOMEGETTHUMBCHECK';
if (!dzz_process::islocked($processname, 60*15)) {
    $locked=false;
}
$limit = 1000;
$start=0;
//dzz_process::unlock($processname);
if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}
$checktime = TIMESTAMP - 300;
$datas = DB::fetch_all("select * from %t where (schk = 0 or lchk = 0) and  checktime < %d order by checktime asc limit $start,$limit",array('thumb_preview',$checktime));

foreach ($datas as $v) {
    $setarr = ['checktime'=>TIMESTAMP];
    //更新当前数据获取缩略图执行次数和时间

    if ($v['swidth'] != $thumbsize['small']['width'] || $v['sheight'] != $thumbsize['small']['height'] ||
        $v['swaterposition'] != $wp || $v['swatertype'] != $wt || $v['swatercontent'] != $wcontent) {

        $setarr['schk'] = 1;
    }
    if ($v['lwidth'] != $thumbsize['large']['width'] || $v['lheight'] != $thumbsize['large']['height'] ||
        $v['lwaterposition'] != $wp || $v['lwatertype'] != $wt || $v['lwatercontent'] != $wcontent) {
        $setarr['lchk'] = 1;
    }
    C::t('thumb_preview')->update($v['id'],$setarr);

}

dzz_process::unlock($processname);



