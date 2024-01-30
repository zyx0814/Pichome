<?php

namespace dzz\ffmpeg\classes;

use \core as C;
use \fmpeg as fmpeg;
use \IO as IO;
class info
{

    public function run($data)
    {
        $app = C::t('app_market')->fetch_by_identifier('ffmpeg', 'dzz');
        $extra = unserialize($app['extra']);


        if (!$extra['status']) {
            return '';
        }

        $exts = $extra['exts_info'] ? explode(',', $extra['exts_info']) : array();

        //如果类型不符合则停止执行
        if (!in_array($data['ext'], $exts)) return '';

        //如果路径为数字视为pichome库
        $cachepath = ($data['aid']) ? intval($data['aid']):($data['rid'] ? $data['rid'] :md5($data['path']));
        if($infodata = C::t('ffmpegimage_cache')->fetch_by_path($cachepath)){
            $info = unserialize($infodata);
            if (isset($info['duration'])) C::t('pichome_resources_attr')->update($data['rid'], array('duration' => $info['duration']));
            if (isset($info['width'])) C::t('pichome_resources')->update($data['rid'], array('width' => $info['width'], 'height' => $info['height']));
            C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>1));
            return false;
        }else{
            require_once DZZ_ROOT . './dzz/ffmpeg/class/class_fmpeg.php';
            try {
                if ($info = fmpeg::getInfo($data)) {
                    $cachearr = [
                        'info'=>serialize($info),
                        'path'=>$cachepath,
                        'dateline'=>TIMESTAMP
                    ];
                    C::t('ffmpegimage_cache')->insert($cachearr);
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
}