
<?php
$gid = isset($_GET['gid']) ? intval($_GET['gid']):0;
//默认导出格式
$exporttype= isset($_GET['exporttype']) ? strtolower($_GET['exporttype']):'xlsx';
//专辑数据
$gdata = C::t('#tab#tab_group')->fetch_by_gid($gid);
$formlist = unserialize($gdata['formfiled']);
$systemfiled = [
    'tabname' => array(
        'show' => 1,
        'sort' => 0,
        'style' => 0,
        'status' => 1,
        'filedcat' => 0,
        'writable' => 1,
        'tofolder' => 0,
        'allowchange' => 1,
        'labelname' => lang('name'),
        'type' => 'input',
        'disp' => 0,
    ),
    'cat' => array(
        'show' => 1,
        'sort' => 0,
        'style' => 0,
        'status' => 1,
        'filedcat' => 0,
        'writable' => 1,
        'tofolder' => 0,
        'allowchange' => 1,
        'labelname' => lang('classify'),
        'type' => 'classify',
        'disp' => 0,
    ),
    'number' => array(
        'show' => 1,
        'sort' => 0,
        'style' => 0,
        'status' => 0,
        'filedcat' => 0,
        'writable' => 1,
        'tofolder' => 0,
        'allowchange' => 1,
        'labelname' => lang('number'),
        'type' => 'input',
        'disp' => 0,
    ),
    'viewperm' => array(
        'show' => 1,
        'sort' => 0,
        'style' => 0,
        'status' => 0,
        'filedcat' => 0,
        'writable' => 1,
        'tofolder' => 0,
        'allowchange' => 1,
        'labelname' => lang('view_perm'),
        'type' => 'user',
        'disp' => 0,
    ),
    'topping_at' => array(
        'show' => 1,
        'sort' => 0,
        'style' => 0,
        'status' => 1,
        'filedcat' => 0,
        'writable' => 1,
        'tofolder' => 0,
        'allowchange' => 1,
        'labelname' => lang('topping'),
        'type' => 'bool',
        'disp' => 0,
    ),
    'is_hidden' => array(
        'show' => 1,
        'sort' => 0,
        'style' => 0,
        'status' => 0,
        'filedcat' => 0,
        'writable' => 1,
        'tofolder' => 0,
        'allowchange' => 1,
        'labelname' => lang('is_hidden'),
        'type' => 'bool',
        'disp' => 0,
    ),
    'is_recommed' => array(
        'show' => 1,
        'sort' => 0,
        'style' => 0,
        'status' => 1,
        'filedcat' => 0,
        'writable' => 1,
        'tofolder' => 0,
        'allowchange' => 1,
        'labelname' => lang('is_recommed'),
        'type' => 'bool',
        'disp' => 0
    )
];
foreach ($formlist as $key => $val) {
    if ($systemfiled[$key]) {
        $val['labelname'] = $systemfiled[$key]['labelname'];
        $val['type'] = $systemfiled[$key]['type'];
        $temp[$key] = $val;
    } elseif($form = C::t('form_setting')->fetch($key)) {
        $val['labelname'] = $form['labelname'];
        $val['type'] = $form['type'];
        $temp[$form['flag']] = $val;
    }


}
$sorts = array('name'); //允许排序的字段
//排序
foreach ($temp as $k => $v) {
    if ($k != 'name' && $temp[$k]['checked']['sort'] > 0) $sorts[] = $k;
    $forms[$k] = $temp[$k];
}

