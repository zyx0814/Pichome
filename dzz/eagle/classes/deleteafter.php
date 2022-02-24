<?php

namespace dzz\eagle\classes;

use \core as C;
use \DB as DB;


class deleteafter
{

    public function run($data)
    {
        C::t('#eagle#eagle_record')->delete_by_rids($data['rids']);

    }



}