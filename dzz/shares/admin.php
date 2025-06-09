<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$navtitle=lang('shareManagement');
$appname=lang('shareManagement');
$do=$_GET['do'];
Hook::listen('admin_login');//检查是否登录，未登录跳转到登录界面
$now = dgmdate(TIMESTAMP, 'Y-m-d');
$actionData = array(
    'all' => array('key' => 'all', 'name' => lang('all'), 'value' => ''),
    'day1' => array('key' => 'day1', 'name' => lang('filter_range_day'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60, 'Y-m-d') . '_' . $now),
    'week' => array('key' => 'week', 'name' => lang('filter_range_week'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 7, 'Y-m-d') . '_' . $now),
    'month' => array('key' => 'month', 'name' => lang('filter_range_month'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 30, 'Y-m-d') . '_' . $now),
    'year' => array('key' => 'year', 'name' => lang('filter_range_year'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 365, 'Y-m-d') . '_' . $now),
);
$statusData=array(
    '1'=>['key'=>1,'label'=>lang('share_status_0')],
    '0'=>['key'=>0,'label'=>lang('all')],
    '-1'=>['key'=>-1,'label'=>lang('share_status_-1')],
    '-2'=>['key'=>-2,'label'=>lang('share_status_-2')],
   // '-3'=>['key'=>-3,'label'=>lang('share_status_-3')]
  );
$stypeData=array(
    ['key'=>-1,'label'=>lang('all')],
    ['key'=>0,'label'=>lang('share_stype_0')],
    ['key'=>1,'label'=>lang('share_stype_1')],
    ['key'=>2,'label'=>lang('share_stype_2')],
    ['key'=>3,'label'=>lang('share_stype_3')],
    // '-3'=>['key'=>-3,'label'=>lang('share_status_-3')]
);
if($do == 'filelist'){

    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 10;//每页数量
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;//页码数
    $start = ($page - 1) * $perpage; //开始条数
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):0;
    $status = isset($_GET['status']) ? intval($_GET['status']):0;
    if($limit){
        //计算开始位置
        $start = $start+$perpage - $limit;
        $perpage = $limit;
    }
    $order = isset($_GET['order']) ? trim($_GET['order']) : 'desc';
    $orderby = isset($_GET['orderby']) ? trim($_GET['orderby']) : 'dateline';
    if(!in_array($orderby,array('dateline'))) $orderby = 'dateline';
    $ordersql="order by $orderby $order";
    $limitsql = "limit $start,$perpage";
    $total = 0; //总条数
    $keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '';
    $wheresql = ' 1 ';
    $params=array('pichome_share');
    if($keyword){
        $wheresql .= ' and title like %s ';
        $params[]='%'.$keyword.'%';
    }
    if($status){
        $wheresql .= ' and status = %d ';
        $params[]=$status==1?0:$status;
    }
    if($_GET['uids']){
        $wheresql .= " and uid IN(%n)";
        $params[]=$_GET['uids'];
    }
    $stype=isset($_GET['stype'])?intval($_GET['stype']):-1;
    if($stype>-1){
        $wheresql .= " and stype=%d";
        $params[]=$stype;
    }
    $dataActive = isset($_GET['date']) ? trim($_GET['date']) : 'all';
    if($dataActive=='custom'){
        $starttime=$_GET['starttime']?strtotime($_GET['starttime']):0;
        $endtime=$_GET['endtime']?strtotime($_GET['endtime']):0;
        if ($starttime) {
            $wheresql .= " and dateline >= %d";
            $params[] = $starttime;
        }
        if ($endtime) {
            $wheresql .= " and dateline >= %d";
            $params[] = $endtime+24*60*60;
        }
    }else{
        $date = $actionData[$dataActive]['value'];
        if ($date) {
            $dateline = explode('_', $date);
            if ($dateline[0]) {
                $wheresql .= " and dateline >= %d";
                $params[] = strtotime($dateline[0]);
            }
            if ($dateline[1]) {
                $wheresql .= " and dateline < %d";
                $params[] = strtotime($dateline[1]) + 24 * 60 * 60;
            }
        }
    }

    $data = [];

    if($count = DB::result_first("select COUNT(*) from %t where $wheresql",$params)) {
        foreach (DB::fetch_all("select id from %t where $wheresql $ordersql $limitsql", $params) as $v) {
            $v = C::t('pichome_share')->fetch_by_sid($v['id']);
            $data[] = $v;
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
        'page'=>$page,
        'perpage' => $perpage,
        'total' => $count,
    );
    exit(json_encode(['success'=>true,'data'=>$return]));
}else{
    include template('pc/page/admin');
    exit();
}

