<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);

$locked = true;
$processnum = getglobal('config/convertprocessnum') ? getglobal('config/convertprocessnum'):1;
for($i=0;$i<$processnum;$i++) {
    $processname = 'DZZ_LOCK_PICHOMEVIDEOCONVERT' . $i;
    if (!dzz_process::islocked($processname, 60 * 15)) {
        $locked = false;
        break;
    }
}

/*$i = 0;
$processname = 'DZZ_LOCK_PICHOMEVIDEOCONVERT'.$i;*/
$limit = 10;
$start=$i*$limit;
/*if (!dzz_process::islocked($processname, 60*15)) {
    $locked=false;
}*/
//dzz_process::unlock($processname);
if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}
//获取所有存储位置的视频处理状态

$cachename = 'PICHOMECONVERTSTATUS';

$convertstatus = C::t('cache')->fetch_cachedata_by_cachename($cachename);
if (!$convertstatus) {
    $convertdata = [];
    $app = C::t('app_market')->fetch_by_identifier('ffmpeg', 'dzz');
    $appextra = unserialize($app['extra']);
    $convertdata['dzz::'] = $appextra['status'];
    foreach(DB::fetch_all("select id,bz,mediastatus from %t where 1",array('connect_storage')) as $v){
        if($v['bz'] == 'dzz') continue;
        $key = $v['bz'].':'.$v['id'].':';
        $convertdata[$key] = intval($v['mediastatus']);
    }
    $setarr = ['cachekey' => $cachename, 'cachevalue' => serialize($convertdata), 'dateline' => time()];
    C::t('cache')->insert_cachedata_by_cachename($setarr);
} else {
    $convertdata =$convertstatus;
}

$convertsate = 0;
foreach($convertdata as $v){
    if($v) $convertsate = 1;
}
if(!$convertsate) exit(json_encode( array('error'=>'转换未开启')));
if($_GET['id']){
    $datas = DB::fetch_all("select vr.* from %t vr left join %t r on vr.rid=r.rid  left join %t v on v.appid = r.appid where  vr.id = %d and vr.status <= 0 and v.isdelete < 1 
   order by vr.jobnum asc limit $start,1",array('video_record','pichome_resources','pichome_vapp',$_GET['id']));
}else{
    //查询符合执行条件的数据
    $datas = DB::fetch_all("select vr.* from %t vr left join %t r on vr.rid=r.rid  left join %t v on v.appid = r.appid where vr.status <= 0 and v.isdelete < 1 
   order by vr.jobnum asc limit $start,$limit",array('video_record','pichome_resources','pichome_vapp'));

}


if($datas){
    foreach($datas as $v){
        $processname1 = 'PICHOMEVIDEOCONVERT'.$v['id'];
        //dzz_process::unlock($processname1);
        //如果当前数据是锁定状态则跳过
        if (dzz_process::islocked($processname1, 60*15)) {
            continue;
        }
        if($v['rid']){
            $metadata = IO::getMeta($v['rid']);
        }else{
            $metadata = IO::getMeta('attach::'.$v['aid']);
        }
        if(!$metadata) continue;
        $bz = $metadata['bz'];
        //$bzarr = explode(':',$bz);
        if(!$v['ctype']){
            //优先获取当前存储位置的视频处理服务是否开启
            if($bz != 'dzz::' && $convertdata[$bz]){
                $v['ctype'] = 2;
            }elseif($convertdata['dzz::']){
                $v['ctype'] = 1;
            }else{
                continue;
            }
        }

        //更新当前数据获取缩略图执行次数和时间
        C::t('video_record')->update($v['id'],array('jobnum'=>intval($v['jobnum'])+1,'endtime'=>strtotime('now')));
        if($v['ctype'] == 1){
            include_once DZZ_ROOT.'dzz'.BS.'ffmpeg'.BS.'class'.BS.'class_fmpeg.php';
            $fm=new fmpeg();
            $ret=$fm->convert($v['id'],$v['format'],$v['videoquality']);
        }
        elseif($v['ctype'] == 2){
            include_once DZZ_ROOT.'dzz'.BS.'qcos'.BS.'class'.BS.'class_video.php';
            $bzarr = explode(':',$bz);
            if($bzarr[0] == 'QCOS'){
                $qcosconfig = C::t('connect_storage')->fetch($metadata['remoteid']);
                $hostarr = explode(':',$qcosconfig['hostname']);
                $config = [
                    'secretId' => trim($qcosconfig['access_id']),
                    'secretKey' => dzzdecode($qcosconfig['access_key'], 'QCOS'),
                    'region' => $hostarr[1],
                    'schema' => $hostarr[0],
                    'bucket'=>trim($qcosconfig['bucket'])
                ];
                $fpatharr = explode(':',$metadata['path']);
                unset($fpatharr[0]);
                $ofpath = $fpatharr[2];

                $object = str_replace(BS,'/',$ofpath);
                $outputpath = 'pichomethumb/'. date('Ym') . '/' . date('d') .'/'.md5($metadata['path']).'.'.$v['format'];
                //创建转码任务
                $fm = new video($config);
                $data = $fm->create_mediajobs($object,$outputpath,$v['videoquality']);
                if($data['error']){
                    C::t('video_record')->update($v['id'],array('status'=>-1,'endtime'=>strtotime('now'),'error'=>$data['error']));
                }else{
                    unset($data['success']);
                    $data['path'] = $outputpath;
                    $data['ctype'] = $v['ctype'];
                    C::t('video_record')->update($v['id'],$data);

                }
            }

        }

        dzz_process::unlock($processname1);

    }
    dzz_process::unlock($processname);
    if(DB::fetch_all("select count(vr.id) from %t vr left join %t r on vr.rid=r.rid  left join %t v on v.appid = r.appid where vr.status <= 0 and v.isdelete < 1 and vr.endtime < v.dateline
 and v.type = 1 order by vr.jobnum asc ",array('video_record','pichome_resources','pichome_vapp'))){
        sleep(2);
        dfsockopen(getglobal('localurl') . 'misc.php?mod=convert', 0, '', '', false, '',1);
    }else{
        sleep(2);
        dfsockopen(getglobal('localurl') . 'misc.php?mod=getConvertStatus', 0, '', '', false, '',1);
    }
}else{
    dzz_process::unlock($processname);
}

exit('success'.$i);