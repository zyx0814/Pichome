<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    ignore_user_abort(true);
    @set_time_limit(0);
    @set_time_limit(0);
    @ini_set('max_execution_time', 0);
    
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):0;
    $force = isset($_GET['force']) ? intval($_GET['force']):0;
    $data = C::t('pichome_vapp')->fetch($appid);
    if(!$data) exit(json_encode(array('error'=>'no data')));
    if($data['type'] == 0){
        include_once dzz_libfile('eagleexport');
        $eagleexport = new eagleexport($data);
        $return = $eagleexport->execExport($force);
        print_r($return);
    }else{
        include_once dzz_libfile('oaooaexport');
        $oaooaexport = new oaooaexport($data);
        //导入标签分类数据
        $oaooaexport->exportTaggroup();
        //执行导入文件
        $return = $oaooaexport->initExport();
    
    }
    