<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
global $_G;
$uid = $_G['uid'];
$do=$_GET['do'];
$now = dgmdate(TIMESTAMP, 'Y-m-d');
$actionData = array(
    'all' => array('key' => 'all', 'name' => lang('all'), 'value' => ''),
    'day1' => array('key' => 'day1', 'name' => lang('filter_range_day'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60, 'Y-m-d') . '_' . $now),
    'week' => array('key' => 'week', 'name' => lang('filter_range_week'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 7, 'Y-m-d') . '_' . $now),
    'month' => array('key' => 'month', 'name' => lang('filter_range_month'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 30, 'Y-m-d') . '_' . $now),
    'year' => array('key' => 'year', 'name' => lang('filter_range_year'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 365, 'Y-m-d') . '_' . $now),
);
if($do=='checkpassword'){
    $sid=dzzdecode($_GET['sid'],'',0);
    $password=$_GET['password'];
    if(!$sharedata=C::t('pichome_share')->fetch($sid)){
        exit(json_encode(array('success'=>false,'msg'=>lang('share_file_iscancled'))));
    }
    if($sharedata['password']==$_GET['password']){
        dsetcookie('share_pass_' . $sid, authcode($_GET['password'], 'ENCODE'));
        $viewurl=C::t('pichome_share')->getViewUrl($sharedata);
        C::t('pichome_share')->add_views_by_id($sid);
        exit(json_encode(array('success'=>true,'viewurl'=>$viewurl)));
    }else{
        return array('success'=>false,'msg'=>lang('password_share_error'));
    }

}

