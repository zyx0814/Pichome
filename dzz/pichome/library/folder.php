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
    Hook::listen('lang_parse',$return,['getFolderLangKey']);
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
    if(!$folderdata || !$fname){
        exit(json_encode(array('success'=>false,'msg'=>'参数不合法!')));
    }
    C::t('pichome_folder')->updateByFids($fid,['fname'=>$fname]);
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
}elseif($operation == 'foldersort'){
    $fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
    $ofid = isset($_GET['ofid']) ? trim($_GET['ofid']):'';
    $disptype = isset($_GET['type']) ? trim($_GET['type']):'';
    C::t('pichome_folder')->move_to_fidandofid($_GET['fid'],$_GET['ofid'],$disptype);
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'setFolderDefaultAttr'){
    $fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
    $folder = C::t('pichome_folder')->fetch($fid);
    if(submitcheck('setFolderDefaultAttr')){
        $setarr = ['fid'=>$fid];
        foreach($_GET['attr'] as $k=>$v){
            $setarr['keys'][$k] = trim($v);
        }

        C::t('pichome_folder_autodata')->addData($setarr);
        exit(json_encode(array('success'=>true)));
    }else{
        $attrdata = C::t('pichome_folder_autodata')->fetch_by_fid($fid);
        foreach ($attrdata as $k=>$v){
            if($k == 'tag'){
                $tids = explode(',',$v);
                $tagdatas = [];
                foreach(DB::fetch_all("select tagname,tid from %t where tid in(%n)",array('pichome_tag',$tids)) as $tv){
                    Hook::listen('lang_parse',$tv,['getTagLangData']);
                    $tagdatas[] = ['tid'=>$tv['tid'],'tagname'=>$tv['tagname']];
                }
                $attrdata[$k] = $tagdatas;
            }elseif(strpos($k,'tabgroup_') === 0){
                $tids = explode(',',$v);
                Hook::listen('gettab',$tids);
                $attrdata[$k] = $tids;
            }
        }
        $fileds = C::t('pichome_vapp')->fetch_fileds_by_appid($folder['appid'],1);
        $appdata = C::t('pichome_vapp')->fetch($folder['appid']);
        $allowfileds= [];
        foreach($fileds as $k=>$v){
            if($appdata['type'] == 1 || $appdata['type'] == 3){
                if($v['flag'] == 'tag'){
                    $v['value'] = $attrdata[$v['flag']];
                    $allowfileds[] = $v;
                }
            }
            if($v['type'] == 'tabgroup'){
                if($attrdata[$v['flag']]){
                    $v['value'] = $attrdata[$v['flag']];
                }
                $allowfileds[] = $v;
            }
        }
        exit(json_encode(['success'=>true,'data'=>$attrdata,'allowfileds'=>$allowfileds]));
    }
}