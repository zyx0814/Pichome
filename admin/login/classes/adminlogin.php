<?php
namespace  admin\login\classes; 
use \core as C; 
class Adminlogin{
    public function run(){
        $dzz = C::app();
        $dzz->init(); 
        $admincp = new \dzz_admincp();
        $admincp->core  =  $dzz;
        $return = $admincp->init();
        if(defined('IS_API') && IS_API && $return === 0){
           exit(json_encode(array('loginstatus'=>0,'hash'=>FORMHASH)));
        }
    }
}