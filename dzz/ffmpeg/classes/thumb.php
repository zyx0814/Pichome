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
        global $_G;
        $app = C::t('app_market')->fetch_by_identifier('ffmpeg', 'dzz');
        $extra = unserialize($app['extra']);


        if (!$extra['status']) {
            return true;
        }
        $exts = explode(',',$_G['config']['pichomeffmpeggetthumbext']);//$extra['exts_thumb'] ? explode(',', $extra['exts_thumb']) : array();

        //如果类型不符合则停止执行
        if (!in_array($data['ext'], $exts)) return '';

        require_once DZZ_ROOT . './dzz/ffmpeg/class/class_fmpeg.php';
        $fm = new fmpeg();
        if ($data['Duration']) {
            $start = ceil($data['duration'] / 5);
        } else {
            $start = 0;
        }
        //执行获取缩略图
        if ($target = $fm->getThumb($data, $start)) {
                return array($target);
        } else {
            return '';
        }


    }
}