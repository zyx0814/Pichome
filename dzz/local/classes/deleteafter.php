<?php

namespace dzz\local\classes;

use \core as C;
use \DB as DB;


class deleteafter
{

    public function run($data)
    {
        if(!empty($data['rids'])){
            $appid = DB::result_first("select appid from %t where rid = %s",array('pichome_resources',$data['rids'][0]));
            $appdata = C::t('pichome_vapp')->fetch($appid);
            if($appdata['type'] == 1){
                $delnum = count($data['rids']);
                C::t('pichome_vapp')->add_getinfonum_by_appid($appid,-$delnum);
            }
            C::t('#local#local_record')->delete_by_rids($data['rids']);
        }


    }



}