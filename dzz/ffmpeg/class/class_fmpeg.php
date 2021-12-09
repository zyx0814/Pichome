<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
require_once DZZ_ROOT . './dzz/ffmpeg/vendor/autoload.php';
require_once(DZZ_ROOT . './dzz/class/class_encode.php');

class fmpeg
{
    private $fm;
    private $logger = NULL;

    public function __construct($options = array())
    {

        $option = array(
            'ffmpeg.binaries' =>(getglobal('config/pichomeffmpegposition')) ? getglobal('config/pichomeffmpegposition'):(strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffmpeg.exe' : '/usr/bin/ffmpeg'),
            'ffprobe.binaries' => (getglobal('config/pichomeffprobeposition')) ? (getglobal('config/pichomeffprobeposition')):(strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffprobe.exe' : '/usr/bin/ffprobe'),
            'timeout' => 3600, // The timeout for the underlying process
            'ffmpeg.threads' => 1,   // The number of threads that FFMpeg should use
        );
        $this->fm = FFMpeg\FFMpeg::create($option, $this->logger);
        return $this->fm;


    }

    public function getInfo($data)
    {

            $option = array(
                'ffmpeg.binaries' =>(getglobal('config/pichomeffmpegposition')) ? getglobal('config/pichomeffmpegposition'):(strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffmpeg.exe' : '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => (getglobal('config/pichomeffprobeposition')) ? (getglobal('config/pichomeffprobeposition')):(strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffprobe.exe' : '/usr/bin/ffprobe'),
                'timeout' => 3600, // The timeout for the underlying process
                'ffmpeg.threads' => 1,   // The number of threads that FFMpeg should use
            );
            $file = $path = $data['realpath'];
            if (!is_file($file)) {
                $cachepath = getglobal('setting/attachdir') . 'cache/ffmpeg_cahce_' . md5($path) . '.' . $data['ext'];
                if (!file_put_contents($cachepath, file_get_contents($file))) {
                    return false;
                }
                $file = $cachepath;
            }

            $ffprobe = FFMpeg\FFProbe::create($option, null);

            if ('audio' == getTypeByExt($data['ext'])) {
                $meta = $ffprobe
                    ->streams($file) // extracts streams informations
                    ->audios()                      // filters video streams
                    ->first();
                $info = array();
                if ($meta) {
                    $info = array(
                        'duration' => round($meta->get('duration'), 2),
                    );
                }
            } else {
                $meta = $ffprobe
                    ->streams($file) // extracts streams informations
                    ->videos()                      // filters video streams
                    ->first();
                $info = array();
                if ($meta) {
                    $info = array(
                        'width' => intval($meta->get('width')),
                        'height' => intval($meta->get('height')),
                        'duration' => round($meta->get('duration'), 2),
                    );
                }
            }

        if ($cachepath) @unlink($cachepath);
        return $info;

    }

    /* 获取缩略图
       $file:文件地址
       $time:获取缩略图的时间点单位秒
    */
    public function getThumb($data,$time = 1)
    {
        global $_G;

        $file = $data['realpath'];
        //本地路径
        if (!is_file($file)) {
            $cachefile = $_G['setting']['attachdir'] . './cache/' . md5($data['path']) . '.' . $data['ext'];
            $handle = fopen($cachefile, 'w+');
            $fp = fopen($file, 'rb');
            while (!feof($fp)) {
                fwrite($handle, fread($fp, 8192));
            }
            fclose($handle);
            fclose($fp);
            $file = $cachefile;
        }
        if ('audio' == getTypeByExt($data['ext'])) {
            $target = md5($data['path']) . '.jpg';

            $jpg = $_G['setting']['attachdir'] . './pichomethumb/' . $data['appid'] . '/' . $target;
            $jpgpath = dirname($jpg);
            dmkdir($jpgpath);
            if (!in_array($data['ext'], array('mp3', 'wav'))) {
                $audio = $this->fm->open($file);
                $audio_format = new FFMpeg\Format\Audio\Mp3();
                $tmp = $_G['setting']['attachdir'] . './cache/' . md5($file) . '.mp3';
                $audio->save($audio_format, $tmp);
            }
            $audio = $this->fm->open($tmp ? $tmp : $file);

            $waveform = $audio->waveform(720, 360, array('#888888'));
            $waveform->save($jpg);
            if ($tmp) @unlink($tmp);
        } else {
            $target = md5($data['path']) . '.jpg';
            $jpg = $_G['setting']['attachdir'] . './pichomethumb/' . $data['appid'] . '/' . $target;
            $jpgpath = dirname($jpg);
            dmkdir($jpgpath);

            try {
                $video = $this->fm->open($file);
                $video
                    ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($time))
                    ->save($jpg);
            } catch (\Exception $e) {
                runlog('ffmpeg', $e->getMessage() . ' File:' . $file);
            }
        }
        if ($cachefile) @unlink($cachefile);
        if (is_file($jpg)) {
            return $jpg;
        } else {
            return false;
        }


    }


    //兼容linux下获取文件名
    public function get_basename($filename)
    {
        if ($filename) {
            return preg_replace('/^.+[\\\\\\/]/', '', $filename);
        }
        return '';

    }

}
