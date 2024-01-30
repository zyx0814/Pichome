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
for($i=0;$i<$processnum;$i++){
    $processname = 'DZZ_LOCK_PICHOMEVIDEOCONVERT'.$i;
    if (!dzz_process::islocked($processname, 60*15)) {
        $locked=false;
        break;
    }
}
//$processname = 'DZZ_LOCK_PICHOMEVIDEOCONVERT'.$i;
$limit = 100;
$start=$i*$limit;
/*if (!dzz_process::islocked($processname, 60*15)) {
    $locked=false;
}*/
//dzz_process::unlock($processname);
if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}
//查询符合执行条件的数据
$datas = DB::fetch_all("select vr.* from %t vr left join %t r on vr.rid=r.rid  left join %t v on v.appid = r.appid where vr.status < 2 and v.isdelete < 1 and vr.endtime < v.dateline
 and v.type = 1  order by vr.jobnum asc limit $start,$limit",array('video_record','pichome_resources','pichome_vapp'));

if($datas){
    foreach($datas as $v){
        $processname1 = 'PICHOMEVIDEOCONVERT'.$v['id'];
        dzz_process::unlock($processname1);
        //如果当前数据是锁定状态则跳过
        if (dzz_process::islocked($processname1, 60*15)) {
            continue;
        }
        //更新当前数据获取缩略图执行次数和时间
        C::t('video_record')->update($v['id'],array('jobnum'=>intval($v['jobnum'])+1,'endtime'=>strtotime('now')));
        if($v['ctype'] == 0){
            include_once DZZ_ROOT.'dzz'.BS.'ffmpeg'.BS.'class'.BS.'class_fmpeg.php';
            $fm=new fmpeg();
            $ret=$fm->convert($v['rid'],$v['format'],$v['videoquality']);
        }
        elseif($v['ctype'] == 2){
            include_once DZZ_ROOT.'dzz'.BS.'qcos'.BS.'class'.BS.'class_video.php';
            $resourcesdata = C::t('pichome_resources')->fetch_data_by_rid($v['rid']);
            $patharr = explode(':',$resourcesdata['realpath']);
            if($patharr[0] != 'QCOS'){
                dzz_process::unlock($processname1);
                continue;
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
            $outputpath = 'tmppichomethumb/'.$resourcesdata['appid'].'/'.md5($resourcesdata['rid']).'.'.$v['format'];
            //创建转码任务
            $fm = new video($config);
            $data = $fm->create_mediajobs($object,$outputpath,$v['videoquality']);
            if($data['error']){
                C::t('video_record')->update($v['id'],array('status'=>-1,'endtime'=>strtotime('now'),'error'=>$data['error']));
            }else{
                unset($data['success']);
                C::t('video_record')->update($v['id'],$data);

            }
        }

        dzz_process::unlock($processname1);

    }
    dzz_process::unlock($processname);
    if(DB::fetch_all("select count(vr.id) from %t vr left join %t r on vr.rid=r.rid  left join %t v on v.appid = r.appid where vr.status < 2 and v.isdelete < 1 and vr.endtime < v.dateline
 and v.type = 1 order by vr.jobnum asc ",array('video_record','pichome_resources','pichome_vapp'))){
        sleep(2);
        dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=getinfo', 0, '', '', false, '',1);
    }else{
        sleep(2);
        dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=getConvertStatus', 0, '', '', false, '',1);
    }
}else{
    dzz_process::unlock($processname);
}

exit('success'.$i);