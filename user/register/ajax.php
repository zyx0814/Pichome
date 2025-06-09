<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

if($_GET['do']=='bbrule'){
	$setting = $_G['setting'];
	$bbrules = $_G['setting']['bbrules'];
	$bbrulestxt = $_G['setting']['bbrulestxt'];
	$bbrulestxt = nl2br("$bbrulestxt");
	exit(json_encode(array('success'=>true,'data'=>$bbrulestxt)));
}