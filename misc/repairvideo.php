<?php
//查询符合执行条件的数据
$datas = DB::fetch_all("SELECT * FROM %t where width=0 and ext=%s", array('pichome_resources','mp4'));
print_r($datas);die;
if ($datas) {
    foreach ($datas as $v) {

        $processname1 = 'PICHOMEGETINFO_' . $v['rid'];
        dzz_process::unlock($processname1);
        //如果当前数据是锁定状态则跳过
        if (dzz_process::islocked($processname1, 60 * 5)) {
            continue;
        }
        //更新当前数据获取缩略图执行次数和时间
        // C::t('pichome_resources_attr')->update($v['rid'],array('getdonum'=>intval($v['getdonum'])+1,'getinfotime'=>TIMESTAMP));
        $resourcesdata = C::t('pichome_resources')->fetch_data_by_rid($v['rid']);
        Hook::listen('pichomegetfileinfo', $resourcesdata);
        dzz_process::unlock($processname1);

    }
}
