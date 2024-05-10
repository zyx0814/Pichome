<?php
namespace dzz\pichome\classes;

use \core as C;
class addfileafter{
    public function run($data)
    {
        dfsockopen(getglobal('localurl') . 'misc.php?mod=addfileafter&rid='.$data['rid'].'&aid='.$data['aid'], 0, '', '', false, '',10);


    }
}