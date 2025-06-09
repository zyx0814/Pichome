<?php

namespace dzz\pichome\classes;

use \core as C;
use \DB as DB;
class pichomevappdelete
{

    public function run($data)
    {
        global $_G;
        if($data['isdelete']){
            DB::delete('pichome_banner',array('bdata'=>$data['appid'],'issystem'=>1));
        }else{
            DB::update('pichome_banner',array('isshow'=>0),array('bdata'=>$data['appid'],'issystem'=>1));
        }


    }
}