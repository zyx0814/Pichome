<?php
namespace  user\classes;

class Checklogin{
    public function run(){


        global $_G;

        if (!$_G['uid']) {
            if(defined('IS_API') && IS_API){
                exit(json_encode(array('loginstatus'=>0,'hash'=>FORMHASH,'isuser'=>true)));
            }else{
                include template('common/header_reload');

                echo "<script type=\"text/javascript\">";

                echo "location.href='user.php?mod=login';";

                echo "</script>";

                include template('common/footer_reload');

                exit();
            }
        }
    }
}