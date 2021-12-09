<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    @set_time_limit(0);
    @ini_set('memory_limit', -1);
    @ini_set('max_execution_time', 0);
    
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):0;
    $force = isset($_GET['force']) ? intval($_GET['force']):0;
    $data = C::t('pichome_vapp')->fetch($appid);
    if(!$data) exit(json_encode(array('error'=>'no data')));
    if($data['type'] == 0 && $data['isdelete'] == 0){
        include_once dzz_libfile('eagleexport');
        $eagleexport = new eagleexport($data);
        $return = $eagleexport->initExport();
    }elseif($data['type'] == 1 && $data['isdelete'] == 0){
        include_once dzz_libfile('localexport');
        $localexport = new localexport($data);
        $return = $localexport->initExport();
    }
    exit(json_encode(array('success'=>true)));
    