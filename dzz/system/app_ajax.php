<?php
/* @authorcode  codestrings
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
$operation = $_GET['operation'] ? $_GET['operation'] : '';

if($operation == 'app'){
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
		 }
        else{//检测不允许删除的应用,重新添加进去
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
		$value['disp']+=1000;//保证库排前面
		//首页固定排在最前面
		if($_G['setting']['default_mod']==$value['identifier']){
			$value['disp']=-1000;
		}
		$applist_1[$value['appid']] = $value; 
	}
	//获取虚拟应用
	$params=array();
	$vapps=Hook::listen('vapplist',$params,null,true);
	foreach($vapps as $key => $value){
		$params=array('name'=>'vapp/'.$value['identifier'],'perm'=>1,'return_type'=>'bool');
		if(Hook::listen('rolecheck',$params,null,true)===false) continue;
		//首页固定排在最前面
		if($_G['setting']['default_mod']=='vapp_'.$value['identify']){
			$value['disp']=-1000;
		}
		$applist_1[$value['appid']] = $value;
	}
	//首页排最前面
	
	
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
	if($sortids=C::t('user_setting')->fetch_by_skey('myappids')){
		$appids=explode(',',$sortids);
		$temp=array();
		foreach($appids as $appid){
			if($applist_1[$appid]){
				$temp[$appid]=$applist_1[$appid];
				unset($applist_1[$appid]);
			}
		}
		
		foreach($applist_1 as $appid =>$value){
			$temp[$appid]=$value;
		}
		$applist_1=array_values($temp);
	}else{
		$applist_1=array_values($applist_1);
	}
	
	include template('app_ajax');
	exit();
}


