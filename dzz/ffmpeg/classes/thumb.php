<?php

namespace dzz\ffmpeg\classes;

use \core as C;
use \fmpeg as fmpeg;
use \IO as IO;
use \DB as DB;
class thumb
{

    public function run(&$data)
    {
        if(strpos($data['realpath'],':') === false){
            $bz = 'dzz';
        }else{
            $patharr = explode(':', $data['realpath']);
            $bz = $patharr[0];
            $did = $patharr[1];

        }
        if(!is_numeric($did) || $did < 2){
            $bz = 'dzz';
        }
        $exts = explode(',',getglobal('config/pichomeffmpeggetthumbext'));

        if(!$data['ext'] || $bz != 'dzz' || !in_array($data['ext'],$exts)){
            return '';
        }

        $videostatus = DB::result_first('select mediastatus from %t where bz = %s',array('connect_storage',$bz));
        if(!$videostatus) return '';
        require_once DZZ_ROOT . './dzz/ffmpeg/class/class_fmpeg.php';
        $fm = new fmpeg();
        if ($data['Duration']) {
            $start = ceil($data['duration'] / 5);
        } else {
            $start = 1;
        }
        if ($target = $fm->getThumb($data, $start)) {
            if ($imginfo = getimagesize($target)) {
                $resourcesarr = [
                    'width' => $imginfo[0] ? $imginfo[0]:0,
                    'height' =>$imginfo[1] ? $imginfo[1]:0
                ];
                C::t('pichome_resources')->update($data['rid'],$resourcesarr);
                C::t('pichome_resources')->update($data['rid'], array('hasthumb' => 1));
                return array($target);
            }else{
                return '';
            }
        }else{
            return '';
        }


    }
}