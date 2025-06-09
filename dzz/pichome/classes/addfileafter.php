<?php
namespace dzz\pichome\classes;

use \core as C;
class addfileafter{
    public function run($data)
    {
        $apptype = $data['apptype'];
        if($apptype==3){
            addFileuploadStats($data['rid'],1);
            dfsockopen(getglobal('localurl') . 'misc.php?mod=addfileafter&rid='.$data['rid'].'&aid='.$data['aid'], 0, '', '', false, '',10);

        }


    }
}