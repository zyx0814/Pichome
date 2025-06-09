<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$navtitle = lang("my_downloads");
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$do = $_GET['do'];
$now = dgmdate(TIMESTAMP, 'Y-m-d');
$actionData = array(
    'all' => array('key' => 'all', 'name' => lang('all'), 'value' => ''),
    'day1' => array('key' => 'day1', 'name' => lang('filter_range_day'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60, 'Y-m-d') . '_' . $now),
    'day3' => array('key' => 'day3', 'name' => lang('filter_range_three_days'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 3, 'Y-m-d') . '_' . $now),
    'week' => array('key' => 'week', 'name' => lang('filter_range_week'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 7, 'Y-m-d') . '_' . $now),
    'month' => array('key' => 'month', 'name' => lang('filter_range_month'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 30, 'Y-m-d') . '_' . $now),
    'year' => array('key' => 'year', 'name' => lang('filter_range_year'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 365, 'Y-m-d') . '_' . $now),
);
if ($do == 'filelist') {

    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 150;//每页数量
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;//页码数
    $start = ($page - 1) * $perpage; //开始条数
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
    if ($limit) {
        //计算开始位置
        $start = $start + $perpage - $limit;
        $perpage = $limit;
    }
    $limitsql = "limit $start,$perpage";
    $total = 0; //总条数
    $keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '';
    $wheresql = ' idtype = %d and uid = %d';
    $params = ['stats_view', 1, $uid];
    $para = [];
    if ($keyword) {
        $wheresql .= ' and name like %s ';
        $param[] = '%' . $keyword . '%';
    }
    $dataActive = isset($_GET['date']) ? trim($_GET['date']) : 'all';
    $date = $actionData[$dataActive]['value'];
    if ($date) {
        $dateline = explode('_', $date);
        if ($dateline[0]) {
            $wheresql .= " and dateline >= %d";
            $param[] = strtotime($dateline[0]);
        }
        if ($dateline[1]) {
            $wheresql .= " and dateline < %d";
            $param[] = strtotime($dateline[1]) + 24 * 60 * 60;
        }
    }

    if ($param) $params = array_merge($params, $param);
    $count = DB::result_first("select count(id) from %t where $wheresql", $params);
    $data = [];
    foreach (DB::fetch_all("select id,idval,name,dateline from %t where $wheresql order by id desc $limitsql", $params) as $v) {
        $icondata = C::t('pichome_resources')->getdatasbyrids($v['idval']);
        if($icondata[0]['rid'] && ($icondata[0]['isdelete'] < 1)){
            $icondata[0]['fdate'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
            $icondata[0]['id'] = $v['id'];
            $icondata[0]['isdelete'] = 0;
            $data[] = $icondata[0];
        }else{
            $icondata[0]['fdate'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
            $icondata[0]['id'] = $v['id'];
            $icondata[0]['name'] = $v['name'];
            $icondata[0]['isdelete'] = 1;
            $data[] = $icondata[0];
        }
    }
    if ($count > $perpage * $page) {
        $next = true;
    } else {
        $next = false;
    }
    $return = array(
        'next' => $next,
        'data' => $data,
        'total' => $count,
    );
    exit(json_encode(['success' => true, 'data' => $return]));
    //$data = json_encode($data);


} elseif ($do == 'delete') {
    $id = intval($_GET['id']);
    if (C::t('stats_view')->delete($id)) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('success' => false, 'msg' => lang('delete_unsuccess'))));
    }
} else {

    $ismobile = helper_browser::ismobile();
    if ($ismobile) {
        include template('mobile/page/down');
    } else {
        include template('pc/page/down');

    }
}