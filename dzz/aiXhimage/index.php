<?php
if(!defined('IN_OAOOA') ) {
    exit('Access Denied');
}
Hook::listen('check_login');
$do = isset($_GET['do']) ? trim($_GET['do']):'';
if($do == 'createchat'){//生成结果

    $id = isset($_GET['id']) ? intval($_GET['id']):0;
    if(!$id || !$data = C::t('ai_model')->fetch($id)) exit(json_encode(['success'=>false,'error'=>'参数错误']));
    $replacearr = [];
    foreach($_GET as $k=>$v){
        if(!in_array($k,['mod','op','do','id'])){
            $replacearr['{'.$k.'}'] = $v;
        }
    }
    $pcontent = str_replace(array_keys($replacearr),array_values($replacearr),$data['pcontent']);
    //$pcontent .= '。段落之间用###分割';
    require_once DZZ_ROOT.MOD_PATH.'/class/xhApi.php';
    $apidata = C::t('setting')->fetch('setting_xhaiModel',true);

    if($apidata){
        $appid = $apidata['appid'];
        $ak = $apidata['ak'];
        $sk = $apidata['sk'];
    }else{
        exit(json_encode(['success'=>false,'msg'=>'lowser xhapi config']));
    }
    //暂时固定参数
    $chat = new AIChatWebSocket($appid, $ak, $sk);
    $ret = $chat->xfyun($pcontent);
    if($ret['error_msg']){
       exit(json_encode(['success'=>false,'msg'=>$ret['error_msg']]));
    }else{
        $result['date'] = dgmdate(time(),'Y-m-d H:i:s');
        $result['msg'] = str_replace("\n",'<br>',$ret['result']);
        exit(json_encode(['success'=>true,'result'=>$result]));
    }

}elseif($do == 'work'){//获取ai
    $id = isset($_GET['id']) ? intval($_GET['id']):'';
    $modeldata = C::t('ai_model')->fetch($id);
    $modeldata['formdata'] = unserialize($modeldata['formdata']);
    $modeldata = json_encode($modeldata);

    include template('chat');
}