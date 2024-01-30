<?php
if (!defined('IN_OAOOA') && !defined('IN_ADMIN')) {
    exit('Access Denied');
}
$do = isset($_GET['do']) ? trim($_GET['do']):'';
if($do == 'addspace'){
    $bz=$_GET['bz'];
    IO::authorize($bz);
    exit();
}else{
    include template('addspace');
}