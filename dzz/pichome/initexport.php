<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@ignore_user_abort(true);
@set_time_limit(0);
@ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
if(getglobal('setting/bbclosed')) exit(json_encode(array('error'=>'站点关闭中,请开启站点后重试')));
$appid = isset($_GET['appid']) ? trim($_GET['appid']):0;
$force = isset($_GET['force']) ? intval($_GET['force']):0;
$data = C::t('pichome_vapp')->fetch($appid);
if(!$data) exit(json_encode(array('error'=>'no data')));
if(($data['state'] > 1 &&  $data['state'] < 4) || $data['isdelete'] != 0) exit(json_encode(array('error'=>'export is runing or is deleted')));
if($data['type'] == 0){
    include_once DZZ_ROOT.'dzz'.BS.'eagle'.BS.'class'.BS.'class_eagleexport.php';
    //include_once dzz_libfile('eagleexport');
    $eagleexport = new eagleexport($data);
    $return = $eagleexport->initExport();
}elseif($data['type'] == 1  ){
    include_once DZZ_ROOT.'dzz'.BS.'local'.BS.'class'.BS.'class_localexport.php';
    //include_once dzz_libfile('localexport');
    $localexport = new localexport($data);
    $return = $localexport->initExport();
}elseif ($data['type'] == 2){
    if(!extension_loaded('pdo_sqlite')){
        exit(json_encode(array('error'=>'缺少 pdo_sqlite拓展')));
    }
    include_once DZZ_ROOT.'dzz'.BS.'billfish'.BS.'class'.BS.'class_billfishexport.php';
    $billfishxport = new billfishxport($data);
    $return = $billfishxport->initExport();
}
if(isset($return['error'])){
    exit(json_encode($return));
}
$data = C::t('pichome_vapp')->fetch($appid);
if($data['state'] == 2){
    dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=exportfile&appid=' . $appid.'&force='.$force, 0, '', '', false, '', 1);
}
exit(json_encode(array('success'=>true)));
    