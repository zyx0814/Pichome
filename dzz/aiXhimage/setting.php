<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/11/28
 * Time: 14:32
 */
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
Hook::listen('adminlogin');
$appname=lang('appname');
$navtitle=lang('setting');

$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if($do == 'addPrompt'){
    $cate = isset($_GET['cate']) ? intval($_GET['cate']) : 0;
    $name = isset($_GET['name']) ? trim($_GET['name']) : '';
    $prompt = isset($_GET['prompt']) ? getstr($_GET['prompt']) : '';
    if(!$name || !$prompt){
        exit(json_encode(array('success'=>false,'error'=>lang('please_input_all_info'))));
    }else{
        $prompts = array(
            'name'=>$name,
            'prompt'=>$prompt,
            'cate'=>$cate,
            'disp'=>DB::result_first("select max(disp) from %t where cate = %d",['ai_xhimageprompt',$cate])+1,
            'isdefault'=>0,
            'status'=>isset($_GET['status']) ? intval($_GET['status']):0
        );
        $id = C::t('ai_xhimageprompt')->insertData($prompts);
        if($id){
            exit(json_encode(array('success'=>true)));
        }else{
            exit(json_encode(array('success'=>false,'error'=>lang('add_unsuccess'))));
        }
    }
}elseif($do == 'editPrompt'){
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $name = isset($_GET['name']) ? trim($_GET['name']) : '';
    $prompt = isset($_GET['prompt']) ? getstr($_GET['prompt']) : '';
    if(!$name || !$prompt){
        exit(json_encode(array('success'=>false,'error'=>lang('please_input_all_info'))));
    }else{
        $prompts = array(
            'name'=>$name,
            'prompt'=>$prompt,
        );
        $oldid = C::t('ai_xhimageprompt')->editById($id,$prompts);
        if($oldid){
            exit(json_encode(array('success'=>true)));
        }else{
            exit(json_encode(array('success'=>false,'error'=>lang('edit_unsuccess'))));
        }
    }

}elseif($do == 'delPrompt'){
    if(C::t('ai_xhimageprompt')->deleteById($_GET['id'])){
        exit(json_encode(array('success'=>true)));
    }else{
        exit(json_encode(array('success'=>false,'error'=>lang('del_unsuccess'))));
    }
}elseif($do == 'sortPrompt'){
    $ids = isset($_GET['ids']) ? $_GET['ids'] : '';
    $ids = explode(',',$ids);
    C::t('ai_xhimageprompt')->sortByIds($ids);
    exit(json_encode(array('success'=>true)));
}elseif($do == 'setStatus'){
    $status = intval($_GET['status']);
    C::t('ai_xhimageprompt')->setStatusById($_GET['id'],$status);
    exit(json_encode(array('success'=>true)));
}elseif($do == 'getPromptByCate'){
    $cate = isset($_GET['cate']) ? intval($_GET['cate']) : 0;
    $data = C::t('ai_xhimageprompt')->fetchPromptByCate($cate);
    exit(json_encode(array('success'=>true,'data'=>$data)));
}else{
    include libfile('function/cache');
    if (submitcheck('settingsumbit')) {
        $status = isset($_GET['status']) ? intval($_GET['status']) : 0;
        $appid = trim($_GET['appid']);
        $ak = trim($_GET['ak']);
        $sk = trim($_GET['sk']);
        if($status && (!$appid || !$ak || !$sk)){
            showmessage(lang('config_not_complete'), dreferer(), array(), array('alert' => 'error'));
        }
        $arr=array(
            'appid'=>trim($_GET['appid']),
            'ak'=>trim($_GET['ak']),
            'sk'=>trim($_GET['sk']),
            'status'=>intval($_GET['status'])
        );
        C::t('setting')->update('setting_xhImageDataSetting',$arr);
        updatecache('setting');
        exit(json_encode(array('success'=>true)));
    }else{
        $setting=C::t('setting')->fetch('setting_xhImageDataSetting',true);
        if(!$setting['status']) $setting['status'] = 0;
        include template('setting');
        exit();
    }
}
