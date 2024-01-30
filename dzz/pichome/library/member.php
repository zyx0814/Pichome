<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
global $_G;
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
$appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
if($_G['adminid'] != 1) exit(json_encode(array('success'=>false,'msg'=>'没有权限')));
if(!$appid){
    exit(json_encode(array('success'=>false,'msg'=>'缺少必要参数')));
}
if($operation == 'addmember'){//添加成员
    $uids = isset($_GET['uids']) ? trim($_GET['uids']):'';
    $uidarr = explode(',',$uids);
    $uid = [];
    foreach ($uidarr as $v){
        if(strpos($v,'g_') === 0){
           $tmporgid = intval(str_replace('g_','',$v));
           $opath = DB::result_first("select pathkey from %t where orgid = %d",array('organization',$tmporgid));
           $opath = str_replace('_','',$opath);
           $orgids = explode('_',$opath);
           $ouids = C::t('organization_user')->fetch_uids_by_orgid($orgids);
           $uid = array_merge($uid,$ouids);
        }else{
            $uid[] = intval($v);
        }
    }
    $uid = array_unique($uid);
    C::t('pichome_vappmember')->addmember($appid,$uid);
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'delmember'){//移除成员
    $uids = isset($_GET['uids']) ? trim($_GET['uids']):'';
    $uidarr = explode(',',$uids);
    $uid = [];
    foreach ($uidarr as $v){
        if(strpos($v,'g_') === 0){
            $tmporgid = intval(str_replace('g_','',$v));
            $opath = DB::result_first("select pathkey from %t where orgid = %d",array('organization',$tmporgid));
            $opath = str_replace('_','',$opath);
            $orgids = explode('_',$opath);
            $ouids = C::t('organization_user')->fetch_uids_by_orgid($orgids);
            $uid = array_merge($uid,$ouids);
        }else{
            $uid[] = intval($v);
        }
    }
    $uid = array_unique($uid);
    if(C::t('pichome_vappmember')->delete_member_by_appid_uid($appid,$uid)){
        exit(json_encode(array('success'=>true)));
    }else{
        exit(json_encode(array('success'=>false)));
    }
}elseif($operation == 'getmember'){//获取成员
	$userdatas = [];
	foreach(DB::fetch_all("select uid from %t where appid = %s ",array('pichome_vappmember',$appid)) as $v){
            $userdata = getuserbyuid($v['uid']);
            $v['username'] = $userdata['username'];
			$v['icon'] = avatar_block($v['uid']);
			$userdatas[] = $v;
	}
	exit(json_encode(array('success'=>true,'data'=>$userdatas)));
}elseif($operation == 'getuser'){
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $params = array('user');
    $wheresql = ' `status` = 0 ';
    if($keyword){
        $wheresql .= ' and username like %s ';
        $params[] = '%'.$keyword.'%';
    }
    $data = [];
    foreach(DB::fetch_all("select uid,username,adminid from %t where $wheresql",$params) as $v){
        $v['icon'] = avatar_block($v['uid']);
        $data[] = $v;
    }

    exit(json_encode(array('success'=>true,'data'=>$data)));
}else{
	include template('librarylist/pc/page/user');
}