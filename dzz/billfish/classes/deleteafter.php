<?php

namespace dzz\billfish\classes;

use \core as C;
use \DB as DB;


class deleteafter
{

    public function run($data)
    {
        C::t('#billfish#billfish_record')->delete_by_rids($data['rids']);

    }



}