<?php
namespace dzz\stats\classes;

use \core as C;

class addTokenuse{

    public function run($data){
        C::t('#stats#stats_token')->insertData($data);
    }
}