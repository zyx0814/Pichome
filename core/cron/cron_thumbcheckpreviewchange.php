<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}

@set_time_limit(0);
dfsockopen(getglobal('localurl'). 'misc.php?mod=chkupdatePreviewthumb',0, '', '', false, '', 1);