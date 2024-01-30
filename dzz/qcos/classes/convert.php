<?php

namespace dzz\qcos\classes;

use \core as C;
use \IO as IO;
use \DB as DB;

class convert
{

    public function run($data, $force = false)
    {

        global $_G;
        //如果是普通目录
        if(!$data['ext'] || strpos($data['bz'],'QCOS') === false){
            return '';
        }
        $videodata = DB::fetch_first('select mediastatus,videoquality from %t where id = %d',array('connect_storage',$data['remoteid']));
        if(!$videodata['mediastatus']) return true;
        $videoexts = array('3gp','asf','avi','dv','flv','f4v','m3u8','m4v','mkv','mov','mp4','mpg','mpeg','mts','ogg','rm','rmvb','swf','vob','wmv','webm','mp3','aac','flac','amr','awb','m4a','wma','wav');
        $pexts=  getglobal('config/pichomeplayermediaext') ? explode(',', getglobal('config/pichomeplayermediaext')):array('mp3','mp4','webm','ogv','ogg','wav','m3u8','hls','mpg','mpeg');
        if (in_array($data['ext'],$pexts) || !in_array($data['ext'],$videoexts)) {
            return '';
        } else {
            if ('audio' == getTypeByExt($data['ext'])) {
                $ext = 'mp3';
            } else {
                $ext = 'mp4';
            }
            $setarr = ['rid' => $data['rid'], 'dateline' => TIMESTAMP, 'ctype' => 2,'format'=>$ext,'videoquality'=>0];
            $setarr['aid']= $data['aid'] ? $data['aid']:0;

            if ($ff = C::t('video_record')->insert_data($setarr)) {
                //if ($force) dfsockopen(getglobal('localurl') . 'index.php?mod=qcosvideo&op=convert&id=' . $id, 0, '', '', false, '', 0.1);
                return false;
            }

        }
        

    }
}