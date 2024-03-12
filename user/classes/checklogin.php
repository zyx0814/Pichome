<?php
namespace  user\classes;

class Checklogin{
    public function run(){


        global $_G;

        if (!$_G['uid']) {
            $isapi = ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || (isset($_GET['inajax']) && $_GET['inajax']))
                ? true : false;
            if($isapi){
                exit(json_encode(array('loginstatus'=>0,'hash'=>FORMHASH,'isuser'=>true)));
            }else{
                include template('common/header_reload');

                echo "<script type=\"text/javascript\" reload=\"1\">";
				echo "var referer=encodeURIComponent(window.location.href);";
                echo "window.location.href='user.php?mod=login&referer='+referer";
                echo "</script>";

                include template('common/footer_reload');

                exit();
            }
        }
    }
}