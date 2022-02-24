<?php

namespace dzz\ffmpeg\classes;

use \core as C;
use \DB as DB;
use \fmpeg as fmpeg;

class thumb
{

    public function run(&$data)
    {
        $exts = (getglobal('config/pichomeffmpeggetthumbext')) ? getglobal('config/pichomeffmpeggetvieoinoext'):[
            'avi', 'rm', 'rmvb', 'mkv', 'mov', 'wmv', 'asf', 'mpg', 'mpe', 'mpeg', 'mp4', 'm4v', 'mpeg', 'f4v', 'vob', 'ogv', 'mts', 'm2ts',
            '3gp', 'webm', 'flv', 'wav', 'mp3', 'ogg', 'midi', 'wma', 'vqf', 'ra', 'aac', 'flac', 'ape', 'amr', 'aiff', 'au', 'm4a'
        ];
        $data['ext'] = strtolower($data['ext']);
        if (empty($data['ext']) || !in_array($data['ext'], $exts)) {

        } else {
            if(strstr(PHP_OS, 'WIN') && !is_file(DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffprobe.exe')){
                return ;
            }elseif(!is_file('/usr/bin/ffprobe')){
                return ;
            }
            $setarr = [
                'rid'=>$data['rid'],
                'appid'=>$data['appid'],
                'ext'=>$data['ext']
            ];
            $isforce = (isset($setarr['isforce'])) ? 1:0;

            C::t('pichome_ffmpeg_record')->insert($setarr,$isforce);
            //dfsockopen(getglobal('localurl') . 'index.php?mod=ffmpeg&op=getinfo&path=' . dzzencode($data['rid']), 0, '', '', false, '', 0.1);
        }
    }

    public function rundata(&$data)
    {


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
                C::t('pichome_ffmpeg_record')->update($data['rid'], array('thumbstatus' => 1,'thumb'=>$target));
                $fdata = C::t('pichome_ffmpeg_record')->fetch($data['rid']);
                if($fdata['thumbstatus'] == 1 && $fdata['infostatus'] == 1){
                    if(!DB::result_first("select isget from %t where rid = %s",array('pichome_resources_attr',$data['rid']))) {
                        C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>1));
                        C::t('pichome_vapp')->add_getinfonum_by_appid($data['appid'], 1);
                    }
                }
            }
        }


    }
}