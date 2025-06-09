<?php

namespace dzz\lang\classes;

use \core as C;


require_once DZZ_ROOT . 'dzz' . BS . 'lang' . BS . 'class' . BS . 'idtypeMap.php';

class lang
{

    public static $filedvaltypes = ['select', 'input', 'textarea', 'multiselect', 'link', 'fulltext'];
    public static $alonepagetypes = ['question', 'rich_text','link'];

    public static $hasoptionstypes = ['select', 'multiselect'];

    /*检查语言包是否开启*/
    public static function checklang(&$lang)
    {
        global $_G;
        if($_G['moreLanguageState']){
            $lang = $_G['language'];
            $lang = str_replace('-', '_', $lang);
            $lang = strtolower($lang);
        }else{
            $lang = '';
        }

    }

    /**
     * 根据传入的方法名动态调用类方法，用于处理语言解析。
     *
     * 此函数旨在提供一种灵活的方式来解析语言数据。它通过传入一个方法名和额外的参数，
     * 来动态调用类中的相应方法。这种方法特别适用于需要根据不同的条件或配置来解析语言数据的场景。
     *
     * @param array &$data 语言数据数组，函数将直接在这个数组上进行操作。
     * @param array $params 一个包含额外参数的数组。其中 'method' 参数指定要调用的方法名，
     *                      'ismore' 参数用于某些方法来指示多个数据需要处理。
     *
     * @return mixed 如果找到指定的方法则返回方法的执行结果，否则返回 false。
     */
    public static function langParse(&$data, $params = array())
    {
        //判断多语言是否开启，如果未开启则不执行
        $lang = '';
        self::checklang($lang);
        if(!$lang) return false;
        // 提取方法名 from the $params array, 然后移除它，以便不影响后续的处理。
        $method = $params[0];
        unset($params[0]);
        if($method == 'getLangData'){
            $extra = array_values($params);
            return  self::getLangData($data, $extra);
        } elseif (method_exists(__CLASS__, $method)) {// 检查当前类中是否存在指定的方法。
            // 如果 'ismore' 参数存在，将其转换为整型，否则设为 0。
            $extpara = $params[1] ?? 0;

            // 这里是动态调用类方法的示例，它使得函数能够根据不同的方法名执行不同的逻辑。
            return self::$method($data, $extpara);
        } else {
            // 如果指定的方法不存在，返回 false。
            // 这可以帮助调用者识别是否发生了错误或是否需要采取其他行动。
            return false;
        }
    }

