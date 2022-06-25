<?php
/**
 * Created by PhpStorm.
 * User: 86187
 * Date: 2020/3/7
 * Time: 15:53
 */
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
$path = dzzdecode($_GET['path']);
if(!$data = C::t('pichome_resources')->fetch_by_rid($path)){
	$data=IO::getMeta($path);
}
if(!$data){
	exit('file not exist!');
}
use dzz\qcosvideo\classes\info as info;

$info =new info;
$info->rundata($data);
exit('success');