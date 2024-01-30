<?php
if(!isset($_GET['count'])){
    $count = DB::result_first("select count(rid) from %t where isnull(pathmd5) or pathmd5 = '' ",array('pichome_resources_attr'));
}else{
    $count = intval($_GET['count']);
}


$donum = 0;
$i = isset($_GET['i']) ? intval($_GET['i']):1;
$perpage = 5;
$start = ($i-1)*$perpage;
$limitsql = " limit $start,$perpage";
$j = 0;
foreach(DB::fetch_all("select rid,appid,path from %t where isnull(pathmd5) or pathmd5 = '' order by rid  $limitsql",array('pichome_resources_attr')) as $v){
    $pathmd5 = md5($v['path'].$v['appid']);
    if(C::t('pichome_resources_attr')->update($v['rid'],['pathmd5'=>$pathmd5])){
        $j++;
    }

}
$donum =  ($i-1)*$perpage+$j;
if($donum < $count){
    $i += 1;
    show_msg('升级属性表数据：'.$donum.'/'.$count, getglobal('siteurl').'index.php?mod=pichome&op=ceshi&i='.$i.'&count='.$count);
}else{
    show_msg('升级完成');
}

function show_msg($message, $url_forward='', $time = 1, $noexit = 0, $notice = '') {

    if($url_forward) {
        $url_forward = $_GET['from'] ? $url_forward.'&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : $url_forward;
        $message = "<a href=\"$url_forward\">$message (跳转中...)</a><br>$notice<script>setTimeout(\"window.location.href ='$url_forward';\", $time);</script>";
    }

    show_header();
    print<<<END
	<table>
	<tr><td>$message</td></tr>
	</table>
END;
    show_footer();
    !$noexit && exit();
}


function show_header() {
    global $config;

    $nowarr = array($_GET['step'] => ' class="current"');
    if(in_array($_GET['step'], array('waitingdb','prepare'))) {
        $nowarr = array('sql' => ' class="current"');
    }
    print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$config[charset]" />
	<title> 数据库升级程序 </title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
	</style>
	</head>
	<body>
	<div class="bodydiv">
	<h1>升级工具</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>

	</tr>
	</table>
	<br>
END;
}

function show_footer() {
    print<<<END
	</div>
	<div id="footer">Copyright © 2012-2021 oaooa.com All Rights Reserved.</div>
	</div>
	<br>
	</body>
	</html>
END;
}