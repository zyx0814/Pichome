<?php

namespace dzz\pichome\classes;

use \core as C;
use \DB as DB;
class pichomevappdelete
{

    public function run($data)
    {
        global $_G;
        DB::delete('pichome_banner',array('bdata'=>$data['appid'],'issystem'=>1));

    }
}