    public static function getSearchtemplateLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['searchtemplate', $ismore]);
    }

    public static function getSearchtemplateLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['searchtemplate', $ismore,$lang]);
    }

    public static function delSearchtemplateLangData($clid)
    {
        if (!is_array($clid)) $clid = array($clid);
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['searchtemplate']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $clid);
        }
    }

    public static function setSearchtemplateLangData(&$data)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        self::getLangKey($data, ['searchtemplate',0,1]);

        foreach ($data['langkey'] as $k => $v) {
            $langarr = Pdecode($v, '');
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {

            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['id'],
                        'filed' => $k
                    ];
                    C::t('#lang#lang')->insertData($table_sf, $setarr);
                    /* if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                         unset($data[$k]);
                     }*/
                }

            }
        }
        unset($data['langkey']);
    }

    public static function getCollectcatLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['collectcat', $ismore]);
    }

    public static function getCollectcatLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['collectcat', $ismore,$lang]);
    }

    public static function delCollectcatLangData($clid)
    {
        if (!is_array($clid)) $clid = array($clid);
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['collectcat']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $clid);
        }
    }
    /*以标签tid为键值的数组*/
    public static function getTagsLangData(&$datas,$lang=''){
       foreach($datas as $k=>&$v){
           self::getLangKey($v, ['tag', 1]);
           self::getLangData($v, ['tag', 1,$lang]);
       }
    }
    public static function getTagLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['tag', $ismore,$lang]);
    }

    public static function getTagLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['tag', $ismore]);
    }

    public static function setTagLangData(&$data,$lang='')
    {
        global $_G, $idTypeMap;
        $lang = $lang ? trim($lang):$_G['language'];
        $table_sf = str_replace('-', '_', $lang);

        //获取tag数据
        //$tag = C::t('tag')->fetch($data['tid']);
        self::getLangKey($data, ['tag',0,1]);

        foreach ($data['langkey'] as $k => $v) {
            $langarr = Pdecode($v, '');
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {

            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['tid'],
                        'filed' => $k
                    ];
                    if(C::t('#lang#lang')->insertData($table_sf, $setarr)){
                        if($lang == 'zh-CN'){
                            $getInitial = C::t('pichome_tag')->getInitial($data[$k]);
                            $tagarr = ['initial'=>$getInitial];
                            C::t('pichome_tag')->update($data['tid'],$tagarr);
                        }
                        if($lang == $_G['defaultlang']){
                            C::t('pichome_tag')->update($data['tid'],['tagname'=>$data[$k]]);
                        }
                    }
                    /*if ($tag['lang'] != $lang) {
                        unset($data[$k]);
                    }*/
                    /* if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                         unset($data[$k]);
                     }*/
                }

            }

        }


        unset($data['langkey']);
    }

    public static function delTagLangData($tid)
    {
        if (!is_array($tid)) $tid = array($tid);
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['tag']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $tid);
        }
    }

    public static function setCollectcatLangData(&$data)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        self::getLangKey($data, ['collectcat',0,1]);

        foreach ($data['langkey'] as $k => $v) {
            $langarr = Pdecode($v, '');
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {

            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['cid'],
                        'filed' => $k
                    ];
                    C::t('#lang#lang')->insertData($table_sf, $setarr);
                    /* if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                         unset($data[$k]);
                     }*/
                }

            }
        }
        unset($data['langkey']);
    }
    public static function getfileCollectLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['fileCollect', $ismore]);
    }

    public static function getfileCollectLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['fileCollect', $ismore,$lang]);
    }
    public static function delfileCollectLangData($cid)
    {
        if (!is_array($cid)) $cid = array($cid);
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['fileCollect']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $cid);
        }
    }

    public static function setfileCollectLangData(&$data)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        self::getLangKey($data, ['fileCollect',0,1]);

        foreach ($data['langkey'] as $k => $v) {
            $langarr = Pdecode($v, '');
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {

            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['cid'],
                        'filed' => $k
                    ];
                    if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                       // unset($data[$k]);
                    }
                }

            }
        }
        unset($data['langkey']);
    }
    public static function getCollectLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['collect', $ismore]);
    }


    public static function getCollectLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['collect', $ismore,$lang]);
    }

    public static function delCollectLangData($clid)
    {
        if (!is_array($clid)) $clid = array($clid);
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['collect']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $clid);
        }
    }

    public static function setCollectLangData(&$data)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        self::getLangKey($data, ['collect',0,1]);

        foreach ($data['langkey'] as $k => $v) {
            $langarr = Pdecode($v, '');
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {

            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['clid'],
                        'filed' => $k
                    ];
                    if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                        unset($data[$k]);
                    }
                }

            }
        }
        unset($data['langkey']);
    }

    public static function getTabcatLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['tabcat', $ismore,$lang]);
    }

    public static function getTabcatLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['tabcat', $ismore]);
    }

    public static function saveTabcatLangData(&$data, $cids = array())
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        $datas = [];
        foreach ($cids as $v) {
            $tmparr = $data;
            $tmparr['cid'] = $v;
            $datas[] = $tmparr;
        }
        self::getLangKey($datas, ['tabcat', 1,1]);
        $delkeys = [];
        foreach ($datas as $langkeydata) {
            foreach ($langkeydata['langkey'] as $k => $v) {
                $langarr = Pdecode($v, '');
                if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                    continue;
                } else {
                    $langkeyarr = explode('|', $langarr['key']);
                    $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                    $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                    $dataKeyarr = explode(':', $dataKey);
                    $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                    if (isset($data[$k])) {
                        $setarr = [
                            'skey' => $langarr['key'],
                            'idtype' => $idtype,
                            'svalue' => $data[$k],
                            'idvalue' => $dataKeyarr[1],
                            'filed' => $dataKeyarr[0]
                        ];
                        if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                            $delkeys[] = $k;

                        }
                    }

                }
            }

        }
        $delkeys = array_unique($delkeys);
        foreach ($delkeys as $val) {
            unset($data[$val]);
        }
    }

    public function delTabbannerLangData($bid)
    {
        if (!is_array($bid)) $bid = array($bid);
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['tabbanner']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $bid);
        }
    }

    public function saveTabbannerLangData(&$data)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        self::getLangKey($data, ['tabbanner',0,1]);

        foreach ($data['langkey'] as $k => $v) {
            $langarr = Pdecode($v, '');
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {

            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['id'],
                        'filed' => $k
                    ];
                    if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                        unset($data[$k]);
                    }
                }

            }
        }
        unset($data['langkey']);


    }

    public function getTabbannerLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['tabbanner', $ismore]);
    }

    public static function getTabbannerLangData(&$data, $ismore = false,$lang='')
    {

        self::getLangData($data, ['tabbanner', $ismore,$lang]);
    }

    public static function getTabattrLangData(&$data, $tid)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        $idtype = $idTypeMap['tabfiledval']['idtype'];
        $langData = C::t('#lang#lang')->getDataByIdtypeIdval($table_sf, $idtype, $tid);
        $newdata = [];
        foreach ($langData as $k => $v) {
            $skey = str_replace(['tabfiledval|tabfiledval:', '-' . $tid], '', $k);
            if ($skey && $v) {
                $newdata[$skey] = $v;
            }
        }
        if (!$data) $data = [];
        $data = array_merge($data, $newdata);
    }

    public function saveTabnameLangData(&$data)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        self::getLangKey($data, ['tab',0,1]);
        foreach ($data['langkey'] as $k => $v) {
            $langarr = Pdecode($v, '');
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {

            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['tid'],
                        'filed' => $k
                    ];
                    C::t('#lang#lang')->insertData($table_sf, $setarr);
                }

            }
        }
        unset($data['langkey']);

    }

    public static function saveTabattrLangeData(&$data, $ismore = false)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        self::getLangKey($data, ['tabfiledval', $ismore,1]);
        $langarr = Pdecode($data['langkey'], '');
        if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {

        } else {
            $langkeyarr = explode('|', $langarr['key']);
            $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
            $dataKey = $langkeyarr ? $langkeyarr[1] : '';
            $filedvalarr = explode(':', $dataKey);
            $filedarr = explode('-', $filedvalarr[1]);
            $filed = $filedarr[0];
            $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
            $setarr = [
                'skey' => $langarr['key'],
                'idtype' => $idtype,
                'svalue' => $data['value'],
                'idvalue' => $data['tid'],
                'filed' => $filed,
            ];
            C::t('#lang#lang')->insertData($table_sf, $setarr);

        }
        unset($data['langkey']);
    }

    //获取filedval的语言包key
    public static function getFiledvalLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['tabfiledval', $ismore]);
        self::getLangKey($data, ['selectoptions', $ismore]);
    }

    //获取tab的语言包值
    public static function getTablangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['tab', $ismore,$lang]);
    }

    //获取tab的语言包key
    public static function getTabLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['tab', $ismore]);
    }

    //获取tabgroup的语言包值
    public static function getTabgrouplangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['tabgroup', $ismore,$lang]);
    }

    //获取tabgroup的语言包key
    public static function getTabgroupLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['tabgroup', $ismore]);
    }

    //获取tabgroup的语言包值
    public static function getFiledcatLangData(&$data, $ismore = false,$lang='')
    {

        self::getLangData($data, ['tabfiledcat', $ismore],$lang);
    }

    //获取tabgroup的语言包key
    public static function getFiledcatLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['tabfiledcat', $ismore]);
    }

    public static function getAlonpagetagdataLangKey(&$data, $type)
    {
        $data['type'] = $type;
        self::getLangKey($data, ['alonepagedata']);
        unset($data['type']);
    }

    public static function getAlonpagetagdataLangData(&$data, $type,$lang='')
    {
        $data['type'] = $type;
        self::getLangData($data, ['alonepagedata',$lang]);
    }

    public static function setAlonpagetagdataLangData(&$data, $type)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        $data['type'] = $type;
        self::getLangKey($data, ['alonepagedata',0,1]);
        foreach ($data['langkey'] as $k => $v) {
            $langarr = Pdecode($v);
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                continue;
            } else {
                $langkeyarr = explode('|', $langarr['key']);

                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;

                if ($k == 'tdata') {
                    $odata = C::t('#lang#lang')->fetchSvalueBySkey($table_sf, $langarr['key']);
                    if ($type == 'rich_text') {
                        if (!$odata) $odata['odata'] = '';
                        $data[$k] = getcontentdata($data[$k], $odata['odata']);
                    } elseif ($type == 'question') {
                        if ($odata) $odata = unserialize($odata['odata']);
                        $compardata = isset($odata['odata'][0]['answer']) ? $odata['odata'][0]['answer'] : '';
                        $data[$k][0]['answer'] = getcontentdata($data[$k][0]['answer'], $compardata);
                    }
                }

                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => is_array($data[$k]) ? serialize($data[$k]) : $data[$k],
                        'idvalue' => $data['id'],
                        'filed' => $k
                    ];
                    C::t('#lang#lang')->insertData($table_sf, $setarr);
                    //if () {
                    // unset($data[$k]);
                    // }
                }

            }
        }

        unset($data['type']);
        unset($data['langkey']);
    }

    public static function getAlonepagetagLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['alonepagetag', $ismore,$lang]);
    }

    public static function getAlonepagetagLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['alonepagetag', $ismore]);
    }

    public static function setAlonepagetagLangData(&$data, $tid)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        $data['tid'] = $tid;
        self::getLangKey($data, ['alonepagetag',0,1]);
        foreach ($data['langkey'] as $k => $v) {

            $langarr = Pdecode($v);
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                continue;
            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $tid,
                        'filed' => $k
                    ];
                    C::t('#lang#lang')->insertData($table_sf, $setarr);
                    /*if () {
                        unset($data[$k]);
                    }*/
                }

            }
        }
        unset($data['langkey']);
        unset($data['tid']);
    }

    public static function getAlonepageLangData(&$data, $ismore = false)
    {
        self::getLangData($data, ['alonepage', $ismore]);
    }

    public static function getAlonepageLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['alonepage', $ismore]);
    }

    public static function setAlonepageLangData(&$data)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        self::getLangKey($data, ['alonepage',0,1]);

        foreach ($data['langkey'] as $k => $v) {

            $langarr = Pdecode($v);
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                continue;
            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['id'],
                        'filed' => $k
                    ];

                    C::t('#lang#lang')->insertData($table_sf, $setarr);
                    // unset($data[$k]);

                }

            }
        }
        unset($data['langkey']);
    }

    public static function getSelectOptionLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['selectoption', $ismore,$lang]);
    }
    public static function getSelectOptionsLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['selectoptions', $ismore,$lang]);
    }
    public static function getSelectOptionLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['selectoption', $ismore]);
    }
    public static function getSelectOptionsLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['selectoptions', $ismore]);
    }
    public static function getSelectLangKey(&$data, $ismore = false){
        global $_G, $idTypeMap;
        $idtypestr = 'selectoptions';
        foreach ($idTypeMap[$idtypestr]['Key'] as $v) {
            if (!$data[$v['inputtype']] || !in_array($data[$v['inputtype']], ['inputselect','inputmultiselect'])) continue;
            if (is_array($data['flag'])) {
                $idstr = implode(',', $data['flag']);
            } else {
                $idstr = $data['flag'];
            }
            $data['langkey']['options'] = Pencode(['key' => $idtypestr . '|' . $idtypestr . ':' . $idstr, 'uid' => $_G['uid']], 86400);
        }
    }
    public static function setSelectOptionLangData(&$data,$lang='')
    {
        global $_G, $idTypeMap;
        $lang = $lang ? trim($lang):$_G['language'];
        $table_sf = str_replace('-', '_', $lang);

        //获取tag数据
        self::getLangKey($data, ['selectoption',0,1]);
        foreach ($data['langkey'] as $k => $v) {
            $langarr = Pdecode($v, '');

            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {

            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['id'],
                        'filed' => $k
                    ];

                    if(C::t('#lang#lang')->insertData($table_sf, $setarr)){
                        if($lang == 'zh-CN'){
                            $getInitial = C::t('form_filedvals')->getInitial($data[$k]);
                            $tagarr = ['initial'=>$getInitial];
                            C::t('form_filedvals')->update($data['id'],$tagarr);
                        }
                        if($lang == $_G['defaultlang']){
                            C::t('form_filedvals')->update($data['id'],['filedval'=>$data[$k]]);
                        }
                        \Hook::listen('updatefiledvalafter',$data['id']);
                    }
                }

            }

        }
        $data = [];
    }
     public function delSelectOptionLangData($id)
    {
        if (!is_array($id)) $id = array($id);
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['selectoption']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $id);
        }
    }
    public static function getBannerLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['banner', $ismore,$lang]);
    }

    public static function getBannerLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['banner', $ismore]);
    }

    public static function setBannerLangData(&$data)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        self::getLangKey($data, ['banner',0,1]);
        foreach ($data['langkey'] as $k => $v) {

            $langarr = Pdecode($v);
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                continue;
            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['id'],
                        'filed' => $k
                    ];
                    C::t('#lang#lang')->insertData($table_sf, $setarr);
                    /*if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                        unset($data[$k]);
                    }*/
                }

            }
        }
        unset($data['langkey']);
    }

    public static function getVappLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['vapp', $ismore,$lang]);
    }

    public static function getVappLangKey(&$data, $ismore = false)
    {

        self::getLangKey($data, ['vapp', $ismore]);
    }

    //获取字段的语言包值
    public static function getFiledLangData(&$data, $ismore = false,$lang='')
    {
        //获取字段的语言数据
        self::getLangData($data, ['tabfiled', $ismore,$lang]);
        self::getLangData($data, ['tabfiledoptions', $ismore,$lang]);

    }

    public function saveFiledLangData(&$data)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);

        self::getLangKey($data, ['tabfiled',0,1]);

        foreach ($data['langkey'] as $k => $v) {
            $langarr = Pdecode($v);
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                continue;
            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['flag'],
                        'filed' => $k
                    ];
                    C::t('#lang#lang')->insertData($table_sf, $setarr);
                    /*
                                        if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                                            unset($data[$k]);
                                        }*/
                }

            }
        }
        unset($data['langkey']);
    }

    public function saveFiledoptionsLangData(&$data)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);

        self::getLangKey($data, ['tabfiledoptions',0,1]);
        foreach ($data['langkey'] as $k => $v) {
            $langarr = Pdecode($v);
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                continue;
            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';

                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    if ($k == 'options') {
                        $idvalue = serialize($data[$k]);
                    } else {
                        $idvalue = $data[$k];
                    }
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $idvalue,
                        'idvalue' => $data['flag'],
                        'filed' => $k
                    ];
                    C::t('#lang#lang')->insertData($table_sf, $setarr);
                    /*if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                        unset($data[$k]);
                    }*/
                }

            }
        }

        unset($data['langkey']);
    }

    //获取字段的语言包key
    public static function getFiledLangKeyandData(&$data, $ismore = false,$lang='')
    {
        //获取字段的langkey
        self::getLangKey($data, ['tabfiled', $ismore]);
        //获取字段选项的langkey
        self::getLangKey($data, ['tabfiledoptions', $ismore]);
        //获取字段的语言数据
        self::getLangData($data, ['tabfiled', $ismore,$lang]);

        self::getLangData($data, ['tabfiledoptions', $ismore,$lang]);
    }

    public static function saveTabgroupLangData(&$data)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);

        self::getLangKey($data, ['tabgroup',0,1]);
        foreach ($data['langkey'] as $k => $v) {

            $langarr = Pdecode($v);
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                continue;
            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['gid'],
                        'filed' => $k
                    ];
                    C::t('#lang#lang')->insertData($table_sf, $setarr);
                    /*if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                        unset($data[$k]);
                    }*/
                }

            }
        }
        unset($data['langkey']);
        //unset($data['gid']);
    }

    public static function saveVppLangData(&$data)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        self::getLangKey($data, ['vapp',0,1]);
        foreach ($data['langkey'] as $k => $v) {
            $langarr = Pdecode($v, '');
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                continue;
            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $filedarr = explode(':', $dataKey);
                $filed = $filedarr[0];
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'svalue' => $data[$k],
                        'idvalue' => $data['appid'],
                        'filed' => $filed
                    ];
                    C::t('#lang#lang')->insertData($table_sf, $setarr);
                    /* if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                         unset($data[$k]);
                     }*/
                }

            }
        }
        unset($data['langkey']);
        //unset($data['appid']);
    }

    public function delAlonepagedataLangData($id)
    {
        if (!is_array($id)) $id = array($id);
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['alonepagedata']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $id);
        }
    }

    public function delAlonepageLangData($id)
    {
        if (!is_array($id)) $id = array($id);
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['alonepage']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $id);
        }
    }

    public function delAlonepagetagLangData($tagid)
    {
        if (!is_array($tagid)) $tagid = array($tagid);
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['alonepagetag']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $tagid);
        }
    }

    public function delBannerLangData($bid)
    {
        if (!is_array($bid)) $bid = array($bid);
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['banner']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $bid);
        }
    }

    //删除自定义字段分类
    public static function delFiledcatLangData($ids)
    {
        global $_G, $idTypeMap;
        if (!is_array($ids)) $ids = (array)$ids;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['tabfiledcat']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $ids);
        }
    }

    //删除自定义字段
    public static function delTabFiledLangData($flag)
    {
        global $_G;
        $langlist = $_G['language_list'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalueOrFiled($table_sf, $flag);
        }
    }

    public static function delVappLangData($appid)
    {
        if (!is_array($appid)) $appids = (array)$appid;
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['vapp']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $appids);
        }
    }

    public static function delTabcatLangData($cids)
    {
        if (!is_array($cids)) $cids = (array)$cids;
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['tabcat']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $cids);
        }
    }

    public static function delTabgroupLangData($gids)
    {
        if (!is_array($gids)) $gids = (array)$gids;
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['tabgroup']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $gids);
        }
    }

    public static function delTabLangData($tids)
    {
        if (!is_array($tids)) $tids = (array)$tids;
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['tab']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $tids);
        }
        $idtype = 3;
        C::t('#lang#lang_search')->deleteByidvalue($idtype, $tids);
    }

    public static function delTabattrLangData($tids)
    {
        if (!is_array($tids)) $tids = (array)$tids;
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['tabfiledval']['idtype'];
        foreach ($langlist as $k => $v) {
            $lang = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($lang, $idtype, $tids);
        }
    }

    public static function delTaggroupLangData($cids)
    {
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['taggroup']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $cids);
        }
    }

    public static function delResourcesLangData($rids)
    {
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['file']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $rids);
        }
        C::t('#lang#lang_search')->deleteByidvalue($idtype, $rids);
    }

    public static function delFolderLangData(&$fids)
    {
        global $_G, $idTypeMap;
        $langlist = $_G['language_list'];
        $idtype = $idTypeMap['folder']['idtype'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            C::t('#lang#lang')->delByIdvalue($table_sf, $idtype, $fids);
        }
    }

    public static function saveFolderLangData(&$data, $fids)
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        $datas = [];
        if (!is_array($fids)) $fids = (array)$fids;
        foreach ($fids as $v) {
            $tmparr = $data;
            $tmparr['fid'] = $v;
            $datas[] = $tmparr;
        }
        self::getLangKey($datas, ['folder', 1,1]);
        $delkeys = [];
        foreach ($datas as $langkeydata) {
            foreach ($langkeydata['langkey'] as $k => $v) {
                $langarr = Pdecode($v, '');
                if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                    continue;
                } else {
                    $langkeyarr = explode('|', $langarr['key']);
                    $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                    $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                    $dataKeyarr = explode(':', $dataKey);

                    $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                    if (isset($data[$k])) {
                        $setarr = [
                            'skey' => $langarr['key'],
                            'idtype' => $idtype,
                            'svalue' => $data[$k],
                            'idvalue' => $dataKeyarr[1],
                            'filed' => $dataKeyarr[0]
                        ];
                        if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                            // $delkeys[] = $k;

                        }
                    }

                }
            }

        }
        $delkeys = array_unique($delkeys);
        foreach ($delkeys as $val) {
            unset($data[$val]);
        }
    }

    public static function getTaggroupLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['taggroup', $ismore, $lang]);
    }

    public static function getTaggroupLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['taggroup', $ismore]);
    }

    public static function saveTaggroupLangData(&$data,$lang='')
    {
        global $_G, $idTypeMap;
        $lang = $lang ? trim($lang):$_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        self::getLangKey($data, ['taggroup',0,1]);
        foreach ($data['langkey'] as $k => $v) {

            $langarr = Pdecode($v);
            if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                continue;
            } else {
                $langkeyarr = explode('|', $langarr['key']);
                $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                if (isset($data[$k])) {
                    $setarr = [
                        'skey' => $langarr['key'],
                        'idtype' => $idtype,
                        'idvalue' => $data['cid'],
                        'svalue' => $data[$k],
                        'filed' => $k
                    ];
                    C::t('#lang#lang')->insertData($table_sf, $setarr);
                    /*if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                        unset($data[$k]);
                    }*/
                }

            }
        }
        unset($data['langkey']);
        //unset($data['cid']);
    }

    public static function saveFiledcatLangData(&$data, $catids = array())
    {
        global $_G, $idTypeMap;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        $datas = [];
        foreach ($catids as $v) {
            $tmparr = $data;
            $tmparr['id'] = $v;
            $datas[] = $tmparr;
        }
        self::getLangKey($datas, ['tabfiledcat', 1,1]);
        $delkeys = [];
        foreach ($datas as $langkeydata) {
            foreach ($langkeydata['langkey'] as $k => $v) {
                $langarr = Pdecode($v, '');
                if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                    continue;
                } else {
                    $langkeyarr = explode('|', $langarr['key']);
                    $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                    $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                    $dataKeyarr = explode(':', $dataKey);
                    $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                    if (isset($data[$k])) {
                        $setarr = [
                            'skey' => $langarr['key'],
                            'idtype' => $idtype,
                            'svalue' => $data[$k],
                            'idvalue' => $dataKeyarr[1],
                            'filed' => $dataKeyarr[0]
                        ];
                        C::t('#lang#lang')->insertData($table_sf, $setarr);
                        /* if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                             $delkeys[] = $k;

                         }*/
                    }

                }
            }

        }
        $delkeys = array_unique($delkeys);
        foreach ($delkeys as $val) {
            unset($data[$val]);
        }
    }
    public static function updateResourcesSearchvalData($rids){
        global $_G;
        if(!is_array($rids)) $rids = (array)$rids;
        $lang = $_G['language_list'];
        foreach($lang as $k=>$l){
            $table_sf = str_replace('-', '_', $k);
            foreach($rids as $rid){
                C::t('#lang#lang')->updateSearchvalById($table_sf, 0,$rid);
            }
        }

    }
    public static function updateTabSearchvalData($tids){
        global $_G;
        if(!is_array($tids)) $tids = (array)$tids;
        $lang = $_G['language_list'];
        foreach($lang as $k=>$l){
            $table_sf = str_replace('-', '_', $k);
            foreach($tids as $tid){
                C::t('#lang#lang')->updateSearchvalById($table_sf, 3,$tid);
            }
        }
    }
    public static function saveResourcesLangData(&$data, $rids)
    {
        global $_G, $idTypeMap;
        if(!is_array($rids)) $rids = (array)$rids;
        $lang = $_G['language'];
        $table_sf = str_replace('-', '_', $lang);
        $datas = [];
        foreach ($rids as $v) {
            $tmparr = $data;
            $tmparr['rid'] = $v;
            $datas[] = $tmparr;
        }
        self::getLangKey($datas, ['file', 1,1]);
        $delkeys = [];
        foreach ($datas as $langkeydata) {
            foreach ($langkeydata['langkey'] as $k => $v) {
                $langarr = Pdecode($v, '');
                if (!isset($langarr['uid']) || !isset($langarr['key']) || $langarr['uid'] != $_G['uid']) {
                    continue;
                } else {
                    $langkeyarr = explode('|', $langarr['key']);
                    $idtypestr = $langkeyarr[0] ? $langkeyarr[0] : '';
                    $dataKey = $langkeyarr ? $langkeyarr[1] : '';
                    $dataKeyarr = explode(':', $dataKey);
                    $idtype = isset($idTypeMap[$idtypestr]['idtype']) ? $idTypeMap[$idtypestr]['idtype'] : 0;
                    if (isset($data[$k])) {
                        $setarr = [
                            'skey' => $langarr['key'],
                            'idtype' => $idtype,
                            'svalue' => $data[$k],
                            'idvalue' => $dataKeyarr[1],
                            'filed' => $dataKeyarr[0],
                        ];
                        C::t('#lang#lang')->insertData($table_sf, $setarr);
                        /*if (C::t('#lang#lang')->insertData($table_sf, $setarr)) {
                            $delkeys[] = $k;

                        }*/
                    }

                }
            }

        }
        $delkeys = array_unique($delkeys);
        foreach ($delkeys as $val) {
            unset($data[$val]);
        }
    }

    public static function getFolderLangData(&$data, $ismore = false,$lang='')
    {
        self::getLangData($data, ['folder', $ismore,$lang]);
    }

    public static function getFolderLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['folder', $ismore]);
    }

    public static function getResourcesLangData(&$data, $ismore = false,$lang='')
    {

        self::getLangData($data, ['file', $ismore,$lang]);
    }

    public static function getResourcesLangKey(&$data, $ismore = false)
    {
        self::getLangKey($data, ['file', $ismore]);
    }

    public static function getLangKey(&$data, $extra)
    {
        global $idTypeMap, $_G;
        if (!$_G['moreLanguageState'] && (!isset($extra[2]) || !$extra[2])) return;
        $uid = $_G['uid'];
        $keydata = [];
        $idtypestr = $extra[0];
        $idtype = $idTypeMap[$idtypestr]['idtype'];
        $ismore = $extra[1] ? true : false;
        switch ($idtype) {
            case 3:
                if ($ismore) {
                    foreach ($data as $k => $idval) {
                        foreach ($idTypeMap[$idtypestr]['Key'] as $v) {
                            if (!$idval[$v['inputtype']] || !in_array($idval[$v['inputtype']], self::$filedvaltypes)) continue;
                            if (is_array($idval['tid'])) {
                                $idstr = implode(',', $idval['tid']);
                            } else {
                                $idstr = $idval['tid'];
                            }
                            $idval['langkey'] = Pencode(['key' => $idtypestr . '|' . $idtypestr . ':' . $idval[$v['id']] . '-' . $idstr, 'uid' => $uid], 86400);
                        }
                        $data[$k] = $idval;
                    }
                } else {

                    foreach ($idTypeMap[$idtypestr]['Key'] as $v) {
                        if (!$data[$v['inputtype']] || !in_array($data[$v['inputtype']], self::$filedvaltypes)) continue;
                        if (is_array($data['tid'])) {
                            $idstr = implode(',', $data['tid']);
                        } else {
                            $idstr = $data['tid'];
                        }
                        $data['langkey'] = Pencode(['key' => $idtypestr . '|' . $idtypestr . ':' . $data[$v['id']] . '-' . $idstr, 'uid' => $uid], 86400);
                    }
                }
                break;
            case 23:
                if ($ismore) {
                    foreach ($data as $k => $idval) {
                        foreach ($idTypeMap[$idtypestr]['Key'] as $v) {
                            if (!$idval[$v['inputtype']] || !in_array($idval[$v['inputtype']], self::$filedvaltypes)) continue;
                            if (is_array($idval['rid'])) {
                                $idstr = implode(',', $idval['rid']);
                            } else {
                                $idstr = $idval['tid'];
                            }
                            $idval['langkey'] = Pencode(['key' => $idtypestr . '|' . $idtypestr . ':' . $idval[$v['id']] . '-' . $idstr, 'uid' => $uid], 86400);
                        }
                        $data[$k] = $idval;
                    }
                } else {

                    foreach ($idTypeMap[$idtypestr]['Key'] as $v) {
                        if (!$data[$v['inputtype']] || !in_array($data[$v['inputtype']], self::$filedvaltypes)) continue;
                        if (is_array($data['rid'])) {
                            $idstr = implode(',', $data['rid']);
                        } else {
                            $idstr = $data['rid'];
                        }
                        $data['langkey'] = Pencode(['key' => $idtypestr . '|' . $idtypestr . ':' . $data[$v['id']] . '-' . $idstr, 'uid' => $uid], 86400);
                    }
                }
                break;
            case 21:
                if ($ismore) {

                    foreach ($data as $k => $idval) {
                        foreach ($idTypeMap[$idtypestr]['Key'] as $v) {
                            if (!$idval[$v['inputtype']] || !in_array($idval[$v['inputtype']], ['inputselect','inputmultiselect'])) continue;
                            if (is_array($idval['flag'])) {
                                $idstr = implode(',', $idval['flag']);
                            } else {
                                $idstr = $idval['flag'];
                            }
                            $idval['langkey'] = Pencode(['key' => $idtypestr . '|' . $idtypestr . ':' . $idstr, 'uid' => $uid], 86400);
                        }
                        $data[$k] = $idval;
                    }
                } else {

                    foreach ($idTypeMap[$idtypestr]['Key'] as $v) {
                        if (!$data[$v['inputtype']] || !in_array($data[$v['inputtype']], ['inputselect','inputmultiselect'])) continue;
                        if (is_array($data['flag'])) {
                            $idstr = implode(',', $data['flag']);
                        } else {
                            $idstr = $data['flag'];
                        }
                        $data['langkey'] = Pencode(['key' => $idtypestr . '|' . $idtypestr . ':' . $idstr, 'uid' => $uid], 86400);
                    }
                }
                break;
            case 5:
                if ($ismore) {
                    foreach ($data as $k => $idval) {
                        foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                            if (!in_array($data['type'], ['select', 'multiselect'])) continue;
                            if (is_array($idval[$idTypeMap[$idtypestr]['id']])) {
                                $idstr = implode(',', $idval[$v['id']]);
                            } else {
                                $idstr = $idval[$v['id']];
                            }
                            $idval['langkey'][$key] = Pencode(['key' => $idtypestr . '|' . $key . ':' . $idstr, 'uid' => $uid], 86400);
                        }
                        $keydata[$k] = $idval;
                    }
                    $data = $keydata;

                } else {
                    foreach ($idTypeMap[$idtypestr]['Key'] as $k => $v) {
                        if (!in_array($data['type'], ['select', 'multiselect'])) continue;
                        if (is_array($data[$idTypeMap[$idtypestr]['id']])) {
                            $idstr = implode(',', $data[$v['id']]);
                        } else {
                            $idstr = $data[$v['id']];
                        }
                        $data['langkey'][$k] = Pencode(['key' => $idtypestr . '|' . $k . ':' . $idstr, 'uid' => $uid], 86400);

                    }
                }
                break;
            case 15:
                if ($ismore) {
                    foreach ($data as $k => $idval) {
                        foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                            if ($key == 'tdata' && !in_array($idval['type'], self::$alonepagetypes)) continue;
                            if (is_array($idval[$idTypeMap[$idtypestr]['id']])) {
                                $idstr = implode(',', $idval[$idTypeMap[$idtypestr]['id']]);
                            } else {
                                $idstr = $idval[$idTypeMap[$idtypestr]['id']];
                            }

                            $idval['langkey'][$idval[$key]] = Pencode(['key' => $idtypestr . '|' . $key . ':' . $idstr, 'uid' => $uid], 86400);

                        }

                        $data[$k] = $idval;
                    }
                } else {

                    $data['langkey'] = [];
                    foreach ($idTypeMap[$idtypestr]['Key'] as $k => $v) {
                        if ($k == 'tdata' && !in_array($data['type'], self::$alonepagetypes)) continue;
                        if (is_array($data[$v['id']])) {
                            $idstr = implode(',', $data[$v['id']]);
                        } else {
                            $idstr = $data[$v['id']];
                        }

                        // echo $idtypestr . '|' . $k . ':' . $idstr;
                        $data['langkey'][$k] = Pencode(['key' => $idtypestr . '|' . $k . ':' . $idstr, 'uid' => $uid], 86400);
                        // print_r(Pdecode( $data['langkey'][$k],''));
                    }


                }
                break;
            default :
                if ($ismore) {

                    foreach ($data as $k => $idval) {
                        foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                            if (!isset($idval[$key]) && ($k != $key)) continue;
                            if (is_array($idval[$v['id']])) {
                                $idstr = implode(',', $idval[$v['id']]);
                            } else {
                                $idstr = $idval[$v['id']];
                            }
                            $idval['langkey'][$key] = Pencode(['key' => $idtypestr . '|' . $key . ':' . $idstr, 'uid' => $uid], 86400);
                        }
                        $keydata[$k] = $idval;
                    }
                    $data = $keydata;

                } else {
                    $data['langkey'] = [];
                    foreach ($idTypeMap[$idtypestr]['Key'] as $k => $v) {
                        if (!isset($data[$k]) || isset($data['langkey'][$k])) continue;
                        if (is_array($data[$v['id']])) {
                            $idstr = implode(',', $data[$v['id']]);
                        } else {
                            $idstr = $data[$v['id']];
                        }
                        $data['langkey'][$k] = Pencode(['key' => $idtypestr . '|' . $k . ':' . $idstr, 'uid' => $uid], 86400);
                    }

                }

                break;

        }
    }


    public static function getLangData(&$data, $extra)
    {
        global $_G, $idTypeMap;
        $skeyarr = [];
        $idtypestr = $extra[0];
        require DZZ_ROOT . 'dzz' . BS . 'lang' . BS . 'class' . BS . 'idtypeMap.php';
        $idtype = $idTypeMap[$idtypestr]['idtype'];
        $ismore = $extra[1] ? true : false;
        $langkeys = array_keys($_G['language_list']);
        if(!isset($extra[2]) || !$extra[2] || !in_array($extra[2],$langkeys)){
            $lang = $_G['language'];
        }else{
            $lang = $extra[2];
        }
        $uid = $_G['uid'];
        $table_sf = str_replace('-', '_', $lang);

        switch ($idtype) {
            case 3:
                if ($ismore) {
                    foreach ($data as $k => $idval) {
                        foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                            $skeyarr[] = $idtypestr . '|' . $key . ':' . $k . '-' . $idval['tid'];
                        }
                    }

                    $langdata = C::t('#lang#lang')->fetchBySkeysIdtypes($table_sf, $idTypeMap[$idtypestr]['idtype'], $skeyarr);

                    foreach ($langdata as $v) {
                        list($pre, $idstr) = explode(":", $v['skey']);
                        list($filedname, $id) = explode('-', $idstr);
                        if ($v['svalue']) $data[$filedname]['value'] = $v['svalue'];
                    }
                } else {
                    $idismore = false;
                    if (is_array($data['tid'])) {
                        $idismore = true;
                    }
                    foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                        if ($idismore) {
                            foreach ($data['tid'] as $idstr) {
                                $skeyarr[] = $idtypestr . '|' . $key . ':' . $data['flag'] . '-' . $idstr;
                            }
                        } else {
                            $skeyarr[] = $idtypestr . '|' . $key . ':' . $data['flag'] . '-' . $data['tid'];
                        }
                    }
                    $langdata = C::t('#lang#lang')->fetchBySkeysIdtypes($table_sf, $idTypeMap[$idtypestr]['idtype'], $skeyarr);
                    $tmpdata = [];
                    foreach ($langdata as $v) {
                        list($pre, $idstr) = explode(":", $v['skey']);
                        list($filedname, $id) = explode('-', $idstr);
                        if ($idismore) {
                            if (isset($tmpdata[$filedname]['value'])) {
                                $tmpdata[$filedname]['value'] = ($tmpdata[$filedname]['value'] == $v['svalue']) ? $v['svalue'] : '';
                            } else {
                                $tmpdata[$filedname]['value'] = $v['svalue'];
                            }
                        } else {
                            if ($v['svalue']) $tmpdata[$filedname]['value'] = $v['svalue'];
                        }
                    }
                    $tmpdata = array_filter($tmpdata);
                    $data = array_merge($data, $tmpdata);
                }
                break;
            case 23:
                if ($ismore) {
                    foreach ($data as $k => $idval) {
                        foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                            $skeyarr[] = $idtypestr . '|' . $key . ':' . $k . '-' . $idval['rid'];
                        }
                    }

                    $langdata = C::t('#lang#lang')->fetchBySkeysIdtypes($table_sf, $idTypeMap[$idtypestr]['idtype'], $skeyarr);

                    foreach ($langdata as $v) {
                        list($pre, $idstr) = explode(":", $v['skey']);
                        list($filedname, $id) = explode('-', $idstr);
                        if ($v['svalue']) $data[$filedname]['value'] = $v['svalue'];
                    }
                } else {
                    $idismore = false;
                    if (is_array($data['rid'])) {
                        $idismore = true;
                    }
                    foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                        if ($idismore) {
                            foreach ($data['rid'] as $idstr) {
                                $skeyarr[] = $idtypestr . '|' . $key . ':' . $data['flag'] . '-' . $idstr;
                            }
                        } else {
                            $skeyarr[] = $idtypestr . '|' . $key . ':' . $data['flag'] . '-' . $data['rid'];
                        }
                    }
                    $langdata = C::t('#lang#lang')->fetchBySkeysIdtypes($table_sf, $idTypeMap[$idtypestr]['idtype'], $skeyarr);
                    $tmpdata = [];
                    foreach ($langdata as $v) {
                        list($pre, $idstr) = explode(":", $v['skey']);
                        list($filedname, $id) = explode('-', $idstr);
                        if ($idismore) {
                            if (isset($tmpdata[$filedname]['value'])) {
                                $tmpdata[$filedname]['value'] = ($tmpdata[$filedname]['value'] == $v['svalue']) ? $v['svalue'] : '';
                            } else {
                                $tmpdata[$filedname]['value'] = $v['svalue'];
                            }
                        } else {
                            if ($v['svalue']) $tmpdata[$filedname]['value'] = $v['svalue'];
                        }
                    }
                    $tmpdata = array_filter($tmpdata);
                    $data = array_merge($data, $tmpdata);
                }
                break;
            case 5:
                if ($ismore) {
                    foreach ($data as $k => $idval) {
                        foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                            if (!in_array($idval['type'], self::$hasoptionstypes)) continue;
                            $skeyarr[] = $idtypestr . '|' . $key . ':' . $idval[$v['id']];
                        }
                    }
                    if (!empty($skeyarr)) {
                        $langdata = C::t('#lang#lang')->fetchBySkeysIdtypes($table_sf, $idTypeMap[$idtypestr]['idtype'], $skeyarr);
                        foreach ($data as $key => &$val) {
                            foreach ($langdata as $v) {
                                $skey = str_replace($idtypestr . '|', '', $v['skey']);
                                list($filedname, $id) = explode(":", $skey);
                                if ($val[$idTypeMap[$idtypestr]['Key'][$filedname]['id']] == $id) {
                                    $langoptions = unserialize($v['svalue']);
                                    if ($langoptions) $val['options'] = unserialize($v['svalue']);

                                }
                            }
                        }
                    }


                } else {
                    foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                        if (!in_array($data['type'], self::$hasoptionstypes)) continue;
                        $skeyarr[] = $idtypestr . '|' . $key . ':' . $data[$v['id']];
                    }

                    if (!empty($skeyarr)) {
                        $langdata = C::t('#lang#lang')->fetchBySkeysIdtypes($table_sf, $idTypeMap[$idtypestr]['idtype'], $skeyarr);

                        foreach ($langdata as $v)
                            $skey = str_replace($idtypestr . '|', '', $v['skey']);
                        {
                            list($filedname, $id) = explode(":", $skey);
                            if ($id == $data[$idTypeMap[$idtypestr]['Key'][$filedname]['id']]) {
                                $langoptions = unserialize($v['svalue']);
                                if ($langoptions) $data['options'] = $langoptions;
                            }
                        }
                    }


                }
                break;
            case 15:

                if ($ismore) {
                    foreach ($data as $k => $idval) {
                        foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                            $skeyarr[] = $idtypestr . '|' . $key . ':' . $idval[$v['id']];
                        }
                    }
                    $langdata = C::t('#lang#lang')->fetchBySkeysIdtypes($table_sf, $idTypeMap[$idtypestr]['idtype'], $skeyarr);

                    foreach ($data as $key => $val) {
                        foreach ($langdata as $v) {
                            $skey = str_replace($idtypestr . '|', '', $v['skey']);
                            list($filedname, $id) = explode(":", $skey);
                            if ($val[$idTypeMap[$idtypestr]['Key'][$filedname]['id']] == $id) {
                                if ($v['svalue']) $data[$key] = $v['svalue'];
                            }
                        }
                    }
                } else {

                    foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                        $skeyarr[] = $idtypestr . '|' . $key . ':' . $data[$v['id']];
                    }
                    $langdata = C::t('#lang#lang')->fetchBySkeysIdtypes($table_sf, $idTypeMap[$idtypestr]['idtype'], $skeyarr);

                    foreach ($langdata as $v) {
                        $skey = str_replace($idtypestr . '|', '', $v['skey']);
                        list($filedname, $id) = explode(":", $skey);
                        if ($v['svalue']) $data[$filedname] = $v['svalue'];
                    }
                }
                break;
            default:
                if ($ismore) {
                    foreach ($data as $k => $idval) {
                        foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                            $skeyarr[] = $idtypestr . '|' . $key . ':' . $idval[$v['id']];
                        }
                    }
                    $langdata = C::t('#lang#lang')->fetchBySkeysIdtypes($table_sf, $idTypeMap[$idtypestr]['idtype'], $skeyarr);
                    foreach ($data as $key => $val) {
                        foreach ($langdata as $v) {
                            $skey = str_replace($idtypestr . '|', '', $v['skey']);
                            list($filedname, $id) = explode(":", $skey);
                            if ($val[$idTypeMap[$idtypestr]['Key'][$filedname]['id']] == $id) {
                                if ($v['svalue']) $data[$key][$filedname] = $v['svalue'];
                            }
                        }
                    }

                } else {

                    foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                        if (is_array($data[$v['id']])) {
                            foreach ($data[$v['id']] as $idstr) {
                                $skeyarr[] = $idtypestr . '|' . $key . ':' . $idstr;
                            }
                        } else {

                            $skeyarr[] = $idtypestr . '|' . $key . ':' . $data[$v['id']];
                        }

                    }
                    $langdata = C::t('#lang#lang')->fetchBySkeysIdtypes($table_sf, $idTypeMap[$idtypestr]['idtype'], $skeyarr);
                    $tmpdata = [];
                    foreach ($langdata as $v) {
                        $skey = str_replace($idtypestr . '|', '', $v['skey']);
                        list($filedname, $id) = explode(":", $skey);
                        if ($v['svalue']) $tmpdata[$filedname] = $v['svalue'];
                    }
                    $tmpdata = array_filter($tmpdata);
                    $data = array_merge($data, $tmpdata);

                }
                break;
        }
    }
}