$hforms = [];
foreach($forms as $key => $val){
    if($val['showlist'] == 1){
        $hforms[$key] = $val;
    }
}
$perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 150;//每页数量
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;//页码数
$start = ($page - 1) * $perpage; //开始条数
$limit = isset($_GET['limit']) ? intval($_GET['limit']):0;
if($limit){
    //计算开始位置
    $start = $start+$perpage - $limit;
    $perpage = $limit;
}
$limitsql = "limit $start,$perpage";
$total = 0; //总条数
$disp = isset($_GET['disp']) ? trim($_GET['disp']) : '';//排序字段
$asc = isset($_GET['asc']) ? trim($_GET['asc']) : 'desc';//排序字段
$nocat = isset($_GET['nocat']) ? intval($_GET['nocat']) : 0;//是否是未分类
$cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
$sql = " FROM %t t ";
$params = array('tab');
$wheresql = " t.gid = %d  and t.isdelete = 0  and t.is_hidden = 0 ";
$para[] = $gid;
$isall = isset($_GET['isall']) ? intval($_GET['isall']):0;
if ($nocat) {
    $sql .= ' LEFT JOIN %t tabcatrelation ON tabcatrelation.tid = t.tid ';
    $params[] = 'tab_cat_relation';
    $wheresql .= ' and ISNULL(tabcatrelation.id) ';
}
if ($cid) {
    $haschildren = isset($_GET['hassub']) ? intval($_GET['hassub']):0;
    $haschildren = 1;
    if($haschildren){
        $pathkey = DB::result_first("select pathkey from %t where cid = %d ",array('tab_cat',$cid));
        $cids = [];
        foreach(DB::fetch_all("select cid from %t where pathkey like %s",array('tab_cat',$pathkey.'%')) as $v){
            $cids[] = $v['cid'];
        }
        $sql .= ' LEFT JOIN %t tabcatrelation ON tabcatrelation.tid = t.tid ';
        $params[] = 'tab_cat_relation';
        $wheresql .= ' and tabcatrelation.cid in (%n) ';
        $para[]= $cids;
    }else{
        $sql .= ' LEFT JOIN %t tabcatrelation ON tabcatrelation.tid = t.tid ';
        $params[] = 'tab_cat_relation';
        $wheresql .= ' and tabcatrelation.cid = %d ';
        $para[]= $cid;
    }

}
$lang= '';
//检查是否开启语言包
Hook::listen('lang_parse',$lang,['checklang']);
$keyword = isset($_GET['S_keyword']) ? htmlspecialchars($_GET['S_keyword']) : '';
if ($keyword) {
    if($lang && !in_array('lang_search',$params)){
        $sql .= " LEFT JOIN %t lang ON lang.idvalue=t.tid and lang.lang = %s ";
        $params[] = 'lang_search';
        $params[] = $lang;
    }
    $sql .= " LEFT JOIN %t searchattr ON searchattr.tid=t.tid and searchattr.skey='searchattr'";
    $params[] = 'tab_attr';
    $keywords = array();
    $arr1 = explode('+', $keyword);
    foreach ($arr1 as $value1) {
        $value1 = trim($value1);
        $arr2 = explode(' ', $value1);
        $arr3 = array();
        foreach ($arr2 as $value2) {
            $arr3[] = "t.tabname LIKE %s";
            $para[] = '%' . $value2 . '%';
            $arr3[] = "searchattr.svalue LIKE %s";
            $para[] = '%' . $value2 . '%';
            if($lang){
                $arr3[] = "lang.svalue LIKE %s";
                $para[] = '%' . $value2 . '%';
            }
        }
        $keywords[] = "(" . implode(" OR ", $arr3) . ")";
    }
    if ($keywords) {
        $wheresql .= " and (" . implode(" AND ", $keywords) . ")";
    }
}
//处理表单项
$formflags = array();
foreach ($_GET as $key => $val) {
    if (strpos($key, 'f_') === 0) {
        $formid = preg_replace("/^f_/", '', $key);
        $formflags[$formid] = $val;
    }
}
$searchlist = getsearchlistbysearchfiled($gdata['searchfiled'],$gdata);
$wheresql1 = array();
foreach ($formflags as $flag => $val) {
    if ($val == 'all' || $val == lang('unlimited')) continue;
    $form = $searchlist[$flag];
    $arr = array();
    if($flag == 'topping_at'){
        if($val){
            $wheresql .= ' and t.topping_at > 0 ';
        }else{
            $wheresql .= ' and t.topping_at = 0 ';
        }
        continue;
    }elseif($flag == 'is_recommed'){
        if($val){
            $wheresql .= ' and t.is_recommed > 0 ';
        }else{
            $wheresql .= ' and t.is_recommed = 0 ';
        }
        continue;
    }
    switch ($form['type']) {
        case 'tabgroup':
            $sql .= " left join %t tabrelation_$flag on tabrelation_$flag.tid = t.tid or tabrelation_$flag.rtid = t.tid ";
            $params[] = 'tab_relation';
            $params[] = $flag;
            $valarr = explode(',', $val);
            $wheresql .= "  and (tabrelation_$flag.tid in(%n) or tabrelation_$flag.rtid in(%n) ";
            $para[] = $valarr;
            $para[] = $valarr;
            break;
        case 'time':
            $sql .= " LEFT JOIN %t attr_$flag ON attr_$flag.tid=t.tid and attr_$flag.skey=%s ";
            $params[] = 'tab_attr';
            $params[] = $flag;
            $dateline = explode('_', $val);
            $arr = [];
            if ($dateline[0]) {
                $arr[] = "unix_timestamp(attr_$flag.svalue) > %d";
                $para[] = strtotime($dateline[0]);
            }
            if ($dateline[1]) {
                $arr[] = "unix_timestamp(attr_$flag.svalue) < %d";
                $para[] = strtotime($dateline[1]) + 24 * 60 * 60;
            }
            if ($arr) {
                $wheresql .= "  and (" . implode(" and ", $arr) . ")";
            }

            break;
        case 'bool':
            $sql .= " LEFT JOIN %t attr_$flag ON attr_$flag.tid=t.tid and attr_$flag.skey=%s ";
            $params[] = 'tab_attr';
            $params[] = $flag;
            if($val){
                $wheresql .= " and attr_$flag.svalue > 0 ";
            }else{
                $wheresql .= " and ( attr_$flag.svalue = 0 or isnull(attr_$flag.svalue) ) ";
            }

            break;
        case 'timerange'://多值日期搜索
            $dateline = explode('_', $val);
            $arr = [];
            $sql .= " LEFT JOIN %t rd_$flag ON rd_$flag.tid=t.tid and rd_$flag.filedname=%s ";
            $params[] = 'tab_rangedate';
            $params[] = $flag;
            $dateline = explode('_', $val);

            $arr = [];
            if ($dateline[0]) {
                $arr[] = " rd_$flag.start <= %d ";
                $para[] = strtotime($dateline[0]);
            }
            if ($dateline[1]) {
                $arr[] = " rd_$flag.end >= %d ";
                $para[] = strtotime($dateline[1]) + 24 * 60 * 60;
            }
            if ($arr) {
                $wheresql .= "  and (" . implode(" and ", $arr) . ")";
            }

            break;
        case 'inputmultiselect':
            $sql .= " LEFT JOIN %t tf_$flag ON tf_$flag.tid=t.tid and tf_$flag.filed=%s   ";
            $params[] = 'tab_filedval';
            $params[] = $flag;
            $valarr = explode(',', $val);
            $wheresql .= "  and tf_$flag.valid in(%n) ";
            $para[] = $valarr;
            break;
        case 'inputselect':
            $sql .= " LEFT JOIN %t tf_$flag ON tf_$flag.tid=t.tid and tf_$flag.filed=%s   ";
            $params[] = 'tab_filedval';
            $params[] = $flag;
            $valarr = explode(',', $val);
            $wheresql .= "  and tf_$flag.valid in(%n) ";
            $para[] = $valarr;
            break;
        case 'multiselect':
            $sql .= " LEFT JOIN %t attr_$flag ON attr_$flag.tid=t.tid and attr_$flag.skey=%s ";
            $params[] = 'tab_attr';
            $params[] = $flag;
            $arr = [];
            if (!is_array($val)) $val = explode(',', $val);

            foreach ($val as $v) {
                if($v == -1){
                    $arr[] = "(isnull(attr_$flag.svalue) OR  attr_$flag.svalue='')";
                }else{
                    $arr[] = " find_in_set(%s,attr_$flag.svalue)";
                    $para[] = trim($v);
                }


            }
            $brr = [];
            if($lang){
                $sql .= " LEFT JOIN %t lang_$flag ON lang_$flag.idvalue=t.tid and lang_$flag.filed = %s ";
                $params[] = 'lang_'.$lang;
                $params[] = $flag;
            }
            foreach ($val as $v) {
                if($v == -1){
                    $brr[] = "(isnull(lang_$flag.svalue) OR  lang_$flag.svalue='')";
                }else{
                    $brr[] = " find_in_set(%s,lang_$flag.svalue)";
                    $para[] = trim($v);
                }

            }
            $tmpwheresql = '';
            if ($arr) {
                $tmpwheresql .= "  (" . implode(" and ", $arr) . ")";
            }

            if ($brr) {
                $tmpwheresql .= " OR (" . implode(" AND ", $brr) . ")";
            }
            if($tmpwheresql) $wheresql .= "  and ($tmpwheresql)";
            break;

        case 'select':
            $sql .= " LEFT JOIN %t attr_$flag ON attr_$flag.tid=t.tid and attr_$flag.skey=%s ";
            $params[] = 'tab_attr';
            $params[] = $flag;
            $arr = [];
            if (!is_array($val)) $val = explode(',', $val);
            foreach ($val as $v) {
                if($v == -1){
                    $arr[] = "(isnull(attr_$flag.svalue) OR  attr_$flag.svalue='')";
                }else {
                    $arr[] = " find_in_set(%s,attr_$flag.svalue)";
                    $para[] = trim($v);
                }
            }

            $brr = [];
            if($lang){
                $sql .= " LEFT JOIN %t lang_$flag ON lang_$flag.idvalue=t.tid and lang_$flag.filed = %s ";
                $params[] = 'lang_'.$lang;
                $params[] = $flag;
            }
            foreach ($val as $v) {
                if($v == -1){
                    $brr[] = "(isnull(lang_$flag.svalue) OR  lang_$flag.svalue='')";
                }else{
                    $brr[] = " find_in_set(%s,lang_$flag.svalue)";
                    $para[] = trim($v);
                }

            }
            $tmpwheresql = '';
            if ($arr) {
                $tmpwheresql .= "  (" . implode(" OR ", $arr) . ")";
            }

            if ($brr) {
                $tmpwheresql .= " OR (" . implode(" OR ", $brr) . ")";
            }
            if($tmpwheresql) $wheresql .= "  and ($tmpwheresql)";
            break;

        case 'input':
            $sql .= " LEFT JOIN %t attr_$flag ON attr_$flag.tid=t.tid and attr_$flag.skey=%s ";
            $params[] = 'tab_attr';
            $params[] = $flag;
            if ($form['range'] == 1) {
                if ($val == '_') break;

                list($start, $end) = explode('_', $val);
                if ($start) {
                    $wheresql .= " and  CAST(attr_$flag.svalue AS unsigned)>=%d";
                    $para[] = intval($start);
                }
                if ($end) {
                    $wheresql .= " and CAST(attr_$flag.svalue AS unsigned)<=%d";
                    $para[] = intval($end);
                }
            } elseif ($form['range'] == 2 || $form['range'] == 3) {
                if (!is_array($val)) $val = explode(',', $val);
                $wheresql .= " and attr_$flag.svalue IN (%n)";
                $para[] = $val;
            } else {
                $wheresql .= " and attr_$flag.svalue=%s";
                $para[] = $val;
            }
            break;
        case 'grade':
            $sql .= " LEFT JOIN %t attr_$flag ON attr_$flag.tid=t.tid and attr_$flag.skey=%s ";
            $params[] = 'tab_attr';
            $params[] = $flag;
            $tarr = array();
            if (!is_array($val)) $val = explode(',', $val);
            foreach ($val as $a) {
                $a = intval($a);
                if ($a == 0) {
                    $tarr[] = "(isnull(attr_$flag.svalue) OR attr_$flag.svalue='0' OR attr_$flag.svalue='') ";
                } else {
                    $tarr[] = "attr_$flag.svalue = %s";
                    $para[] = $a;
                }
            }
            if ($tarr) {
                $wheresql .= " and (" . implode(" OR ", $tarr) . ")";
            }
            break;

        case 'label':
            $sql .= " LEFT JOIN %t attr_$flag ON attr_$flag.tid=t.tid and attr_$flag.skey=%s ";
            $params[] = 'tab_attr';
            $params[] = $flag;
            $tarr = array();
            $tids = !is_array($val) ? explode(',', $val) : $val;
            foreach ($tids as $tid) {
                if ($tid == -1) {
                    $tarr[] = "(isnull(attr_$flag.svalue) OR  attr_$flag.svalue='')";
                } else {
                    $tarr[] = "find_in_set(%d,attr_$flag.svalue)";
                    $para[] = $tid;
                }

            }
            if ($tarr) {
                $wheresql .= " and (" . implode(" OR ", $tarr) . ")";
            }

            break;
        case 'user':
            $sql .= " LEFT JOIN %t attr_$flag ON attr_$flag.tid=t.tid and attr_$flag.skey=%s ";
            $params[] = 'tab_attr';
            $params[] = $flag;
            $tarr = array();
            $fuids = !is_array($val) ? explode(',', $val) : $val;
            foreach ($fuids as $_uid) {
                $tarr[] = "find_in_set(%d,attr_$flag.svalue)";
                $para[] = $_uid;
            }
            if ($tarr) {
                $wheresql .= " and (" . implode(" OR ", $tarr) . ")";
            }

            break;
    }

}
if (!isset($_GET['disp'])) {
    //获取用户默认排序方式
    $sortdata = C::t('user_setting')->fetch_by_skey('tabsortfileds_'.$gid);
    $sortfilearr = ['dateline', 'sumnum', 'updatedate','is_recommed','topping_at'];
    if ($sortdata) {
        $sortdatarr = unserialize($sortdata);
        $disp = $sortdatarr['filed'];
        $asc = $sortdatarr['sort'] ? 'asc' : 'desc';
    } else {
        $disp = 'topping_at';
        $asc = 'desc';
    }
}
$groupsql = ' group by t.tid ';
switch ($disp){
    case 'is_recommend':
        $order = 't.is_recommed';
        break;
    /* case 'topping_at':
         $order = 't.topping_at';
         break;*/
    case 'dateline':
        $order = 't.tid';
        break;
    case 'views':
        $sql .= 'left join %t v on v.idval = t.tid and v.idtype = 2 ';
        $dispfiled = 'v.nums as num';
        $params[] = 'views';
        $order = ' num   ';
        break;
    case 'updatedate':
        $order = 't.updatedate';
        break;
    default:
        $sql .= " LEFT JOIN %t attr_$disp ON attr_$disp.tid=t.tid and attr_$disp.skey=%s ";
        $params[] = 'tab_attr';
        $params[] = $disp;
        $order = "val";
        $dispfiled = 'any_value(attr_'.$disp.'.svalue) as val';
        break;
}
$ordersql = ' order by  t.topping_at desc,';
//if($order != 't.topping_at') $ordersql .= " t.topping_at desc, ";
$ordersql .= "  $order $asc ";
//if($order != 't.topping_at') $ordersql .= " ,t.topping_at desc ";
if($order != 't.is_recommed') $ordersql .= ' ,t.is_recommed desc ';
$tids = [];
if(!$dispfiled) $dispfiled = 't.'.$disp;

