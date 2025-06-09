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
$appdata = C::t('app_market')->fetch_by_identifier('ffmpeg', 'dzz');
$app = unserialize($appdata['extra']);
$status = $app['status'];
if(!$status){
    exit('admin set not convert!');
}
include_once dzz_libfile('ffmpeg');
$max=10;
$time=TIMESTAMP-60*60*5;
if($max<=DB::result_first("select COUNT(*) from %t where percent>0 and percent<100 and dateline>%d",array('video_record',$time))){
    exit('Over the maximum load, please wait a moment.');
}
$id=intval($_GET['id']);

if(!$ff=C::t('video_record')->fetch($id)){
    exit('convert error');
}
dzz_process::unlock('PICHOMEVIDEOCONVERT'.$id);

if(dzz_process::islocked('PICHOMEVIDEOCONVERT'.$id,60*60)){
    exit('converting!');
}

if($ff['status']==2){
    dzz_process::unlock('PICHOMEVIDEOCONVERT'.$id);
    exit('convert completed');
}
$fm=new fmpeg();
$ret=$fm->convert($ff['id'],$ff['format'],$ff['videoquality']);
dzz_process::unlock('PICHOMEVIDEOCONVERT'.$id);
exit('success');