if($do=='delete') {
    $id = intval($_GET['id']);
    if (!$data = C::t('pichome_share')->fetch($id)) {
        exit(json_encode(array('success' => false, 'msg' => lang("share_file_iscancled"))));
    }
    //判断权限
    if($data['uid']!=$_G['uid'] && $_G['adminid']!=1){
        exit(json_encode(array('success' => false,'msg'=>lang('no_privilege'))));
    }
    if (C::t('pichome_share')->delete($id)) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('success' => false, 'msg' => lang("delete_unsuccess"))));
    }
} elseif ($do == 'getShareUser') {//获取分享用户列表
    $q=trim($_GET['q']);
    $params=['pichome_share'];
    $sql='1';
    if($q){
        $sql.=" and username LIKE %s";
        $params[]='%'.$q.'%';
    }
    foreach(DB::fetch_all("select uid ,username from %t where $sql group by uid order by username asc limit 100",$params) as $value){

        $ret[$value['uid']]=array('uid'=>intval($value['uid']),'username'=>$value['username']);
    }
    exit(json_encode(array('success'=>true,'data'=>array_values($ret))));
} elseif ($do == 'getShareData') {//获取链接二维码
    $sid=intval($_GET['sid']);
    if(!$data=C::t('pichome_share')->fetch($sid)){
        exit(json_encode(array('success'=>false,'msg'=>'Share not found')));
    }
    $data['fendtime']='';

    if($data['endtime']){
        $data['fendtime']=dgmdate($data['endtime'], 'Y-m-d');
    }
    $ret=array(
        'sid'=>$data['id'],
        'title'=>$data['title'],
        'times'=>intval($data['times']),
        'password'=>$data['password'],
        'fendtime'=>$data['fendtime'],
        'perm'=>perm::check('download2',intval($data['perm']))?true:false,
    );
    exit(json_encode(['success' => true, 'data' => $ret]));
} elseif ($do == 'getShareDataById') {//获取分享数据
    $id=trim($_GET['id']);
    $stype=intval($_GET['stype']);
    $uid=$_G['uid'];
    $sharedata=array();
    if(!$data=DB::fetch_first("select * from %t where uid=%d and stype=%d and filepath=%s",array('pichome_share',$uid,$stype,$id))){

        switch ($stype){
            case 0://文件
                if($resourcedata= C::t('pichome_resources')->fetch($id)){
                    Hook::listen('lang_parse', $resourcedata,['getResourcesLangData']);
                    $data['title']=$resourcedata['name'];
                }
            break;
            case 1://收藏夹文件
                if($cdata = C::t('pichome_collectlist')->fetch($id) ){
                    $resource= C::t('pichome_resources')->fetch($cdata['rid']);
                    Hook::listen('lang_parse', $resource,['getResourcesLangData']);
                    $data['title']=$resource['name'];
                }

                break;
            case 2://收藏夹
                if($cdata = C::t('pichome_collect')->fetch($id)){
                    Hook::listen('lang_parse', $cdata,['getCollectcatLangData']);
                    $data['title']=$cdata['name'];
                }

                break;
            case 3: //专辑
                $tabstatus = 0;
                Hook::listen('checktab', $tabstatus);
                if($tabstatus){
                    if($cdata = C::t('#tab#tab')->fetch($id)){
                        Hook::listen('lang_parse', $cdata,['getTablangData']);
                        $data['title']=$cdata['tabname'];
                    }
                }


                break;
        }
        $data['perm']=7;
        $sharedata['sid']=0;
    }else{

        $sharedata=C::t('pichome_share')->fetch_by_sid($data['id']);
        $sharedata['sid']=$sharedata['id'];
        $sharedata['perm']=intval($sharedata['perm']);

        $data['title']=$sharedata['title'];
        $data['password']=$sharedata['password'];
        $data['endtime']=$sharedata['endtime'];
        $data['perm']=intval($sharedata['perm']);
        $data['sid']=$sharedata['id'];

    }

    $fendtime='';

    if($data['endtime']){
        $fendtime=dgmdate($data['endtime'], 'Y-m-d');
    }
    $ret=array(
        'sid'=>$data['id'],
        'id'=>$id,
        'stype'=>$stype,
        'title'=>$data['title'],
        'times'=>intval($data['times']),
        'password'=>$data['password'],
        'fendtime'=>$fendtime,
        'perm'=>perm::check('download2',intval($data['perm']))?true:false,
    );
    exit(json_encode(['success' => true, 'data' => $ret,'sharedata'=>$sharedata]));
} elseif ($do == 'shareEditSubmit') {//编辑分享提交
    $sid = intval($_GET['sid']);
    $title = isset($_GET['title']) ? getstr($_GET['title']) : '';
    $password = isset($_GET['password']) ? trim($_GET['password']) : '';
    $endtime = isset($_GET['fendtime']) ? strtotime($_GET['fendtime']) : 0;
    $perm = ($_GET['perm']=='true') ? 1 : 0;
    $setarr = array(
        'title' => $title,
        'times'=>intval($_GET['times']),
        'password' => $password,
        'endtime' => $endtime,
        'perm' => $perm?perm::setPerm(['read1','read2','download1','download2'],1):7,
    );
    C::t('pichome_share')->update($sid, $setarr);
    $data=C::t('pichome_share')->fetch_by_sid($sid);

    exit(json_encode(['success' => true,'data'=>$data]));
} elseif ($do == 'shareAddSubmit') {//创建分享提交

    $title = isset($_GET['title']) ? getstr($_GET['title']) : '';
    if(empty($title)){
        exit(json_encode(['success' => false, 'msg' => lang('share_title_empty')]));
    }
    $stype=intval($_GET['stype']);
    if($stype>0){//非文件ID
        $id=intval($_GET['id']);
    }else{//文件rid
        if(!preg_match("/^\w{32}$/i",$_GET['id'])){
            exit(json_encode(['success' => false, 'msg' => lang('param rid error')]));
        }
        $id=htmlspecialchars($_GET['id']);
    }
    $password = isset($_GET['password']) ? trim($_GET['password']) : '';
    $endtime = isset($_GET['fendtime']) ? strtotime($_GET['fendtime']) : 0;

    $perm = ($_GET['perm']=='true') ? 1 : 0;

    $params = array(
        'title' => $title,
        'times'=>intval($_GET['times']),
        'password' => $password,
        'endtime' => $endtime,
        'perm' => $perm?perm::setPerm(['read1','read2','download1','download2'],1):7,
    );

    if(!$data=C::t('pichome_share')->add_share($id,$stype, $params)){
        exit(json_encode(['success' => false, 'msg' => lang('share add error')]));
    }

    exit(json_encode(['success' => true,'data'=>$data]));

} elseif ($do == 'geturlqrcode') {//获取链接二维码
    $sid = isset($_GET['sid']) ? intval($_GET['sid']) : 0;
    $qrcode = C::t('pichome_share')->getQRcodeBySid($sid);
    exit(json_encode(['success' => true, 'qrcode' => $qrcode]));
}elseif($do == 'filelist'){
	
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
	$wheresql = ' uid = %d ';
	$params=array('pichome_share',$uid);
	if($keyword){
		$wheresql .= ' and title like %s ';
        $params[]='%'.$keyword.'%';
	}
    if($status){
        $wheresql .= ' and status = %d ';
        $params[]=$status==1?0:$status;
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
}