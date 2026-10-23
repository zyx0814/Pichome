<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$navtitle="发布搜索";
$do=isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'getsearchkeyword') {//热搜关键词
    $cachetime = 3600;
    $id = isset($_GET['id']) ? intval($_GET['id']) : '';

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 10;
    $cachename = 'SEARCHHOTKEYWORD_PUBLISH_' . $id . '_' . $page;
    $hotdatas = false;
    $hotdatas = C::t('cache')->fetch_cachedata_by_cachename($cachename, $cachetime);
    if (!$hotdatas) {
        $hotdatas = C::t('keyword_hots')->fetch_by_idtype(4, $id, $page, $perpage);
        if ($hotdatas) {
            $setarr = ['cachekey' => $cachename, 'cachevalue' => serialize($hotdatas), 'dateline' => time()];
            C::t('cache')->insert_cachedata_by_cachename($setarr, $cachetime, 1);
        }
    }
    if (!$hotdatas) {
        $hotdatas=array(array('keyword'=>'第一个','nums'=>10),array('keyword'=>'第二个','nums'=>10));
    }
    exit(json_encode(array('success' => true, 'data' => $hotdatas)));
}elseif($do == 'getCollectList') {
    $limit = 20;

    $data = [];
    $sql = "pstatus=1 and ptype=6 ";
    $params = array('publish_list');

    foreach (DB::fetch_all("select * from %t where $sql limit $limit", $params) as $v) {
        $data[$v['id']] = array('id' => $v['id'], 'ptype' => $v['ptype'], 'name' => $v['pname']);
    }
    exit(json_encode(['success' => true, 'data' => array_values($data)]));
}elseif($do == 'getLatest') {//获取搜索首页最热发布
    $limit = $_GET['limit'] ? intval($_GET['limit']) : 10;
    $data = array();
    $sql = " p.pstatus='1' and ptype!=6 and v.idtype='6' ";
    $param = array( 'publish_list','stats_view');

    foreach (DB::fetch_all("select p.id,count(*) as sum from %t p  LEFT JOIN %t v  on p.id=v.idval  where $sql group by v.idval  order by sum DESC  limit $limit", $param) as $value) {
        $sum=$value['sum'];
        $value = C::t('publish_list')->fetch_by_id($value['id']);
        $value['dateline'] = dgmdate($value['dateline'], 'Y-m-d H:I:s');
        $value['hots']=$sum;
        if ($value['pageset']['_file_cover'][0]['src']) {
            $value['img'] = $value['pageset']['_file_cover'][0]['src'];
        }

        //处理地址
        $url = 'index.php?mod=publish&id=' . $value['id'];
        $value['address'] = C::t('pichome_route')->update_path_by_url($url, $value['address']);
        if (strpos($value['url'], 'http') === false) {
            $value['url'] = $_G['siteurl'] . $value['address'];
        } else {
            $value['url'] = $_G['siteurl'] . $url;
        }

        $data[$value['id']] = $value;
    }
    exit(json_encode(['success' => true, 'data' => array_values($data)]));

}elseif($do == 'getPublishList') {
    $page = $_GET['page'] ? intval($_GET['page']) : 1;
    $perpage = $_GET['perpage'] ? intval($_GET['perpage']) : 20;
    $start = ($page - 1) * $perpage;
    $pid = $_GET['id'] ? intval($_GET['id']) : 0;
    $sql = "p.pstatus='1'";
    $param = array( 'publish_list','publish_relation');
    $allowed_orderby = ['dateline', 'title', 'cview', 'cdownload'];
    $allowed_order   = ['ASC', 'DESC'];
    $orderby = (is_string($_GET['orderby'] ?? null)) ? $_GET['orderby'] : 'dateline';
    $order   = strtoupper((is_string($_GET['order'] ?? null)) ? $_GET['order'] : 'DESC');
    if (!in_array($orderby, $allowed_orderby, true)) {
        $orderby = 'dateline';
    }
    if (!in_array($order, $allowed_order, true)) {
        $order = 'DESC';
    }

    if($pid){
        $sql .= " and r.rpid =%d";
        $param[]=$pid;
    }
    if ($_GET['keyword']) {
        $keyword=trim($_GET['keyword']);
        $sql .= " and (p.pname like %s OR p.metakeywords like %s OR p.metadescription like %s)";
        $param[] = '%' . $keyword . '%';
        $param[] = '%' . $keyword . '%';
        $param[] = '%' . $keyword . '%';
    }
    $ordersql = "order by p.$orderby $order";
    $data = array();
    if ($count = DB::result_first("select COUNT(DISTINCT p.id) from %t p LEFT JOIN %t r on r.pid=p.id where $sql", $param)) {
        foreach (DB::fetch_all("select DISTINCT p.id from %t p LEFT JOIN %t r on r.pid=p.id where $sql $ordersql limit $start,$perpage", $param) as $value) {

            $value = C::t('publish_list')->fetch_by_id($value['id']);
            $value['dateline'] = dgmdate($value['dateline'], 'Y-m-d H:I:s');
            if ($value['pageset']['_file_cover'][0]['src']) {
                $value['img'] = $value['pageset']['_file_cover'][0]['src'];
            }

            //处理地址
            $url = 'index.php?mod=publish&id=' . $value['id'];
            $value['address'] = C::t('pichome_route')->update_path_by_url($url, $value['address']);
            if (strpos($value['url'], 'http') === false) {
                $value['url'] = $_G['siteurl'] . $value['address'];
            } else {
                $value['url'] = $_G['siteurl'] . $url;
            }
            $data[$value['id']] = $value;

        }
    }
    exit(json_encode(array('success' => true, 'data' => array_values($data), 'total' => intval($count), 'page' => $page, 'perpage' => $perpage)));
}elseif($do=='search'){
    $id=intval($_GET['id']);
    $keyword=trim($_GET['keyword']);
    if($id){
        $pdata=C::t('publish_list')->fetch($id);
    }

    include template('search/page/list');
    exit();
}else {
    include template('search/page/index');
    exit();
}
