<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
	
if(!defined('IN_OAOOA')) {
	exit('Access Denied');
}
global $_G;
Hook::listen('adminlogin');
$uid = $_G['uid'];
include libfile('function/filerouterule');
require libfile('function/code');
$operation = $_GET['operation'] ? trim($_GET['operation']) : '';
if($operation == 'getApp'){//获取当前用户应用
	$config = array();
	if($_G['uid']){
		$config= dzz_userconfig_init();
		if(!$config){
			//$config= dzz_userconfig_init();
			if($config['applist']){
				$applist=explode(',',$config['applist']);
			}else{
				$applist=array();
			}
		 }else{//检测不允许删除的应用,重新添加进去
			if($config['applist']){
				$applist=explode(',',$config['applist']);
			}else{
				$applist=array();
			}
			if($applist_n =array_keys(C::t('app_market')->fetch_all_by_notdelete($_G['uid']))) {
			
				$newappids = array();
				foreach ($applist_n as $appid) {
					if (!in_array($appid, $applist)) {
						$applist[] = $appid;
						$newappids[] = $appid;
					}
				}
				if ($newappids) C::t('app_user')->insert_by_uid($_G['uid'], $newappids);
				C::t('user_field')->update($_G['uid'], array('applist' => implode(',', $applist)));
			}
		 }

	}else{
		 $applist =array_keys(C::t('app_market')->fetch_all_by_default());
	}

	//获取已安装应用
	$app=C::t('app_market')->fetch_all_by_appid($applist); 
	
	$applist_1=array();
	$applist_2=array();
	$identifier=array('imageTool','picture');
	foreach($app as $key => $value){
		if($value['isshow']<1) continue;
		if($value['available']<1) continue;
		if($value['position']<1) continue;//位置为无的忽略
		//判断管理员应用
		if($_G['adminid']!=1 && $value['group']==3){
			continue;
		}
		$params=array('name'=>$value['identifier'],'perm'=>1,'return_type'=>'bool');
		if(Hook::listen('rolecheck',$params,null,true)===false) continue;
		//if($value['system'] == 2) continue;
		if(in_array($value['identifier'], $identifier)){
			$applist_1[] = $value; 
		}else{
			$applist_2[] = $value; 
		}
		
	}
	//获取虚拟应用
	$params=array();
	$vapps=Hook::listen('vapplist',$params,null,true);
	foreach($vapps as $key => $value){
		$params=array('name'=>'vapp/'.$value['identifier'],'perm'=>1,'return_type'=>'bool');
		if(Hook::listen('rolecheck',$params,null,true)===false) continue;
		$applist_1[] = $value;
	}
	//对应用根据disp 排序
	if($applist_1){
		$sort = array(
			  'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
			  'field'     => 'disp', //排序字段
		);
		$arrSort = array();
		foreach($applist_1 AS $uniqid => $row){
			foreach($row AS $key=>$value){
				$arrSort[$key][$uniqid] = $value;
			}
		}
		if($sort['direction']){
			array_multisort($arrSort[$sort['field']], constant($sort['direction']), $applist_1);
		} 
	}
	exit(json_encode(array('data'=>array_merge($applist_1,$applist_2))));

}else{
	// 地址栏名称
	$navtitle = $_G['setting']['sitename'];
	// 获取hash
	$hash = FORMHASH;
	
	// 获取当前用户信息
	$userData = array();
	$udata = DB::fetch_first("select u.uid,u.avatarstatus,u.username,u.groupid,u.adminid,u.nickname,s.svalue from %t u
	left join %t s on s.uid = u.uid and s.skey=%swhere u.uid =%d",
	array('user','user_setting','headerColor',$uid));
		if($udata['avatarstatus'] == 1){
			$userData['icon'] = 'avatar.php?uid='.$udata['uid'].'&random='.VERHASH;
		}elseif($udata['svalue']){
			$userData['firstword'] = strtoupper(new_strsubstr($udata['username'],1,''));
			$userData['headerColor'] = $udata['svalue'];
			$userData['icon'] = false;
		}else{
			$colorkey = rand(1,15);
			$headerColor = $colors[$colorkey];
			C::t('user_setting')->insert_by_skey('headerColor',$headerColor,$udata['uid']);
			$userData['firstword'] = strtoupper(new_strsubstr($udata['username'],1,''));
			$userData['headerColor'] = $udata['svalue'];
		}
		if( C::t('user')->checkfounder($udata)){
			$userData['perm'] = 3;
		}elseif($udata['adminid'] == 1){
			$userData['perm'] = 2;
		}else{
			$userData['perm'] = 1;
		}
	$userData['username'] = $udata['username'];
	$userData['uid'] = $uid;
	$userData['language'] = $_G[language];
	$userData['upgrade'] = $_G['setting']['upgrade'];
	$userData['version'] = $_G['setting']['version'];
	
	// 获取顶部应用
	if($userData['language'] == 'en-US'){
		$navMenuNames = ['System setting','Cloud settings','Orguser','App','Statistics','System Tool'];
	}else{
		$navMenuNames = ['系统设置','云设置','用户','应用','统计','系统工具'];
	}
	$navMenu = array(
		array(
			'name'=>$navMenuNames[0],
			'index'=>'setting',
			'type'=>'admin'
		),
		array(
			'name'=>$navMenuNames[1],
			'index'=>'cloudsetting',
			'type'=>'admin'
		),
		array(
			'name'=>$navMenuNames[2],
			'index'=>'orguser',
			'type'=>'admin'
		),
		array(
			'name'=>$navMenuNames[3],
			'index'=>'app',
			'type'=>'admin'
		),
		array(
			'name'=>$navMenuNames[4],
			'index'=>'stats',
			'type'=>'dzz'
		),
		array(
			'name'=>$navMenuNames[5],
			'index'=>'system',
			'type'=>'admin'
		),
	);
	// 获取通知
	$notice_num=DB::result_first("select COUNT(*) from %t where new>0 and uid=%d",array('notification',$_G['uid']));

	exit(json_encode(array('hash'=>$hash,'navMenu'=>$navMenu,'userData'=>$userData,'notice_num'=>$notice_num,'navtitle'=>$navtitle)));
}
?>
