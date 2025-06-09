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

$overt = getglobal('setting/overt');
if(!$overt && !$overt = C::t('setting')->fetch('overt')){
    Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
}
global $_G;
$uid = $_G['uid'];
$gid = intval($_GET['gid']);
$gdata = C::t('#tab#tab_group')->fetch_by_gid($gid);
$do = isset($_GET['do']) ? trim($_GET['do']):'';
if($do == 'filelist'){//卡片列表
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
                if($form['way'] == 2){
                    $dateline = explode('_', $val);
                    $arr = [];
                    if ($dateline[0]) {
                        $arr[] = "attr_$flag.svalue > %d";
                        $para[] = strtotime($dateline[0]);
                    }
                    if ($dateline[1]) {
                        $arr[] = "attr_$flag.svalue < %d";
                        $para[] = strtotime($dateline[1]) + 24 * 60 * 60;
                    }
                    if ($arr) {
                        $wheresql .= "  and (" . implode(" and ", $arr) . ")";
                    }
                }elseif($form['way'] == 0){
                    $val = intval($val);
                    if($val){
                        $wheresql .= " and DATE_FORMAT(FROM_UNIXTIME(attr_$flag.svalue), %s) = %d ";
                        $para[] = '%Y';
                        $para[] = $val;
                    }
                }elseif($form['way'] == 1){
                    if($val){
                        $wheresql .= " and  DATE_FORMAT(FROM_UNIXTIME(attr_$flag.svalue), %s) = %s ";
                        $para[] = '%Y-%m';
                        $para[] = $val;
                    }
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
                $val =intval($val);
                $wheresql .= "  and tf_$flag.valid = %d ";
                $para[] = $val;
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
    $sum = DB::result_first("SELECT count(distinct t.tid) as num  $sql where $wheresql  ",
        array_merge($params, $para));

    foreach (DB::fetch_all("SELECT t.tid,$dispfiled  $sql where $wheresql $groupsql $ordersql $limitsql",
        array_merge($params, $para)) as $value) {

        $tids[] = $value['tid'];
    }
    //卡片信息
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
    if(!isset($formlist['tabname'])) $formlist['tabname']=['showlist'=>1,'status'=>1];

    foreach ($formlist as $key => $val) {
        if ((!$val['showlist'] || !$val['status']) && $key != 'tabname') continue;
        if ($systemfiled[$key]) {
            $val['labelname'] = $systemfiled[$key]['labelname'];
            $val['type'] = $systemfiled[$key]['type'];
            $temp[$key] = $val;
        } elseif($form = C::t('form_setting')->fetch_by_flag($key)) {
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
    //获取字段语言包
    Hook::listen('lang_parse', $forms, ['getFiledLangData', 1]);
    $data =[];
    if ($tids) {
        foreach ($tids as $tid) {
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
                }elseif(in_array($val['type'],['inputselect','inputmultiselect'])){
                    $idvals = C::t('#tab#tab_filedval')->fetch_by_tid($tid,$key);
                    if($idvals){
                        $valarr = [];
                        foreach(DB::fetch_all("select filedval from %t where id in(%n)",['form_filedvals',$idvals]) as $fv){
                            $valarr[] = $fv['filedval'];
                        }
                        $formdatas[$key]['value'] = implode(',',$valarr);
                    }else{
                        $formdatas[$key]['value'] = '';
                    }


                }elseif ($val['type'] == 'tabgroup') {
                    $rgid = $val['extra']['gid'];
                    if (DB::result_first("select isdelete from %t where gid = %d", ['tab_group', $rgid])) {
                        continue;
                    }
                    $rtids = C::t('#tab#tab_relation')->fetch_by_tidFiledname($tid, $key);
                    if (!empty($rtids)) {
                        $tabdata = C::t('tab')->fetch_by_tids($rtids, 1);
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
                            $formdatas[$key]['value'] = parserichtextdata($data[$key]);
                        }elseif($val['type'] == 'time'){
                            $filedname = $key;
                            $dateformat = ($val['extra']['dateformat']) ? $val['extra']['dateformat']:'Y-m-d';
                            $formdatas[$key]['value'] = $cdata[$key] ? dgmdate($cdata[$key],$dateformat):'-';
                        } elseif ($val['type'] == 'fulltext') {
                            $form[$key]['value'] = nl2br($cdata[$key]);
                        } else {
                            $formdatas[$key]['value'] = $cdata[$key];
                        }
                    }

                    unset($cdata[$key]);
                }
            }
            $cdata['sumnum'] = DB::result_first("select count(id) from %t where tid = %d", array('pichome_resourcestab', $tid));
            $cdata['showlist'] = $formdatas;
            $data[] = $cdata;
        }
    }
    //$data = C::t('#tab#tab')->fetch_by_tids($tids,1);
    $next = false;
    if(count($tids) >= $perpage){
        $next = true;
    }
//返回数据
    $return = array(
        'next' => $next,
        'data' => $data ? $data : array(),
        'gdata'=>$gdata,
        'param' => array(
            'disp' => $disp,
            'page' => $page,
            'perpage' => $perpage,
            'total' => intval($sum),
            'asc' => $asc,
            'keyword' => $keyword,
        )
    );
    exit(json_encode(array('data'=>$return)));
}
elseif($do == 'getTabShowList'){
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
        if ((!$val['showlist'] || !$val['status']) && $key != 'tabname') continue;
        if ($systemfiled[$key]) {
            $val['labelname'] = $systemfiled[$key]['labelname'];
            $val['type'] = $systemfiled[$key]['type'];
            $temp[$key] = $val;
        } elseif($form = C::t('form_setting')->fetch_by_flag($key)) {
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
    $showlist = [];
    if(!empty($temp)){
        foreach($temp as $k => $v){
            $showlist[] = ['flag'=>$k,'labelname'=>$v['labelname']];
        }
    }
    if(empty($showlist)){
        $showlist[] = ['flag'=>'tabname','labelname'=>$systemfiled['tabname']['labelname']];
    }
    exit(json_encode(['data'=>$showlist]));
}elseif($do == 'getscreen'){//获取筛选项
    $cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
    $ExpandedCids = [];
    if ($cid) {
        $catdata = C::t('#tab#tab_cat')->fetch($cid);
        $catdata['pathkey'] = str_replace('-_', '', $catdata['pathkey']);
        $catdata['pathkey'] = trim($catdata['pathkey'], '_');
        $ExpandedCids = explode('_', $catdata['pathkey']);
    }

    $page = isset($_GET['prepage']) ? intval($_GET['prepage']) : 10;
    $searchlist = getsearchlistbysearchfiled($gdata['searchfiled'],$gdata,$page);
    foreach ($searchlist as $k => $v) {
        $returndata = [];
        $defaultval = isset($_GET[$k]) ? trim($_GET[$k]):'';
        if($k == 'number'){
            $defaultvalarr = explode(',',$defaultval);

            $params = ['tab',$gid];
            $wheresql = " !isnull(number) and number != '' and gid = %d ";
            foreach (DB::fetch_all("select number from %t where $wheresql order by dateline desc limit 0,$page", $params) as $nv) {
                $returndata[] = $nv['number'];
            }

            if($defaultval) $returndata = array_merge($returndata,$defaultvalarr);

            $returndata = array_unique($returndata);
            $searchlist[$k]['options'] = $returndata;

        }
        elseif ($v['type'] == 'timerange') {
            $yeardata = [];
            foreach (DB::fetch_all("select DISTINCT YEAR(FROM_UNIXTIME(`start`)) AS year from %t where filedname = %s order by year",
                array('tab_rangedate', $k)) as $sy) {
                $yeardata[] = $sy['year'];
            }
            foreach (DB::fetch_all("select DISTINCT YEAR(FROM_UNIXTIME(`end`)) AS year from %t where filedname = %s order by year",
                array('tab_rangedate', $k)) as $ey) {
                $yeardata[] = $ey['year'];
            }
            $yeardata = array_unique($yeardata);
            sort($yeardata);
            $searchlist[$k]['timerangeyear'] = $yeardata;
            $searchlist[$k]['multiple'] = 0;
        }
        elseif ($v['type'] == 'tabgroup') {
            $rgid = $v['extra']['gid'];
            $rtids = C::t('#tab#tab_relation')->fetchReltidBygidRgid($gid, $rgid);
            $tabdata = C::t('#tab#tab')->fetch_by_tids($rtids, 1);
            $rtdata = [];
            foreach ($tabdata as $tab) {
                $rtdata[] = ['tid' => $tab['tid'], 'tabname' => $tab['tabname']];
            }
            $searchlist[$k]['values'] = $rtdata;
        }
        elseif($defaultval && in_array($v['type'],['inputselect','inputmultiselect'])){
            $defaultvalarr = explode(',',$defaultval);
            $defaultdata = C::t('form_filedvals')->fetch_by_id($defaultvalarr);
            $ndefaultdata = [];
            foreach($defaultdata as $dv){
                $ndefaultdata[$dv['id']] = $dv['filedval'];
            }
            $dids = array_keys($ndefaultdata);
            $optionsdata = $v['options'];
            $oids = array_column($optionsdata,'id');
            $diffdata = array_diff($dids,$oids);
            if($diffdata){
                foreach($diffdata as $did){
                    $v['options'][] = ['id'=>(string)$did,'name'=>$ndefaultdata[$did]];
                }
            }
            $searchlist[$k] = $v;
        }
        elseif($v['type']=='input'){
            $defaultvalarr = explode(',',$defaultval);
            $params = ['tab_attr', $k];
            $wheresql = " 1 ";
            $sql = " SELECT DISTINCT svalue FROM %t  WHERE skey=%s limit 0,$page ";
            foreach (DB::fetch_all($sql, $params) as $iv) {
                $returndata[] = $iv['svalue'];
            }
            if($defaultval)$returndata = array_merge($returndata,$defaultvalarr);
            $returndata = array_unique($returndata);
            $searchlist[$k]['options'] = $returndata;
        }elseif($v['type'] == 'time'){
            if(!empty($v['times'])){
                $options = $v['times'];
            }else{
                $options = [];
            }

            if($v['way'] == 0){
                $params = ['%Y','tab_attr',$k];
                $wheresql = " skey = %s ";
                if(!empty($options)){
                    $wheresql .= " and DATE_FORMAT(FROM_UNIXTIME(svalue), %s) not in(%n) ";
                    $params[] = '%Y';
                    $params[] = $options;
                }
                foreach(DB::fetch_all("select DATE_FORMAT(FROM_UNIXTIME(svalue), %s) as year from %t 
                    where $wheresql group by year order by year desc limit 0,10",$params) as $year){
                    $options[] = $year['year'];
                }
            }elseif($v['way'] == 1){
                $params = [ '%Y-%m','tab_attr',$k];
                $wheresql = " skey = %s ";
                if(!empty($options)){
                    $wheresql .= " and DATE_FORMAT(FROM_UNIXTIME(svalue),%s) not in(%n) ";
                    $params[] = '%Y-%m';
                    $params[] = $options;
                }
                foreach(DB::fetch_all("select DATE_FORMAT(FROM_UNIXTIME(svalue), %s) as year from %t 
                    where $wheresql group by year order by year desc limit 0,10",$params) as $year){
                    $options[] = $year['year'];
                }
            }
            $options = array_unique($options);
            $searchlist[$k]['timerangeyear'] = $options;
        }
    }
    exit(json_encode(array('data' => $searchlist, 'expandedcids' => $ExpandedCids)));
}elseif($do =='getfiledvals') {
    $filed = isset($_GET['filed']) ? trim($_GET['filed']) : '';
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $defaultvals = isset($_GET['defaultvals']) ? trim($_GET['defaultvals']):'';
    $prepage = isset($_GET['prepage']) ? intval($_GET['prepage']) : 10;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limitsql = ($page - 1) * $prepage . ',' . $prepage;
    $returndata = [];
    //如果是编号
    if ($filed == 'number') {
        $params = ['tab',$gid];
        $wheresql = " !isnull(number) and number != '' and gid = %d  ";
        if ($keyword) {
            $wheresql .= " and number like %s ";
            $params[] = '%' . $keyword . '%';
        }
        if($defaultvals){
            $defaultvalarr = explode(',', $defaultvals);
            $wheresql .= " and number not in (%n)";
            $params[] = $defaultvalarr;
        }
        foreach (DB::fetch_all("select number from %t where $wheresql order by dateline desc limit $limitsql", $params) as $v) {
            $returndata[] = $v['number'];
        }
    }
    else {

        $form = C::t('form_setting')->fetch_by_flag($filed);
        if ($form['type'] == 'input') {
            $params = ['tab_attr', $filed];
            $wheresql = " 1 ";
            if ($keyword) {
                $wheresql .= " and svalue like %s ";
                $params[] = '%' . $keyword . '%';
            }
            if($defaultvals){
                $defaultvalarr = explode(',', $defaultvals);
                $wheresql .= " and svalue not in (%n)";
                $params[] = $defaultvalarr;
            }
            $sql = " SELECT DISTINCT svalue FROM %t  WHERE skey=%s and $wheresql limit $limitsql ";
            foreach (DB::fetch_all($sql, $params) as $v) {
                $returndata[] = $v['svalue'];
            }
        } elseif (in_array($form['type'], ['inputselect', 'inputmultiselect'])) {
            $params = ['form_filedvals', $filed];
            $wheresql = " 1 ";
            if ($keyword) {
                $wheresql .= " and filedval like %s ";
                $params[] = '%' . $keyword . '%';
            }
            if($defaultvals){
                $defaultvalarr = explode(',', $defaultvals);
                $wheresql .= " and id not in (%n)";
                $params[] = $defaultvalarr;
            }
            $sql = " SELECT DISTINCT id, filedval FROM %t  WHERE filed=%s and $wheresql limit $limitsql ";
            foreach (DB::fetch_all($sql, $params) as $v) {
                $returndata[] = ['id'=>$v['id'],'name'=>$v['filedval']];
            }
        }
        elseif($form['type'] == 'time'){
            $searchlist = getsearchlistbysearchfiled($gdata['searchfiled'],$gdata,$page);
            if(!empty($searchlist[$filed]['times'])){
                $options = $searchlist[$filed]['times'];
            }else{
                $options = [];
            }
            $returndata = [];
            if($searchlist[$filed]['way'] == 0){
                $params = ['%Y','tab_attr',$filed];
                $wheresql = " skey = %s ";
                if(!empty($options)){
                    $wheresql .= " and DATE_FORMAT(FROM_UNIXTIME(svalue), %s) not in(%n) ";
                    $params[] = '%Y';
                    $params[] = $options;
                }
                foreach(DB::fetch_all("select DATE_FORMAT(FROM_UNIXTIME(svalue), %s) as year from %t 
                    where $wheresql group by year order by year desc limit $limitsql",$params) as $year){
                    $returndata[] = $year['year'];
                }
            }
            elseif($searchlist[$filed]['way'] == 1){
                $params = [ '%Y-%m','tab_attr',$filed];
                $wheresql = " skey = %s ";
                if(!empty($options)){
                    $wheresql .= " and DATE_FORMAT(FROM_UNIXTIME(svalue),%s) not in(%n) ";
                    $params[] = '%Y-%m';
                    $params[] = $options;
                }
                foreach(DB::fetch_all("select DATE_FORMAT(FROM_UNIXTIME(svalue), %s) as year from %t 
                    where $wheresql group by year order by year desc $limitsql",$params) as $year){
                    $returndata[] = $year['year'];
                }
            }
            $returndata = array_unique($returndata);
        }

    }
    $next = (count($returndata) >= $prepage) ? true:false;
    exit(json_encode(['success' => true, 'data' => $returndata,'next'=>$next]));
}
elseif($do == 'getfiled'){
    $gdata = C::t('#tab#tab_group')->fetch($gid);
    $filedType = [
        'input'=>lang('input'),
        'time'=>lang('timefiled'),
        'fulltext'=>lang('fulltext'),
        'textarea'=>lang('textarea'),
        'timerange'=>lang('timerange'),
        'select'=>lang('select'),
        'multiselect'=>lang('multiselect'),
        'link'=>lang('link'),
        'bool'=>lang('bool'),
        'tabgroup'=>lang('album'),
        'inputselect'=>lang('inputselect'),
        'inputmultiselect'=>lang('inputmultiselect'),
    ];
    $systemfiled = [
        'tabname'=>array(
            'show' => 1,
            'sort' => 0,
            'style' => 0,
            'status' => 1,
            'filedcat' => 0,
            'writable' => 1,
            'tofolder' => 0,
            'allowchange' => 1,
            'labelname'=>$gdata['aliasname'] ? $gdata['aliasname']: lang('name'),
            'type'=>'input',
            'disp'=>0,
            'allowsort'=>0,
            'allowlist'=>1
        ),
        'cat'=>array(
            'show' => 1,
            'sort' => 0,
            'style' => 0,
            'status' => 1,
            'filedcat' => 0,
            'writable' => 1,
            'tofolder' => 0,
            'allowchange' => 1,
            'labelname'=>$gdata['aliascat'] ? $gdata['aliascat']:lang('classify'),
            'type'=>'classify',
            'disp'=>0,
            'allowsort'=>0,
            'allowlist'=>1
        ),
        'number'=>array(
            'show' => 1,
            'sort' => 0,
            'style' => 0,
            'status' => 0,
            'filedcat' => 0,
            'writable' => 1,
            'tofolder' => 0,
            'allowchange' => 1,
            'labelname'=>$gdata['aliasnumber'] ? $gdata['aliasnumber']: lang('tabnumber'),
            'type'=>'input',
            'disp'=>0,
            'allowsort'=>0,
            'allowlist'=>1
        )

    ];
    $tabgroupdatas =  DB::fetch_all("select * from %t where   isdelete = 0",['tab_group']);
    Hook::listen('lang_parse',$tabgroupdatas,['getTabgrouplangData',1]);
    //获取目录模板数据
    $foldertemplate = unserialize($gdata['formfiled']);
    $foldertemplatedata = array();
    $formlist = array();
    $sysformlist = [];
    $tabformlist = [];
    $formfileds = C::t('form_setting')->fetch_flags_by_gid($gid);

    foreach($formfileds as $k=>$v){
        if(!isset($foldertemplate[$v['flag']]) && $v['flag'] != 'tab_'.$gid){
            $foldertemplate[$v['flag']] =[
                'flag' =>$v['flag'],
                'name' =>$v['labelname'],
                'show' => $v['show'] ?? 0,
                'disp' => $v['disp'],
                'filedcat' => $v['filedcat'],
                'fsort' => $v['sort'] ?? 0,//排序
                'showpage' => $v['showpage'] ? intval($v['showpage']):0,
                'type' => $v['type'],
                'showlist'=>$v['showlist'] ?? 0,
                'writable' => $v['writable'] ?? 1,
                'tofolder' => $v['tofolder'] ?? 0,
                'style' => $v['style'],//排列方式
                'gid' => $v['tabgroupid'],//属性
                'ison' => 0,//状态
                'allowchange'=>1,
                'extra'=>$v['extra'],
                'allowlist'=>1,
                'allowsort'=>0
            ];
        }
    }

    foreach ($foldertemplate as $k => $v) {
        if ($v['allowchange']) {
            if(isset($systemfiled[$k])){
                $sysformlist[$k] = array(
                    'flag' => $k,
                    'name' => $systemfiled[$k]['labelname'],
                    'show' => $v['show'],
                    'disp' => $systemfiled[$k]['disp'],
                    'filedcat' => $systemfiled[$k]['filedcat'],
                    'fsort' => $v['sort'],//排序
                    'showpage' => $v['showpage'] ? intval($v['showpage']):0,
                    'type' => $systemfiled[$k]['type'],
                    'writable' => $v['writable'],
                    'tofolder' => $v['tofolder'],
                    'style' => $foldertemplate[$k]['style'],//排列方式
                    'appid' => $systemfiled[$k]['appid'],//属性
                    'ison' => $v['status'],//状态
                    'allowsort'=>$systemfiled[$k]['allowsort'],
                    'allowlist'=>$systemfiled[$k]['allowlist'],
                    'showlist'=>$v['showlist'] ?? 0,

                );
            }
            elseif ($form = C::t('form_setting')->fetch($k)) {
                if ($form['labelname']) {
                    $form['allowsort'] = 0;
                    $form['allowlist'] = 1;
                    if($form['type'] == 'time'){
                        $form['allowsort'] = 1;
                    }
                    if($form['type'] == 'fulltext' || $form['type'] == 'textarea'){
                        $form['allowlist'] = 0;
                    }
                    $formlist[] = array(
                        'flag' => $k,
                        'name' => $form['labelname'],
                        'show' => $v['show'],
                        'disp' => $form['disp'],
                        'filedcat' => $form['filedcat'],
                        'fsort' => $v['sort'],//排序
                        'showpage' => $v['showpage'] ? intval($v['showpage']):0,
                        'type' => $form['type'],
                        'writable' => $v['writable'],
                        'tofolder' => $v['tofolder'],
                        'style' => $foldertemplate[$k]['style'],//排列方式
                        'appid' => $form['appid'],//属性
                        'ison' => $v['status'],//状态
                        'extra'=>$form['extra'],
                        'allowsort'=>$form['allowsort'],
                        'allowlist'=>$form['allowlist'],
                        'showlist'=>$v['showlist'] ?? 0,

                    );
                }
            }
        }
    }

    foreach($systemfiled as $k=>$v){
        if(!isset($sysformlist[$k])){
            $sysformlist[$k] = array(
                'flag' => $k,
                'name' => $systemfiled[$k]['labelname'],
                'show' => 1,
                'disp' => $systemfiled[$k]['disp'],
                'filedcat' => $systemfiled[$k]['filedcat'],
                'fsort' => 0,//排序
                'showpage' => 0,
                'type' => $systemfiled[$k]['type'],
                'writable' => 0,
                'tofolder' => 0,
                'style' => 0,//排列方式
                'appid' => $systemfiled[$k]['appid'],//属性
                'ison' => 0,//状态
                'allowsort'=>$systemfiled[$k]['allowsort']
            );
        }
    }

    Hook::listen('lang_parse',$formlist,['getFiledLangData',1]);
    //获取字段分类
    $filedcatdata = C::t('form_setting_filedcat')->fetch_all_by_tabgroupid($gid);
    $formlists = [];
    foreach ($filedcatdata as $k => $v) {
        foreach ($formlist as $key=>$val) {
            if($val['labelname']){
                $val['name'] = $val['labelname'];
                unset($val['labelname']);
            }
            if ($val['filedcat'] == $k) {
                $v['fileds'][] = $val;
                unset($formlist[$key]);
            }
        }
        $formlists[] = $v;
    }

    foreach($formlist as $nocatfileds){
        $formlists[] = array(
            'id' => 0,
            'catname' => lang('unclassify'),
            'fileds' => $nocatfileds,
            'disp'=>0
        );

    }

    $return = array('data' => $formlists, 'appdata' => $gdata,'tabgroupdatas'=>$tabgroupdatas,'tabformlist'=>$tabformlist,'filedType'=>$filedType,'sysformlist'=>$sysformlist);
    exit(json_encode($return));
}elseif ($do == 'getsearchfolder') {//分类数据
    $pcid = isset($_GET['pcid']) ? intval($_GET['pcid']):0;
    $catdatanum = C::t('#tab#tab_cat')->fetch_all_cat_by_gid($gid,$pcid);

    exit(json_encode(array( 'data' => $catdatanum)));
} elseif ($do == 'getsortfiled') {//获取排序字段
    $fixedfiled = [
        'topping_at' => array(
            'flag' => 'topping_at',
            'type' => 'bool',
            'labelname' => lang('topping'),
            'system' => 1,
            'disp' => 0,
            'catname' => 'system',
            'multiple' => 0,
            'dw' => '',
            'status' => 0,
            'isdefault' => 0,
            'allowsearch' => 1,
        ),
        'is_recommed' => array(
            'flag' => 'is_recommed',
            'type' => 'bool',
            'labelname' => lang('is_recommed'),
            'system' => 1,
            'disp' => 0,
            'catname' => 'system',
            'multiple' => 0,
            'dw' => '',
            'status' => 0,
            'isdefault' => 0,
            'allowsearch' => 1,
        ),
        'is_hidden' => array(
            'flag' => 'is_hidden',
            'type' => 'bool',
            'labelname' => lang('is_hidden'),
            'system' => 1,
            'disp' => 0,
            'catname' => 'system',
            'multiple' => 0,
            'dw' => '',
            'status' => 0,
            'isdefault' => 0,
            'allowsearch' => 1,
        ),
    ];
    $sortfiled = [
        ['filed' => 'sumnum', 'lang' => lang('filenums')],
        ['filed' => 'updatedate', 'lang' => lang('update_time')],
        ['filed' => 'dateline', 'lang' => lang('creation_time')],
    ];
    $gdata = C::t('tab_group')->fetch_by_gid($gid);
    $formfiled = unserialize($gdata['formfiled']);
    foreach ($formfiled as $k => $v) {
        if ($v['sort']) {
            if ($form = C::t('form_setting')->fetch_by_flag($k)) $sortfiled[] = ['filed' => $k, 'lang' => $form['labelname']];
            elseif ($form = $fixedfiled[$k]) $sortfiled[] = ['filed' => $k, 'lang' => $form['labelname']];
        }
    }
    exit(json_encode(['sortfiled' => $sortfiled]));
}elseif($do == 'getfiled'){
    $gdata = C::t('tab_group')->fetch($gid);
    $filedType = [
        'input'=>lang('input'),
        'time'=>lang('timefiled'),
        'fulltext'=>lang('fulltext'),
        'textarea'=>lang('textarea'),
        'timerange'=>lang('timerange'),
        'select'=>lang('select'),
        'multiselect'=>lang('multiselect'),
        'link'=>lang('link'),
        'bool'=>lang('bool'),
        'tabgroup'=>lang('album'),
        'inputselect'=>lang('inputselect'),
        'inputmultiselect'=>lang('inputmultiselect'),
    ];
    $systemfiled = [
        'tabname'=>array(
            'show' => 1,
            'sort' => 0,
            'style' => 0,
            'status' => 1,
            'filedcat' => 0,
            'writable' => 1,
            'tofolder' => 0,
            'allowchange' => 1,
            'labelname'=>$gdata['aliasname'] ? $gdata['aliasname']: lang('name'),
            'type'=>'input',
            'disp'=>0,
            'allowsort'=>0,
            'allowlist'=>1
        ),
        'cat'=>array(
            'show' => 1,
            'sort' => 0,
            'style' => 0,
            'status' => 1,
            'filedcat' => 0,
            'writable' => 1,
            'tofolder' => 0,
            'allowchange' => 1,
            'labelname'=>$gdata['aliascat'] ? $gdata['aliascat']:lang('classify'),
            'type'=>'classify',
            'disp'=>0,
            'allowsort'=>0,
            'allowlist'=>1
        ),
        'number'=>array(
            'show' => 1,
            'sort' => 0,
            'style' => 0,
            'status' => 0,
            'filedcat' => 0,
            'writable' => 1,
            'tofolder' => 0,
            'allowchange' => 1,
            'labelname'=>$gdata['aliasnumber'] ? $gdata['aliasnumber']: lang('tabnumber'),
            'type'=>'input',
            'disp'=>0,
            'allowsort'=>0,
            'allowlist'=>1
        ),
        'viewperm'=>array(
            'show' => 1,
            'sort' => 0,
            'style' => 0,
            'status' => 0,
            'filedcat' => 0,
            'writable' => 1,
            'tofolder' => 0,
            'allowchange' => 1,
            'labelname'=>lang('view_perm'),
            'type'=>'user',
            'disp'=>0,
            'allowsort'=>0,
            'allowlist'=>0
        ),
        'topping_at'=>array(
            'show' => 1,
            'sort' => 0,
            'style' => 0,
            'status' => 0,
            'filedcat' => 0,
            'writable' => 1,
            'tofolder' => 0,
            'allowchange' => 1,
            'labelname'=>lang('topping'),
            'type'=>'bool',
            'disp'=>0,
            'allowsort'=>0,
            'allowlist'=>1
        ),
        'is_hidden'=>array(
            'show' => 1,
            'sort' => 0,
            'style' => 0,
            'status' => 0,
            'filedcat' => 0,
            'writable' => 1,
            'tofolder' => 0,
            'allowchange' => 1,
            'labelname'=>lang('is_hidden'),
            'type'=>'bool',
            'disp'=>0,
            'allowsort'=>0,
            'allowlist'=>1
        ),
        'is_recommed'=>array(
            'show' => 1,
            'sort' => 0,
            'style' => 0,
            'status' => 0,
            'filedcat' => 0,
            'writable' => 1,
            'tofolder' => 0,
            'allowchange' => 1,
            'labelname'=>lang('is_recommed'),
            'type'=>'bool',
            'disp'=>0,
            'allowsort'=>0,
            'allowlist'=>1
        )
    ];

    //$tabgroupdatas =  DB::fetch_all("select * from %t where gid != %d and isdelete = 0",['tab_group',$gid]);
    $tabgroupdatas =  DB::fetch_all("select * from %t where   isdelete = 0",['tab_group']);
    Hook::listen('lang_parse',$tabgroupdatas,['getTabgrouplangData',1]);
    //获取目录模板数据
    $foldertemplate = unserialize($gdata['formfiled']);
    $foldertemplatedata = array();
    $formlist = array();
    $sysformlist = [];
    $tabformlist = [];
    $formfileds = C::t('form_setting')->fetch_flags_by_gid($gid);

    foreach($formfileds as $k=>$v){
        if(!isset($foldertemplate[$v['flag']]) && $v['flag'] != 'tab_'.$gid){
            $foldertemplate[$v['flag']] =[
                'flag' =>$v['flag'],
                'name' =>$v['labelname'],
                'show' => $v['show'] ?? 0,
                'disp' => $v['disp'],
                'filedcat' => $v['filedcat'],
                'fsort' => $v['sort'] ?? 0,//排序
                'showpage' => $v['showpage'] ? intval($v['showpage']):0,
                'type' => $v['type'],
                'showlist'=>$v['showlist'] ?? 0,
                'writable' => $v['writable'] ?? 1,
                'tofolder' => $v['tofolder'] ?? 0,
                'style' => $v['style'],//排列方式
                'gid' => $v['tabgroupid'],//属性
                'ison' => 0,//状态
                'allowchange'=>1,
                'extra'=>$v['extra'],
                'allowlist'=>1,
                'allowsort'=>0
            ];
        }
    }

    foreach ($foldertemplate as $k => $v) {
        if ($v['allowchange']) {
            if(isset($systemfiled[$k])){
                $sysformlist[$k] = array(
                    'flag' => $k,
                    'name' => $systemfiled[$k]['labelname'],
                    'show' => $v['show'],
                    'disp' => $systemfiled[$k]['disp'],
                    'filedcat' => $systemfiled[$k]['filedcat'],
                    'fsort' => $v['sort'],//排序
                    'showpage' => $v['showpage'] ? intval($v['showpage']):0,
                    'type' => $systemfiled[$k]['type'],
                    'writable' => $v['writable'],
                    'tofolder' => $v['tofolder'],
                    'style' => $foldertemplate[$k]['style'],//排列方式
                    'appid' => $systemfiled[$k]['appid'],//属性
                    'ison' => $v['status'],//状态
                    'allowsort'=>$systemfiled[$k]['allowsort'],
                    'allowlist'=>$systemfiled[$k]['allowlist'],
                    'showlist'=>$v['showlist'] ?? 0,

                );
            }
            elseif ($form = C::t('form_setting')->fetch($k)) {
                if ($form['labelname']) {
                    $form['allowsort'] = 0;
                    $form['allowlist'] = 1;
                    if($form['type'] == 'time'){
                        $form['allowsort'] = 1;
                    }
                    if($form['type'] == 'fulltext' || $form['type'] == 'textarea'){
                        $form['allowlist'] = 0;
                    }
                    $formlist[] = array(
                        'flag' => $k,
                        'name' => $form['labelname'],
                        'show' => $v['show'],
                        'disp' => $form['disp'],
                        'filedcat' => $form['filedcat'],
                        'fsort' => $v['sort'],//排序
                        'showpage' => $v['showpage'] ? intval($v['showpage']):0,
                        'type' => $form['type'],
                        'writable' => $v['writable'],
                        'tofolder' => $v['tofolder'],
                        'style' => $foldertemplate[$k]['style'],//排列方式
                        'appid' => $form['appid'],//属性
                        'ison' => $v['status'],//状态
                        'extra'=>$form['extra'],
                        'allowsort'=>$form['allowsort'],
                        'allowlist'=>$form['allowlist'],
                        'showlist'=>$v['showlist'] ?? 0,

                    );
                }
            }
        }
    }

    foreach($systemfiled as $k=>$v){
        if(!isset($sysformlist[$k])){
            $sysformlist[$k] = array(
                'flag' => $k,
                'name' => $systemfiled[$k]['labelname'],
                'show' => 1,
                'disp' => $systemfiled[$k]['disp'],
                'filedcat' => $systemfiled[$k]['filedcat'],
                'fsort' => 0,//排序
                'showpage' => 0,
                'type' => $systemfiled[$k]['type'],
                'writable' => 0,
                'tofolder' => 0,
                'style' => 0,//排列方式
                'appid' => $systemfiled[$k]['appid'],//属性
                'ison' => 0,//状态
                'allowsort'=>$systemfiled[$k]['allowsort']
            );
        }
    }

    Hook::listen('lang_parse',$formlist,['getFiledLangData',1]);
    //获取字段分类
    $filedcatdata = C::t('form_setting_filedcat')->fetch_all_by_tabgroupid($gid);
    $formlists = [];
    foreach ($filedcatdata as $k => $v) {
        foreach ($formlist as $key=>$val) {
            if($val['labelname']){
                $val['name'] = $val['labelname'];
                unset($val['labelname']);
            }
            if ($val['filedcat'] == $k) {
                $v['fileds'][] = $val;
                unset($formlist[$key]);
            }
        }
        $formlists[] = $v;
    }

    foreach($formlist as $nocatfileds){
        $formlists[] = array(
            'id' => 0,
            'catname' => lang('unclassify'),
            'fileds' => $nocatfileds,
            'disp'=>0
        );

    }

    $return = array('data' => $formlists, 'appdata' => $gdata,'tabgroupdatas'=>$tabgroupdatas,'tabformlist'=>$tabformlist,'filedType'=>$filedType,'sysformlist'=>$sysformlist);
    exit(json_encode($return));

}else{
    include template('page/list');
    exit();
}

function getsearchlistbysearchfiled($searchfiled,$gdata,$optionlimit=10){
    $fixedform = array(
        'cat' => array(
            'flag' => 'cat',
            'type' => 'select',
            'labelname' => lang('classify'),
            'system' => 1,
            'disp'=>0,
            'catname' => 'system',
            'multiple'=>0,
            'dw'=>'',
            'status'=>0,
            'isdefault' => 0,
            'allowsearch' => 1,
        ),
        'topping_at'=>array(
            'flag' => 'topping_at',
            'type' => 'bool',
            'labelname' => lang('topping'),
            'system' => 1,
            'disp'=>0,
            'catname' => 'system',
            'multiple'=>0,
            'dw'=>'',
            'status'=>0,
            'isdefault' => 0,
            'allowsearch' => 1,
        ),
        'is_recommed'=>array(
            'flag' => 'is_recommed',
            'type' => 'bool',
            'labelname' => lang('is_recommed'),
            'system' => 1,
            'disp'=>0,
            'catname' => 'system',
            'multiple'=>0,
            'dw'=>'',
            'status'=>0,
            'isdefault' => 0,
            'allowsearch' => 1,
        ),
        'number'=>array(
            'flag'=>'number',
            'show' => 1,
            'sort' => 0,
            'style' => 0,
            'status' => 0,
            'filedcat' => 0,
            'writable' => 1,
            'tofolder' => 0,
            'allowchange' => 1,
            'labelname'=>$gdata['aliasnumber'] ? $gdata['aliasnumber']:lang('tabnumber'),
            'type'=>'input',
            'disp'=>0,
            'allowsort'=>0,
            'allowlist'=>1
        )

    );

    $searchlist = unserialize($searchfiled);

    foreach($searchlist as $flag => $value){
        if(!$value['status']) {
            unset($searchlist[$flag]);
            continue;
        }
        else{
            if($fixedform[$flag]){
                $v= $fixedform[$flag];
                $v['multiple']=$value['multiple'];
                $v['range']=$value['range'];
                $v['dw']=$value['dw'];
                $v['dropmenu']=$value['dropmenu'];
            } elseif($form = C::t('form_setting')->fetch_by_flag($flag,$optionlimit)){
                $v= $form;
                $v['multiple']=$value['multiple'];
                $v['range']=$value['range'];
                $v['dw']=$value['dw'];
                $v['dropmenu']=$value['dropmenu'];
            }
        }
        $v['status'] = $value['status'];
        $v['times'] = $value['times'] ? $value['times'] : [];
        $v['way'] = $value['way'] ? $value['way'] : 0;
        $searchlist[$flag]=$v;
    }
    Hook::listen('lang_parse',$searchlist,['getFiledLangData',1]);
    return $searchlist;
}
function parserichtextdata($data)
{
$pattern = "/(https?:\/\/)?\w+\.\w+\.\w+\.\w+?(:[0-9]+)?\/index\.php\?mod=io&amp;op=getfileStream&amp;path=(.+)/";
$data = preg_replace_callback($pattern, function ($matchs) {

    return 'index.php?mod=io&op=getfileStream&path=' . $matchs[3];

}, $data);

$data = preg_replace_callback('/path=(\w+)&amp;aflag=(attach::\d+)/', function ($matchs) {
    if (isset($matchs[2])) {
        return 'path=' . dzzencode($matchs[2], '', 0);
    }

}, $data);
//$data=htmlspecialchars_decode($data);

return $data;
}

