<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
$appids = [''];
foreach(DB::fetch_all("select appid from %t where `type` = %d and getinfo = %d and isdelete < 1",array('pichome_vapp',1,1)) as $v){
    $appids[] = $v['appid'];
}
if(empty($appids)){
    exit('success');
}
$locked = true;
/*for($i=0;$i<1;$i++){
    $processname = 'DZZ_LOCK_PICHOMEGETCOLOR'.$i;
    if (!dzz_process::islocked($processname, 60*60)) {
        $locked=false;
        break;
    }
}*/
$i = 0;
$processname = 'DZZ_LOCK_PICHOMEGETCOLOR'.$i;
$limit = 10;
$start=$i*$limit;
if (!dzz_process::islocked($processname, 60*30)) {
    $locked=false;
}
//dzz::unlock($processname);
if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}
$datas = DB::fetch_all("select * from %t where thumbstatus = 0 or colorstatus = 0  and appid in(%n)
order by thumbdonum asc,colordonum asc limit $start,$limit",array('pichome_imagickrecord',$appids));

use dzz\imageColor\classes\getcolor as getcolor;

if($datas){
    $getcolor =new getcolor;
    foreach($datas as $v){
        $processname1 = 'PICHOMEGETCOLOR_'.$v['rid'];
        if (dzz_process::islocked($processname1, 60*5)) {
            continue;
        }
        $data = C::t('pichome_resources')->fetch_data_by_rid($v['rid']);

        if(empty($data)){
            C::t('pichome_imagickrecord')->delete($v['rid']);
            dzz_process::unlock($processname1);
            continue;
        }else{
            //如果缩略图执行次数大于三次，直接赋值为1，不再尝试生成
            if($v['thumbdonum'] > 3 && $v['thumbstatus'] == 0){
                $v['thumbstatus'] = 1;
                //如果当前文件为pdf,直接赋值颜色生成为1
                if(strtolower($v['ext']) == 'pdf'){
                    $v['colorstatus'] = 1;
                    C::t('pichome_imagickrecord')->update($v['rid'],array('thumbstatus'=>1,'colorstatus'=>1));
                }else{
                    C::t('pichome_imagickrecord')->update($v['rid'],array('thumbstatus'=>1));
                }
            }
            if($v['colordonum'] > 3 && $v['colorstatus'] == 0){
                $v['colorstatus'] = 1;
                C::t('pichome_imagickrecord')->update($v['rid'],array('colorstatus'=>1));
            }
            //如果颜色和缩略图标记为已生成，标记该文件信息状态为已获取
            if($v['thumbstatus'] == 1 && $v['colorstatus'] == 1){
                if(!DB::result_first("select isget from %t where rid = %s",array('pichome_resources_attr',$v['rid']))){
                    C::t('pichome_resources_attr')->update($v['rid'],array('isget'=>1));
                    C::t('pichome_vapp')->add_getinfonum_by_appid($v['appid'], 1);
                }

                dzz_process::unlock($processname1);
                continue;
            }
            // $data['colorstatus'] = $v['colorstatus'];
            //$data['thumbstatus'] = $v['thumbstatus'];
            if(strtolower($data['ext']) == 'pdf'){
                DB::query("update %t set thumbdonum=thumbdonum+%d where rid = %s ", array('pichome_imagickrecord', 1, $data['rid']));
                $getcolor->getpdfthumb($data);
            }else{
                if(!$v['colorstatus']) DB::query("update %t set colordonum=colordonum+%d where rid = %s ", array('pichome_imagickrecord', 1, $data['rid']));
                if(!$v['thumbstatus'])  DB::query("update %t set thumbdonum=thumbdonum+%d where rid = %s ", array('pichome_imagickrecord', 1, $data['rid']));
                $getcolor->rundata($data);
            }
        }
        dzz_process::unlock($processname1);
    }
    dzz_process::unlock($processname);
    if(DB::result_first("select count(*) from %t where thumbstatus = 0 or colorstatus = 0 and appid in(%n) ",array('pichome_imagickrecord',$appids))){
        dfsockopen(getglobal('localurl') . 'index.php?mod=imageColor&op=index', 0, '', '', false, '', 5*60);
    }
}
dzz_process::unlock($processname);
exit('success'.$i);