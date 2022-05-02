<?php

namespace dzz\billfish\classes;

use \core as C;
use \DB as DB;
use \fmpeg as fmpeg;

class delpichomefolderafter
{

    public function run($data)
    {
        C::t("#billfish#billfish_folderrecord")->delete_by_appid($data['appid']);
    }


}