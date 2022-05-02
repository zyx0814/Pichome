<?php
    ignore_user_abort(true);
    @set_time_limit(0);
    dfsockopen(getglobal('localurl'). 'index.php?mod=pichome&op=checkexport',0, '', '', false, '', 1);
    exit('success');