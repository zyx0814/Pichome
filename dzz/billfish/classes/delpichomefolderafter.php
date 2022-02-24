<?php

namespace dzz\billfish\classes;

use \core as C;
use \DB as DB;
use \fmpeg as fmpeg;

class delpichomefolderafter
{

    public function run($appid)
    {
        C::t("#billfish#billfish_folderrecord")->delete_by_appid($appid);
    }


}