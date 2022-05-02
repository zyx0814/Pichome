<?php

namespace dzz\local\classes;

use \core as C;
use \DB as DB;

class pichomevappdelete
{

    public function run($data)
    {
        if($data['apptype'] == 1){
            C::t("#local#local_record")->delete_by_appid($data['appid']);
        }
        return true;

    }



}