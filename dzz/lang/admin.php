<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
Hook::listen('adminlogin');
global $_G;
$LanguageList = require(DZZ_ROOT . 'dzz' . BS . 'lang' . BS . 'class' . BS . 'languageList.php');

$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
if ($operation == 'list') {//获取语言列表
    $langlist = [];
    foreach (DB::fetch_all("select * from %t where 1", ['language']) as $v) {
        //$v['icon'] = 'dzz/lang/images/w80/' . $v['langflag'] . '.png';
        $langlist[] = $LanguageList[$v['langflag']];
    }
    exit(json_encode(['data' => $langlist]));
}  elseif ($operation == 'addLanguage') {//添加语言
    $langflag = isset($_GET['langflag']) ? trim($_GET['langflag']) : '';
    if ($langflag && isset($LanguageList[$langflag])) {
        $setarr = [
            'langflag' => $langflag,
            'state' => 0,
        ];
        $langreturn = C::t('language')->insertData($setarr);
        if (isset($langreturn['error'])) {
            exit(json_encode(['success' => false, 'msg' => $langreturn['error']]));
        } else {
            exit(json_encode(['success' => true, 'icon'=>'dzz/lang/images/w80/'.$langflag.'.png']));
        }
    } else {
        exit(json_encode(['success' => false, 'error' => lang('langflag_error')]));
    }
} elseif ($operation == 'enableLanguage') {//启用语言
    $langflag = isset($_GET['langflag']) ? trim($_GET['langflag']) : '';
    if ($langflag && isset($LanguageList[$langflag])) {
        $setarr = [
            'state' => 0,
        ];
        $langreturn = C::t('language')->enableLanguage($langflag, $setarr);
        $ret=updateLanguageList();
        if ($langreturn) {
            exit(json_encode(['success' => true,'refresh'=>$ret]));
        } else {
            exit(json_encode(['success' => false, 'msg' => lang('lang_enable_fail')]));
        }
    } else {
        exit(json_encode(['success' => false, 'error' => lang('langflag_error')]));
    }
} elseif ($operation == 'unEnableLanguage') {//禁用语言
    $langflag = isset($_GET['langflag']) ? trim($_GET['langflag']) : '';
    if ($langflag && isset($LanguageList[$langflag])) {
        $setarr = [
            'state' => 0,
        ];
        if(DB::result_first("select count(*) from %t where state = %d ",['language',1]) < 2){
            exit(json_encode(['success' => false, 'msg' => lang('not_allow_disable_language')]));
        }
        $langreturn = C::t('language')->update($langflag, $setarr);
        $ret=updateLanguageList();
        if ($langreturn) {
            exit(json_encode(['success' => true,'refresh'=>$ret]));
        } else {
            exit(json_encode(['success' => false, 'msg' => lang('lang_enable_fail')]));
        }
    } else {
        exit(json_encode(['success' => false, 'error' => lang('langflag_error')]));
    }
} elseif ($operation == 'setDefaultLanguage') {//设置默认语言
    $langflag = isset($_GET['langflag']) ? trim($_GET['langflag']) : '';
    if ($langflag && isset($LanguageList[$langflag])) {

        $langreturn = C::t('language')->fetch($langflag);
        if ($langreturn['state']) {
            $setarr = [
                'isdefault' => 1,
            ];
            DB::update('language', ['isdefault' => 0], 'isdefault=1');
            $langreturn = C::t('language')->update($langflag, $setarr);
            if ($langreturn) {
                include_once libfile('function/cache');
                C::t('setting')->update('defaultlang', $langflag);
                updatecache('setting');
                exit(json_encode(['success' => true]));
            } else {
                exit(json_encode(['success' => false, 'msg' => lang('lang_set_default_fail')]));
            }
        } else {
            exit(json_encode(['success' => false, 'msg' => lang('lang_enable_fail')]));
        }
    } else {
        exit(json_encode(['success' => false, 'error' => lang('langflag_error')]));
    }
} else {
    $langlist = [];
    foreach (DB::fetch_all("select * from %t where 1", ['language']) as $v) {
        $v= ['langflag'=>$v['langflag'],'isdefault'=>$v['isdefault'],'state'=>$v['state']];
        $v = array_merge($v, $LanguageList[$v['langflag']]);
        $langlist[] = $v;
    }
    $LanguageList = json_encode($LanguageList);
    $langlist = json_encode($langlist);
    include template('locale');
}

function updateLanguageList()
{
    global $LanguageList,$_G;
    include_once libfile('function/cache');
    $langlist = [];
    $defaultlang='';
    foreach (DB::fetch_all("select * from %t where `state` = %d ", ['language', 1]) as $v) {

        if($v['isdefault']) $defaultlang = $v['langflag'];
        $v= ['langflag'=>$v['langflag'],'isdefault'=>$v['isdefault']];
        $v = array_merge($v, $LanguageList[$v['langflag']]);
        if($v['elementflag']){
            $v['elementflagCamel'] = 'ElementPlusLocale'.toCamelCase($v['elementflag'],true);
        }
        $langlist[$v['langflag']] =$v;

    }
    $moreLanguageState = (count($langlist) >1 ) ? 1 : 0;
    C::t('setting')->update('moreLanguageState', $moreLanguageState);
    C::t('setting')->update('language_list', $langlist);
    C::t('setting')->update('defaultlang', $defaultlang);
    updatecache('setting');
    if(!$moreLanguageState  && $_G['language']!= $defaultlang){
        dsetcookie('language', $defaultlang, 60 * 60 * 24 * 30);
        return 'needrefresh';
    }
    return '';

}