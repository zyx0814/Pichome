<?php

namespace dzz\eagle\classes;

use \core as C;
use \DB as DB;
use \fmpeg as fmpeg;

class pichomevappdelete
{

    public function run($data)
    {
        if($data['apptype'] == 0){
            C::t("#eagle#eagle_record")->delete_by_appid($data['appid']);
            C::t("#eagle#eagle_folderrecord")->delete_by_appid($data['appid']);
        }
        return true;

    }



}