<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
global $_G;
$rid = isset($_GET['rid']) ? trim($_GET['rid']):'';

if($rid){
    //记录缩略图数据
    $resourcedata = C::t('pichome_resources')->fetch_data_by_rid($rid);
    $wp = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarkstatus']:'';
    $wt = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarktype']:'';
    $wcontent = $_G['setting']['IsWatermarkstatus'] ? ($_G['setting']['watermarktype'] == 'png' ? $_G['setting']['waterimg']:''):'';
    //缩略图数据
    $thumbrecorddata = [
        'rid' => $rid,
        'ext' => $resourcedata['ext'],
        'filesize'=>$resourcedata['size'],
        'width'=>$resourcedata['width'],
        'height'=>$resourcedata['height'],
        'swidth'=>$_G['setting']['thumbsize']['small']['width'],
        'sheight'=>$_G['setting']['thumbsize']['small']['height'],
        'lwidth' => $_G['setting']['thumbsize']['large']['width'],
        'lheight' => $_G['setting']['thumbsize']['large']['height'],
        'lwaterposition'=>$wp,
        'lwatertype'=>$wt,
        'lwatercontent'=>$wcontent,
        'swaterposition'=>$wp,
        'swatertype'=>$wt,
        'swatercontent'=>$wcontent,
    ];
    C::t('thumb_record')->insert($thumbrecorddata);
    $videoexts =  array('3gp','asf','avi','dv','flv','f4v','m3u8','m4v','mkv','mov','mp4','mpg','mpeg','mts','ogg','rm','rmvb','swf','vob','wmv','webm','mp3','aac','flac','amr','awb','m4a','wma','wav');
    //如果类型不符合则停止执行
    if ($exts && !in_array($data['ext'], $videoexts)) exit();
    $pexts = getglobal('config/pichomeplayermediaext') ? explode(',', getglobal('config/pichomeplayermediaext')) : array('mp3', 'mp4', 'webm', 'ogv', 'ogg', 'wav', 'm3u8', 'hls', 'mpg', 'mpeg');
    if (in_array($data['ext'], $pexts)) {
       exit();
    } else {

        if ('audio' == getTypeByExt($data['ext'])) {
            $ext = 'mp3';
        } else {
            $ext = 'webm';
        }
        $setarr = ['rid' =>$rid, 'dateline' => TIMESTAMP, 'ctype' => 0,'format'=>$ext,'videoquality'=>0];
        $setarr['aid']= $resourcedata['aid'] ? $resourcedata['aid']:0;
        $ff = C::t('video_record')->insert_data($setarr);
    }

}
