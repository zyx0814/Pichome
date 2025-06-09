<?php
if(!defined('IN_OAOOA') ) {
    exit('Access Denied');
}
Hook::listen('adminlogin');
$do = isset($_GET['do']) ? trim($_GET['do']):'';
if($do == 'add'){//请求api
    $id = isset($_GET['id']) ? intval($_GET['id']):0;

    if(submitcheck('adddata')){
        $name = isset($_GET['name']) ? getstr($_GET['name']):'';
        $content = isset($_GET['content']) ? $_GET['content']:'';
        if(!$name || !$content) exit(json_encode(['success'=>false,'error'=>'参数错误']));
        $carr = replaceInputAndTextarea($content);
        $setarr = [
            'name'=>$name,
            'content'=>$content,
            'desc'=>$_GET['desc'] ? getstr($_GET['desc']):'',
            'pcontent'=>$carr['pcontent'],
            'formdata'=>serialize($carr['formdata']),
            'dateline'=>TIMESTAMP
        ];

        if($id){
            if(C::t('ai_model')->update($id,$setarr)){
                exit(json_encode(array('success'=>true,'msg'=>MOD_URL.'&op=admin')));
            }else{
                exit(json_encode(array('error'=>true)));
            }
        }else{
            if(C::t('ai_model')->insert($setarr,1)){
                exit(json_encode(array('success'=>true,'msg'=>MOD_URL.'&op=admin')));
            }else{
                exit(json_encode(array('error'=>true)));
            }
        }

    }else{
        if($id)$data = C::t('ai_model')->fetch ($id);
        include template('add');
        exit();
    }


}elseif($do == 'delete'){
    $id = isset($_GET['id']) ? intval($_GET['id']):'';
    C::t('ai_model')->delete($id);
    showmessage('成功',MOD_URL.'&op=admin');
}elseif($do == 'updatepcontent'){
    foreach(DB::fetch_all("select id,content,pcontent from %t where 1 order by dateline desc",array('ai_model')) as $v){
        echo $v['content'];
        $arr = replaceInputAndTextarea($v['content']);
        $srtarr = [
            'pcontent'=>$arr['pcontent'],
            'formdata'=>serialize($arr['formdata'])
        ];
        C::t('ai_model')->update($v['id'],$srtarr);
    }
    exit('成功');
}else{
    $datas = [];
    foreach(DB::fetch_all("select name,id,`desc`,content from %t where 1 order by dateline desc",array('ai_model')) as $v){
        $datas[] = $v;
    }
    $datas = json_encode($datas);
    include template('list');
}
function replaceInputAndTextarea($content) {
    $inputCount = 1;
    $formarr = [];
    $content = preg_replace_callback('/\{input:([^}]+)\}/', function($matches) use (&$inputCount,&$formarr) {
        $key = 'input' . $inputCount++;
        if($matches[1]){
            $tmparr = explode('|',$matches[1]);
            $formarr[$key]['type'] = 'input';
            $formarr[$key]['name'] = $key;
            if($tmparr[0]) $formarr[$key]['lable'] = $tmparr[0];
            if($tmparr[1]) $formarr[$key]['default'] = $tmparr[1];
        }
        return '{'.$key. '}';
    }, $content);

    $textareaCount = 1;
    $content = preg_replace_callback('/\{textarea:([^}]+)\}/', function($matches) use (&$textareaCount,&$formarr) {
        $key = 'textarea' . $textareaCount++;
        if($matches[1]){
            $tmparr = explode('|',$matches[1]);
            $formarr[$key]['type'] = 'textarea';
            $formarr[$key]['name'] = $key;
            if($tmparr[0]) $formarr[$key]['lable'] = $tmparr[0];
            if($tmparr[1]) $formarr[$key]['default'] = $tmparr[1];
        }
        return '{'.$key. '}';
    }, $content);
    return ['pcontent'=>$content,'formdata'=>$formarr];
}