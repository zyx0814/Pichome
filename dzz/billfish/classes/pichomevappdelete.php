<?php

namespace dzz\billfish\classes;

use \core as C;
use \DB as DB;
use \fmpeg as fmpeg;

class pichomevappdelete
{

    public function run($data)
    {
        if($data['type'] == 2){
            C::t("#billfish#billfish_record")->delete_by_appid($data['appid']);
            C::t("#billfish#billfish_folderrecord")->delete_by_appid($data['appid']);
            C::t("#billfish#billfish_tagrecord")->delete_by_appid($data['appid']);
            C::t("#billfish#billfish_taggrouprecord")->delete_by_appid($data['appid']);
        }
        return true;

    }



}