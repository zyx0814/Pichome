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
    $wheresql = " t.gid = %d  and t.isdelete = 0 ";
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
    $keyword = isset($_GET['S_keyword']) ? htmlspecialchars($_GET['S_keyword']) : '';
    if ($keyword) {
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
    $searchlist = getsearchlistbysearchfiled($gdata['searchfiled']);
    $wheresql1 = array();
    foreach ($formflags as $flag => $val) {
        if ($val == 'all' || $val == lang('unlimited')) continue;
        $form = $searchlist[$flag];
        $arr = array();
        switch ($form['type']) {
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
            case 'timerange'://多值日期搜索
                $dateline = explode('_', $val);
                $arr = [];
                $sql .= " LEFT JOIN %t rd_$flag ON rd_$flag.tid=t.tid and rd_$flag.filedname=%s ";
                $params[] = 'tab_rangedate';
                $params[] = $flag;
                $dateline = explode('_', $val);

                $arr = [];
                if ($dateline[0]) {
                    $arr[] = " rd_$flag.start >= %d ";
                    $para[] = strtotime($dateline[0].'-01-01');
                
                }
                $dateline[1] = $dateline[0]+1;
                if ($dateline[1]) {
                    $arr[] = " rd_$flag.end <= %d ";
                    //$para[] = strtotime($dateline[1]) + 24 * 60 * 60;
                    $para[] = strtotime($dateline[1].'-01-01');
                }
            
                if ($arr) {
                    $wheresql .= "  and (" . implode(" and ", $arr) . ")";
                }

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
                if ($arr) {
                    $wheresql .= " and (" . implode(" and ", $arr) . ")";
                }

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

                if ($arr) {
                    $wheresql .= "  and (" . implode(" OR ", $arr) . ")";
                }
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
        $sortfilearr = ['dateline', 'sumnum', 'updatedate'];
        if ($sortdata) {
            $sortdatarr = unserialize($sortdata);
            $disp = $sortdatarr['filed'];
            $asc = $sortdatarr['sort'] ? 'asc' : 'desc';
        } else {
            $disp = 'dateline';
            $asc = 'desc';
        }
    }
    $groupsql = ' group by t.tid ';
    switch ($disp){
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
    $ordersql = " order by $order $asc ";
    if($order != 't.tid') $ordersql .= ' ,t.tid asc ';
    $tids = [];
    if(!$dispfiled) $dispfiled = 't.'.$disp;
    $sum = DB::result_first("SELECT count(distinct t.tid) as num  $sql where $wheresql  ",
        array_merge($params, $para));
    foreach (DB::fetch_all("SELECT t.tid,$dispfiled  $sql where $wheresql $groupsql $ordersql $limitsql",
        array_merge($params, $para)) as $value) {

        $tids[] = $value['tid'];
    }
    /*//增加统计关键词次数
    if($tids && $keyword){
        $statskeywords = array();
        $arr1 = explode('+', $keyword);
        foreach($arr1 as $v){
            $arr2 = explode(' ', $value1);
            foreach($arr2 as $kval){
                addTabgroupkeywordStats($kval,$gid);

            }
        }

    }*/
    $data = C::t('#tab#tab')->fetch_by_tids($tids);
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
}elseif($do == 'getscreen'){//获取筛选项
    $cid = isset($_GET['cid']) ? intval($_GET['cid']):0;
    $ExpandedCids = [];
    if($cid){
        $catdata = C::t('#tab#tab_cat')->fetch($cid);
        $catdata['pathkey'] = str_replace('-_','',$catdata['pathkey']);
        $catdata['pathkey'] = trim($catdata['pathkey'],'_');
        $ExpandedCids=explode('_',$catdata['pathkey']);
    }
    $searchlist = getsearchlistbysearchfiled($gdata['searchfiled']);
    //获取时间范围年份筛选值
    foreach($searchlist as $k=>$v){
        if($v['type'] =='timerange'){
            $yeardata = [];
            foreach(DB::fetch_all("select DISTINCT YEAR(FROM_UNIXTIME(`start`)) AS year from %t where filedname = %s order by year",
            array('tab_rangedate',$k)) as $sy){
                $yeardata[] = $sy['year'];
            }
            foreach(DB::fetch_all("select DISTINCT YEAR(FROM_UNIXTIME(`end`)) AS year from %t where filedname = %s order by year",
                array('tab_rangedate',$k)) as $ey){
                $yeardata[] = $ey['year'];
            }
            $yeardata = array_unique($yeardata);
            sort($yeardata);
            $searchlist[$k]['timerangeyear'] = $yeardata;
        }
    }
    exit(json_encode(array('data'=>$searchlist,'expandedcids'=>$ExpandedCids)));
}
elseif ($do == 'getsearchfolder') {//分类数据
    $pcid = isset($_GET['pcid']) ? intval($_GET['pcid']):0;
    $catdatanum = C::t('#tab#tab_cat')->fetch_all_cat_by_gid($gid,$pcid);
    exit(json_encode(array( 'data' => $catdatanum)));
}else{
    include template('page/list');
    exit();
}
function getsearchlistbysearchfiled($searchfiled){
    $fixedform = array(
        'cat' => array(
            'flag' => 'cat',
            'type' => 'select',
            'labelname' => '分类',
            'system' => 1,
            'disp'=>0,
            'catname' => 'system',
            'multiple'=>0,
            'dw'=>'',
            'status'=>0,
            'isdefault' => 0,
            'allowsearch' => 1,
        )
    );
    $searchlist = unserialize($searchfiled);
    foreach($searchlist as $flag => $value){
        //if(!$value['status']) continue;
        if($form = c::t('form_setting')->fetch($flag)){
            $v= $form;
            $v['multiple']=$value['multiple'];
            $v['range']=$value['range'];
            $v['dw']=$value['dw'];
            $v['dropmenu']=$value['dropmenu'];
        }elseif(isset($fixedform[$flag])){
            $v= $fixedform[$flag];
            $v['multiple']=$value['multiple'];
            $v['range']=$value['range'];
            $v['dw']=$value['dw'];
            $v['dropmenu']=$value['dropmenu'];
        }

        $v['status'] = $value['status'];
        $searchlist[$flag]=$v;
    }
    return $searchlist;
}

