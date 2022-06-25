<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 *
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
ignore_user_abort(true);
@set_time_limit(0);
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$setting = C::t('setting')->fetch('ffmpeg_setting',true);
if($setting['upload_convert_immediately']>1){
    exit('admin set not convert!');
}
include_once dzz_libfile('video');
$max=10;
$time=TIMESTAMP-60*60*5;
if($max<=DB::result_first("select COUNT(*) from %t where percent>0 and percent<100 and dateline>%d",array('video_record',$time))){
    exit('Over the maximum load, please wait a moment.');
}
$id=intval($_GET['id']);

if(!$ff=C::t('video_record')->fetch($id)){
    exit('convert error');
}
$processname = 'PICHOMEVIDEOCONVERT'.$id;
if(dzz_process::islocked($processname,60*60)){
    exit('converting!');
}

if($ff['status']==2){
    dzz_process::unlock($processname);
    exit('convert completed');
}
$resourcesdata = C::t('pichome_resources')->fetch_data_by_rid($ff['rid']);
$patharr = explode(':',$resourcesdata['realpath']);
if($patharr[0] != 'QCOS'){
    dzz_process::unlock($processname);
    exit('error');
}
//获取存储配置信息
$did = $patharr[1];
$qcosconfig = C::t('connect_storage')->fetch( $did);
$hostarr = explode(':',$qcosconfig['hostname']);
$config = [
    'secretId' => trim($qcosconfig['access_id']),
    'secretKey' => dzzdecode($qcosconfig['access_key'], 'QCOS'),
    'region' => $hostarr[1],
    'schema' => $hostarr[0],
    'bucket'=>trim($qcosconfig['bucket'])
];
$fpatharr = explode('/',$resourcesdata['realpath']);
unset($fpatharr[0]);
$ofpath = implode('/',$fpatharr);
$object = str_replace(BS,'/',$ofpath);
$outputpath = 'tmppichomethumb/'.$resourcesdata['appid'].'/'.md5($resourcesdata['realpath']).'.'.$ff['format'];
//创建转码任务
$fm = new video($config);
$data = $fm->create_mediajobs($object,$outputpath,$ff['videoquality']);
if($data['error']){
    C::t('video_record')->update($ff['id'],array('status'=>-1,'endtime'=>strtotime('now'),'error'=>$data['error']));
}else{
    unset($data['success']);
    C::t('video_record')->update($ff['id'],$data);

}
dzz_process::unlock($processname);