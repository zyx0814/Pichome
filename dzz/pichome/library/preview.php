<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
if($operation == 'addPreview'){
    $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
    $aid = isset($_GET['aid']) ? intval($_GET['aid']):0;
    if(!$rid || !$aid){
        exit(json_encode(array('success'=>false,'msg'=>'参数错误!')));
    }
    $id = C::t('thumb_preview')->addPreview($rid,$aid);
    if($id){
        dfsockopen(getglobal('localurl') . 'misc.php?mod=getPreviewThumb', 0, '', '', false, '',1);
        //$icon = IO::getPreviewThumb($rid,'small',0,1,1);
        exit(json_encode(array('success'=>true,'data'=>$id)));
    }else{
        exit(json_encode(array('success'=>false)));
    }
}elseif($operation == 'editCover'){//修改封面
    $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
    $aid = isset($_GET['aid']) ? intval($_GET['aid']):'';
    if(!$rid || !$aid){
        exit(json_encode(array('success'=>false,'msg'=>'参数错误!')));
    }
      if(C::t('thumb_preview')->editCover($rid,$aid)){
          dfsockopen(getglobal('localurl') . 'misc.php?mod=getPreviewThumb', 0, '', '', false, '',1);
          $icon = C::t('pichome_resources')->geticondata_by_rid($rid,1,1);
          exit(json_encode(array('success'=>true,'data'=>$icon)));
      }else{
          exit(json_encode(array('success'=>false)));
      }
}elseif($operation == 'delCover'){//恢复封面
    $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
    if(!$rid){
        exit(json_encode(array('success'=>false,'msg'=>'参数错误!')));
    }
    if(C::t('thumb_preview')->delCover($rid)){
        $icon = IO::getThumb($rid,'small',0,1,1);
        exit(json_encode(array('success'=>true,'data'=>$icon)));
    }else{
        exit(json_encode(array('success'=>false)));
    }

}elseif($operation == 'delPreview'){//删除预览图
    $id = isset($_GET['id']) ? trim($_GET['id']):'';
    if(!$id){
        exit(json_encode(array('success'=>false,'msg'=>'参数错误!')));
    }
    C::t('thumb_preview')->delPreview($id);
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'sortPreview'){//排序
    $ids = isset($_GET['ids']) ? $_GET['ids']:[];
    foreach($ids as $key=>$value){
       if($value) C::t('thumb_preview')->update($value,['disp'=>$key]);
    }
    exit(json_encode(array('success'=>true)));
}