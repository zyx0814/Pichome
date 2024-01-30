<?php
if (!defined('IN_OAOOA') && !defined('IN_ADMIN')) {
    exit('Access Denied');
}
$did= 3;
$clouddata = C::t('connect_storage')->fetch($did);
$hostarr = explode(':',$clouddata['hostname']);
$jsondata['bucket'] = $clouddata['bucket'];
$jsondata['region'] = $hostarr[1];
$jsondata['did'] = $clouddata['id'];
$jsondata['bz'] = $clouddata['bz'];

$jsondata = json_encode($jsondata);
include template('upload');
