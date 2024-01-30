<?php

namespace dzz\pichome\classes;

use \core as C;
class addvappafter
{

    public function run($data)
    {
        global $_G;

            $setarr = [
                'bannername'=>$data['appname'],
                'bdata'=>$data['appid'],
                'btype'=>0,
                'issystem'=>1,
                'icon'=>0,
            ];
            C::t('pichome_banner')->insert_data($setarr);



    }
}