foreach (DB::fetch_all("SELECT t.tid,$dispfiled  $sql where $wheresql $groupsql $ordersql $limitsql",
    array_merge($params, $para)) as $value) {

    $tids[] = $value['tid'];
}
$formdatas = [];
$datas = [];
//获取字段语言包
foreach(DB::fetch_all("select tid from %t where gid =%d and tid in(%n)",['tab',$gid,$tids]) as $v){
    $tid = $v['tid'];
    $cdata = C::t('#tab#tab')->fetch_tab_by_tid($tid);
    $cdata['topping_at'] = $cdata['topping_at'] ? dgmdate($cdata['topping_at'], 'Y-m-d H:i:s') : -1;
    foreach ($forms as $key => $val) {
        $fixedkeyarr = ['tabname','number','topping_at','is_hidden','is_recommed'];
        if ($key == 'cat') {
            $catdata = C::t('#tab#tab_cat_relation')->fetch_catname_by_tid($tid);
            $catnames = array_column($catdata,'catname');
            $formdatas[$key]['value'] = (!empty($catnames)) ? implode(',',$catnames):'';
        } elseif(in_array($key,$fixedkeyarr)){
            if($key == 'tabname' || $key == 'number') {
                $formdatas[$key]['value'] = $cdata[$key];
            }else{
                $formdatas[$key]['value'] = $cdata[$key] ? lang('is'):lang('no');
            }
        }elseif ($val['type'] == 'tabgroup') {
            $rgid = $val['extra']['gid'];
            if (DB::result_first("select isdelete from %t where gid = %d", ['tab_group', $rgid])) {
                continue;
            }
            $rtids = C::t('#tab#tab_relation')->fetch_by_tidFiledname($tid, $key);
            if (!empty($rtids)) {
                $tabdata = C::t('#tab#tab')->fetch_by_tids($rtids, 1);
                $rtdata = [];
                foreach ($tabdata as $tab) {
                    $rtdata[] =  $tab['tabname'];
                }
                $formdatas[$key]['value'] = (!empty($rtdata)) ? implode(',',$rtdata):'';

            } else $formdatas[$key]['value'] ='';
        } elseif ($val['type'] == 'timerange') {
            $filedname = $key;
            $timedataarr =[];
            foreach (C::t('#tab#tab_rangedate')->fetch_by_tid($tid, $filedname) as $val) {
                $timedataarr[] = $val;
            }
            $formdatas[$key]['value'] = (!empty($timedataarr)) ? implode(',',$timedataarr):'';
        } else{
            if(isset($cdata[$key])){

                if ($val['type'] == 'fulltext') {
                    if($exporttype == 'xlsx'){
                        $parsedData = parserichtextdataToexcel($cdata[$key]);
                        $formdatas[$key]['value']['value'] = $parsedData['text'];
                        $formdatas[$key]['value']['images'] = $parsedData['images'];
                    }else{
                        $formdatas[$key]['value'] = parserichtextdata($cdata[$key]);
                    }

                } else {
                    $formdatas[$key]['value'] = $cdata[$key];
                }
            }

            unset($cdata[$key]);
        }
    }
    $cdata['sumnum'] = DB::result_first("select count(id) from %t where tid = %d", array('pichome_resourcestab', $tid));
    $cdata['showlist'] = $formdatas;
    $datas[] = $cdata;
}
function parserichtextdataToexcel($data){
    $data = str_replace('&amp;', '&', $data);
    // 使用 preg_match_all 进行全局匹配
    preg_match_all('/<img(.*?)=(.*?)mod=io&op=getfileStream&path=(\w+)&aflag=(attach::\d+)(.*?)\/>/', $data, $matches, PREG_SET_ORDER);

    // 存储图片路径
    $imagePaths = [];
    $textParts = [];
    // 处理每个匹配项
    foreach ($matches as $match) {
        if (isset($match[4])) {
            $url = IO::getStream($match[4]);
            // 替换匹配项为占位符
            $placeholder = '[IMAGE_' . count($imagePaths) . ']';
            $data = str_replace($match[0], $placeholder, $data);
            $imagePaths[] = $url;
        }
    }
    $data = strip_tags($data);
    // 分割文字和占位符
    $textParts = explode('[IMAGE_', $data);
    $finalText = [];
    foreach ($textParts as $part) {
        if (strpos($part, ']') !== false) {
            list($index, $rest) = explode(']', $part, 2);
            $finalText[] = ['text' => '', 'imageIndex' => $index];
            $finalText[] = ['text' => $rest, 'imageIndex' => null];
        } else {
            $finalText[] = ['text' => $part, 'imageIndex' => null];
        }
    }
    return ['text' => $finalText, 'images' => $imagePaths];
}
function parserichtextdata($data)
{
    $data = str_replace('&amp;','&',$data);
    // 使用 preg_match_all 进行全局匹配
    preg_match_all('/=(.*?)mod=io&op=getfileStream&path=(\w+)&aflag=(attach::\d+)/', $data, $matches, PREG_SET_ORDER);


    // 处理每个匹配项
    foreach ($matches as $match) {
        if (isset($match[3])) {
            $url = IO::getStream($match[3]);
            $ext = strtolower(substr(strrchr(preg_replace("/\.dzz$/i", '', preg_replace("/\?.*/i", '', $url)), '.'), 1, 10));

            $mime = dzz_mime::get_type($ext);
            $imageData = file_get_contents($url);
            $imageBase64 = base64_encode($imageData);
            $imageBase64Url = 'data:' . $mime . ';base64,' . $imageBase64;

            // 替换匹配项
            $data = str_replace($match[0], '="' . $imageBase64Url, $data);
        }
    }

    $data = strip_tags($data, '<img>');
    return $data;
}
if($exporttype == 'xlsx'){
    //标题头
    $h0= [];
    foreach($hforms as $k=>$v){
        $h0[$k] = $v['labelname'];
    }

    $showdatas = [];
    foreach($datas as $v){
        $tnparr = [];
        foreach($v['showlist'] as $key=>$val){
            $tnparr[$key] = $val['value'];
        }
        $showdatas[] = $tnparr;
    }
    global $_G;
    $title = $gdata['name'];
    require_once DZZ_ROOT . './core/class/class_PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator($_G['username'])
        //->setTitle($title . ' - ' . lang('tab_information_table') . ' - oaooa')
        ->setTitle($title)
        ->setSubject($title . ' - ' . lang('tab_information_table'))
        ->setDescription($title . ' - ' . lang('tab_information_table') . ' Export By oaooa  ' . date('Y-m-d H:i:s'))
        ->setKeywords($title . ' - ' . lang('tab_information_table'))
        ->setCategory(lang('tab_information_table'));
// Create a first sheet
    $objPHPExcel->setActiveSheetIndex(0);
    $list = [];
    $j = 0;
    foreach ($h0 as $key => $value) {
        $index = getColIndex($j) . '1';
        $objPHPExcel->getActiveSheet()->setCellValue($index, $value);
        $list[1][$index] = $value;
        $j++;
    }

    $j = 0;
    foreach ($h0 as $key => $value) {
        $index = getColIndex($j) . '1';
        $objPHPExcel->getActiveSheet()->setCellValue($index, $value);
        $list[1][$index] = $value;
        $j++;
    }

    $i = 2;
    foreach($showdatas as $value){
        $j = 0;
        foreach ($h0 as $key1 => $fieldid) {
            $index = getColIndex($j) . ($i);
            if ($hforms[$key1]['type'] == 'fulltext') {
                $textParts = $value[$key1]['value'];
                $images = $value[$key1]['images'];
                $currentColumn = $j;
                foreach ($textParts as $part) {
                    if ($part['text']) {
                        $textIndex = getColIndex($currentColumn) . ($i);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit($textIndex, $part['text'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $currentColumn++;
                    }
                    if ($part['imageIndex'] !== null) {
                        $imagePath = $images[$part['imageIndex']];
                        $objDrawing = new PHPExcel_Worksheet_Drawing();
                        $objDrawing->setName('Image');
                        $objDrawing->setDescription('Image');
                        $objDrawing->setPath($imagePath);
                        $objDrawing->setHeight(100); // 设置图片高度
                        $objDrawing->setCoordinates(getColIndex($currentColumn) . ($i));
                        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                        $currentColumn++;
                    }
                }
            }else{
                $objPHPExcel->getActiveSheet()->getColumnDimension(getColIndex($j))->setWidth(20);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit($index, (isset($value[$key1]) ? $value[$key1]:''), PHPExcel_Cell_DataType::TYPE_STRING);
            }

            $j++;
            $list[$i][$index] = $value[$key1];
        }
        $i++;
    }

    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $filename = $_G['setting']['attachdir'] . './cache/' . random(5) . '.xlsx';
    $objWriter->save($filename);


    $name = $title . ' - ' . lang('tab_information_table') . '.xlsx';
    $name = '"' . (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name) . '"';

    $filesize = filesize($filename);
    $chunk = 10 * 1024 * 1024;
    if (!$fp = @fopen($filename, 'rb')) {
        exit(lang('export_failure'));
    }
    dheader('Date: ' . gmdate('D, d M Y H:i:s', TIMESTAMP) . ' GMT');
    dheader('Last-Modified: ' . gmdate('D, d M Y H:i:s', TIMESTAMP) . ' GMT');
    dheader('Content-Encoding: none');
    dheader('Content-Disposition: attachment; filename=' . $name);
    dheader('Content-Type: application/octet-stream');
    dheader('Content-Length: ' . $filesize);
    @ob_end_clean();
    if (getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    while (!feof($fp)) {
        echo fread($fp, $chunk);
        @ob_flush();  // flush output
        @flush();
    }
    @unlink($filename);
    exit();
}else{
    include template('test');

    $content = ob_get_contents();
//取得php页面输出的全部内容
    $fp = fopen(DZZ_ROOT.'/test.html', "w+");
    fwrite($fp, $content);
    fclose($fp);
    ob_end_clean();

    include_once DZZ_ROOT.'dzz/onlyoffice_view/class/thumb.php';
    $onlyoffice = new dzz\onlyoffice_view\classes\thumb();
    $data = [
        'name'=>$gdata['name'],
        'aid'=>time(),
        'stream'=>DZZ_ROOT.'./test.html',
        'ext'=>'html',
    ];
    $onlyoffice->convertHtmlToword($data,$exporttype);
}


function getColIndex($index)
{
    $string = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $ret = '';
    if ($index > 255) return '';
    for ($i = 0; $i < floor($index / strlen($string)); $i++) {
        $ret = $string[$i];
    }
    $ret .= $string[($index % (strlen($string)))];
    return $ret;
}

function getsearchlistbysearchfiled($searchfiled){
    $searchlist = unserialize($searchfiled);
    foreach($searchlist as $flag => $value){
        if($form = c::t('form_setting')->fetch($flag)){
            $v= $form;
            $v['multiple']=$value['multiple'];
            $v['range']=$value['range'];
            $v['dw']=$value['dw'];
            $v['dropmenu']=$value['dropmenu'];
        }

        $form['status'] = $value['status'];
        $searchlist[$flag]=$form;
    }
    return $searchlist;
}


