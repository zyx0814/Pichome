<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/3/1
 * Time: 16:26
 */
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
Hook::listen('mod_run');//执行配置
@include realpath(Hook::listen('mod_start',$_GET,null,true));//模块路由
dexit();