<?php

namespace dzz\banner\classes;

use \DB as DB;
use \core as C;

class  statsviewaddafter
{
    public function run(&$data)
    {
        C::t('views')->insert_data($data);
    }

}