<?php

if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
include_once DZZ_ROOT . 'dzz' . BS . 'lang' . BS . 'class' . BS . 'idtypeMap.php';
global $_G,$idTypeMap;
$do = isset($_GET['do']) ? trim($_GET['do']) : 'getLangkeyList';
//获取语言键值列表
if ($do == 'getLangkeyList') {
    $langkey = isset($_GET['langkey']) ? trim($_GET['langkey']) : '';
    if ($langkey) {
        $langkeyarr = Pdecode($langkey, '');
        if (!isset($langkeyarr['uid']) || !isset($langkeyarr['key']) || $langkeyarr['uid'] != $_G['uid']) {
            exit(json_encode(['success' => false, 'msg' => 'Params Error']));
        } else {
            //语言列表
            $langlist = $_G['language_list'];
            $renturnlangkey = ['formhash'=>FORMHASH];
            $keyarr = explode('|', $langkeyarr['key']);
            $idmapdata = $idTypeMap[$keyarr[0]];
            $renturnlangkey['pagestyle'] = $idmapdata['pagestyle'];
            $idtype = $idmapdata['idtype'];
            $plangkey = $keyarr[1];
            list($defaultfiled, $defautid) = explode(':', $plangkey);
            $fileddata  = $idTypeMap[$keyarr[0]]['Key'][$defaultfiled];
            $iddata = explode(',', $defautid);
            $idataismore = false;
            if (count($iddata) > 1) $idataismore = true;
            switch ($idtype) {
                case 0:
                case 1:
                case 2:
                case 4:
                case 6:
                case 7:
                case 8:
                case 9:
                case 10:
                case 11:
                case 12:
                case 13:
                case 14:
                case 16:
                case 17:
                case 18:
                case 19:
                case 20:
                    $table = $fileddata['table'];
                    $idval = $fileddata['id'];
                    if ($idataismore) {
                        $defaultval = '';
                        $i = 0;
                        foreach (DB::fetch_all("select `$defaultfiled` from %t where $idval in (%n)", [$table, $iddata]) as $v) {
                            if ($i == 0) {
                                $defaultval = $v[$defaultfiled];
                            } else {
                                $defaultval = ($defaultval == $v[$defaultfiled]) ? $v[$defaultfiled] : '';
                            }
                        }

                    } else {
                        $defaultval = DB::result_first("select `$defaultfiled` from %t where $idval = %s ", [$table, $defautid]);
                    }
                    break;
                case 21:
                    //字段名称
                    $filedname = $defautid;
                    $table = $fileddata['table'];
                    $optionsdata = [];
                    //查询所有选项
                    foreach (DB::fetch_all("select id,filedval from %t where filed  = %s ", [$table, $filedname]) as $v) {
                       $optionsdata[] = ['id'=> $v['id'],'name'=>$v['filedval']];
                    }
                    break;
                case 3:
                    $dataKey = $langkeyarr['key'];
                    $idarr = explode('-', $dataKey);
                    $iddata = explode(',', $idarr[1]);
                    $skeyarr = explode(':', $idarr[0]);
                    $filed= C::t('form_setting')->fetch($skeyarr[1]);
                    //获取默认值
                    $tid = $iddata[0];
                    $defaultval = C::t('#tab#tab_attr')->fetch_by_skey($skeyarr[1],$tid);
                    if($filed['type'] != 'multiselect' && $filed['type'] != 'select'){
                        $defaultval = $defaultval ?? '';
                    }
                    break;
                case 5:
                    $table = $fileddata['table'];
                    $idval = $fileddata['id'];
                    $defaultval = DB::result_first("select `options` from %t where $idval = %s ", [$table, $defautid]);
                    $defaultval =$defaultval ? unserialize($defaultval):[];
                    break;

            }
            $renturnlangkey['defaultval'] = $defaultval ?? '';
            foreach ($langlist as $k => $v) {
                $dataValue = '';
                $table_sf = str_replace('-', '_', $k);

                $key = Pencode(['key' => $langkeyarr['key'] . '|' . $table_sf, 'uid' => $langkeyarr['uid']], 86400);
                $dataKey = $langkeyarr['key'];

                if ($idtype == 3) {
                    $idarr = explode('-', $dataKey);
                    $iddata = explode(',', $idarr[1]);
                    $skeyarr = explode(':', $idarr[0]);
                    $filed= C::t('form_setting')->fetch($skeyarr[1]);

                    if (count($iddata) > 1) {
                        $iddata = array_unique($iddata);
                        $selkeyarr = [];
                        foreach ($iddata as $id) {
                            $selkeyarr[] = $idarr[0] . '-' . $id;
                        }
                        if($filed['type'] == 'multiselect'  || $filed['type'] == 'select' || $filed['type'] == 'timerange'){
                            $i = 0;
                            foreach (C::t('lang')->fetchBySkeysIdtypes($table_sf, $idtype, $selkeyarr) as $value) {
                                if ($i == 0) {
                                    $dataValue = $value['svalue'] ? explode(',',$value['svalue']):[];
                                } else {
                                    $cdataValue = $value['svalue'] ? explode(',',$value['svalue']):[];
                                    $dataValue = array_intersect($dataValue,$cdataValue);
                                }
                                $i++;
                            }

                            sort($dataValue);
                        }
                        else{
                            $i = 0;
                            foreach (C::t('lang')->fetchBySkeysIdtypes($table_sf, $idtype, $selkeyarr) as $value) {
                                if ($i == 0) {
                                    $dataValue = $value['svalue'] ?? '';
                                } else {
                                    $cdataValue = $value['svalue'] ?? '';
                                    $dataValue = ($cdataValue == $dataValue) ? $cdataValue : '';
                                }
                                $i++;
                            }
                        }
                    } else {
                        $tid = $iddata[0];

                        $dataValue = C::t('lang')->fetchSvalueBySkey($table_sf, $dataKey);
                        $dataValue = $dataValue ?? $defaultval;
                    }
                    if($filed['type'] == 'fulltext'){
                        $dataValue = parserichtextdata($dataValue);
                    }
                    if($filed['type'] != 'multiselect' && $filed['type'] != 'select'){
                        $dataValue = $dataValue ?? $defaultval;
                    }
                    $tmpreturn =  ['key' => $key, 'value' => $dataValue,'lang'=>$k,
                        'lablename' => $v['langval'],'inputtype'=>$filed['type']];
                    if($filed['type'] == 'multiselect' || $filed['type'] == 'select'){
                        $optionskey = 'tabfiledoptions|options:'.$skeyarr[1];
                        $optionsData = C::t('lang')->fetchSvalueBySkey($table_sf,$optionskey);
                        $tmpreturn['options'] = unserialize($optionsData);

                    }
                    $renturnlangkey['langkey'][] =$tmpreturn;
                }
                elseif($idtype == 5){
                    $dataValue = C::t('lang')->fetchSvalueBySkey($table_sf, $dataKey);
                    $dataValue = $dataValue ? unserialize($dataValue):$defaultval;
                }
                elseif($idtype == 15){
                    $dataarr = explode(":",$dataKey);

                    $tdataid = $dataarr[1];
                    $tdata = C::t('pichome_templatetagdata')->fetch($tdataid);
                    $tagid = $tdata['tid'];
                    $tagdata = C::t('pichome_templatetag')->fetch($tagid);
                    //$tag
                    $dataValue = C::t('lang')->fetchSvalueBySkey($table_sf, $dataKey);
                    //if(!$dataValue) $dataValue = (preg_match('/^alonepagedata\|tdata\:\d+/',$dataKey)) ? $tdata['tdata']:$tdata['tdataname'];
                    if(preg_match('/^alonepagedata\|tdata\:\d+/',$dataKey)){
                        $dataValue = $dataValue ?? $tdata['tdata'];
                       if($svalue = unserialize($dataValue)){
                           if($tagdata['tagtype'] != 'link'){
                               foreach($svalue as $sk=>$sval){
                                   $svalue[$sk]['answer'] = parserichtextdata($svalue[$sk]['answer']);
                               }

                           }else{
                               $renturnlangkey['pagestyle'] = 'link';
                           }
                           $dataValue = $svalue;

                       }else{

                           $dataValue = parserichtextdata($dataValue);
                       }
                        $renturnlangkey['langkey'][] = ['key' => $key, 'value' => $dataValue,'lang'=>$k,
                            'lablename' => $v['langval'],'inputtype'=>$tagdata['tagtype']];
                    }else{
                        $dataValue = $dataValue ?? $tdata['tdataname'];
                       $renturnlangkey['langkey'][] = ['key' => $key, 'value' => $dataValue,'lang'=>$k,
                           'lablename' => $v['langval'],'inputtype'=>'input'];

                    }


                }
                elseif($idtype == 21){

                    foreach($optionsdata as $ck=>$cv){
                        $cdatakey = 'selectoption|filedval:'.$cv['id'];
                        $cdataValue = C::t('lang')->fetchSvalueBySkey($table_sf, $dataKey) ?? $cv['name'];
                        $optionsdata[$ck]['name'] = $cdataValue;
                    }
                    $dataValue = $optionsdata;
                }
                else {
                    $dataKeyarr = explode(':', $dataKey);
                    $iddata = explode(',', $dataKeyarr[1]);

                    if (count($iddata) > 1) {
                        $iddata = array_unique($iddata);
                        $selkeyarr = [];
                        foreach ($iddata as $id) {
                            $selkeyarr[] = $dataKeyarr[0] . ':' . $id;
                        }

                        $i = 0;
                        foreach (C::t('lang')->fetchBySkeysIdtypes($table_sf, $idtype, $selkeyarr) as $value) {
                            if ($i == 0) {
                                $dataValue = $value['svalue'];
                            } else {
                                $dataValue = ($dataValue == $value['svalue']) ? $value['svalue'] : '';
                            }
                            $i++;
                        }
                        $dataValue = $dataValue ?? $defaultval;
                    } else {
                        $dataValue = C::t('lang')->fetchSvalueBySkey($table_sf, $dataKey);
                        $dataValue = $dataValue ?? $defaultval;
                    }
                }
                if($idtype == 21){
                    $renturnlangkey['langkey'][] = ['key' => $key, 'options' => $dataValue,
                        'lablename' =>  $v['langval'],'lang'=>$k,'inputtype'=>$fileddata['inputtype']];
                } elseif(!in_array($idtype,[3,15]) && $idmapdata['lablename'] != 'langparse'){
                    $renturnlangkey['langkey'][] = ['key' => $key, 'value' => $dataValue,
                        'lablename' =>  $v['langval'],'lang'=>$k,'inputtype'=>$fileddata['inputtype']];
                }
                $savekey = '';
            }
            exit(json_encode(['success' => true, 'langkey' => $renturnlangkey,'lang'=>$_G['language']]));
        }
    } else {
        exit(json_encode(['success' => false, 'msg' => 'Params Error']));
    }
}
elseif ($do == 'saveData') {//保存语言值
    $langdata = isset($_GET['langdata']) ? $_GET['langdata'] : [];

    if (submitcheck('langsave') && $langdata) {
        $langlist = $_G['language_list'];
        $langkeylist = [];
        foreach ($langlist as $key => $val) {
            $langkeylist[] = str_replace('-', '_', $key);
        }

        foreach ($langdata as $k => $v) {
            $langarr = Pdecode($k, '');
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                continue;
            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';

                $table_sf = $langkeyarr[2] ? str_replace('-', '_', $langkeyarr[2]) : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;

                if ($idtype == 3) {//如果是字段值
                    $idarr = explode('-', $dataKey);
                    $filedarr = explode(':',$idarr[0]);
                    $iddata = explode(',', $idarr[1]);
                    if (count($iddata) > 1) {
                        $iddata = array_unique($iddata);
                        foreach ($iddata as $id) {
                            $setarr = [
                                'skey' => $idtypestr.'|'.$idarr[0].'-'.$id,
                                'idtype' => $idtype,
                                'svalue' => $v,
                                'filed'=>$filedarr[1],
                                'idvalue'=>$id
                            ];
                            if ($table_sf && in_array($table_sf, $langkeylist)) {
                                C::t('lang')->insertData($table_sf, $setarr);
                            }
                        }
                    } else {
                        $setarr = [
                            'skey' =>$idtypestr.'|'.$dataKey,
                            'idtype' => $idtype,
                            'svalue' => $v,
                            'filed'=>$filedarr[1],
                            'idvalue' => $iddata[0]
                        ];
                        $forms = [];
                        if($setarr['idtype'] == 3){
                            $skey = str_replace( ['tabfiledval|tabfiledval:','-'.$setarr['idvalue']],'',$setarr['skey']);
                            $odata = DB::fetch_first("select * from %t where skey = %s", [strtolower('lang_' . $table_sf), $setarr['skey']]);
                            $forms = C::t('form_setting')->fetch($skey);
                            if($forms && $forms['type'] == 'fulltext'){
                                $setarr['svalue'] = getcontentdata($setarr['svalue'],$odata['svalue']);
                            }

                        }
                        if ($table_sf && in_array($table_sf, $langkeylist)) {
                            C::t('lang')->insertData($table_sf, $setarr);
                        }
                    }
                }
                else{
                    $dataKeyarr = explode(':', $dataKey);
                    $iddata = explode(',', $dataKeyarr[1]);

                    if (count($iddata) > 1) {
                        $iddata = array_unique($iddata);

                        foreach ($iddata as $id) {
                            $setarr = [
                                'skey' => $idtypestr.'|'.$idarr[0].'-'.$id,
                                'idtype' => $idtype,
                                'svalue' => $v,
                                'idvalue' => $id,
                                'filed'=>$dataKeyarr[0]
                            ];
                            if ($table_sf && in_array($table_sf, $langkeylist)) {
                                C::t('lang')->insertData($table_sf, $setarr);
                            }
                        }
                    } else {
                        $setarr = [
                            'skey' => $idtypestr.'|'.$dataKey,
                            'idtype' => $idtype,
                            'svalue' => is_array($v) ? serialize($v):$v,
                            'idvalue' => $iddata[0],
                            'filed'=>$dataKeyarr[0]
                        ];

                        $dataarr = explode(":",$dataKey);
                        $tdataid = $dataarr[1];
                        $tdata = C::t('pichome_templatetagdata')->fetch($tdataid);
                        $tagid = $tdata['tid'];
                        $tagdata = C::t('pichome_templatetag')->fetch($tagid);
                        if($setarr['idtype'] == 15 && preg_match('/alonepagedata|tdata:\d+/',$setarr['skey'])){
                            $odata = DB::fetch_first("select * from %t where skey = %s", [strtolower('lang_' . $table_sf), $setarr['skey']]);
                            if($tagdata['tagtype'] == 'link'){
                                $data = unserialize($setarr['svalue']);
                                $olddata = unserialize($odata['svalue']);
                                $setarr['svalue'] = serialize(parseaidarrdata($data,$olddata));
                            }elseif($ovalue = unserialize($odata['svalue'])){
                                $v['svalue'][0] = getcontentdata($v['svalue'][0]['answer'],$ovalue[0]['answer']);
                            }else{
                                $v['svalue'] = getcontentdata($v['svalue'],$ovalue);
                            }
                            $setarr['svalue'] = is_array($v) ? serialize($v):$v;
                        }

                        if ($table_sf && in_array($table_sf, $langkeylist)) {
                            C::t('lang')->insertData($table_sf, $setarr);
                        }
                    }

                }

            }
        }
        exit(json_encode(['success' => true]));
    }
}
function parseaidarrdata($data,$olddata){

    $naids =  [];
    foreach($data as $v){
        $naids[] = $v['aid'];
    }
    if($olddata){
        $oaids = [];
        foreach($olddata as $idata){
            $oaids[] = $idata['aid'];
        }
        $delaids = array_diff($oaids,$naids);
        foreach($delaids as $v){
            C::t('attachment')->delete_by_aid($v['aid']);
        }
        $naids = array_diff($naids,$oaids);
    }
    C::t('attachment')->addcopy_by_aid($naids);
    return $data;
}
function parserichtextdata($data){
    $pattern = "/(https?:\/\/)?\w+\.\w+\.\w+\.\w+?(:[0-9]+)?\/index\.php\?mod=io&amp;op=getfileStream&amp;path=(.+)/";
    $data= preg_replace_callback($pattern,function($matchs){

        return 'index.php?mod=io&op=getfileStream&path='.$matchs[3];

    },$data);

    $data= preg_replace_callback('/path=(\w+)&amp;aflag=(attach::\d+)/',function($matchs){
        if(isset($matchs[2])){
            return 'path='.dzzencode($matchs[2]);
        }

    },$data);

    return $data;
}

