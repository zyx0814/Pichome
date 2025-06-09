<?php

namespace dzz\banner\classes;

use \DB as DB;
use \core as C;

class  statskeywordaddafter
{
    public function run(&$data)
    {
       C::t('keyword_hots')->insert_data($data);
    }

}