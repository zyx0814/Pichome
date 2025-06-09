<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/14
 * Time: 21:33
 */
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
if(!defined('IS_API')) define('IS_API',true);


Hook::listen('adminlogin');
global $_G;
$do = isset($_GET['do']) ? trim($_GET['do']):'';
$day = isset($_GET['day']) ? trim($_GET['day']):'';
$after = isset($_GET['after']) ? $_GET['after']:'';
$before = isset($_GET['before']) ? $_GET['before']:'';
$isadmin = ($_G['adminid'] == 1) ? true:false;
$lang = '';
//检查是否开启语言包
Hook::listen('lang_parse', $lang, ['checklang']);
if($day){
    if($day == 'current'){
        $start = strtotime(dgmdate(TIMESTAMP,'Y-m-d'));
        $end =  $start+60*60*24;
    }elseif($day == -1){
        $start = strtotime(date('Y-m-d',strtotime($day.'day')));
        $end = $start+60*60*24;
    }else{
        $start = strtotime(date('Y-m-d',strtotime($day.'day')));
        $end = strtotime(dgmdate(TIMESTAMP,'Y-m-d'))+60*60*24;
    }

}
elseif ($after || $before){
    if($after){
        $start = strtotime($after);
    }
    if($before){
        $end = strtotime($before)+60*60*24;
    }
}
$navtitle = lang('app_name').'-'.$_G['setting']['sitename'];

