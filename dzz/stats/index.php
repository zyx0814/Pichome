<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/14
 * Time: 15:53
 */
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
Hook::listen('check_login');
require_once(__DIR__.'/dist/index.html');