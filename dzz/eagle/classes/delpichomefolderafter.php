<?php

namespace dzz\eagle\classes;

use \core as C;
use \DB as DB;
use \fmpeg as fmpeg;

class delpichomefolderafter
{

    public function run($data)
    {
        C::t("#eagle#eagle_folderrecord")->delete_by_appid($data['appid']);
    }


}