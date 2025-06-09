<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
global $_G;
$aid = isset($_GET['aid']) ? intval($_GET['aid']):0;
$remoteid = isset($_GET['remoteid']) ? intval($_GET['remoteid']):0;
if(!$remoteid) {
    $defaultspace = $_G['setting']['defaultspacesetting'];
    $remoteid = $defaultspace['did'];
}
if($attach = C::t('attachment')->fetch($aid)){
    io_remote::Migrate($attach,$remoteid);
}
exit('success');