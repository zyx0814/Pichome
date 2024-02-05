<?php

namespace dzz\ffmpeg\classes;

use \core as C;
use \DB as DB;
use \IO as IO;

class convert
{

    public function run($data, $force = false)
    {
        global $_G;
        //如果是普通目录
        $app = C::t('app_market')->fetch_by_identifier('ffmpeg', 'dzz');
        $appextra = unserialize($app['extra']);
       // if (!$appextra['status']) return true;
        $exts = explode(',',$_G['config']['pichomeffmpegconvertext']);//$appextra['exts'] ? explode(',', $appextra['exts']) : explode(',',$_G['config']['pichomeffmpegconvertext']);
        //如果类型不符合则停止执行
        if ($exts && !in_array($data['ext'], $exts)) return true;
        $videoexts = $appextra ? getglobal('config/pichomeffmpegconvertext') : 'avi,rm,rmvb,mkv,mov,wmv,asf,mpg,mpe,mpeg,mp4,m4v,mpeg,f4v,vob,ogv,mts,m2ts,3gp,webm,flv,wav,mp3,ogg,midi,wma,vqf,ra,aac,flac,ape,amr,aiff,au,m4a';
        $videoarr = explode(',', $videoexts);
        $pexts = getglobal('config/pichomeplayermediaext') ? explode(',', getglobal('config/pichomeplayermediaext')) : array('mp3', 'mp4', 'webm', 'ogv', 'ogg', 'wav', 'm3u8', 'hls', 'mpg', 'mpeg');
        if (in_array($data['ext'], $pexts) || !in_array($data['ext'], $videoarr)) {
            return '';
        } else {

            if ('audio' == getTypeByExt($data['ext'])) {
                $ext = 'mp3';
            } else {
                $ext = 'mp4';
            }
            $setarr = ['rid' => $data['rid'], 'dateline' => TIMESTAMP, 'ctype' => 0,'format'=>$ext,'videoquality'=>getglobal('config/defaultvideoquality')];
            $setarr['aid']= $data['aid'] ? $data['aid']:0;
            $ff = C::t('video_record')->insert_data($setarr);
            if ($ff['id']) return false;
            //if ($force) dfsockopen(getglobal('localurl') . 'index.php?mod=ffmpeg&op=convert&id=' . $ff['id'], 0, '', '', false, '', 0.1);
        }
    }

}