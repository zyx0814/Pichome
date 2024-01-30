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
$langList = $_G['config']['output']['language_list']; 

$langset=trim($_GET['lang']);
if(!isset($langList[$langset])){
	exit(json_encode(array('error'=>'error')));
}else{
	if($_G['uid'])	C::t('user')->update($_G['uid'], array('language' => ($langset)));
	dsetcookie('language',$langset,60*60*24*30);
	include libfile('function/cache');
	cleartemplatecache();
	C::memory()->clear();
	exit(json_encode(array('msg'=>'success')));
}