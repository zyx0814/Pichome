<?php

namespace dzz\ffmpeg\classes;

use \core as C;
use \DB as DB;
use \fmpeg as fmpeg;

class info
{

    public function run(&$data)
    {
        $exts = (getglobal('config/pichomeffmpeggetvieoinoext')) ? getglobal('config/pichomeffmpeggetvieoinoext'):[
            'avi', 'rm', 'rmvb', 'mkv', 'mov', 'wmv', 'asf', 'mpg', 'mpe', 'mpeg', 'mp4', 'm4v', 'mpeg', 'f4v', 'vob', 'ogv', 'mts', 'm2ts',
            '3gp', 'webm', 'flv', 'wav', 'mp3', 'ogg', 'midi', 'wma', 'vqf', 'ra', 'aac', 'flac', 'ape', 'amr', 'aiff', 'au', 'm4a'
        ];
        $data['ext'] = strtolower($data['ext']);
        if (empty($data['ext']) || !in_array($data['ext'], $exts)) {
                return true;
        } else {
            $ffprobeposition = getglobal('config/pichomeffprobeposition') ? getglobal('config/pichomeffprobeposition'):(strstr(PHP_OS, 'WIN')?DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffprobe.exe':'/usr/bin/ffprobe');

            if(!is_file($ffprobeposition))  return ;

            $setarr = [
                'rid'=>$data['rid'],
                'appid'=>$data['appid'],
                'ext'=>$data['ext']
            ];
            $isforce = (isset($setarr['isforce'])) ? 1:0;

            C::t('pichome_ffmpeg_record')->insert($setarr,$isforce);
        }
    }

    public function rundata($data)
    {
        require_once DZZ_ROOT . './dzz/ffmpeg/class/class_fmpeg.php';
        try {
            if ($info = fmpeg::getInfo($data)) {

                if (isset($info['duration'])) C::t('pichome_resources_attr')->update($data['rid'], array('duration' => $info['duration']));
                if (isset($info['width'])) C::t('pichome_resources')->update($data['rid'], array('width' => $info['width'], 'height' => $info['height']));
                C::t('pichome_ffmpeg_record')->update($data['rid'],array('infostatus'=>1));
                $fdata = C::t('pichome_ffmpeg_record')->fetch($data['rid']);
                if($fdata['thumbstatus'] == 1 && $fdata['infostatus'] == 1){
                    if(!DB::result_first("select isget from %t where rid = %s",array('pichome_resources_attr',$data['rid']))) {
                        C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>1));
                        C::t('pichome_vapp')->add_getinfonum_by_appid($data['appid'], 1);
                    }

                }
            }

        } catch (\Exception $e) {
            runlog('ffmpeg', $e->getMessage() . 'rid:' . $data['rid']);
        }

    }
}