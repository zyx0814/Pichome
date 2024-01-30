<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
$rid  = isset($_GET['rid']) ? trim($_GET['rid']):'';
$aid  = isset($_GET['aid']) ? intval($_GET['aid']):0;

//生成缩略图和转换记录数据
if($rid)dfsockopen(getglobal('localurl') . 'misc.php?mod=thumbconvertrecord&rid='.$rid, 0, '', '', false, '',1);
//移动文件默认位置
if($aid)dfsockopen(getglobal('localurl') . 'misc.php?mod=movespce&aid='.$aid, 0, '', '', false, '',1);
//执行缩略图转换
dfsockopen(getglobal('localurl') . 'misc.php?mod=getthumb', 0, '', '', false, '',1);
//获取文件信息
dfsockopen(getglobal('localurl') . 'misc.php?mod=getinfo', 0, '', '', false, '',1);
//执行音视频转换
dfsockopen(getglobal('localurl') . 'misc.php?mod=convert', 0, '', '', false, '',1);