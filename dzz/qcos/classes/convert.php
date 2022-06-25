<?php

namespace dzz\qcos\classes;

use \core as C;
use \IO as IO;
use \DB as DB;

class convert
{

    public function run($data, $force = false)
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
        if($bz != 'QCOS' || !$data['ext'])return '';
        $videodata = DB::fetch_first('select mediastatus,videoquality from %t where id = %d',array('connect_storage',$did));

        if(!$videodata['mediastatus']) return '';

        $videoexts = getglobal('config/qcosmedia') ? explode(',', getglobal('config/qcosmedia')):array('3gp','avi','flv','m3u8','mpg','asf','wmv','mkv','mov','ts','webm','mxf');
        $pexts=  getglobal('config/pichomeplayermediaext') ? explode(',', getglobal('config/pichomeplayermediaext')):array('mp3','mp4','webm','ogv','ogg','wav','m3u8','hls','mpg','mpeg');
        if (in_array($data['ext'],$pexts) || !in_array($data['ext'],$videoexts)) {
            return '';
        } else {
            if ('audio' == getTypeByExt($data['ext'])) {
                $ext = 'mp3';
            } else {
                $ext = 'mp4';
            }
            $setarr = ['rid' => $data['rid'], 'dateline' => TIMESTAMP, 'ctype' => 2,'format'=>$ext,'videoquality'=>$videodata['videoquality']];

            if ($id = C::t('video_record')->insert($setarr, 1)) {
                //if ($force) dfsockopen(getglobal('localurl') . 'index.php?mod=qcosvideo&op=convert&id=' . $id, 0, '', '', false, '', 0.1);
                return false;
            }

        }


    }
}