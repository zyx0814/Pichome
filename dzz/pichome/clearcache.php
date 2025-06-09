<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@ignore_user_abort(true);
@set_time_limit(0);
@ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);

$appid = isset($_GET['appid']) ? trim($_GET['appid']):0;
$processname = 'DZZ_CLEARPERMCACHE_LOCK_'.$appid;

$locked = true;
if (!dzz_process::islocked($processname, 60*10)) {
    $locked=false;
}
if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}
$fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
$hassub = isset($_GET['hassub']) ? intval($_GET['hassub']):0;
$forceset = isset($_GET['forceset']) ? intval($_GET['forceset']):0;
$i = isset($_GET['i']) ? intval($_GET['i']):1;
$isfolder = isset($_GET['isfolder']) ? intval($_GET['isfolder']):1;
$perpage = 1000;
$data = C::t('pichome_vapp')->fetch($appid);
if(!$data) {
    dzz_process::unlock($processname);
    exit(json_encode(array('error'=>'no data')));
}
if($isfolder){
    $fids = [];
    $start = ($i-1)*$perpage;
    if(!$fid){
        if($hassub){
            $count = DB::result_first("select count(fid) from %t where appid = %s",array('pichome_folder',$appid));
            foreach(DB::fetch_all("select fid from %t where appid = %s order by fid asc limit $start,$perpage",['pichome_folder',$appid]) as $v){
                $fids[] = $v['fid'];
            }
        }
        else $count = 0;
    }else{
        if($hassub){
            $cpathkey = DB::result_first("select pathkey from %t where fid = %s and appid = %s",array('pichome_folder',$fid,$appid));
            $count = DB::result_first("select count(fid) from %t where appid = %s and pathkey like %s",['pichome_folder',$appid,$cpathkey]);
            foreach(DB::fetch_all("select fid from %t where appid = %s and pathkey like %s order by fid asc limit $start,$perpage",['pichome_folder',$appid,$cpathkey]) as $v){
                $fids[] = $v['fid'];
            }
        }else{
           $count = 1;
           $fids[] = $fid;

        }
    }
    if(!$fids){
        dzz_process::unlock($processname);
        dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=clearcache&appid=' . $appid.'&hassub='.$hassub.'&fid='.$fid.'&forceset='.$forceset.'&isfolder=0',
            0, '', '', false, '', 1);
    }else{
        C::t('pichome_folder')->clear_cache($fids);
        dzz_process::unlock($processname);
        if(count($fids) > $start+$perpage){
            $i += 1;
            dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=clearcache&appid=' . $appid.'&hassub='.$hassub.'&fid='.$fid.'&forceset='.$forceset.'&i='.$i,
                0, '', '', false, '', 1);
        }else{
           if($forceset) dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=clearcache&appid=' . $appid.'&hassub='.$hassub.'&fid='.$fid.'&forceset='.$forceset.'&isfolder=0',
                0, '', '', false, '', 1);
           else exit('success');
        }

    }

}else{
    $rids = [];
    $start = ($i-1)*$perpage;
        if(!$fid){
            if($hassub){
                $count = DB::result_first("select count(rid) from %t where appid = %s",array('pichome_resources',$appid));
                foreach(DB::fetch_all("select rid from %t where appid = %s order by rid asc limit $start,$perpage",array('pichome_resources',$appid)) as $v){
                    $rids[] = $v;
                }
            }else{
                $count =  DB::result_first("select count(rid) from %t where appid = %s  and isnull(fids)",array('pichome_resources',$appid));
                foreach(DB::fetch_all("select rid from %t where appid = %s  and isnull(fids) order by rid asc limit $start,$perpage",array('pichome_resources',$appid)) as $v){
                    $rids[] = $v;
                }
            }
        }else{
            if($hassub){
                $cpathkey = DB::result_first("select pathkey from %t where fid = %s and appid = %s",array('pichome_folder',$fid,$appid));
                $count = DB::result_first("select distinct count(rid) from %t r left join %t rf on r.rid=fr.rid left join %t f on f.fid=fr.fid where r.appid=%s and f.pathkey like %s",
                ['pichome_resources','pichome_folderresources','pichome_folder',$appid,$cpathkey]);
                foreach(DB::fetch_all("select distinct rid from %t r left join %t rf on r.rid=fr.rid left join %t f on f.fid=fr.fid where r.appid=%s and f.pathkey like %s 
                    order by rid asc limit $start,$perpage",
                    ['pichome_resources','pichome_folderresources','pichome_folder',$appid,$cpathkey]) as $v){
                    $rids[] = $v;
                }
            }else{
                $count =  DB::result_first("select count(rid) from %t where appid = %s  and and find_in_set(%s,fids)",array('pichome_resources',$appid,$fid));
                foreach(DB::fetch_all("select rid from %t where appid = %s  and and find_in_set(%s,fids)) order by rid asc limit $start,$perpage",array('pichome_resources',$appid,$fid)) as $v){
                    $rids[] = $v;
                }
            }

        }
    if(!$rids){
        dzz_process::unlock($processname);
        exit('success');
    }else{
        C::t('pichome_resources')->clear_cache($rids);
        dzz_process::unlock($processname);
        if(count($rids) > $start+$perpage){
            $i += 1;
            dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=clearcache&appid=' . $appid.'&hassub='.$hassub.'&fid='.$fid.'&isfolder=0&forceset='.$forceset.'&i='.$i,
                0, '', '', false, '', 1);
        }else{
            dzz_process::unlock($processname);
            exit('success');
        }

    }
}



