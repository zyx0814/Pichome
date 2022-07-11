<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
$appids = [''];
foreach(DB::fetch_all("select appid,path from %t where `type` = %d and isdelete < 1",array('pichome_vapp',1,1)) as $v){
    if(IO::checkfileexists($v['path'],1))$appids[] = $v['appid'];
}
if(empty($appids)){
    exit('success');
}
$locked = true;
$processnum = getglobal('config/thumbprocessnum') ? getglobal('config/thumbprocessnum'):1;
for($i=0;$i<$processnum;$i++){
    $processname = 'DZZ_LOCK_PICHOMEGETTHUMB'.$i;
    if (!dzz_process::islocked($processname, 60*15)) {
        $locked=false;
        break;
    }
}
//$processname = 'DZZ_LOCK_PICHOMEGETTHUMB'.$i;
/*if (!dzz_process::islocked($processname, 60*15)) {
    $locked=false;
}*/
$limit = 100;
$start=$i*$limit;

//dzz_process::unlock($processname);
if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}
//查询符合执行条件的数据
$datas = DB::fetch_all("select r.rid,r.ext,r.thumbdonum,v.path from %t r left join %t v on r.appid = v.appid where r.hasthumb < 1 and r.appid in(%n) and r.thumbdotime < v.dateline
order by r.thumbdonum asc limit $start,$limit",array('pichome_resources','pichome_vapp',$appids));
/*//查询符合执行条件的数据
$datas = DB::fetch_all("select r.rid,r.thumbdonum from %t r left join %t v on r.appid = v.appid where r.hasthumb < 1 and r.appid in(%n) 
order by r.thumbdonum asc limit $start,$limit",array('pichome_resources','pichome_vapp',$appids));*/

if($datas){
    foreach($datas as $v){
        if(strpos($v['path'],':') === false){
            $bz = 'dzz';
            $did = 1;
        }else{
            $patharr = explode(':', $v['path']);
            $bz = $patharr[0];
            $did = $patharr[1];

        }
        if($bz == 'dzz') $did = 1;
        $imagestatus = 0;

        //获取导入记录表基本数据
        if(!is_numeric($did) || $did < 2){
            $status =  DB::fetch_first("select * from %t where bz = %s",array('connect_storage','dzz'));
            if(in_array($v['ext'],explode(',',getglobal('config/pichomeffmpeggetthumbext')))){
                $imagestatus = $status['mediastatus'];
            }elseif(in_array($v['ext'],explode(',',getglobal('config/onlyofficeviewextlimit')))){
                $imagestatus = $status['docstatus'];
            }else{
                $imagestatus = $status['imagestatus'];
            }
        }else{
            $status =  DB::fetch_first("select * from %t where id = %d",array('connect_storage',$did));
            if(in_array($v['ext'],explode(',', getglobal('config/qcosmedia')))){
                $imagestatus = $status['mediastatus'];
            }elseif(in_array($v['ext'],explode(',',getglobal('config/qcosoffice')))){
                $imagestatus = $status['docstatus'];
            }else{
                $imagestatus = $status['imagestatus'];
            }
        }
        if(!$imagestatus){
            C::t('pichome_resources')->update($v['rid'],array('thumbdonum'=>intval($v['thumbdonum'])+1,'thumbdotime'=>TIMESTAMP));
            continue;
        }
        $processname1 = 'PICHOMEGETTHUMB_'.$v['rid'];
        //dzz_process::unlock($processname1);
        //如果当前数据是锁定状态则跳过
        if (dzz_process::islocked($processname1, 60*5)) {
            continue;
        }
        //更新当前数据获取缩略图执行次数和时间
        C::t('pichome_resources')->update($v['rid'],array('thumbdonum'=>intval($v['thumbdonum'])+1,'thumbdotime'=>TIMESTAMP));

        $width = getglobal('config/pichomethumsmallwidth') ? getglobal('config/pichomethumsmallwidth') : 512;
        $height = getglobal('config/pichomethumsmallheight') ? getglobal('config/pichomethumsmallheight') : 512;
        //调用系统获取缩略图
        $returnurl = IO::getThumb($v['rid'],$width,$height,0,1,1);
        dzz_process::unlock($processname1);

    }
    dzz_process::unlock($processname);
    if(DB::result_first("select count(r.rid) from %t r left join %t v  on r.appid = v.appid where r.hasthumb=0 and r.appid in(%n) and r.thumbdotime < v.dateline
order by r.thumbdonum ",array('pichome_resources','pichome_vapp',$appids))){
        sleep(2);
        dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=getthumb', 0, '', '', false, '',1);
    }
}else{
    dzz_process::unlock($processname);
}

exit('success'.$i);