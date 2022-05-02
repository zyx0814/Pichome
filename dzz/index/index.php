<?php
    ///*
    // * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
    // * @license     https://www.oaooa.com/licenses/
    // *
    // * @link        https://www.oaooa.com
    // * @author      zyx(zyx@oaooa.com)
    // */
    ////此页的调用地址  index.php?mod=test;
    ////同目录的其他php文件调用  index.php?mod=test&op=test1;
    //
    if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    	exit('Access Denied');
    }
    //Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
    //设置默认首页
    $configfile = DZZ_ROOT.'data/cache/default_mod.php';
    $configarr = array();
    //$vapp=DB::fetch_first("select * from %t where 1",array('vapp'));
    $configarr['default_mod' ]='pichome';
    @file_put_contents($configfile,"<?php \t\n return ".var_export($configarr,true).";");
    C::t('setting')->update('default_mod','pichome');
    include_once libfile('function/cache');
    updatecache('setting');
    /*//修改地址
    $file=realpath(DZZ_ROOT.'./dzz/imageTool/js/widgets.js');
    $file1=(DZZ_ROOT.'./dzz/imageTool/js/widgets.min.js');
    $content=file_get_contents($file);
    $content=str_replace('http://127.0.0.2',rtrim($_G['siteurl'],'/'),$content);
    file_put_contents($file1,$content);*/
    dheader("location: ".$_G['siteurl'].'index.php?mod=pichome');
    exit();
    Hook::listen("adminlogin");
    // <!-- require_once('vue/dist/index.html'); -->