if($do == 'downloads'){//下载统计
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $keytype = isset($_GET['keytype']) ? trim($_GET['keytype']):'filename';
    $order = $_GET['order'] ? intval($_GET['order']):1;
    $disp = $_GET['disp'] ? intval($_GET['disp']):0;
    $params =array('stats_view','pichome_resources',1);
    $whereparams = array();
    $wheresql = ' s.idtype = %d ';
    $disp = $disp ? 'desc':'asc';
    $next = false;
    $ordersql = '';
    switch ($order){
        case 1:
            $ordersql = 'order by s.dateline '.$disp;
            break;
        case 2:
            $ordersql = 'order by s.username '.$disp;
            break;
        case 3:
            $ordersql = 'order by s.ip '.$disp;
            break;
    }
    if($start){
        $params[] = $start;
        $wheresql .= " and s.dateline >= %d";
    }
    if($end){
        $params[] = $end;
        $wheresql .= " and s.dateline < %d";
    }
    if($keyword){
        switch($keytype){
            case 'filename':
                $wheresql .= ' and s.name like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'username':
                $wheresql .= ' and s.username like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'ip':
                $wheresql .= ' and s.ip = %s';
                $params[] = $keyword;
                break;
            default:
                $wheresql .= ' and s.name like %s';
                $params[] = '%'.$keyword.'%';
        }
    }
    $count = DB::result_first("select count(id) from %t s  left join %t r on r.rid = s.idval  where $wheresql",$params);
    $limitstart = ($page - 1)*$limit;
    $limitsql = ' limit '.$limitstart.','.$limit;
    $data = array();
    foreach(DB::fetch_all("select s.* from %t s left join %t  r on s.idval = r.rid where $wheresql $ordersql $limitsql",
        $params) as $v){
        if($resourcesdata = C::t('pichome_resources')->fetch_by_rid($v['idval'])){
            unset($resourcesdata['dateline']);
            $v['filename']=$v['name'];
            unset($resourcesdata['username']);
            $v['viewperm'] = 1;
            $v['downloadperm'] = $resourcesdata['download'];
            $v['opensrc'] = $resourcesdata['opensrc'];
            $v['img'] = $resourcesdata['icondata'];
            $v['url'] = $resourcesdata['opensrc'];
            $v['position'] = DB::result_first("select appname from %t where appid = %s",['pichome_vapp',$resourcesdata['appid']]);
        }else{
            $v['rid']='';
            $v['viewperm'] = 0;
            $v['downloadperm'] = 0;
            $v['filename']=$v['name'];
            $pathinfo=pathinfo($v['filename']);
            $v['img']=geticonfromext($pathinfo['extension']);
            $v['url']=geticonfromext($pathinfo['extension']);
            $v['type']='attach';
            $v['position'] = '';

        }
        $v['fdateline'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
        $v['dateline'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
        $data[] = $v;
    }
    if($count >= ($limitstart+$limit)){
        $next = true;
    }
    exit(json_encode(array('data'=>$data,'next'=>$next,'total'=>$count,'page'=>$page,'isadmin'=>$isadmin,'navtitle'=>lang('downloadfiletitle').'-'.$navtitle)));
}
elseif($do == 'downloadsbyfilename'){//下载统计(以文件纬度统计)
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $keytype = isset($_GET['keytype']) ? trim($_GET['keytype']):'filename';
    $order = $_GET['order'] ? intval($_GET['order']):2;
    $disp = $_GET['disp'] ? intval($_GET['disp']):0;
    $params =array('stats_view',1);
    $whereparams = array();
    $wheresql = ' idtype = %d';
    $disp = $disp ? 'desc':'asc';
    $next = false;
    $ordersql = '';
    switch ($order){
        case 1:
            $ordersql = 'order by dateline '.$disp;
            break;
        case 2:
            $ordersql = 'order by num '.$disp;
            break;
        case 3:
            $ordersql = 'order by ip '.$disp;
            break;
        default :
            $ordersql = 'order by num '.$disp;
            break;
    }
    if($start){
        $params[] = $start;
        $wheresql .= " and dateline >= %d";
    }
    if($end){
        $params[] = $end;
        $wheresql .= " and dateline < %d";
    }
    if($keyword){
        switch($keytype){
            case 'filename':
                $wheresql .= ' and name like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'username':
                $wheresql .= ' and username like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'ip':
                $wheresql .= ' and ip = %s';
                $params[] = $keyword;
                break;
            default:
                $wheresql .= ' and name like %s';
                $params[] = '%'.$keyword.'%';
        }
    }

    $count = DB::result_first("select count(distinct  idval) from %t  where $wheresql ",$params);
    $filedowntotal = DB::result_first("select count(id) from %t  where $wheresql ",$params);
    $limitstart = ($page - 1)*$limit;
    $limitsql = ' limit '.$limitstart.','.$limit;
    $data = array();

    foreach(DB::fetch_all("select SUBSTRING_INDEX(GROUP_CONCAT(username),',',1) as username,SUBSTRING_INDEX(GROUP_CONCAT(name),',',1) as filename,SUBSTRING_INDEX(GROUP_CONCAT(dateline ORDER BY dateline desc),',',1) as dateline,
 SUBSTRING_INDEX(GROUP_CONCAT(ip ORDER BY dateline desc),',',1) as ip,SUBSTRING_INDEX(GROUP_CONCAT(idval),',',1) as rid,
count(id) as num from %t  where $wheresql group by idval $ordersql $limitsql",
        $params) as $v){


        if($resourcesdata = C::t('pichome_resources')->fetch_by_rid($v['idval'])){
            unset($resourcesdata['dateline']);
            $v['filename']=$v['name'];
            unset($resourcesdata['username']);
            $v['viewperm'] = 1;
            $v['downloadperm'] = $resourcesdata['download'];
            $v['opensrc'] = $resourcesdata['opensrc'];
            $v['img'] = $resourcesdata['icondata'];
            $v['url'] = $resourcesdata['opensrc'];
            $v['position'] = DB::result_first("select appname from %t where appid = %s",['pichome_vapp',$resourcesdata['appid']]);
        }else{
            $v['rid']='';
            $v['viewperm'] = 0;
            $v['downloadperm'] = 0;
            $pathinfo=pathinfo($v['filename']);
            $v['img']=geticonfromext($pathinfo['extension']);
            $v['url']=geticonfromext($pathinfo['extension']);
            $v['type']='attach';

        }
        $v['fdateline'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
        $v['dateline'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
        $data[] = $v;
    }
    //print_r($data);die;
    if($count >= ($limitstart+$limit)){
        $next = true;
    }

    exit(json_encode(array('data'=>$data,'next'=>$next,'total'=>$count,'alltotal'=>$filedowntotal,'page'=>$page,'isadmin'=>$isadmin,'navtitle'=>lang('downloadfiletitle').'-'.$navtitle)));
}
elseif($do == 'downloadsbyusername'){//下载统计(以用户纬度统计)
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $keytype = isset($_GET['keytype']) ? trim($_GET['keytype']):'filename';
    $order = $_GET['order'] ? intval($_GET['order']):2;
    $disp = $_GET['disp'] ? intval($_GET['disp']):0;
    $params =array('stats_view',1);
    $whereparams = array();
    $wheresql = ' idtype = %d ';
    $disp = $disp ? 'desc':'asc';
    $next = false;
    $ordersql = '';
    switch ($order){
        case 1:
            $ordersql = 'order by dateline '.$disp;
            break;
        case 2:
            $ordersql = 'order by num '.$disp;
            break;
        case 3:
            $ordersql = 'order by ip '.$disp;
            break;
    }
    if($start){
        $params[] = $start;
        $wheresql .= " and dateline >= %d";
    }
    if($end){
        $params[] = $end;
        $wheresql .= " and dateline < %d";
    }
    if($keyword){
        switch($keytype){
            case 'filename':
                $wheresql .= ' and name like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'username':
                $wheresql .= ' and username like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'ip':
                $wheresql .= ' and ip = %s';
                $params[] = $keyword;
                break;
            default:
                $wheresql .= ' and name like %s';
                $params[] = '%'.$keyword.'%';
        }
    }
    $count = DB::result_first("select count(distinct  uid) from %t  where $wheresql ",$params);
    $filedowntotal = DB::result_first("select count(id) from %t where $wheresql",$params);
    $limitstart = ($page - 1)*$limit;
    $limitsql = ' limit '.$limitstart.','.$limit;
    $data = array();
    $uids = [];

    //各用户文件数
    $colors=array('#6b69d6','#a966ef','#e9308d','#e74856','#f35b42','#00cc6a','#0078d7','#5290f3','#00b7c3','#0099bc','#018574','#c77c52','#ff8c00','#68768a','#7083cb','#26a255');
    foreach(DB::fetch_all("select  SUBSTRING_INDEX(GROUP_CONCAT(username),',',1) as username,
SUBSTRING_INDEX(GROUP_CONCAT(uid),',',1) as uid,
SUBSTRING_INDEX(GROUP_CONCAT(dateline ORDER BY dateline desc),',',1) as dateline, 
SUBSTRING_INDEX(GROUP_CONCAT(ip ORDER BY dateline desc),',',1) as ip, count(id) as num from %t  where $wheresql group by uid $ordersql $limitsql",
        $params) as $v){
        $udata = DB::fetch_first("select u.uid,u.avatarstatus,u.username,s.svalue from %t u 
    left join %t s on s.uid = u.uid and s.skey=%swhere u.uid =%d",
            array('user','user_setting','headerColor',$v['uid']));
        $v['isdeluser'] = false;
        if(!$udata){
            $v['isdeluser'] = true;
        }else{
            if($udata['avatarstatus'] == 1){
                $v['icon'] = 'avatar.php?uid='.$v['uid'];
            }elseif($udata['svalue']){
                $v['firstword'] = strtoupper(new_strsubstr($v['username'],1,''));
                $v['headerColor'] = $udata['svalue'];
                $v['icon'] = false;
            }else{
                $colorkey = rand(1,15);
                $headerColor = $colors[$colorkey];
                C::t('user_setting')->insert_by_skey('headerColor',$headerColor,$v['uid']);
                $v['firstword'] = strtoupper(new_strsubstr($v['username'],1,''));
                $v['headerColor'] = $headerColor;
            }
        }

        $v['fdateline'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
        $v['dateline'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
        $data[] = $v;
        //$orgnamedata = DB::fetch_all("select o.orgname from %t ou left join %t o on o.orgid = ou.orgid and o.type = 0 where ou.uid =%d",array('organization_user','organization',$v['uid']));
        if(!$v['isdeluser'])$uids[] = $v['uid'];
    }
    $orgdata = [];
    //用户机构部门信息
    foreach(DB::fetch_all("select o.orgname,ou.uid from %t ou left join %t o on o.orgid = ou.orgid and o.type = 0 where ou.uid in(%n)",array('organization_user','organization',$uids)) as $o){
        if($o['orgname']){
            $orgdata[$o['uid']]['orgname'][] = $o['orgname'];
        }
    }
//print_r($data);die;
    $returndata = array();
    foreach($data as $d){
        if(!isset($orgdata[$d['uid']]['orgname'])){
            if(!$d['isdeluser']) $d['orgname'] = lang('no_institution_users1');
            else  $d['orgname'] = lang('user_isdelete');
        }else{
            $d['orgname'] = implode(',',$orgdata[$d['uid']]['orgname']);
        }
        $returndata[] = $d;
    }
    if($count >= $limitstart+$limit){
        $next = true;
    }


    exit(json_encode(array('data'=>$data,'next'=>$next,'total'=>$count,'alltotal'=>$filedowntotal,'page'=>$page,'isadmin'=>$isadmin,'navtitle'=>lang('downloadfiletitle').'-'.$navtitle)));
}
elseif($do == 'views'){//查看统计

}
elseif($do=='userlogin'){//用户登录统计
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $keytype = isset($_GET['keytype']) ? trim($_GET['keytype']):'username';
    $order = $_GET['order'] ? intval($_GET['order']):1;
    $disp = $_GET['disp'] ? intval($_GET['disp']):1;
    $params =array('stats_userlogin');
    $whereparams = array();
    $wheresql = ' 1 ';
    $disp = $disp ? 'desc':'asc';
    $next = false;
    $ordersql = '';
    switch ($order){
        case 1:
            $ordersql = 'order by dateline '.$disp;
            break;
        case 2:
            $ordersql = 'order by username '.$disp;
            break;
        case 3:
            $ordersql = 'order by ip '.$disp;
            break;
    }
    if($start){
        $params[] = $start;
        $wheresql .= " and dateline >= %d";
    }
    if($end){
        $params[] = $end;
        $wheresql .= " and dateline < %d";
    }
    if($keyword){
        switch($keytype){
            case 'username':
                $wheresql .= ' and username like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'ip':
                $wheresql .= ' and ip = %s';
                $params[] = $keyword;
                break;
            case 'machine':
                $wheresql .= ' and machine like %s';
                $params[] = '%'.$keyword.'%';
                break;
            default:
                $wheresql .= ' and username like %s';
                $params[] = '%'.$keyword.'%';
        }
    }
    $count = DB::result_first("select count(id) from %t  where $wheresql",$params);
    $limitstart = ($page - 1)*$limit;
    $limitsql = ' limit '.$limitstart.','.$limit;
    $data = array();
    foreach(DB::fetch_all("select * from %t  where $wheresql $ordersql $limitsql",
        $params) as $v){
        $v['fdateline'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
        $v['dateline'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
        $data[] = $v;
    }
    if($count >= ($limitstart+$limit)){
        $next = true;
    }
    exit(json_encode(array('data'=>$data,'next'=>$next,'total'=>$count,'page'=>$page,'isadmin'=>$isadmin,'navtitle'=>lang('userlogintitle').'-'.$navtitle)));

}
elseif($do == 'downloadwarning'){//下载预警
    $setting = C::t('setting')->fetch('downloadwaringsetting',true);
    //$setting = isset($setting) ? intval($setting):0;
    if (submitcheck('submit')) {
        $newsetting['num'] = isset($_GET['num']) ? intval($_GET['num']) : 0;
        $newsetting['rate'] = isset($_GET['time']) ? floatval($_GET['time']) : 0;
        include_once libfile('function/cache');
        if(C::t('setting')->update('downloadwaringsetting',$newsetting)) {
            updatecache("setting");
            exit(json_encode(array('success'=>true)));
        }
    }else{
        $page = isset($_GET['page']) ? intval($_GET['page']):1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
        $order = $_GET['order'] ? intval($_GET['order']):2;
        $disp = $_GET['disp'] ? intval($_GET['disp']):0;
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
        $params =array('stats_downloadwaring');
        $whereparams = array();
        $settingnum = isset($setting['num']) ? intval($setting['num']):0;
        if(!$settingnum){
            $wheresql = ' 0 ';
        }else{
            $wheresql = ' downnum >= %d ';
            $params[] = $settingnum;
        }

        $disp = $disp ? 'desc':'asc';
        $next = false;
        $ordersql = '';
        switch ($order){
            case 1:
                $ordersql = 'order by ddateline '.$disp;
                break;
            case 2:
                $ordersql = 'order by downnum '.$disp;
                break;
        }
        if($start){
            $params[] = $start;
            $wheresql .= " and ddateline >= %d";
        }
        if($end){
            $params[] = $end;
            $wheresql .= " and ddateline < %d";
        }
        if($keyword){
            $wheresql .= ' and username like %s';
            $params[] = '%'.$keyword.'%';
        }

        $count = DB::result_first("select count(id) from %t  where $wheresql ",$params);
        $limitstart = ($page - 1)*$limit;
        $limitsql = ' limit '.$limitstart.','.$limit;
        $data = array();
        foreach(DB::fetch_all("select * from %t where $wheresql $ordersql $limitsql",
            $params) as $v){
            //记录下载时间
            $v['fdateline'] = dgmdate($v['ddateline'],'Y-m-d');
            //记录最后更新时间
            $v['dateline'] = dgmdate($v['dateline'],'Y-m-d H:i:s');

            $data[] = $v;
        }

        if($count >= ($limitstart+$limit)){
            $next = true;
        }
        exit(json_encode(array('data'=>$data,'next'=>$next,'total'=>$count,'page'=>$page,'isadmin'=>$isadmin,'setting'=>$setting,'navtitle'=>lang('downloadwaringtitle').'-'.$navtitle)));
    }
}
elseif($do == 'collects'){//收藏统计

}
elseif($do == 'getuserdata'){
    $uid = $_G['uid'];
    $data = array();
    $colors=array('#6b69d6','#a966ef','#e9308d','#e74856','#f35b42','#00cc6a','#0078d7','#5290f3','#00b7c3','#0099bc','#018574','#c77c52','#ff8c00','#68768a','#7083cb','#26a255');
    $udata = DB::fetch_first("select u.uid,u.avatarstatus,u.username,s.svalue from %t u 
    left join %t s on s.uid = u.uid and s.skey=%swhere u.uid =%d",
        array('user','user_setting','headerColor',$uid));

    if($udata['avatarstatus'] == 1){
        $data['icon'] = 'avatar.php?uid='.$udata['uid'];
    }elseif($udata['svalue']){
        $data['firstword'] = strtoupper(new_strsubstr($udata['username'],1,''));
        $data['headerColor'] = $udata['svalue'];
        $data['icon'] = false;
    }else{
        $colorkey = rand(1,15);
        $headerColor = $colors[$colorkey];
        C::t('user_setting')->insert_by_skey('headerColor',$headerColor,$udata['uid']);
        $data['firstword'] = strtoupper(new_strsubstr($udata['username'],1,''));
        $data['headerColor'] = $headerColor;
    }
    $data['username'] = $udata['username'];
    exit(json_encode(array('data'=>$data,'hash'=>FORMHASH)));

}
elseif($do == 'uploads'){//上传统计
    $next = false;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $keytype = isset($_GET['keytype']) ? trim($_GET['keytype']):'filename';
    $sparams = array('stats_view','user','user_setting','headerColor',3);
    $swheresql = ' up.idtype = %d ';
    $cwheresql = ' up.idtype = %d ';
    $cparams =  array('stats_view','user_status',3);
    if($start){
        $sparams[] = $start;
        $cparams[] = $start;
        $swheresql .= " and up.dateline >= %d";
        $cwheresql .= " and up.dateline >= %d";
    }
    if($end){
        $sparams[] = $end;
        $cparams[] = $end;
        $cwheresql .= " and dateline < %d";
        $swheresql .= " and up.dateline < %d";
    }
    if($keyword){
        switch($keytype){
            case 'filename':
                $swheresql .= ' and up.name like %s';
                $cwheresql .= ' and up.name like %s';
                $sparams[] = '%'.$keyword.'%';
                $cparams[] = '%'.$keyword.'%';
                break;
            case 'username':
                $swheresql .= ' and up.username like %s';
                $cwheresql .= ' and up.username like %s';
                $sparams[] = '%'.$keyword.'%';
                $cparams[] = '%'.$keyword.'%';
                break;
            case 'ip':
                $swheresql .= ' and up.ip = %s';
                $cwheresql .= ' and up.ip = %s';
                $sparams[] = $keyword;
                $cparams[] = $keyword;
                break;
            default:
                $swheresql .= ' and up.filename like %s';
                $cwheresql .= ' and up.filename like %s';
                $sparams[] = '%'.$keyword.'%';
                $cparams[] = '%'.$keyword.'%';
        }
    }
    $limitstart = ($page - 1)*$limit;
    $limitsql = ' limit '.$limitstart.','.$limit;
    $data = array();
    $count = DB::result_first("select count(distinct up.uid) as num from %t up left join %t us on us.uid=up.uid where $cwheresql",$cparams);
    $udata = $uids =array();
    $uploadtotal = DB::result_first("select count(up.id) as num from %t up left join %t us on us.uid=up.uid
      where $cwheresql $limitsql",$cparams);
    //各用户文件数
    $colors=array('#6b69d6','#a966ef','#e9308d','#e74856','#f35b42','#00cc6a','#0078d7','#5290f3','#00b7c3','#0099bc','#018574','#c77c52','#ff8c00','#68768a','#7083cb','#26a255');
    foreach(DB::fetch_all("select count(up.id) as num,up.uid,max(up.dateline) as dateline,up.ip,u.avatarstatus,up.username,s.svalue
      from %t up left join %t u on up.uid=u.uid
      left join %t s on s.uid = up.uid and s.skey=%s
      where $swheresql group by up.uid order by num desc $limitsql  ",$sparams) as $v){
        $data[$v['uid']]['num'] = $v['num'];
        $v['userisdelete'] = false;
        if(!isset($v['avatarstatus'])){
            $v['userisdelete'] = true;
        }
        if($v['avatarstatus'] == 1){
            $v['icon'] = 'avatar.php?uid='.$v['uid'];
        }elseif($v['svalue']){
            $v['firstword'] = strtoupper(new_strsubstr($v['username'],1,''));
            $v['headerColor'] = $v['svalue'];
            $v['icon'] = false;
        }else{
            $colorkey = rand(1,15);
            $headerColor = $colors[$colorkey];
            if($v['userisdelete'])C::t('user_setting')->insert_by_skey('headerColor',$headerColor,$v['uid']);
            $v['firstword'] = strtoupper(new_strsubstr($v['username'],1,''));
            $v['headerColor'] = $v['svalue'];
        }
        $v['lastvisit'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
        $data[$v['uid']] =$v;
        $uids[] = $v['uid'];
    }
    //用户机构部门信息
    foreach(DB::fetch_all("select o.orgname,ou.uid from %t ou left join %t o on o.orgid = ou.orgid and o.type = 0 where ou.uid in(%n)",array('organization_user','organization',$uids)) as $o){
        if($o['orgname']){
            $data[$o['uid']]['orgname'][] = $o['orgname'];
        }
    }
    $returndata = array();
    foreach($data as $d){
        if(!isset($d['orgname'])){
            $d['orgname'] = lang('no_institution_users1');
        }else{
            $d['orgname'] = implode(',',$d['orgname']);
        }
        $returndata[] = $d;
    }
    if($count >= $limitstart+$limit){
        $next = true;
    }
    exit(json_encode(array('data'=>$returndata,'total'=>$count,'uploadtotal'=>$uploadtotal,'next'=>$next,'page'=>$page,'isadmin'=>$isadmin,'navtitle'=>lang('uploadfiletitle').'-'.$navtitle)));
}
elseif($do == 'files'){//文件统计
    $next = false;
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $limitstart = ($page-1)*$limit;
    $data = array();
    $filewhere = " 1 ";
    $fileparams = array('pichome_resources');
    if($start){
        $filewhere .= " and btime >= %d";
        $fileparams[] = $start*1000;
    }
    if($end){
        $filewhere .= " and btime < %d";
        $fileparams[] = $end*1000;
    }

    $count = DB::result_first("select count(distinct ext)  from %t where $filewhere  ",$fileparams);
    $totalsize = $extnum = $filenum = 0;
    foreach(DB::fetch_all("select ext,count(rid) as extnum,sum(size) as size  from %t 
   where $filewhere GROUP BY ext order by extnum desc limit $limitstart,$limit",$fileparams) as $v){
        $totalsize += intval($v['size']);
        $extnum +=1;
        $filenum += intval($v['extnum']);
        $v['fsize'] = formatsize($v['size']);
        $data['extdatas'][] = $v;
    }
    if($count  >= ($limitstart+$limit)){
        $next = true;
    }
    $data['totalsize']=formatsize($totalsize);
    $data['extnum']=$extnum;
    $data['filenum']=$filenum;
    exit(json_encode(array('data'=>$data,'next'=>$next,'total'=>$count,'page'=>$page,'isadmin'=>$isadmin,'navtitle'=>lang('exportfiletitle').'-'.$navtitle)));
}
elseif($do == 'tags'){//标签统计

}
elseif($do == 'search'){//搜索统计

}
elseif($do == 'exportdownloadsbyusername'){
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $keytype = isset($_GET['keytype']) ? trim($_GET['keytype']):'filename';
    $order = $_GET['order'] ? intval($_GET['order']):1;
    $disp = $_GET['disp'] ? intval($_GET['disp']):2;
    $params =array('stats_view',1);
    $whereparams = array();
    $wheresql = ' idtype = %d ';
    $disp = $disp ? 'desc':'asc';
    $next = false;
    $ordersql = '';
    switch ($order){
        case 1:
            $ordersql = 'order by dateline '.$disp;
            break;
        case 2:
            $ordersql = 'order by num '.$disp;
            break;
        case 3:
            $ordersql = 'order by ip '.$disp;
            break;
    }
    if($start){
        $params[] = $start;
        $wheresql .= " and dateline >= %d";
    }
    if($end){
        $params[] = $end;
        $wheresql .= " and dateline < %d";
    }
    if($keyword){
        switch($keytype){
            case 'filename':
                $wheresql .= ' and name like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'username':
                $wheresql .= ' and username like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'ip':
                $wheresql .= ' and ip = %s';
                $params[] = $keyword;
                break;
            default:
                $wheresql .= ' and name like %s';
                $params[] = '%'.$keyword.'%';
        }
    }
    $limitstart = ($page - 1)*$limit;
    $limitsql = ' limit '.$limitstart.','.$limit;
    $data = array();
    $uids = [];
    //各用户文件数
    $colors=array('#6b69d6','#a966ef','#e9308d','#e74856','#f35b42','#00cc6a','#0078d7','#5290f3','#00b7c3','#0099bc','#018574','#c77c52','#ff8c00','#68768a','#7083cb','#26a255');
    foreach(DB::fetch_all("select  SUBSTRING_INDEX(GROUP_CONCAT(username),',',1) as username,
SUBSTRING_INDEX(GROUP_CONCAT(uid),',',1) as uid,
SUBSTRING_INDEX(GROUP_CONCAT(dateline ORDER BY dateline desc),',',1) as dateline, 
SUBSTRING_INDEX(GROUP_CONCAT(ip ORDER BY dateline desc),',',1) as ip, count(id) as num from %t  where $wheresql group by uid $ordersql $limitsql",
        $params) as $v){
        $udata = DB::fetch_first("select uid from %t where uid =%d",
            array('user',$v['uid']));
        $v['isdeluser'] = false;
        if(!$udata){
            $v['isdeluser'] = true;
        }
        $data[$v['uid']] = [
            'username'=>$v['username'],
            'num'=>$v['num'],
            'lastdownloadtime'=>dgmdate($v['dateline'],'Y-m-d H:i:s'),
        ];
        if(!$v['isdeluser'])$uids[] = $v['uid'];
    }
    //用户机构部门信息
    $orgdata = [];
    //用户机构部门信息
    foreach(DB::fetch_all("select o.orgname,ou.uid from %t ou left join %t o on o.orgid = ou.orgid and o.type = 0 where ou.uid in(%n)",array('organization_user','organization',$uids)) as $o){
        if($o['orgname']){
            $orgdata[$o['uid']]['orgname'][] = $o['orgname'];
        }
    }

    $returndata = array();
    foreach($data as $k=>$d){
        if(!isset($orgdata[$k]['orgname'])){
            if(!$d['isdeluser']) $d['orgname'] = lang('no_institution_users1');
            else  $d['orgname'] = lang('user_isdelete');
        }else{
            $d['orgname'] = implode(',',$orgdata[$k]['orgname']);
        }
        $returndata[] = $d;
    }

    $title = lang('downloadfiletitlebyusername').'-'.$page;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator($_G['username'])
        ->setTitle($title.'- oaooa')
        ->setSubject($title.' - oaooa')
        ->setDescription($title.' - oaooa Export By oaooa  '.date('Y-m-d H:i:s'))
        ->setKeywords($title.' - oaooa')
        ->setCategory($title);

    $list = array();
    $objPHPExcel->setActiveSheetIndex(0);
    $h0 = array('username'=>lang('username'),'downloadnum'=>lang('downloadnum'),'lastdownloadtime'=>lang('lastdownloadtime'),'orgname'=>lang('orgname'));
    $j=0;
    foreach($h0 as $key =>$value){
        $index=getColIndex($j).'1';
        $objPHPExcel->getActiveSheet()->setCellValue($index,$value);
        $list[1][$index]=$value;
        $j++;
    }
    $i = 2;
    foreach($returndata as $key=>$v){
        $j = 0;
        foreach($v as $val){
            $index=getColIndex($j).$i;
            $objPHPExcel->getActiveSheet()->setCellValue($index,$val);
            $j++;
            $list[$i][$index]=$v[$key];
        }
        $i++;

    }

    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $filename=$_G['setting']['attachdir'].'./cache/'.random(5).'.xlsx';
    $objWriter->save($filename);

    if($start){
        $name = dgmdate($start,'Y-m-d').' - '.dgmdate($end,'Y-m-d').$title.'.xlsx';
    }else{
        $name=$title.'.xlsx';
    }

    $name = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name).'"';

    $filesize=filesize($filename);
    $chunk = 10 * 1024 * 1024;
    if(!$fp = @fopen($filename, 'rb')) {
        exit(lang('export_failure'));
    }
    dheader('Date: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Content-Encoding: none');
    dheader('Content-Disposition: attachment; filename='.$name);
    dheader('Content-Type: application/octet-stream');
    dheader('Content-Length: '.$filesize);
    @ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    while (!feof($fp)) {
        echo fread($fp, $chunk);
        @ob_flush();  // flush output
        @flush();
    }
    @unlink($filename);
    exit();
}
elseif($do == 'exportdownloadsbyfilename'){
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $keytype = isset($_GET['keytype']) ? trim($_GET['keytype']):'filename';
    $order = $_GET['order'] ? intval($_GET['order']):1;
    $disp = $_GET['disp'] ? intval($_GET['disp']):2;
    $params =array('stats_view',1);
    $whereparams = array();
    $wheresql = ' idtype = %d';
    $disp = $disp ? 'desc':'asc';
    $next = false;
    $ordersql = '';
    switch ($order){
        case 1:
            $ordersql = 'order by dateline '.$disp;
            break;
        case 2:
            $ordersql = 'order by num '.$disp;
            break;
        case 3:
            $ordersql = 'order by ip '.$disp;
            break;
        default :
            $ordersql = 'order by num '.$disp;
            break;
    }
    if($start){
        $params[] = $start;
        $wheresql .= " and dateline >= %d";
    }
    if($end){
        $params[] = $end;
        $wheresql .= " and dateline < %d";
    }
    if($keyword){
        switch($keytype){
            case 'filename':
                $wheresql .= ' and name like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'username':
                $wheresql .= ' and username like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'ip':
                $wheresql .= ' and ip = %s';
                $params[] = $keyword;
                break;
            default:
                $wheresql .= ' and name like %s';
                $params[] = '%'.$keyword.'%';
        }
    }
    $limitstart = ($page - 1)*$limit;
    $limitsql = ' limit '.$limitstart.','.$limit;
    $data = array();

    foreach(DB::fetch_all("select SUBSTRING_INDEX(GROUP_CONCAT(idval),',',1) as rid,SUBSTRING_INDEX(GROUP_CONCAT(name),',',1) as filename,SUBSTRING_INDEX(GROUP_CONCAT(dateline ORDER BY dateline desc),',',1) as dateline,
count(id) as num from %t  where $wheresql group by idval $ordersql $limitsql",
        $params) as $v){
       /* $positiondata = C::t('stats_position')->fetch($v['position']);
        if($v['identify'] == 'picture') $v['position'] = '相册/'.$positiondata['position'];*/
        //else $v['position'] = $positiondata['position'];
        $resourcesdata = C::t('pichome_resources')->fetch_by_rid($v['rid']);
        $data[] = [
            'filename'=>$v['filename'],
            'num'=>$v['num'],
            'filesize'=>formatsize($resourcesdata['size']),
            'position'=>DB::result_first("select appname from %t where appid = %s",['pichome_vapp',$resourcesdata['appid']]),
            'lastdownloadtime'=>dgmdate($v['dateline'],'Y-m-d H:i:s'),

        ];
    }
    $title = lang('downloadfiletitlebyname').'-'.$page;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator($_G['username'])
        ->setTitle($title.'- oaooa')
        ->setSubject($title.' - oaooa')
        ->setDescription($title.' - oaooa Export By oaooa  '.date('Y-m-d H:i:s'))
        ->setKeywords($title.' - oaooa')
        ->setCategory($title);

    $list = array();
    $objPHPExcel->setActiveSheetIndex(0);
    $h0 = array('filename'=>lang('filename'),'downloadnum'=>lang('downloadnum'),'filesize'=>lang('filesize'),'position'=>lang('position'),'lastdownloadtime'=>lang('lastdownloadtime'));
    $j=0;
    foreach($h0 as $key =>$value){
        $index=getColIndex($j).'1';
        $objPHPExcel->getActiveSheet()->setCellValue($index,$value);
        $list[1][$index]=$value;
        $j++;
    }
    $i = 2;
    foreach($data as $key=>$v){
        $j = 0;
        foreach($v as $val){
            $index=getColIndex($j).$i;
            $objPHPExcel->getActiveSheet()->setCellValue($index,$val);
            $j++;
            $list[$i][$index]=$v[$key];
        }
        $i++;

    }

    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $filename=$_G['setting']['attachdir'].'./cache/'.random(5).'.xlsx';
    $objWriter->save($filename);

    if($start){
        $name = dgmdate($start,'Y-m-d').' - '.dgmdate($end,'Y-m-d').$title.'.xlsx';
    }else{
        $name=$title.'.xlsx';
    }

    $name = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name).'"';

    $filesize=filesize($filename);
    $chunk = 10 * 1024 * 1024;
    if(!$fp = @fopen($filename, 'rb')) {
        exit(lang('export_failure'));
    }
    dheader('Date: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Content-Encoding: none');
    dheader('Content-Disposition: attachment; filename='.$name);
    dheader('Content-Type: application/octet-stream');
    dheader('Content-Length: '.$filesize);
    @ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    while (!feof($fp)) {
        echo fread($fp, $chunk);
        @ob_flush();  // flush output
        @flush();
    }
    @unlink($filename);
    exit();
}
elseif($do == 'exportdownloads'){
    if(!$isadmin) Hook::listen('adminlogin');
    require_once DZZ_ROOT.'./core/class/class_PHPExcel.php';
    $data = array();
    //获取数据
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $keytype = isset($_GET['keytype']) ? trim($_GET['keytype']):'filename';
    $order = $_GET['order'] ? intval($_GET['order']):1;
    $disp = $_GET['disp'] ? intval($_GET['disp']):0;
    $params =array('stats_view','pichome_resources',1);
    $whereparams = array();
    $wheresql = ' s.idtype = %d ';
    $disp = $disp ? 'desc':'asc';
    $next = false;
    $ordersql = '';
    switch ($order){
        case 1:
            $ordersql = 'order by s.dateline '.$disp;
            break;
        case 2:
            $ordersql = 'order by s.username '.$disp;
            break;
        case 3:
            $ordersql = 'order by s.ip '.$disp;
            break;
    }
    if($start){
        $params[] = $start;
        $wheresql .= " and s.dateline >= %d";
    }
    if($end){
        $params[] = $end;
        $wheresql .= " and s.dateline < %d";
    }
    if($keyword){
        switch($keytype){
            case 'filename':
                $wheresql .= ' and s.name like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'username':
                $wheresql .= ' and s.username like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'ip':
                $wheresql .= ' and s.ip = %s';
                $params[] = $keyword;
                break;
            default:
                $wheresql .= ' and s.name like %s';
                $params[] = '%'.$keyword.'%';
        }
    }
    $limitstart = ($page - 1)*$limit;
    $limitsql = ' limit '.$limitstart.','.$limit;
    $data = array();
    foreach(DB::fetch_all("select s.* from %t s  left join %t  r on s.idval = r.rid where $wheresql $ordersql $limitsql",
        $params) as $v){
        $resourcesdata = C::t('pichome_resources')->fetch_by_rid($v['idval']);
        $v['username'] = $v['username'];
        $v['downloadip'] =$v['ip'];
        $v['filename'] = $v['name'];
        $v['ext'] =$resourcesdata['ext'];
        $v['position'] = DB::result_first("select appname from %t where appid = %s",['pichome_vapp',$resourcesdata['appid']]);
        $v['downloatime'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
        $v['filesize'] = formatsize($resourcesdata['size']);

        unset($v['idtype']);
        unset($v['size']);
        unset($v['dateline']);
        unset($v['id']);
        unset($v['name']);
        unset($v['ip']);
        unset($v['uid']);
        unset($v['idval']);
        unset($v['isadmin']);
        unset($v['identify']);
        unset($v['sourcesid']);
        unset($v['sourcestype']);

        $data[] = $v;
    }
    $title = lang('downloadfiletitle').'-'.$page;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator($_G['username'])
        ->setTitle($title.'- oaooa')
        ->setSubject($title.' - oaooa')
        ->setDescription($title.' - oaooa Export By oaooa  '.date('Y-m-d H:i:s'))
        ->setKeywords($title.' - oaooa')
        ->setCategory($title);

    $list = array();
    $objPHPExcel->setActiveSheetIndex(0);
    $h0 = array('username'=>lang('username'),'downloadip'=>lang('downloadip'),'filename'=>lang('filename'),'ext'=>lang('ext'),'position'=>lang('position'),'downtime'=>lang('downtime'),'filesize'=>lang('filesize'));
    $j=0;
    foreach($h0 as $key =>$value){
        $index=getColIndex($j).'1';
        $objPHPExcel->getActiveSheet()->setCellValue($index,$value);
        $list[1][$index]=$value;
        $j++;
    }
    $i = 2;
    foreach($data as $key=>$v){
        $j = 0;
        foreach($v as $val){
            $index=getColIndex($j).$i;
            $objPHPExcel->getActiveSheet()->setCellValue($index,$val);
            $j++;
            $list[$i][$index]=$v[$key];
        }
        $i++;

    }

    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $filename=$_G['setting']['attachdir'].'./cache/'.random(5).'.xlsx';
    $objWriter->save($filename);

    if($start){
        $name = dgmdate($start,'Y-m-d').' - '.dgmdate($end,'Y-m-d').$title.'.xlsx';
    }else{
        $name=$title.'.xlsx';
    }

    $name = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name).'"';
    $filesize=filesize($filename);
    $chunk = 10 * 1024 * 1024;
    if(!$fp = @fopen($filename, 'rb')) {
        exit(lang('export_failure'));
    }
    dheader('Date: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Content-Encoding: none');
    dheader('Content-Disposition: attachment; filename='.$name);
    dheader('Content-Type: application/octet-stream');
    dheader('Content-Length: '.$filesize);
    @ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    while (!feof($fp)) {
        echo fread($fp, $chunk);
        @ob_flush();  // flush output
        @flush();
    }
    @unlink($filename);
    exit();
}
elseif($do == 'exportuploads'){
    if(!$isadmin) Hook::listen('adminlogin');
    require_once DZZ_ROOT.'./core/class/class_PHPExcel.php';
    $data = array();

    $next = false;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $keytype = isset($_GET['keytype']) ? trim($_GET['keytype']):'filename';
    $sparams = array('stats_view','user','user_setting','headerColor',3);
    $swheresql = ' up.idtype = %d ';
    $cwheresql = ' up.idtype = %d ';
    $cparams =  array('stats_view','user_status',3);
    if($start){
        $sparams[] = $start;
        $cparams[] = $start;
        $swheresql .= " and up.dateline >= %d";
        $cwheresql .= " and up.dateline >= %d";
    }
    if($end){
        $sparams[] = $end;
        $cparams[] = $end;
        $cwheresql .= " and dateline < %d";
        $swheresql .= " and up.dateline < %d";
    }
    if($keyword){
        switch($keytype){
            case 'filename':
                $swheresql .= ' and up.name like %s';
                $cwheresql .= ' and up.name like %s';
                $sparams[] = '%'.$keyword.'%';
                $cparams[] = '%'.$keyword.'%';
                break;
            case 'username':
                $swheresql .= ' and up.username like %s';
                $cwheresql .= ' and up.username like %s';
                $sparams[] = '%'.$keyword.'%';
                $cparams[] = '%'.$keyword.'%';
                break;
            case 'ip':
                $swheresql .= ' and up.ip = %s';
                $cwheresql .= ' and up.ip = %s';
                $sparams[] = $keyword;
                $cparams[] = $keyword;
                break;
            default:
                $swheresql .= ' and up.filename like %s';
                $cwheresql .= ' and up.filename like %s';
                $sparams[] = '%'.$keyword.'%';
                $cparams[] = '%'.$keyword.'%';
        }
    }
    $limitstart = ($page - 1)*$limit;
    $limitsql = ' limit '.$limitstart.','.$limit;
    $data = array();

    $udata = $uids =array();
    //各用户文件数
    foreach(DB::fetch_all("select count(up.id) as num,up.uid,up.dateline,up.ip,u.avatarstatus,up.username,s.svalue
      from %t up left join %t u on up.uid=u.uid
      left join %t s on s.uid = up.uid and s.skey=%s
      where $swheresql group by up.uid order by num desc $limitsql  ",$sparams) as $v){
        $data[$v['uid']]['num'] = $v['num'];
        $v['userisdelete'] = false;
        if(!isset($v['avatarstatus'])){
            $v['userisdelete'] = true;
        }
        $v['dateline'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
        $data[$v['uid']] =$v;
        $uids[] = $v['uid'];
    }
    //用户机构部门信息
    foreach(DB::fetch_all("select o.orgname,ou.uid from %t ou left join %t o on o.orgid = ou.orgid and o.type = 0 where ou.uid in(%n)",array('organization_user','organization',$uids)) as $o){
        if($o['orgname']){
            $data[$o['uid']]['orgname'][] = $o['orgname'];
        }
    }
    $returndata = array();
    foreach($data as $d){
        if(!isset($d['orgname'])){
            if(!$d['userisdelete'])$d['orgname'] = lang('no_institution_users1');
            else $d['orgname'] = lang('user_isdelete');
        }else{
            $d['orgname'] = implode(',',$d['orgname']);
        }
        $returndata[] = [
            'usernmae'=>$d['username'],
            'num'=>$d['num'],
            'lastupload'=>$d['dateline'],
            'lastip'=>$d['ip']

        ];
    }


    $title = lang('uploadfiletitle').'-'.$page;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator($_G['username'])
        ->setTitle($title.'- oaooa')
        ->setSubject($title.' - oaooa')
        ->setDescription($title.' - oaooa Export By oaooa  '.date('Y-m-d H:i:s'))
        ->setKeywords($title.' - oaooa')
        ->setCategory($title);
    $list = array();
    $objPHPExcel->setActiveSheetIndex(0);
    $h0 = array('username'=>lang('username'),'filenum'=>lang('filenum'),'lastupload'=>lang('lastupload'),'lastip'=>lang('lastip'));
    $j=0;
    foreach($h0 as $key =>$value){
        $index=getColIndex($j).'1';
        $objPHPExcel->getActiveSheet()->setCellValue($index,$value);
        $list[1][$index]=$value;
        $j++;
    }
    $i = 2;
    foreach($returndata as $key=>$v){
        $j = 0;
        foreach($v as $val){
            $index=getColIndex($j).$i;
            $objPHPExcel->getActiveSheet()->setCellValue($index,$val);
            $j++;
            $list[$i][$index]=$v[$key];
        }
        $i++;

    }

    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $filename=$_G['setting']['attachdir'].'./cache/'.random(5).'.xlsx';
    $objWriter->save($filename);

    if($start){
        $name = dgmdate($start,'Y-m-d').' - '.dgmdate($end,'Y-m-d').$title.'.xlsx';
    }else{
        $name=$title.'.xlsx';
    }

    $name = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name).'"';

    $filesize=filesize($filename);
    $chunk = 10 * 1024 * 1024;
    if(!$fp = @fopen($filename, 'rb')) {
        exit(lang('export_failure'));
    }
    dheader('Date: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Content-Encoding: none');
    dheader('Content-Disposition: attachment; filename='.$name);
    dheader('Content-Type: application/octet-stream');
    dheader('Content-Length: '.$filesize);
    @ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    while (!feof($fp)) {
        echo fread($fp, $chunk);
        @ob_flush();  // flush output
        @flush();
    }
    @unlink($filename);
    exit();
}
elseif($do == 'exportlogin'){//登录统计导出
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $keytype = isset($_GET['keytype']) ? trim($_GET['keytype']):'username';
    $order = $_GET['order'] ? intval($_GET['order']):1;
    $disp = $_GET['disp'] ? intval($_GET['disp']):0;
    $params =array('stats_userlogin');
    $whereparams = array();
    $wheresql = ' 1 ';
    $disp = $disp ? 'desc':'asc';
    $next = false;
    $ordersql = '';
    switch ($order){
        case 1:
            $ordersql = 'order by dateline '.$disp;
            break;
        case 2:
            $ordersql = 'order by username '.$disp;
            break;
        case 3:
            $ordersql = 'order by ip '.$disp;
            break;
    }
    if($start){
        $params[] = $start;
        $wheresql .= " and dateline >= %d";
    }
    if($end){
        $params[] = $end;
        $wheresql .= " and dateline < %d";
    }
    if($keyword){
        switch($keytype){
            case 'username':
                $wheresql .= ' and username like %s';
                $params[] = '%'.$keyword.'%';
                break;
            case 'ip':
                $wheresql .= ' and ip = %s';
                $params[] = $keyword;
                break;
            case 'machine':
                $wheresql .= ' and machine like %s';
                $params[] = '%'.$keyword.'%';
                break;
            default:
                $wheresql .= ' and username like %s';
                $params[] = '%'.$keyword.'%';
        }
    }
    $limitstart = ($page - 1)*$limit;
    $limitsql = ' limit '.$limitstart.','.$limit;
    $data = array();
    foreach(DB::fetch_all("select * from %t  where $wheresql $ordersql $limitsql",
        $params) as $v){
        $data[] = [
            'username'=>$v['username'],
            'dateline'=>dgmdate($v['dateline'],'Y-m-d H:i:s'),
            'machine'=>$v['machine'],
            'ip'=>$v['ip']
        ];
    }
    $title = lang('userlogintitle').'-'.$page;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator($_G['username'])
        ->setTitle($title.'- oaooa')
        ->setSubject($title.' - oaooa')
        ->setDescription($title.' - oaooa Export By oaooa  '.date('Y-m-d H:i:s'))
        ->setKeywords($title.' - oaooa')
        ->setCategory($title);
    $list = array();
    $objPHPExcel->setActiveSheetIndex(0);
    $h0 = array('username'=>lang('username'),'logindateline'=>lang('logindateline'),'machine'=>lang('machine'),'loginip'=>lang('loginip'));
    $j=0;
    foreach($h0 as $key =>$value){
        $index=getColIndex($j).'1';
        $objPHPExcel->getActiveSheet()->setCellValue($index,$value);
        $list[1][$index]=$value;
        $j++;
    }
    $i = 2;
    foreach($data as $key=>$v){
        $j = 0;
        foreach($v as $val){
            $index=getColIndex($j).$i;
            $objPHPExcel->getActiveSheet()->setCellValue($index,$val);
            $j++;
            $list[$i][$index]=$v[$key];
        }
        $i++;

    }

    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $filename=$_G['setting']['attachdir'].'./cache/'.random(5).'.xlsx';
    $objWriter->save($filename);

    if($start){
        $name = dgmdate($start,'Y-m-d').' - '.dgmdate($end,'Y-m-d').$title.'.xlsx';
    }else{
        $name=$title.'.xlsx';
    }

    $name = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name).'"';

    $filesize=filesize($filename);
    $chunk = 10 * 1024 * 1024;
    if(!$fp = @fopen($filename, 'rb')) {
        exit(lang('export_failure'));
    }
    dheader('Date: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Content-Encoding: none');
    dheader('Content-Disposition: attachment; filename='.$name);
    dheader('Content-Type: application/octet-stream');
    dheader('Content-Length: '.$filesize);
    @ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    while (!feof($fp)) {
        echo fread($fp, $chunk);
        @ob_flush();  // flush output
        @flush();
    }
    @unlink($filename);
    exit();
}
elseif($do == 'exportdownloadwaring'){
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $order = $_GET['order'] ? intval($_GET['order']):2;
    $disp = $_GET['disp'] ? intval($_GET['disp']):0;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $params =array('stats_downloadwaring');
    $whereparams = array();
    $setting = C::t('setting')->fetch('downloadwaringsetting',true);
    $settingnum = isset($setting['num']) ? intval($setting['num']):0;
    if(!$settingnum){
        $wheresql = ' 0 ';
        exit('无预警数据');
    }else{
        $wheresql = ' downnum >= %d ';
        $params[] = $settingnum;
    }
    $disp = $disp ? 'desc':'asc';
    $next = false;
    $ordersql = '';
    switch ($order){
        case 1:
            $ordersql = 'order by ddateline '.$disp;
            break;
        case 2:
            $ordersql = 'order by downnum '.$disp;
            break;
    }
    if($start){
        $params[] = $start;
        $wheresql .= " and ddateline >= %d";
    }
    if($end){
        $params[] = $end;
        $wheresql .= " and ddateline < %d";
    }
    if($keyword){
        $wheresql .= ' and username like %s';
        $params[] = '%'.$keyword.'%';
    }

    $count = DB::result_first("select count(id) from %t  where $wheresql ",$params);
    $limitstart = ($page - 1)*$limit;
    $limitsql = ' limit '.$limitstart.','.$limit;
    $data = array();
    foreach(DB::fetch_all("select * from %t where $wheresql $ordersql $limitsql",
        $params) as $v){
        $data[] = [
            'username'=>$v['username'],
            'downloadnum'=>$v['downnum'],
            'downloaddate'=>dgmdate($v['ddateline'],'Y-m-d'),
            'lastdownloadtime'=>dgmdate($v['dateline'],'Y-m-d H:i:s')
        ];
    }

    $title = lang('downloadwaringtitle').'-'.$page;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator($_G['username'])
        ->setTitle($title.'- oaooa')
        ->setSubject($title.' - oaooa')
        ->setDescription($title.' - oaooa Export By oaooa  '.date('Y-m-d H:i:s'))
        ->setKeywords($title.' - oaooa')
        ->setCategory($title);
    $list = array();
    $objPHPExcel->setActiveSheetIndex(0);
    $h0 = array('username'=>lang('username'),'downloadnum'=>lang('downloadnum'),'waringdate'=>lang('waringdate'),'lastdownloadtime'=>lang('lastdownloadtime'));
    $j=0;
    foreach($h0 as $key =>$value){
        $index=getColIndex($j).'1';
        $objPHPExcel->getActiveSheet()->setCellValue($index,$value);
        $list[1][$index]=$value;
        $j++;
    }
    $i = 2;
    foreach($data as $key=>$v){
        $j = 0;
        foreach($v as $val){
            $index=getColIndex($j).$i;
            $objPHPExcel->getActiveSheet()->setCellValue($index,$val);
            $j++;
            $list[$i][$index]=$v[$key];
        }
        $i++;

    }

    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $filename=$_G['setting']['attachdir'].'./cache/'.random(5).'.xlsx';
    $objWriter->save($filename);

    if($start){
        $name = dgmdate($start,'Y-m-d').' - '.dgmdate($end,'Y-m-d').$title.'.xlsx';
    }else{
        $name=$title.'.xlsx';
    }

    $name = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name).'"';

    $filesize=filesize($filename);
    $chunk = 10 * 1024 * 1024;
    if(!$fp = @fopen($filename, 'rb')) {
        exit(lang('export_failure'));
    }
    dheader('Date: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Content-Encoding: none');
    dheader('Content-Disposition: attachment; filename='.$name);
    dheader('Content-Type: application/octet-stream');
    dheader('Content-Length: '.$filesize);
    @ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    while (!feof($fp)) {
        echo fread($fp, $chunk);
        @ob_flush();  // flush output
        @flush();
    }
    @unlink($filename);
    exit();
}
elseif($do == 'exportfiles'){
    if(!$isadmin) Hook::listen('adminlogin');
    require_once DZZ_ROOT.'./core/class/class_PHPExcel.php';
    $data = array();
    $filewhere = " `type` != %s ";
    $fileparams = array('resources','folder');
    if($start){
        $filewhere .= " and dateline >= %d";
        $fileparams[] = $start;
    }
    if($end){
        $filewhere .= " and dateline < %d";
        $fileparams[] = $end;
    }

    $totalsize = $extnum = $filenum = 0;
    foreach(DB::fetch_all("select ext,count(rid) as extnum,sum(size) as size  from %t 
   where $filewhere GROUP BY ext order by extnum desc ",$fileparams) as $v){
        $totalsize += intval($v['size']);
        $extnum +=1;
        $filenum += intval($v['extnum']);
        $v['fsize'] = formatsize($v['size']);
        unset($v['size']);
        $data['extdatas'][] = $v;
    }
    $data['total'] = array(
        'name'=>lang('sumdata'),
        'filenum'=>$filenum,
        'totalsize'=>formatsize($totalsize),
    );
    $data['totalsize']=formatsize($totalsize);
    $data['extnum']=$extnum;
    $data['filenum']=$filenum;
    $title = lang('exportfiletitle');
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator($_G['username'])
        ->setTitle($title.'- oaooa')
        ->setSubject($title.' - oaooa')
        ->setDescription($title.' - oaooa Export By oaooa  '.date('Y-m-d H:i:s'))
        ->setKeywords($title.' - oaooa')
        ->setCategory($title);
    $list = array();
    $objPHPExcel->setActiveSheetIndex(0);
    $h0 = array('ext'=>lang('exttitle'),'filenum'=>lang('filenum'),'size'=>lang('filesize')/*,'sum'=>lang('sumdata')*/);
    $j=0;
    foreach($h0 as $key =>$value){
        $index=getColIndex($j).'1';
        $objPHPExcel->getActiveSheet()->setCellValue($index,$value);
        $list[1][$index]=$value;
        $j++;
    }
    $i = 2;
    foreach($data['extdatas'] as $key=>$v){
        $j = 0;
        foreach($v as $val){
            $index=getColIndex($j).$i;
            $objPHPExcel->getActiveSheet()->setCellValue($index,$val);
            $j++;
            $list[$i][$index]=$v[$key];
        }
        $i++;

    }
    $j = 0;
    foreach($data['total'] as $tv){
        $index=getColIndex($j).$i;
        $objPHPExcel->getActiveSheet()->setCellValue($index,$tv);
        $j++;
        $list[$i][$index]=$tv;
    }
    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $filename=$_G['setting']['attachdir'].'./cache/'.random(5).'.xlsx';
    $objWriter->save($filename);

    if($start){
        $name = dgmdate($start,'Y-m-d').' - '.dgmdate($end,'Y-m-d').$title.'.xlsx';
    }else{
        $name=$title.'.xlsx';
    }

    $name = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name).'"';

    $filesize=filesize($filename);
    $chunk = 10 * 1024 * 1024;
    if(!$fp = @fopen($filename, 'rb')) {
        exit(lang('export_failure'));
    }
    dheader('Date: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Content-Encoding: none');
    dheader('Content-Disposition: attachment; filename='.$name);
    dheader('Content-Type: application/octet-stream');
    dheader('Content-Length: '.$filesize);
    @ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    while (!feof($fp)) {
        echo fread($fp, $chunk);
        @ob_flush();  // flush output
        @flush();
    }
    @unlink($filename);
    exit();
}
elseif($do == 'getdownloadcurve'){//获取下载曲线数据
    $dstatart = isset($_GET['dstart']) ? $_GET['dstart']:'';
    $dend = isset($_GET['dend']) ? $_GET['dend']:'';
    $dday = isset($_GET['dday']) ? trim($_GET['dday']):'';

    if($dday){
        $dstatarttime = strtotime(date('Y-m-d',strtotime($dday.'day')));
        $dendtime = strtotime(dgmdate(TIMESTAMP,'Y-m-d'))+60*60*24;
    }elseif ($dstatart || $dend){
        if($dstatart){
            $dstatarttime = strtotime($dstatart);
        }
        if($dend){
            $dendtime = strtotime($dend)+60*60*24;
        }
    }
    $params = array('stats_view',1);
    $wheresql = '  idtype = %d  ';
    if($dstatarttime){
        $params[] = $dstatarttime;
        $wheresql .= " and dateline >= %d";
    }
    if($dendtime){
        $params[] = $dendtime;
        $wheresql .= " and dateline < %d";
    }
    foreach(DB::fetch_all("SELECT count(id) as num,dateline FROM %t where $wheresql GROUP BY dateline",$params) as $v){
        $day = dgmdate($v['dateline'],'Y-m-d');
        if(!isset($data[$day])){
            $num = intval($v['num']);
            $data[$day] = array('date'=>$day,'num'=>$num);
        }else{
            $num = intval($data[$day]['num'])+intval($v['num']);
            $data[$day] = array('date'=>$day,'num'=>$num);
        }

    }
    exit(json_encode(array('data'=>$data)));
}
elseif($do == 'getuploadcurve'){//获取下载曲线数据
    $dstatart = isset($_GET['dstart']) ? $_GET['dstart']:'';
    $dend = isset($_GET['dend']) ? $_GET['dend']:'';
    $dday = isset($_GET['dday']) ? trim($_GET['dday']):'';
    if($dday){
        $dstatarttime = strtotime(date('Y-m-d',strtotime($dday.'day')));
        $dendtime = strtotime(dgmdate(TIMESTAMP,'Y-m-d'))+60*60*24;
    }elseif ($dstatart || $dend){
        if($dstatart){
            $dstatarttime = strtotime($dstatart);
        }
        if($dend){
            $dendtime = strtotime($dend)+60*60*24;
        }
    }
    $params = array('stats_view',3);
    $wheresql = " idtype = %d ";
    if($dstatarttime){
        $params[] = $dstatarttime;
        $wheresql .= " and dateline >= %d";
    }
    if($dendtime){
        $params[] = $dendtime;
        $wheresql .= " and dateline < %d";
    }
    foreach(DB::fetch_all("SELECT count(id) as num,dateline FROM %t where $wheresql GROUP BY dateline",$params) as $v){
        $day = dgmdate($v['dateline'],'Y-m-d');
        if(!isset($data[$day])){
            $num = intval($v['num']);
            $data[$day] = array('date'=>$day,'num'=>$num);
        }else{
            $num = intval($data[$day]['num'])+intval($v['num']);
            $data[$day] = array('date'=>$day,'num'=>$num);
        }

    }
    exit(json_encode(array('data'=>$data)));
}
elseif($do == 'getuser'){//获取下载曲线数据
    //条件类型0，默认用户名，1用户id,2用户邮箱
    $searchtype = isset($_GET['searchtype']) ? intval($_GET['searchtype']) : 0;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $params = array('user');
    $wheresql = ' 1 = 1 ';
    if ($keyword) {
        switch ($searchtype) {
            case 0:
                $params[] = '%' . $keyword . '%';
                $wheresql .= " and nickname like %s";
                break;
            case 1:
                $params[] = intval($keyword);
                $wheresql .= " and uid = %d";
                break;
            case 2:
                $params[] = '%' . $keyword . '%';
                $wheresql .= " and email like %s";
                break;
        }
    }
    $uids = $data = $userdata = array();
    foreach (DB::fetch_all("select uid,username,nickname,email from %t  where $wheresql  order by regdate desc limit 0,10 ", $params) as $val) {
        $datas[] = $val;
        $uids[] = $val['uid'];
    }
    getuserIcon($uids, $datas, $data);
    exit(json_encode(array('data' => $data)));
}elseif($do == 'tabgroupheader'){
    $gid = isset($_GET['gid']) ? intval($_GET['gid']) : 0;
    $navtitles = [];
    //获取当前专辑文件和专辑模块
    foreach(DB::fetch_all("select * from %t where (cate = %d or cate = %d) and gid = %d",array('tab_banner',0,2,$gid)) as $v){
        Hook::listen('lang_parse',$v,['getTabbannerLangData']);
        $navtitles[$v['id']] = $v['name'];
    }
    exit(json_encode(array('data'=>$navtitles)));
}
elseif($do == 'tabgroup'){
    $gid = isset($_GET['gid']) ? intval($_GET['gid']) : 0;
    $next = false;
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $limitstart = ($page-1)*$limit;
    $limitsql = " limit $limitstart,$limit";
    $lang= '';
    //检查是否开启语言包
    Hook::listen('lang_parse',$lang,['checklang']);
    $navtitles = [];
    //获取当前专辑文件和专辑模块
    foreach(DB::fetch_all("select * from %t where (cate = %d or cate = %d) and gid = %d",array('tab_banner',0,2,$gid)) as $v){
        Hook::listen('lang_parse',$v,['getTabbannerLangData']);
        $navtitles[$v['id']] = $v['name'];
    }
    $para = [$gid];
    $leftsql = '';
    $wheresql = ' t.gid = %d and  t.isdelete = 0 ';
    $kwheresql = '';
    if($keyword){
       $kwheresql .= ' t.tabname like %s ';
       $para[] = '%'.$keyword.'%';
       if($lang){
          $leftsql = " left join  ".DB::table("lang_$lang")." lang on lang.idvalue = t.tid and lang.idtype = 16 and lang.filed='tabname' ";
          $kwheresql .= " or lang.svalue like %s ";
          $para[] = '%'.$keyword.'%';
       }

    }
    if($kwheresql) $wheresql .= ' and ('.$kwheresql.')';
    $order = isset($_GET['order']) ? intval($_GET['order']):0;
    $disp = isset($_GET['disp']) ? intval($_GET['disp']):1;
    $disp = $disp ? 'DESC':'ASC';
    $ordersql = '';
    switch ($order){
        CASE -1:
            $ordersql = 'ORDER BY ts.dateline '.$disp;
            break;
            case 0:
                $ordersql = 'ORDER BY ts.infopercent '.$disp;
                break;
        default:
            if(is_numeric($order)) $ordersql = ' ORDER BY bnum_'.$order.' '.$disp;
            eLse $ordersql = 'ORDER BY ts.dateline '.$disp;
            break;

    }
    //获取项目个数
    $tabnum= DB::result_first("select count(*) from %t t $leftsql where $wheresql ",array_merge(array('tab'),$para));
    //获取项目关联文件个数
    $tabfilenum = DB::result_first("select count(DISTINCT rt.rid ) from %t t left join %t rt on t.tid = rt.tid $leftsql where $wheresql ",array_merge(array('tab','pichome_resourcestab'),$para));
    $data = array();
    $casesqlarr = [];
    foreach($navtitles as $bid=>$name){
        $casesqlarr[] = "MAX(CASE WHEN tm.bid = $bid THEN tm.num ELSE 0 END) AS bnum_$bid";
    }
    if(!empty($casesqlarr))$casesql = implode(',',$casesqlarr);
    $selectsql = 't.*,ts.infopercent,ts.dateline as updatedateline';
    if($casesql) $selectsql .= ','.$casesql;
    $gdata = C::t('#tab#tab_group')->fetch($gid);
    $navtitle = $gdata['name'];
    $defautico = $_G['siteurl'].$_G['setting']['attachurl'].$gdata['defaultico'].'?'.VERHASH;
    foreach(DB::fetch_all("select $selectsql from %t t 
    left join %t ts on t.tid= ts.tid 
    left join %t tm on tm.tid=t.tid 
$leftsql  where $wheresql group by t.tid $ordersql $limitsql",array_merge(['tab','tab_stats','tab_statsmodel'],$para)) as $v){
        if($v['icon']){
            $v['icon'] = $_G['setting']['attachurl'].'/'.$v['icon'].'?'.VERHASH;
        }elseif($defautico){
            $v['icon'] = $defautico;
        }else{
            $v['icon'] = false;
        }
        Hook::listen('lang_parse',$v,['getTabLangData']);
        $v['updatedateline'] = dgmdate($v['updatedateline'],'Y-m-d H:i:s');
        $data[] = $v;
    }
    if($tabnum  >= ($limitstart+$limit)){
        $next = true;
    }
    exit(json_encode(array('data'=>$data,'next'=>$next,'tabnavtitles'=>$navtitles,'total'=>$tabnum,'tabfilenum'=>$tabfilenum,'page'=>$page,'isadmin'=>$isadmin,'navtitle'=>lang('exporttabgrouptitle').'-'.$navtitle)));
}
elseif($do == 'exporttabgroup'){
    $gid = isset($_GET['gid']) ? intval($_GET['gid']) : 0;
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):50;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $limitstart = ($page-1)*$limit;
    $limitsql = " limit $limitstart,$limit";
    $lang= '';
    //检查是否开启语言包
    Hook::listen('lang_parse',$lang,['checklang']);
    $navtitles = [];
    //获取当前专辑文件和专辑模块
    foreach(DB::fetch_all("select * from %t where (cate = %d or cate = %d) and gid = %d",array('tab_banner',0,2,$gid)) as $v){
        Hook::listen('lang_parse',$v,['getTabbannerLangData']);
        $navtitles[$v['id']] = $v['name'];
    }

    ksort($navtitles);
    $para = [$gid];
    $leftsql = '';
    $wheresql = ' t.gid = %d and  t.isdelete = 0 ';
    $kwheresql = '';
    if($keyword){
        $kwheresql .= ' t.tabname like %s ';
        $para[] = '%'.$keyword.'%';
        if($lang){
            $leftsql = " left join  ".DB::table("lang_$lang")." lang on lang.idvalue = t.tid and lang.idtype = 16 and lang.filed='tabname' ";
            $kwheresql .= " or lang.svalue like %s ";
            $para[] = '%'.$keyword.'%';
        }

    }
    if($kwheresql) $wheresql .= ' and ('.$kwheresql.')';
    $order = isset($_GET['order']) ? intval($_GET['order']):0;
    $disp = isset($_GET['disp']) ? intval($_GET['disp']):1;
    $disp = $disp ? 'DESC':'ASC';
    $ordersql = '';
    switch ($order){
        CASE -1:
            $ordersql = 'ORDER BY ts.dateline '.$disp;
            break;
        case 0:
            $ordersql = 'ORDER BY ts.infopercent '.$disp;
            break;
        default:
            if(is_numeric($order)) $ordersql = ' ORDER BY bnum_'.$order.' '.$disp;
            eLse $ordersql = 'ORDER BY ts.dateline '.$disp;
            break;

    }
    //获取项目个数
    $tabnum= DB::result_first("select count(*) from %t t $leftsql where $wheresql ",array_merge(array('tab'),$para));
    //获取项目关联文件个数
    $tabfilenum = DB::result_first("select count(DISTINCT rt.rid ) from %t t left join %t rt on t.tid = rt.tid $leftsql where $wheresql ",array_merge(array('tab','pichome_resourcestab'),$para));
    $data = array();
    $casesqlarr = [];
    foreach($navtitles as $bid=>$name){
        $casesqlarr[] = "MAX(CASE WHEN tm.bid = $bid THEN tm.num ELSE 0 END) AS bnum_$bid";
    }
    if(!empty($casesqlarr))$casesql = implode(',',$casesqlarr);

    $selectsql = 't.*,ts.infopercent,ts.dateline as updatedateline';
    if($casesql) $selectsql .= ','.$casesql;
    $gdata = C::t('#tab#tab_group')->fetch($gid);
    foreach(DB::fetch_all("select $selectsql from %t t 
    left join %t ts on t.tid= ts.tid 
    left join %t tm on tm.tid=t.tid 
$leftsql  where $wheresql group by t.tid $ordersql $limitsql",array_merge(['tab','tab_stats','tab_statsmodel'],$para)) as $v){

        Hook::listen('lang_parse',$v,['getTabLangData']);

        $tmparr = [
            'tabname'=>$v['tabname'],
            'infopercent'=>$v['infopercent'],
            'updatedateline'=>dgmdate($v['updatedateline'],'Y-m-d H:i:s'),

        ];
        $navnumarr = [];
        foreach($v as $k=>$val){
            if(strpos($k,'bnum_') === 0){
                $bid =  intval(str_replace('bnum_','',$k));
                $navnumarr[$bid] = $val;
            }
        }

        ksort($navnumarr);
        $tmparr = array_replace($tmparr,$navnumarr);
        $data[] = $tmparr;
    }

    $title = lang('tabgrouptitle').'-'.$page.'-'.$gdata['name'];
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator($_G['username'])
        ->setTitle($title.'- oaooa')
        ->setSubject($title.' - oaooa')
        ->setDescription($title.' - oaooa Export By oaooa  '.date('Y-m-d H:i:s'))
        ->setKeywords($title.' - oaooa')
        ->setCategory($title);
    $list = array();
    $objPHPExcel->setActiveSheetIndex(0);
    $h0 = array('tabname'=>lang('tabname'),'infopercent'=>lang('infopercent'),'updatedateline'=>lang('updatedate'));
    $h0 = array_replace($h0, $navtitles);
    $j=0;
    foreach($h0 as $key =>$value){
        $index=getColIndex($j).'1';
        $objPHPExcel->getActiveSheet()->setCellValue($index,$value);
        $list[1][$index]=$value;
        $j++;
    }
    $i = 2;
    foreach($data as $key=>$v){
        $j = 0;
        foreach($v as $val){
            $index=getColIndex($j).$i;
            $objPHPExcel->getActiveSheet()->setCellValue($index,$val);
            $j++;
            $list[$i][$index]=$v[$key];
        }
        $i++;

    }

    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $filename=$_G['setting']['attachdir'].'./cache/'.random(5).'.xlsx';
    $objWriter->save($filename);

    if($start){
        $name = dgmdate($start,'Y-m-d').' - '.dgmdate($end,'Y-m-d').$title.'.xlsx';
    }else{
        $name=$title.'.xlsx';
    }

    $name = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name).'"';

    $filesize=filesize($filename);
    $chunk = 10 * 1024 * 1024;
    if(!$fp = @fopen($filename, 'rb')) {
        exit(lang('export_failure'));
    }
    dheader('Date: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
    dheader('Content-Encoding: none');
    dheader('Content-Disposition: attachment; filename='.$name);
    dheader('Content-Type: application/octet-stream');
    dheader('Content-Length: '.$filesize);
    @ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    while (!feof($fp)) {
        echo fread($fp, $chunk);
        @ob_flush();  // flush output
        @flush();
    }
    @unlink($filename);
    exit();
}
elseif($do == 'updateTabdataBytid'){
    $tid = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
    $tabdata = C::t('#tab#tab')->fetch($tid);
    $gid = $tabdata['gid'];
    //获取当前专辑的字段数
    $tabgroupdata = C::t('#tab#tab_group')->fetch($gid);
    $formfiled = unserialize($tabgroupdata['formfiled']);
    $tabgroupfileds = [];
    foreach($formfiled as $k=>$v){
        if(strpos($k,'tabgroup_')===0 && $v['status']){
            $tabgroupfileds[] = $k;
        }
    }

    $totalnum = count($tabgroupfileds);
    $hasdatanum =  DB::result_first("select count(*) from %t where tid = %d and skey in(%n)",['tab_attr',$tid,$tabgroupfileds]);
    //信息完整度
    $infopercent = round(100*$hasdatanum/$totalnum,2);
    $modelnumarr = [];
    
    //文件和专辑模块数据个数
    foreach(DB::fetch_all("select * from %t where (cate = %d or cate = %d) and gid = %d",array('tab_banner',0,2,$gid)) as $val){
        if($v['cate'] == 0){
            $ulevel = $_G['pichomelevel'];
            $sql = ' from %t r left join %t rtab on rtab.rid = r.rid ';
            $selectsql = "select count(DISTINCT r.rid) ";
            $params = ['pichome_resources', 'pichome_resourcestab'];
            $para = [$tid, $ulevel];
            $wheresql = ' r.isdelete = 0 and rtab.tid = %d and r.level <= %d ';

            $content = unserialize($val['content']);
            if ($content['exts']) {
                $wheresql .= ' and r.ext in (%n) ';
                $para[] = explode(',', $content['exts']);
            }

            if ($content['tags']) {
                $tagnames = explode(',', $content['tags']);
                if($lang){
                    foreach(DB::fetch_all("select idvalue from %t where idtype=8 and svalue in(%n)",['lang_'.$lang,$tagnames]) as $tag){
                        $tagval[] = $tag['idvalue'];
                    }
                }
                //获取标签id
                $tagval = [];
                foreach (DB::fetch_all("select tid from %t where tagname in(%n)", ['pichome_tag', $tagnames]) as $tag) {
                    $tagval[] = $tag['tid'];
                }
                $tagval = array_unique($tagval);

                $tagwheresql = [];
                foreach ($tagval as $k => $v) {
                    $sql .= ' left join %t rt' . ($k + 1) . ' on rt' . ($k + 1) . '.rid = r.rid  ';
                    $params[] = 'pichome_resourcestag';
                    $tagwheresql[] = '  (rt' . ($k + 1) . '.tid = %d and !isnull(rt' . ($k + 1) . '.tid)) ';
                    $para[] = $v;
                }
                if (empty($tagwheresql)) $wheresql .= ' and 0 ';
                if (count($tagwheresql) > 1) $wheresql .= " and (" . implode(' or ', $tagwheresql) . ')';
                elseif (count($tagwheresql)) $wheresql .= " and $tagwheresql[0] ";
            }
            $whererangesql = [];
            foreach ($content['range'] as $v) {
                if ($v['appid']) {
                    $tmpwhererangesql = ' r.appid = %s ';
                    $para[] = $v['appid'];

                    if ($v['fids']) {
                        if (!in_array('pichome_folderresources', $params)) {
                            $sql .= ' LEFT JOIN %t fr on r.rid=fr.rid ';
                            $params[] = 'pichome_folderresources';
                        }
                        $childsqlarr = [];
                        foreach ($v['fids'] as $v1) {
                            $childsqlarr[] = " fr.pathkey like %s ";
                            $tpathkey = DB::result_first("select pathkey from %t where fid = %s", array('pichome_folder', $v1));
                            $para[] = $tpathkey . '%';
                        }
                        if ($childsqlarr) $tmpwhererangesql .= ' and (' . implode(' or ', $childsqlarr) . ')';
                    }
                    $whererangesql[] = $tmpwhererangesql;
                }
            }
            $params = array_merge($params, $para);
            if ($whererangesql) {
                $wheresql .= ' and (' . implode(' or ', $whererangesql) . ')';
            }
            $num = DB::result_first( "$selectsql $sql where $wheresql",$params);
            $modelnumarr[] = ['tid'=>$tid,'bid'=>$val['id'],'num'=>$num,'gid'=>$gid];
        }elseif($v['cate'] == 2){
            $content = unserialize($val['content']);

            $rgids = [];
            foreach ($content['range'] as $v) {
                $rgids[] = $v['gid'];
            }

            $relationtype = $content['relationtype'];
            $rtids = [];
            $selectsql = 'count(DISTINCT(t.tid))';
            $sql = " from %t t  ";
            $params = ['tab'];
            if ($relationtype) {
                //查询关联档前tid的卡片
                $crtids = [];
                foreach (DB::fetch_all("select tid,rtid from %t where (tid = %d and rgid in(%n)) or (rtid=%d and gid in(%n))", ['tab_relation', $tid, $rgids, $tid, $rgids]) as $v) {

                    if ($v['tid'] == $tid) {
                        $crtids[] = $v['rtid'];
                    } else {
                        $crtids[] = $v['tid'];
                    }
                }

                $wheresql = " where (t.tid in(select rt.tid from %t rt where rt.rtid in(%n) and rt.rgid in(%n) and rt.gid = %d) or 
                t.tid in(select rt.rtid from %t rt where rt.tid in(%n) and rt.gid in(%n) and rt.rgid = %d))  ";
                $para[] = 'tab_relation';
                $para[] = $crtids;
                $para[] = $rgids;
                $para[] = $gid;
                $para[] = 'tab_relation';
                $para[] = $crtids;
                $para[] = $rgids;
                $para[] = $gid;
            } else {
                $wheresql = " where (t.tid in(select rt.rtid from %t rt where rt.rgid in(%n) and rt.tid = %d) or 
                t.tid in(select rt.tid from %t rt where rt.gid in(%n) and rt.rtid = %d))  ";
                $para[] = 'tab_relation';
                $para[] = $rgids;
                $para[] = $tid;
                $para[] = 'tab_relation';
                $para[] = $rgids;
                $para[] = $tid;
            }


            $params = array_merge($params, $para);
            $num = DB::result_first("select $selectsql $sql  $wheresql  ", $params);
            $modelnumarr[] = ['tid'=>$tid,'bid'=>$val['id'],'num'=>$num,'gid'=>$gid];
        }
    }
    
    $returnarr = ['infopercent'=>$infopercent,'updatedateline'=>dgmdate(TIMESTAMP,'Y-m-d H:i:s')];
    foreach($modelnumarr as $v){
        C::t('#tab#tab_statsmodel')->insertData($v);
        $returnarr['bnum_'.$v['bid']] = $v['num'];
    }
    
    $statsdata = ['tid'=>$tid,'infopercent'=>$infopercent,'gid'=>$gid,'dateline'=>TIMESTAMP];
    C::t('#tab#tab_stats')->insertData($statsdata);
    exit(json_encode(array('success'=>true,'data'=>$returnarr)));

}
elseif($do == 'getSatasLeftMenu'){//获取左侧菜单列表
    $data = [
            [
            'title'=>lang('overview').lang('statistics'),
             'type'=>'view',
            ],
           [
            'title'=>lang('download').lang('statistics'),
             'type'=>'download',
            ],
          [
            'title'=>lang('upload').lang('statistics'),
             'type'=>'upload',
            ],
          [
            'title'=>lang('file').lang('statistics'),
             'type'=>'file',
            ],
          [
            'title'=>lang('login').lang('statistics'),
             'type'=>'userlogin',
            ]
    ];
    $tabgroupdata = [];
    //获取所有开启的专辑列表
    Hook::listen('gettabgroupdata',$tabgroupdata,'edits');
    if(!empty($tabgroupdata)){
        foreach($tabgroupdata as $v){
            if($v['isdelete'] == 0){
                $data[] = array(
                    'title'=>$v['name'],
                    'type'=>'tabgroup'.$v['gid'],
                    'gid'=>$v['gid']
                );
            }

        }
    }
    exit(json_encode(['success'=>true,'data'=>$data]));

}
else{//概览
    $data = array();
     $userwhere = $filewhere = " 1 ";
    $fileparams = array('pichome_resources');
    $userparams = array('pichome_resources');
    $downparams = array('stats_view',1);
    $uploadparams = array('stats_view',3);
    $duwhere = " idtype = %d  ";
    $uploadwhere = " idtype = %d ";
    if($start){
        $filewhere .= " and btime >= %d";
        $fileparams[] = $start*1000;
        $userwhere .= " and regdate >= %d";
        $userparams[] = $start;
        $duwhere .= " and dateline >= %d";
        $downparams[] = $start;
        $uploadwhere .= " and dateline >= %d";
        $uploadparams[] = $start;
    }
    if($end){
        $filewhere .= " and btime < %d";
        $fileparams[] = $end*1000;
        $userwhere .= " and regdate < %d";
        $userparams[] = $end;
        $duwhere .= " and dateline < %d";
        $downparams[] = $end;

        $uploadwhere .= " and dateline >= %d";
        $uploadparams[] = $end*1000;
    }
    //文件总数和大小查询
    $fliedata = DB::fetch_first("select count(rid) as num ,sum(size) as totalsize from %t where $filewhere",$fileparams);

    //类型统计最多的十种
    $extdata = DB::fetch_all("select ext,count(rid) as extnum from %t where $filewhere GROUP BY ext order by extnum desc limit 0,10",$fileparams);
    //如果统计文件类型有十种则判断是否有其他类型文件，有归类于其它
    if(count($extdata) == 10){
        $extnums = 0;
        foreach($extdata as $v){
            $extnums += intval($v['extnum']);
        }
        $othernum = $fliedata['num'] - $extnums;
        if($othernum > 0) $extdata[] = array('ext'=>'other','extnum'=>$othernum);
    }
    //用户统计
    $usernmu = DB::result_first("select count(uid) from %t where 1",array('user'));

    //下载统计
    $downloadnum = DB::result_first("select count(id) from %t where $duwhere",$downparams);

    //上传统计
    $uploadnum = DB::result_first("select count(id) from %t  where $uploadwhere",$uploadparams);
    $data = array(
        'filenum'=>intval($fliedata['num']),
        'fsize'=>formatsize($fliedata['totalsize']),
        'extdata'=>$extdata,
        'downloadnum'=>$downloadnum ? $downloadnum:0,
        'uploadnum'=>$uploadnum ? $uploadnum:0,
        'usernum'=>$usernmu ? $usernmu:0
    );
    exit(json_encode(array('data'=>$data,'isadmin'=>$isadmin,'navtitle'=>$navtitle)));
}
function getColIndex($index){
    $string="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $ret='';
    if($index>255) return '';
    for($i=0;$i<floor($index/strlen($string));$i++){
        $ret=$string[$i];
    }
    $ret.=$string[($index%(strlen($string)))];
    return $ret;
}
function getOpenUrl($datas){
    static $extall=array();
    if($datas['type'] == 'link'){
        return array('type'=>'attach','url'=>$datas['url']);
    }
    if(empty($extall)) $extall=C::t('app_open')->fetch_all_ext();
    $exts=array();
    foreach($extall as $value){
        if(!isset($exts[$value['ext']]) || $value['isdefault']) $exts[$value['ext']]=$value;
    }
    $ext = $datas['ext'];
    $dpath = $datas['dpath'];
    $type= $datas['type'];
    $filename= $datas['filename'];
    if($exts[$ext]){
        $data=$exts[$ext];
    }elseif($exts[$type]){
        $data=$exts[$type];
    }else $data=array();
    if($data){
        $url=$data['url'];
        if(strpos($url,'dzzjs:OpenPicWin')!==false){//dzzjs形式时
            return array('type'=>'image','url'=>'index.php?mod=io&op=thumbnail&size=large&path='.$dpath);
        }else{
            //替换参数
            $url=preg_replace_callback("/{(\w+)}/i", function($matches) use($ext,$dpath){
                $key=$matches[1];
                if($key=='path'){
                    return $dpath;
                }elseif($key=='ext'){
                    return $ext;
                }else{
                    return '';
                }
            }, $url);
            //添加path参数；
            if(strpos($url,'?')!==false  && strpos($url,'path=')===false){
                $url.='&path='.$dpath;
            }
            return array('type'=>'attach','url'=>$url);
        }

    }else{//没有可用的打开方式，转入下载；
        return array('type'=>'download','url'=>'index.php?mod=io&op=download&filename='.$filename.'&path='.$dpath);
    }
}
function getuserIcon($uids, $datas, &$data)
{
    $colors = array('#6b69d6', '#a966ef', '#e9308d', '#e74856', '#f35b42', '#00cc6a', '#0078d7', '#5290f3', '#00b7c3', '#0099bc', '#018574', '#c77c52', '#ff8c00', '#68768a', '#7083cb', '#26a255');
    $uids = array_unique($uids);
    $avatars = array();
    foreach (DB::fetch_all('select u.avatarstatus,u.uid,s.svalue from %t  u left join %t  s on u.uid=s.uid and s.skey=%s where u.uid in(%n)', array('user', 'user_setting', 'headerColor', $uids)) as $v) {
        if ($v['avatarstatus'] == 1) {
            $avatars[$v['uid']]['avatarstatus'] = 1;
        } else {
            $avatars[$v['uid']]['avatarstatus'] = 0;
            $avatars[$v['uid']]['headerColor'] = $v['svalue'];
        }
    }
    foreach ($datas as $v) {
        $uid = $v['uid'];
        $v['text'] = $v['username'];
        if ($avatars[$v['uid']]['avatarstatus']) {
            $v['icon'] = 'avatar.php?uid=' . $uid;
        } elseif ($avatars[$uid]['headerColor']) {
            $v['headerColor'] = $avatars[$uid]['headerColor'];
            $v['firstword'] = strtoupper(new_strsubstr($v['username'], 1, ''));
            $v['icon'] = false;
            $v['text'] = '<span class="Topcarousel" style="background:' . $v['headerColor'] . ';" title="' . preg_replace("/<em.+?\/em>/i", '', $v['text']) . '">' . $v['firstword'] . '</span>' . $v['text'];

        } else {
            $v['icon'] = false;
            $colorkey = rand(1, 15);
            $headerColor = $colors[$colorkey];
            C::t('user_setting')->insert_by_skey('headerColor', $headerColor, $uid);
            $v['firstword'] = strtoupper(new_strsubstr($v['text'], 1, ''));
            $v['headerColor'] = $headerColor;
        }
        $data[$uid] = $v;
    }
}