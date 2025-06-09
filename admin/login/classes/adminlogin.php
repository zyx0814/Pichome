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
        $firstlogin = getglobal('adminfirstlogin') ? getglobal('adminfirstlogin'):1;

        if($firstlogin || !getglobal('machinecode')){
            \Hook::listen('sysreg');
        }
        $isapi = ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || (isset($_GET['inajax']) && $_GET['inajax']))
        ? true : false;
        if($isapi && $return === 0){
           exit(json_encode(array('loginstatus'=>0,'hash'=>FORMHASH)));
        }
    }
}