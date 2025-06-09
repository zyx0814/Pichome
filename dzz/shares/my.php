<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$navtitle=lang('myShares');
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$do=$_GET['do'];
$now = dgmdate(TIMESTAMP, 'Y-m-d');
$actionData = array(
    'all' => array('key' => 'all', 'name' => lang('all'), 'value' => ''),
    'day1' => array('key' => 'day1', 'name' => lang('filter_range_day'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60, 'Y-m-d') . '_' . $now),
    'week' => array('key' => 'week', 'name' => lang('filter_range_week'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 7, 'Y-m-d') . '_' . $now),
    'month' => array('key' => 'month', 'name' => lang('filter_range_month'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 30, 'Y-m-d') . '_' . $now),
    'year' => array('key' => 'year', 'name' => lang('filter_range_year'), 'value' => dgmdate(TIMESTAMP - 24 * 60 * 60 * 365, 'Y-m-d') . '_' . $now),
);
$ismobile = helper_browser::ismobile();
if ($ismobile) {
    include template('mobile/page/myshares');
} else {
    include template('pc/page/myshares');

}
