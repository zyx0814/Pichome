<?php

namespace dzz\ffmpeg\classes;

use \core as C;
use \fmpeg as fmpeg;
use \IO as IO;
class info
{

    public function run($data)
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
        if(!$data['ext'] || $bz != 'dzz' || !in_array($data['ext'],explode(',',getglobal('config/pichomeffmpeggetvieoinfoext')))){
            return '';
        }
        $videostatus = DB::result_first('select mediastatus from %t where bz = %s',array('connect_storage',$bz));
        if(!$videostatus) return '';
        require_once DZZ_ROOT . './dzz/ffmpeg/class/class_fmpeg.php';
        try {
            if ($info = fmpeg::getInfo($data)) {

                if (isset($info['duration'])) C::t('pichome_resources_attr')->update($data['rid'], array('duration' => $info['duration']));
                if (isset($info['width'])) C::t('pichome_resources')->update($data['rid'], array('width' => $info['width'], 'height' => $info['height']));
                C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>1));
                return false;
            }else{
                C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>-1));
            }

        } catch (\Exception $e) {
            runlog('ffmpeg', $e->getMessage() . 'rid:' . $data['rid']);
            C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>-1));
        }

    }
}