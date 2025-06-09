<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 *
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
global $_G;
$operation = trim($_GET['operation']);
$appid = isset($_GET['appid']) ? trim($_GET['appid']) : 'W3L0gO';
if (!$gdata = C::t('pichome_vapp')->fetch($appid)) {
    exit(json_encode(array('success' => false, 'msg' => lang('vapp_is_not_exist'))));
}
if ($operation == 'importing') {
    //获取导入数据的字段
    $allowkeys = array_keys($_G['language_list']);
    $defaultlang = $_G['language'];
    $tagdata = $_GET['data'];
    $tid = isset($tagdata['tid']) ? intval($tagdata['tid']) : 0;
    $cid = isset($tagdata['cid']) ? trim($tagdata['cid']) : '';
    unset($tagdata['tid']);
    unset($tagdata['cid']);
    foreach ($tagdata as $langkey => $tagval) {
        if (!in_array($langkey, $allowkeys)) continue;
        $catname = getstr($tagval['taggroup']);
        if ($catname) {
            if ($cid || $cid = DB::result_first("select cid from %t where catname = %s and appid = %s", ['pichome_taggroup', getstr($tagdata[$defaultlang]['taggroup']),$appid])) {
                $tabcatarr = [
                    'cid' => $cid,
                    'catname' => $catname
                ];
                Hook::listen('lang_parse', $tabcatarr, ['saveTaggroupLangData', $langkey]);
            } else {
                $setarr = array(
                    'pcid' => 0,
                    'catname' => $catname,
                    'appid' => $appid,
                    'dateline' => TIMESTAMP
                );
                $cid = C::t('pichome_taggroup')->insert($setarr);
                $setarr['cid'] = $cid;
                Hook::listen('lang_parse', $setarr, ['saveTaggroupLangData', $langkey]);
            }
        }
        $tagname = getstr($tagval['tagname']);
        if ($tagname) {
            if ($tid) {
                $tagarr = [
                    'tid' => $tid,
                    'tagname' => $tagname
                ];
                if($defaultlang == $langkey){
                    C::t('pichome_tag')->update($tid,['tagname'=>$tagname]);
                }
                Hook::listen('lang_parse', $tagarr, ['setTagLangData', $langkey]);
                $tagvapp = array(
                    'tid' => $tid,
                    'appid' => $appid,
                );
                C::t('pichome_vapp_tag')->insert($tagvapp);
            } else {
                //如果没有默认标签对应标签，则新建标签
                $tagname = getstr($tagdata[$defaultlang]['tagname']);
                if (!$tid = DB::result_first("select tid from %t where tagname = %s", ['pichome_tag', $tagname])) {
                    $setarr = array(
                        'tagname' => $tagname,
                        'initial' => C::t('pichome_tag')->getInitial($tagname),
                        'lang' => $defaultlang
                    );
                    $tid = C::t('pichome_tag')->insert($tagname, 1);
                    $setarr['tid'] = $tid;
                    Hook::listen('lang_parse', $setarr, ['setTagLangData', $defaultlang]);
                    $tagvapp = array(
                        'tid' => $tid,
                        'appid' => $appid,
                    );
                    C::t('pichome_vapp_tag')->insert($tagvapp);
                    if ($defaultlang != $langkey) {
                        $tagarr = [
                            'tid' => $tid,
                            'tagname' => $tagname
                        ];
                        Hook::listen('lang_parse', $tagarr, ['setTagLangData', $langkey]);
                    }
                } else {
                    continue;
                }

            }

            if ($cid) {
                $tagrelationarr = [
                    'appid' => $appid,
                    'cid' => $cid,
                    'tid' => $tid
                ];
                C::t('pichome_tagrelation')->insert($tagrelationarr);
            }
        } else {
            continue;
        }


    }


    exit(json_encode(array('success' => true, 'tid' => $tid)));
} elseif ($operation == 'list') {
    require_once DZZ_ROOT . './core/class/class_PHPExcel.php';
    $inputFileName = $_G['setting']['attachdir'] . $_GET['file'];
    //$inputFileName = $_G['setting']['attachdir'] . 'export_tag-ceshi.xlsx';

    if (!is_file($inputFileName)) {
        exit(json_encode(array('success' => false, 'msg' => lang('lack file'))));
    }
    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);
    $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
    $h0keys = array_keys($_G['language_list']);
    $h0 = [];
    foreach ($h0keys as $k => $v) {
        $h0[$v] = $_G['language_list'][$v]['langval'];
    }
    //获取可导入信息
    $h = array();
    foreach ($sheetData[1] as $key => $value) {
        $value = trim($value);
        foreach ($h0 as $fieldid => $title) {
            if ($title == $value) {
                $h[$key] = $fieldid;
            }
        }
    }

    $h1arr = ['tagname' => lang('tag'), 'taggroup' => lang('label_classification')];
    $count = count($h0);

    $h1 = ['tid', 'cid'];
    for ($i = 0; $i < $count; $i++) {
        $h1valarr = array_keys($h1arr);
        $h1 = array_merge($h1, $h1valarr);
    }
    $hh = [];
    $hh = $sheetData[2];
    /* foreach ($sheetData[2] as $key => $value) {
         $value = trim($value);
         foreach ($h1 as $fieldid => $title) {
             if ($title == $value) {
                 $hh[$key] = $fieldid;
             }
         }
     }*/
    $result = [];
    // 遍历$h
    foreach ($h as $key1 => $value) {
        // 初始化内层数组
        $innerArray = [];
        // 获取与$key1相关的键
        $relatedKeys = [$key1, chr(ord($key1) + 1)];

        // 遍历相关键，从$hh中获取值
        foreach ($relatedKeys as $relatedKey) {
            if (isset($hh[$relatedKey])) {
                $innerArray[$relatedKey] = $hh[$relatedKey];
            }
        }
        // 添加到结果数组
        $result[$value] = $innerArray;
    }
    $aresult['A'] = 'tid';
    $aresult['B'] = 'cid';
    $list = array();
    foreach ($sheetData as $key => $value) {
        if ($key <= 2) continue;

        $temp = array();
        foreach ($value as $col => $val) {
            if (trim($val) == '') continue;
            foreach ($aresult as $k1 => $v1) {
                if ($k1 == $col) {
                    $temp[$v1] = $val;
                }
            }
            foreach ($result as $k => $v) {
                if (isset($v[$col])) {
                    $temp[$k][$v[$col]] = $val;
                }
            }


        }
        $list[] = $temp;
    }
    $list = json_encode($list);
    $h0 = json_encode($h0);
    $h = array_unique($h);

    include template('librarylist/pc/page/import');
    exit();
    //exit(json_encode(array('success'=>true,'data'=>$h)));
} else {
    if (submitcheck('importfilesubmit')) {
        if ($_FILES['file']['tmp_name']) {
            $allowext = array('xls', 'xlsx');
            $ext = strtolower(substr(strrchr($_FILES['file']['name'], '.'), 1, 10));
            if (!in_array($ext, $allowext)) exit(json_encode(array('success' => false, 'msg' => '文件格式错误')));

            if ($file = uploadtolocal($_FILES['file'], 'cache', '', array('xls', 'xlsx'))) {
                $url = outputurl($_G['siteurl'] . MOD_URL . '&op=library&do=importtag&operation=list&appid=' . $appid . '&file=' . urlencode($file));
                exit(json_encode(array('success' => true, 'url' => $url)));
            } else {
                exit(json_encode(array('success' => false, 'msg' => lang('save_unsuccess'))));
            }
        } else {
            exit(json_encode(array('success' => false, 'msg' => lang('upload_failed'))));
        }
    }
}

