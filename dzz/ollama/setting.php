<?php

if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
/*$content='根据图片内容和要求，我将给出以下10个关键词作为图片标签： "秋景", "河流", "黄叶", "红岩", "晴天", "自然", "生态", "植物", "地质", "户外" 这些标签概括了图片的主要元素：秋季的景色、河水、黄色树叶以及红色的岩石地貌。同时也包含了天气状况（晴天）、场景环境（自然景观，户外活动）和植被类型等方面的信息。 标签1: 秋景 标签2: 河流 标签3: 黄叶';
echo $content;
$content=strip_tags($content);
$content = str_replace('、',',',$content);
$content = str_replace('，',',',$content);
$content = str_replace("\n",',',$content);
$content = str_replace("：",':',$content);
$content = preg_replace('/标签\d+:/', ',', $content);
$content = str_replace('标签:', ',', $content);
$tags = explode(',',$content);
$tags=array_unique($tags);
print_R($tags);
$tids = [];
foreach ($tags as $v) {

    $v = trim($v);
    $v = str_replace(['[',']',',','，','.','。','"',"\n"],'',$v);
    $v = trim($v);
    $v = preg_replace("/^\d+\s+/",'',$v);
    $v = preg_replace("/^\d+/",'',$v);
    $v = trim($v);
    if ($v) {
        if(mb_strlen($v)>6) continue;
        echo "[".$v."]";
    }
}

exit('ddd');*/

Hook::listen('adminlogin');
$appname=lang('appname');
$navtitle=lang('setting');

$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if($do == 'addPrompt'){
    $cate = isset($_GET['cate']) ? intval($_GET['cate']) : 0;
    $name = isset($_GET['name']) ? trim($_GET['name']) : '';
    $prompts=$_GET['prompts'];
    $prompt=is_array($prompts)?$prompts[0]:array('model'=>'','prompt'=>'');
    if(!$name || empty($prompt['model']) || empty($prompt['prompt'])){
        exit(json_encode(array('success'=>false,'error'=>lang('please_input_all_info'))));
    }else{
        $setarr = array(
            'name'=>$name,
            'prompts'=>$prompts,
            'cate'=>$cate,
            'disp'=>DB::result_first("select max(disp) from %t where cate = %d",['ollama_imageprompt',$cate])+1,
            'isdefault'=>0,
            'status'=>isset($_GET['status']) ? intval($_GET['status']):0
        );

        $id = C::t('ollama_imageprompt')->insertData($setarr);
        if($id){
            exit(json_encode(array('success'=>true)));
        }else{
            exit(json_encode(array('success'=>false,'error'=>lang('add_unsuccess'))));
        }
    }
}elseif($do == 'editPrompt'){
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $name = isset($_GET['name']) ? trim($_GET['name']) : '';

    $prompts=$_GET['prompts'];
    $prompt=is_array($prompts)?$prompts[0]:array('model'=>'','prompt'=>'');
    if(!$name || empty($prompt['model']) || empty($prompt['prompt'])){
        exit(json_encode(array('success'=>false,'error'=>lang('please_input_all_info'))));
    }else{
        $setarr = array(
            'name'=>$name,
            'prompts'=>$prompts,
        );

        $ret = C::t('ollama_imageprompt')->editById($id,$setarr);
        if(isset($ret['error'])){
            exit(json_encode(array('success'=>false,'error'=>$ret['error'])));
        }else{
            exit(json_encode(array('success'=>true)));
        }
    }

}elseif($do == 'delPrompt'){
    if(C::t('ollama_imageprompt')->deleteById($_GET['id'])){
        exit(json_encode(array('success'=>true)));
    }else{
        exit(json_encode(array('success'=>false,'error'=>lang('del_unsuccess'))));
    }
}elseif($do == 'sortPrompt'){
    $ids = isset($_GET['ids']) ? $_GET['ids'] : '';
    $ids = explode(',',$ids);
    C::t('ollama_imageprompt')->sortByIds($ids);
    exit(json_encode(array('success'=>true)));
}elseif($do == 'setStatus'){
    $status = intval($_GET['status']);
    C::t('ollama_imageprompt')->setStatusById($_GET['id'],$status);
    exit(json_encode(array('success'=>true)));
}elseif($do == 'getPromptByCate'){
    $cate = isset($_GET['cate']) ? intval($_GET['cate']) : 0;
    $data = C::t('ollama_imageprompt')->fetchPromptByCate($cate);
    exit(json_encode(array('success'=>true,'data'=>$data)));
}else{
    include libfile('function/cache');
    if (submitcheck('settingsumbit')) {
        $status = isset($_GET['status']) ? intval($_GET['status']) : 0;

        $apiurl = trim($_GET['apiurl']);

        if($status && empty($apiurl)){
            showmessage(lang('config_not_complete'), dreferer(), array(), array('alert' => 'error'));
        }
        $arr=array(
            'apikey'=>trim($_GET['appid']),
            'apiurl'=>trim($_GET['apiurl']),
            'chatModel'=>trim($_GET['chatModel']),
            'status'=>intval($_GET['status'])
        );
        C::t('setting')->update('setting_ollama',$arr);
        updatecache('setting');
        exit(json_encode(array('success'=>true)));
    }else{
        $setting=C::t('setting')->fetch('setting_ollama',true);
        if(!$setting['status']) $setting['status'] = 0;

        if(empty($setting['apiurl'])) $setting['apiurl']='http://localhost:11434/';
        //获取当前的模型
        $ollama=new \ollamaApi();
        $models=array();
        if($ret=$ollama->modelList()){
            $models=array_keys($ret);
        }
        include template('setting');
        exit();
    }
}
