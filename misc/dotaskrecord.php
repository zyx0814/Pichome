<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
$id = isset($_GET['id']) ? intval($_GET['id']):0;
$locked = true;
$processnamepre = 'DZZ_LOCK_TASKRECORD_';
if($id){
    $taskdata = C::t('task_record')->fetch($id);
    $processname = $processnamepre.$id;
}else{
    $taskdata = DB::fetch_first("select * from %t where donum < totalnum limit 0,1", array('task_record'));
    $processname = $processnamepre.$taskdata['id'];
}
//dzz_process::unlock($processname);
if (!dzz_process::islocked($processname, 60*5)) {
    $locked=false;
}
if ($locked) {
    exit(json_encode(array('error' => '进程已被锁定请稍后再试')));
}

$limit = 100;

if($taskdata && $taskdata['idtype'] == 0){//目录任务
    $fid = $taskdata['idvalue'];
    $folderdata = C::t('pichome_folder')->fetch($fid);
    $sql = " select r.rid,r.dateline from %t r left join %t fr  on fr.rid = r.rid  ";
    $params = ['pichome_resources','pichome_folderresources',$folderdata['pathkey'].'%'];

    $count = DB::result_first("select count( DISTINCT r.rid) from %t r left join %t fr  on fr.rid = r.rid where fr.pathkey like %s ", $params);

    C::t('task_record')->update($taskdata['id'],array('totalnum'=>$count));
    $wheresql = " where  fr.pathkey like %s ";
    $ordersql = " group by r.rid order by r.dateline asc,r.rid asc limit $limit ";
    if($taskdata['lastid']){
        $wheresql .= " and ((r.rid > %s and r.dateline = %d) or r.dateline > %d)  ";
        $param = array($taskdata['lastid'],$taskdata['lastdate'],$taskdata['lastdate']);
    }else{
        $where = '';
        $param = array();
    }

    $params = array_merge($params,$param);
    $donum = $taskdata['donum'] ? intval($taskdata['donum']):0;
    if($donum < $count){
        $attrs = C::t('pichome_folder_autodata')->fetch_attrs_by_fid($fid);
        foreach(DB::fetch_all("$sql $wheresql $ordersql",$params) as $v){
            $processname1 = $processnamepre.$v['rid'];
            if (!dzz_process::islocked($processname1, 60)) {
                foreach($attrs as $key=>$val){
                    if($key=='tag'){
                        foreach($val as $tid){
                            if(!$tid) continue;
                            $rtag = ['appid' => $folderdata['appid'], 'rid' => $v['rid'], 'tid' => $tid];
                            C::t('pichome_resourcestag')->insert($rtag);
                        }
                    }elseif(strpos($key,'tabgroup_') === 0){
                        $gid = intval(str_replace('tabgroup_','',$key));
                        foreach($val as $tabid){
                            if(!$tabid) continue;
                            $rtabg = ['appid' => $folderdata['appid'], 'rid' => $v['rid'], 'tid' => $tabid,'gid'=>$gid];
                            C::t('pichome_resourcestab')->insert($rtabg);
                        }
                    }
                }
                $donum ++;
                C::t('task_record')->update($taskdata['id'],array('donum'=>$donum,'lastid'=>$v['rid'],'lastdate'=>$v['dateline']));
                dzz_process::unlock($processname1);
            }else{
                continue;
            }


        }
    }
}

dzz_process::unlock($processname);
if ($count && $donum < $count) {
    sleep(1);
    $ret=dfsockopen(getglobal('localurl') . 'misc.php?mod=dotaskrecord&id=' . $taskdata['id'], 0, 0, '', '', false, 1);
    if($ret){
        exit('next task start');
    }else{
        exit('next task error');
    }

} else {
    C::t('task_record')->delete($taskdata['id']);
    exit('success');
}