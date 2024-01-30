<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
if($operation == 'deletefolder'){//删除目录
    $fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
    $folderdata = C::t('pichome_folder')->fetch($fid);
    $isdelfile = isset($_GET['isdelfile']) ? intval($_GET['isdelfile']):0;
    C::t('pichome_folder')->remove_folder_data($fid,$isdelfile);
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'newfolder'){//创建目录
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    $pfid = isset($_GET['pfid']) ? trim($_GET['pfid']):'';
    $foldername = getstr($_GET['foldername']);
    $return = C::t('pichome_folder')->create_folder_by_appid($appid,$foldername,$pfid);
    exit(json_encode(['success'=>true,'data'=>$return]));
}elseif($operation == 'setfoldercover'){//设置文件夹封面
    $fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
    $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
    C::t('pichome_folder')->update($fid,['cover'=>$rid]);
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'rename'){//修改文件夹名称
    $fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
    $fname = isset($_GET['name']) ? getstr($_GET['name']):'';
    $folderdata = C::t('pichome_folder')->fetch($fid);
    if(!$fname){
        exit(json_encode(array('success'=>false,'msg'=>'文件名不能为空!')));
    }
    C::t('pichome_folder')->update_name_by_fid($fid,$fname);
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'copy'){//粘贴文件
  $iscopy = isset($_GET['iscopy']) ? intval($_GET['iscopy']):1;//是否是复制
  $tfid = isset($_GET['tfid']) ? intval($_GET['tfid']):'';//目标位置
  $ofid = isset($_GET['ofid']) ? intval($_GET['ofid']):'';//原始位置
  $rids = isset($_GET['rids']) ? explode(',',$_GET['rids']):'';//粘贴文件
  if(!$ofid || !$tfid || !$rids) exit(json_encode(array('success'=>false,'msg'=>'参数错误!')));
 $returndata['failedrids'] = $returndata['successrids'] = [];
  foreach($rids as $rid){
      if(C::t('pichome_reources')->move_file_to_folder($rid,$tfid,$iscopy)) $successrids[] = $rid;
      else  $failedrids[] = $rid;
  }
    exit(json_encode(array('success'=>true,'data'=>$returndata)));
}