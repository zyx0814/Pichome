<?php
	ignore_user_abort(true);
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
	
    @set_time_limit(0);
    ini_set('memory_limit', -1);
    @ini_set('max_execution_time', 0);
   
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):0;
    $processname = 'DZZ_EXPORTFILE_LOCK_'.$appid;
   // dzz_process::unlock($processname);
    $locked = true;
    if (!dzz_process::islocked($processname, 60*5)) {
        $locked=false;
    }
    if ($locked) {
        exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
    }
   // var_dump($locked);die;
    $force = isset($_GET['force']) ? intval($_GET['force']):0;
    $data = C::t('pichome_vapp')->fetch($appid);
    if(!$data) exit(json_encode(array('error'=>'no data')));
    if($data['type'] == 0 && $data['state'] == 1  && $data['isdelete'] == 0){

        include_once dzz_libfile('eagleexport');
        $eagleexport = new eagleexport($data);
        $return = $eagleexport->execExport($force);
    }elseif($data['type'] == 1 && $data['state'] == 1 && $data['isdelete'] == 0){
        include_once dzz_libfile('localexport');
        $localexport = new localexport($data);
        //执行导入文件
        $return = $localexport->execExport($force);
    }
    dzz_process::unlock($processname);
    exit('success');
    