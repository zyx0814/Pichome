<?php
    if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
        exit('Access Denied');
    }
	$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
	if ($operation == 'column') {
		include template('admin/pc/page/site/column');
	}elseif($operation == 'home'){
		include template('admin/pc/page/site/home');
	}elseif($operation == 'about'){
		include template('admin/pc/page/site/about');
	}elseif($operation == 'casepartner'){
		include template('admin/pc/page/site/casepartner');
	}
	
