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
            include_once libfile('function/cache');
            updatecache(array('machinecode','adminfirstlogin'));
            self::upgradeinformation($mcode);
        }else{
            C::t('setting')->update('adminfirstlogin',0);

        }
    }
    private static function upgradeinformation($machinecode) {
        global $_SERVER;
        $update = array();
        $update[ 'mcode' ] = $machinecode;
        $update[ 'usum' ] = 1;
        $update[ 'siteurl' ] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
        $update[ 'sitename' ] = getglobal('sitename');
        $update[ 'version' ] = CORE_VERSION;
        $update[ 'version_level' ] = CORE_VERSION_LEVEL;
        $update[ 'release' ] = CORE_RELEASE;
        $update[ 'fixbug' ] = CORE_FIXBUG;
        $update[ 'license_version' ] = LICENSE_VERSION;
        $update[ 'license_limit' ] = LICENSE_LIMIT;
        $data = '';
        foreach ( $update as $key => $value ) {
            $data .= $key . '=' . rawurlencode( $value ) . '&';
        }
        $upgradeurl = APP_CHECK_URL . "authlicense/count/info/" . rawurlencode( base64_encode( $data ) ) . "/" . time();
        dfopen( $upgradeurl );
    }

}
