<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
$id = isset($_GET['id']) ? intval($_GET['id']):0;
if(!$id) exit('success');
$locked = true;
$processname = 'DZZ_LOCK_AICRONTOAITSAK_'.$id;
if (!dzz_process::islocked($processname, 60*5)) {
    $locked=false;
}
//dzz_process::unlock($processname);
if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}

$crondata = C::t('ai_cron')->fetch($id);
if(!$crondata) exit('success');
$getTypeArr = ['name'=>0,'tag'=>1,'desc'=>2];
$limit = 1000;
$docount = $crondata['docount'] ? intval($crondata['docount']):0;
//如果是文件
if($crondata['idtype'] == 0){
    $rids = explode(',',$crondata['idval']);
    $count = count($rids);
    $appid = DB::result_first("select appid from %t where rid = %s",['pichome_resources',$rids[0]]);
    $getcontent = unserialize($crondata['getContent']);
    if($docount < $count){

            foreach($rids as $rid){
                foreach($getcontent as $k=>$v){
                $taskarr = [
                    'rid'=>$rid,
                    'gettype'=>$getTypeArr[$v['flag']],
                    'tplid'=>$v['tplid'],
                    'uid'=>$crondata['uid'],
                    'aikey'=>$v['key'],
                    'appid'=>$appid,
                    'dateline'=>TIMESTAMP
                ];
                C::t('ai_task')->insertData($taskarr);
            }
            $docount++;
            C::t('ai_cron')->update($id,['docount'=>$docount]);
        }
        if($docount >= $count){
            C::t('ai_cron')->delete($id);
        }
    }else{
        C::t('ai_cron')->delete($id);
    }


}elseif($crondata['idtype'] == 1){
    $fid = $crondata['idval'];
    $folderarr = DB::fetch_first("select pathkey,appid from %t where fid = %s", array('pichome_folder', $fid));
    $pathkey = $folderarr['pathkey'];
    $appid = $folderarr['appid'];
    $params = ['pichome_resources','pichome_folderresources','pichome_folder',$pathkey.'%'];
    if(!$crondata['totalcount']){
        $count = DB::result_first("select count(DISTINCT r.rid) from %t r 
left join %t fr on fr.rid = r.rid 
left join %t f on fr.fid = f.fid 
where r.isdelete = 0  and f.pathkey like %s ",$params);
        C::t('ai_cron')->update($id,['totalcount'=>$count]);
    }else{
        $count = $crondata['totalcount'];
    }
    if($docount < $count){
        $getcontent = unserialize($crondata['getContent']);
        $start = $docount ? intval($docount-1):0;
        $rids =  DB::fetch_all("select DISTINCT r.rid from %t r 
left join %t fr on fr.rid = r.rid 
left join %t f on fr.fid = f.fid 
where r.isdelete = 0  and f.pathkey like %s limit $start,$limit",$params);
            foreach($rids as $rid){
                foreach($getcontent as $k=>$v){
                $taskarr = [
                    'rid'=>$rid['rid'],
                    'gettype'=>$getTypeArr[$v['flag']],
                    'tplid'=>$v['tplid'],
                    'uid'=>$crondata['uid'],
                    'aikey'=>$v['key'],
                    'appid'=>$appid,
                    'dateline'=>TIMESTAMP
                ];
                C::t('ai_task')->insertData($taskarr);
            }
            $docount++;
            C::t('ai_cron')->update($id,['docount'=>$docount]);
        }
        if($docount >= $count){
            C::t('ai_cron')->delete($id);
        }
    }else{
        C::t('ai_cron')->delete($id);
    }
}elseif($crondata['idtype'] == 2){

    $appid = $crondata['idval'];
    $params = ['pichome_resources',$appid];
    if(!$crondata['totalcount']){
        $count = DB::result_first("select count(DISTINCT rid) from %t  where isdelete = 0  and appid = %s ",$params);
        C::t('ai_cron')->update($id,['totalcount'=>$count]);
    }else{
        $count = $crondata['totalcount'];
    }

    if($docount < $count){
        $getcontent = unserialize($crondata['getContent']);
        $start = $docount ? intval($docount-1):0;
        $rids =  DB::fetch_all("select DISTINCT rid from %t where isdelete = 0  and appid = %s 
limit $start,$limit",$params);


            foreach($rids as $rid){
                foreach($getcontent as $k=>$v){
                $taskarr = [
                    'rid'=>$rid['rid'],
                    'gettype'=>$getTypeArr[$v['flag']],
                    'tplid'=>$v['tplid'],
                    'uid'=>$crondata['uid'],
                    'aikey'=>$v['key'],
                    'appid'=>$appid,
                    'dateline'=>TIMESTAMP
                ];
                C::t('ai_task')->insertData($taskarr);
            }
            $docount++;
            C::t('ai_cron')->update($id,['docount'=>$docount]);
        }
        if($docount >= $count){
            C::t('ai_cron')->delete($id);
        }
    }else{
        C::t('ai_cron')->delete($id);
    }
}
dfsockopen(getglobal('localurl') . 'misc.php?mod=doaitask', 0, '', '', false, '', 0.5);
dzz_process::unlock($processname);
if($docount < $count){
    dfsockopen(getglobal('localurl') . 'misc.php?mod=aicrontotask&id='.$id, 0, '', '', false, '', 0.1);
}else{
    exit('success');
}
