<?php

namespace dzz\eagle\classes;

use \core as C;
use \DB as DB;
use \fmpeg as fmpeg;

class delpichomefolderafter
{

    public function run($appid)
    {
        C::t("#eagle#eagle_folderrecord")->delete_by_appid($appid);
    }


}