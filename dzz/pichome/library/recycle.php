<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
if($operation == 'deletefile'){//删除文件到回收站
    $rids = isset($_GET['rids']) ? trim($_GET['rids']):'';
    $rids = explode(',',$rids);
    C::t('pichome_resources')->recycle_data_by_rids($rids);
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'removefolder'){//将文件从目录中移除
    $fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
    $rids = isset($_GET['rids']) ? trim($_GET['rids']):'';
    $rids = explode(',',$rids);
    C::t('pichome_folderresources')->delete_by_ridfid($rids,$fid);
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'finallydel'){//彻底删除文件
    if(getglobal('adminid') != 1){
        exit(json_encode(array('success'=>false,'msg'=>'没有权限')));
    }
    $rids = isset($_GET['rids']) ? trim($_GET['rids']):'';
    $rids = explode(',',$rids);
    C::t('pichome_resources')->delete_by_rid($rids);
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'recoverfile'){//恢复文件
    $rids = isset($_GET['rids']) ? trim($_GET['rids']):'';
    $rids = explode(',',$rids);
    C::t('pichome_resources')->recover_file_by_rids($rids,['isdelete'=>0]);
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'emptyrecycle'){//清空回收站
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    if(!$appid)exit(json_encode(array('success'=>false,'msg'=>lang('params_error'))));
    if(getglobal('adminid') != 1){
        exit(json_encode(array('success'=>false,'msg'=>lang('no_perm'))));
    }
    C::t('pichome_resources')->empty_recycle_data($appid);
    dfsockopen(getglobal('localurl') . 'misc.php?mod=finalydelfile', 0, '', '', false, '',0.1);
    exit(json_encode(array('success'=>true)));
}