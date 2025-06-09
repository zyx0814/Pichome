<?php

namespace dzz\pichome\classes;

use \DB as DB;
use \core as C;

class  deltabgroupafter
{
    public function run(&$gid)
    {
        if (!$gid) return false;
        foreach (DB::fetch_all("select appid,fileds form %t where isdelete < 1", array('pichome_vapp')) as $v) {
            $appid = $v['appid'];
            $fileds = unserialize($v['fileds']);
            if ($fileds) {
                foreach ($fileds as $k => $val) {
                    if ($val['type'] == 'tabgroup' && $val['flag'] == 'tabgroup_' . $gid) {
                        unset($fileds[$k]);
                        C::t('#pichome#pichome_vapp')->update($appid, ['fileds' => serialize($fileds)]);
                    }

                }
            }
        }
    }

}