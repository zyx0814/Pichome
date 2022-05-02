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
if(!$_G['uid']) exit();
require libfile('function/organization');

$toporgid=0;
$orgid=intval($_GET['orgid']);
if($org=C::t('organization')->fetch($orgid)){
	if($org['forgid']==0){
		$toporgid=$orgid;
	}else{
		//获取此部门的顶级机构
		$orguptree= C::t('organization')->fetch_parent_by_orgid($orgid,true);
		$toporgid=$orguptree[0];
	}
}
//机构列表
$orgtree=getDepartmentOption($toporgid,'',true);

//获取部门的用户列表；

$userlist=C::t('organization_user')->fetch_user_by_orgid($orgid);

//获取机构部门树
//$departmenttree=getDepartmentOption($orgid);

include template('misc_seluser');
?>
