<?php
namespace core\dzz;

use \core as C;
use \core\dzz\Hook as Hook;
use \DB as DB;
use \IO as IO;
class Sysreg{
    public $official = 'http://oaooa.com/';
    public function run(){
        $mcode = C::t('setting')->fetch('machinecode');
        if(!$mcode){
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            $onlineip = $_SERVER['REMOTE_ADDR'];
            $mcode = 'PH'.$chars[date('y')%60].$chars[date('n')].
                $chars[date('j')].$chars[date('G')].$chars[date('i')].
                $chars[date('s')].substr(md5($onlineip.TIMESTAMP), 0, 4).random(4);
            C::t('setting')->update('machinecode',$mcode);
            C::t('setting')->update('adminfirstlogin',0);
        }else{
            C::t('setting')->update('adminfirstlogin',0);

        }
    }

}
