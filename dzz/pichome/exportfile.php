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
    $processname = 'DZZ_EXPORTFILE_LOCK_'.$appid;
    //dzz_process::unlock($processname);
    $locked = true;
    if (!dzz_process::islocked($processname, 60*15)) {
        $locked=false;
    }
    if ($locked) {
        exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
    }
   // var_dump($locked);die;
    $force = isset($_GET['force']) ? intval($_GET['force']):0;
    $data = C::t('pichome_vapp')->fetch($appid);
    if(!$data) exit(json_encode(array('error'=>'no data')));
    if($data['state'] != 2  && $data['isdelete'] != 0) exit(json_encode(array('error'=>'is deleted or state is not allow')));
    if($data['type'] == 0){
        include_once DZZ_ROOT.'dzz'.BS.'eagle'.BS.'class'.BS.'class_eagleexport.php';
        //include_once dzz_libfile('eagleexport');
        $eagleexport = new eagleexport($data);
        try{
            $return = $eagleexport->execExport($force);
        }catch (Exception $e){
            dzz_process::unlock($processname);
        }

    }elseif($data['type'] == 1){
        include_once DZZ_ROOT.'dzz'.BS.'local'.BS.'class'.BS.'class_localexport.php';
        //include_once dzz_libfile('localexport');
        $localexport = new localexport($data);
        //执行导入文件
        try{
            $return = $localexport->execExport($force);
        }catch (Exception $e){
            dzz_process::unlock($processname);
        }
    }elseif ($data['type'] == 2){
        include_once DZZ_ROOT.'dzz'.BS.'billfish'.BS.'class'.BS.'class_billfishexport.php';
        $billfishxport = new billfishxport($data);
        try{
            $return = $billfishxport->execExport($force);
        }catch (Exception $e){
            dzz_process::unlock($processname);
        }
    }
    dzz_process::unlock($processname);
    $data = C::t('pichome_vapp')->fetch($appid);
    if($data['state'] == 2){
        dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=exportfile&appid=' . $appid.'&force='.$force, 0, '', '', false, '', 1);
    }elseif($data['state'] == 3){
        dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=exportfilecheck&appid=' . $appid, 0, '', '', false, '', 1);
    }
   // exit('success');
    