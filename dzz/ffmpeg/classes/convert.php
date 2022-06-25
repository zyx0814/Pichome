<?php
namespace dzz\ffmpeg\classes;

use \core as C;
use \DB as DB;
use \IO as IO;
class convert{
  
   public function run($data,$force=false)
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
       if (!$data['ext'] || $bz != 'dzz') {
           return '';
       }
       $videoexts = getglobal('config/pichomeffmpegconvertext') ?  getglobal('config/pichomeffmpegconvertext'):'avi,rm,rmvb,mkv,mov,wmv,asf,mpg,mpe,mpeg,mp4,m4v,mpeg,f4v,vob,ogv,mts,m2ts,3gp,webm,flv,wav,mp3,ogg,midi,wma,vqf,ra,aac,flac,ape,amr,aiff,au,m4a';
       $videoarr = explode(',',$videoexts);
       $pexts=  getglobal('config/pichomeplayermediaext') ? explode(',', getglobal('config/pichomeplayermediaext')):array('mp3','mp4','webm','ogv','ogg','wav','m3u8','hls','mpg','mpeg');
       if (in_array($data['ext'],$pexts) || !in_array($data['ext'],$videoarr)) {
           return '';
       }  else {

           $videodata= DB::fetch_first('select mediastatus,videoquality from %t where bz = %s',array('connect_storage',$bz));
           if(!$videodata['mediastatus']) return '';
           if ('audio' == getTypeByExt($data['ext'])) {
               $ext = 'mp3';
           } else {
               $ext = 'webm';
           }
           $setarr = ['rid' => $data['rid'], 'format' => $ext, 'dateline' => TIMESTAMP, 'videoquality' => $videodata['videoquality']];
           $ff = C::t('video_record')->insert($setarr);
           if($ff['id']) return false;
           //if ($force) dfsockopen(getglobal('localurl') . 'index.php?mod=ffmpeg&op=convert&id=' . $ff['id'], 0, '', '', false, '', 0.1);
       }
   }

}