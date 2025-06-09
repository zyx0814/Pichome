<?php
namespace dzz\shares\classes;

use \core as C;

class mynavigation
{

    public function run(&$data)
    {
        global $_G;
        $viewsnum = C::t('pichome_share')->count_by_uid($_G['uid']);
        $data[] = ['id' => 'myshares', 'name' => lang('myShares'), 'url' => 'index.php?mod=shares&op=my', 'number' => $viewsnum];
    }
}