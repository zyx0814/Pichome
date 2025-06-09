<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);

foreach (DB::fetch_all("select id,cachetime from %t where cachetime > 0", array('pichome_templatetagdata')) as $v) {
    $cachename = 'templatetagdata_' . $v['id'];
    //获取缓存提前生成时间间隔
    $beforetime = floor($v['cachename'] * 0.1);
    //只有间隔时间大于1分钟才执行更新缓存
    if ($beforetime > 60) {
        //获取缓存时间
        $startdateline = C::t('cache')->get_cachetime_by_cachename($cachename);
        //如果时间到达生成时间条件
        if (($startdateline + $v['cachename'] - $beforetime) >= TIMESTAMP) {
            dfsockopen(getglobal('localurl') . 'misc.php?mod=updatepagedata&tdid='.$v['id'], 0, '', '', false, '', 1);
        }
    }

}