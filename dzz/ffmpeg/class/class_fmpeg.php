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
    private $status = 0;
    private $logger = NULL;

    public function __construct($options = array())
    {
        $appdata = C::t('app_market')->fetch_by_identifier('ffmpeg', 'dzz');
        $app = unserialize($appdata['extra']);
        $this->status = $app['status'];
        $option = array(
            'ffmpeg.binaries' => $app['ffmpeg.binaries'] ? $app['ffmpeg.binaries'] : (strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffmpeg.exe' : '/usr/bin/ffmpeg'),
            'ffprobe.binaries' => $app['ffprobe.binaries'] ? $app['ffprobe.binaries'] : (strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffprobe.exe' : '/usr/bin/ffprobe'),
            'timeout' => $app['timeout'] ? $app['timeout'] : 3600, // The timeout for the underlying process
            'ffmpeg.threads' => $app['ffmpeg.threads'] ? $app['ffmpeg.threads'] : 1,   // The number of threads that FFMpeg should use
        );
        $this->fm = FFMpeg\FFMpeg::create($option, $this->logger);
        return $this->fm;


    }

    public function getInfo($data)
    {
        global $_G;
        if($data['aid']){
            $attachment = IO::getMeta('attach::'.$data['aid']);
        }else{
            $attachment = IO::getMeta($data['rid']);
        }
        $file = IO::getStream($attachment['path']);;
        //本地路径
        if ($attachment['bz'] != 'dzz::') {
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
        $app = C::t('app_market')->fetch_by_identifier('ffmpeg', 'dzz');
        $option = array(
            'ffmpeg.binaries' => $app['ffmpeg.binaries'] ? $app['ffmpeg.binaries'] : (strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffmpeg.exe' : '/usr/bin/ffmpeg'),
            'ffprobe.binaries' => $app['ffprobe.binaries'] ? $app['ffprobe.binaries'] : (strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffprobe.exe' : '/usr/bin/ffprobe'),
            'timeout' => $app['timeout'] ? $app['timeout'] : 3600, // The timeout for the underlying process
            'ffmpeg.threads' => $app['ffmpeg.threads'] ? $app['ffmpeg.threads'] : 1,   // The number of threads that FFMpeg should use
        );
        $ffprobe = FFMpeg\FFProbe::create($option, null);

        if ('audio' == getTypeByExt($attachment['ext'])) {
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
                    'tags' => $meta->get('tags'),
                );
            }
        }
        if ($cachefile) @unlink($cachefile);
        if(isset($info['tags']['rotate'])){
            $rotate = intval($info['tags']['rotate'])%360;
            if($rotate == 90){
                $width = $info['width'];
                $height = $info['height'];
                $info['width'] = $height;
                $info['height'] = $width;
            }
        }
        unset($info['tags']);
        return $info;

    }

    /* 获取缩略图
       $file:文件地址
       $time:获取缩略图的时间点单位秒
    */
    public function getThumb($data, $time = 0)
    {
        global $_G;
        if($data['aid']){
            $attachment = IO::getMeta('attach::'.$data['aid']);
        }else{
            $attachment = IO::getMeta($data['rid']);
        }

        $file = IO::getStream($attachment['path']);
        //本地路径
        if ($attachment['bz'] != 'dzz::') {
            $cachefile = $_G['setting']['attachdir'] . './cache/' . md5($attachment['path']) . '.' . $attachment['ext'];
            $handle = fopen($cachefile, 'w+');
            $fp = fopen($file, 'rb');
            while (!feof($fp)) {
                fwrite($handle, fread($fp, 8192));
            }
            fclose($handle);
            fclose($fp);
            $file = $cachefile;
        }
        $thumbpath = $this->getthumbpath('pichomethumb');
        if($data['aid'])$thumbname = md5($data['aid'].$data['thumbsign']).'_original.webp';
        else $thumbname = md5($data['path'].$data['thumbsign']).'_original.webp';
        $target =  $thumbpath.$thumbname;
        if ('audio' == getTypeByExt($attachment['ext'])) {
            //$target = ($data['thumbsign']) ? $data['rid']. '.webp' : $data['rid'] . '.webp';

            $jpg = $_G['setting']['attachdir']  . $target;
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
          //  $target = $data['rid'] . '.jpg';
            $jpg = $_G['setting']['attachdir']. $target;
            $jpgpath = dirname($jpg);
            dmkdir($jpgpath);
            $app = C::t('app_market')->fetch_by_identifier('ffmpeg', 'dzz');
            if(!$time){
                $option = array(
                    'ffmpeg.binaries' => $app['ffmpeg.binaries'] ? $app['ffmpeg.binaries'] : (strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffmpeg.exe' : '/usr/bin/ffmpeg'),
                    'ffprobe.binaries' => $app['ffprobe.binaries'] ? $app['ffprobe.binaries'] : (strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffprobe.exe' : '/usr/bin/ffprobe'),
                    'timeout' => $app['timeout'] ? $app['timeout'] : 3600, // The timeout for the underlying process
                    'ffmpeg.threads' => $app['ffmpeg.threads'] ? $app['ffmpeg.threads'] : 1,   // The number of threads that FFMpeg should use
                );
                $ffprobe = FFMpeg\FFProbe::create($option, null);
                $meta = $ffprobe
                    ->streams($file) // extracts streams informations
                    ->videos()                      // filters video streams
                    ->first();
                $duration = 0;
                if ($meta) {
                   $duration = round($meta->get('duration'), 2);

                }
                $time = getglobal('config/audiothumetime') ? intval(getglobal('config/audiothumetime')):5;
                $time = ($duration > $time) ? $time:ceil($data['duration']);
            }

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
            $defaultspace = $_G['setting']['defaultspacesetting'];
            //如果原文件位置不在本地，则将转换完成文件迁移到对应位置
            if ($defaultspace['bz'] != 'dzz') {
                $cloudpath = $defaultspace['bz'].':'.$defaultspace['did'] . ':/' .$target;
                $filepath = \IO::moveThumbFile($cloudpath, $jpg);
                if (!isset($filepath['error'])) {
                    @unlink($jpg);
                    return $target;
                }
            } else {

                return $target;
            }
        }
        return false;

    }

    public function getVideoQuality($videoquality = 1)
    {
        $templatename = '';
        switch ($videoquality) {
            case 0://流畅
                $templatename = 'pichomeconvert-mp4-640-360-400-mp3';
                $width = 640;
                $height = 360;
                $bitrate = 400;
                break;
            case 1://标清
                $templatename = 'pichomeconvert-mp4-960-540-900-mp3';
                $width = 960;
                $height = 540;
                $bitrate = 900;
                break;
            case 2://高清
                $templatename = 'pichomeconvert-mp4-1280-720-1500-mp3';
                $width = 1280;
                $height = 720;
                $bitrate = 1500;
                break;
            case 3://超清
                $templatename = 'pichomeconvert-mp4-1920-1080-3000-mp3';
                $width = 1920;
                $height = 1080;
                $bitrate = 3000;
                break;
            case 4://2k
                $templatename = 'pichomeconvert-mp4-3500-2560-1440-mp3';
                $width = 3500;
                $height = 2560;
                $bitrate = 1440;
                break;
            case 5://4k
                $templatename = 'pichomeconvert-mp4-3840-2160-6000-mp3';
                $width = 3840;
                $height = 2160;
                $bitrate = 6000;
                break;
        }
        return array($templatename, $width, $height, $bitrate);
    }

    //转码,windows下大文件可能出现内部错误，X264报错，不知原因
    public function convert($id, $ext = 'mp4', $videoquality = 1, $extra = array())
    {
        global $_G;
        //获取附件信息

        //获取记录表数据
        $cron = C::t('video_record')->fetch($id);
        if($cron['aid']){
            $attachment = IO::getMeta('attach::'.$cron['aid']);
        }else{
            $attachment = IO::getMeta($cron['rid']);
        }
        list($templatename, $fwidth, $fheight, $fbitrate) = $this->getVideoQuality($videoquality);
        //本地文件路径
        $target = 'pichomethumb/' . date('Ym') . '/' . date('d') .'/'.md5($attachment['path']) . '.' . $cron['format'];
        //本地存储时路径
        $recordpath = 'dzz::' . $target;
        //文件保存路径
        $mp4 = $_G['setting']['attachdir'].$target;
        $mp4path = dirname($mp4);

        dmkdir($mp4path);


        //开始转换过程
        $file = IO::getStream($attachment['path']);
        if ($attachment['bz'] != 'dzz::') {
            $cachefile = $_G['setting']['attachdir'] . './cache/' . md5($attachment['path']) . '.' . $attachment['ext'];
            $handle = fopen($cachefile, 'w+');
            $fp = fopen($file, 'rb');
            while (!feof($fp)) {
                fwrite($handle, fread($fp, 8192));
            }
            fclose($handle);
            fclose($fp);
            $file = $cachefile;
        }

        //更新转换执行次数
        C::t('video_record')->update($cron['id'], array('status' => 1,'path'=>$target, 'dateline' => TIMESTAMP, 'jobnum' => (($cron['jobnum']) ? intval($cron['jobnum']) + 1 : 1)));
        $video = $this->fm->open($file);
        
        //水印
//       $video->filters() ->watermark($watermarkPath, array('position' => 'relative','bottom' => 50, 'right' => 50 ));
        $video->path = $cron['id'];

        switch ($ext) {
            case 'mp4':
                $format = new FFMpeg\Format\Video\X264('aac');
                break;
            case 'webm':
                $format = new FFMpeg\Format\Video\WebM();
                break;
            case 'ogg':
                $format = new FFMpeg\Format\Video\Ogg();
                break;
            case 'wmv':
                $format = new FFMpeg\Format\Video\WMV();
                break;
            case 'wav':
                $format = new FFMpeg\Format\Audio\Wav();
                break;
            case 'mp3':
                $format = new FFMpeg\Format\Audio\Mp3();
                break;
            default:
                $format = new FFMpeg\Format\Video\X264('aac');

        }
        if (!in_array($ext, array('mp3', 'wav'))) {
            //获取视频信息
            try {
                $info = $this->getInfo($attachment);

				if(!in_array($ext,array('mp3','wav'))){
					if($info['width']){
						$width=$fwidth;
						$height=$info['height']?($width*$info['height']/$info['width']):$fheight;
						//指定视频宽高
						$video->filters()->resize(new FFMpeg\Coordinate\Dimension($width, $height))->synchronize();
					}else{
						$video->filters()->resize(new FFMpeg\Coordinate\Dimension($fwidth, $fheight))->synchronize();
					}
				}
				$bitrate=intval($fbitrate>$info['bit_rate']?$fbitrate:$info['bit_rate']);
				$format->setKiloBitrate($bitrate);
               
            } catch (\Exception $e) {
            };
        }
        $format->on('progress', function ($video, $format, $percentage) {
            C::t('video_record')->update($video->path, array('percent' => $percentage > 0 ? $percentage : 1));
        });
        try {
            $video->save($format, $mp4);
        } catch (\Exception $e) {
            //失败时记录失败信息
            C::t('video_record')->update($cron['id'], array('endtime' => strtotime('now'), 'status' => -1, 'error' => $e->getMessage()));
            return array('error' => $e->getMessage());
        }
        //如果未发生错误更改记录表数据为成功，并存储本地存储时路径
        C::t('video_record')->update($cron['id'], array('percent' => 100, 'endtime' => strtotime('now'), 'status' => 2, 'path' => $recordpath));
        if ($cachefile) @unlink($cachefile);
        //如果原文件位置不在本地，则将转换完成文件迁移到对应位置
        if ($attachment['bz'] != 'dzz::') {
            //组合云端保存位置
            $cloudpath =$attachment['bz']. ':' . '/tmppichomethumb/' . md5($attachment['path']) . '.' . $cron['format'];
            $filepath = IO::moveThumbFile($cloudpath, $mp4);
            if (!isset($filepath['error'])) {
                C::t('video_record')->update($cron['id'], array('path' => $cloudpath,'remoteid'=>$attachment['remoteid']));
                @unlink($mp4);
            }
        }

        return $cron;
    }

    //兼容linux下获取文件名
    public function get_basename($filename)
    {
        if ($filename) {
            return preg_replace('/^.+[\\\\\\/]/', '', $filename);
        }
        return '';

    }

    public function getPath($dir = 'imgcache/ffmpeg')
    {
        global $_G;
        $target1 = $dir . '/index.html';
        $target_attach = $_G['setting']['attachdir'] . './' . $target1;
        $targetpath = dirname($target_attach);
        dmkdir($targetpath);
        return $dir;
    }
    public function getthumbpath($dir = 'pichomethumb'){
        $subdir = $subdir1 = $subdir2 = '';
        $subdir1 = date('Ym');
        $subdir2 = date('d');
        $subdir = $subdir1 . '/' . $subdir2 . '/';
        // $target1 = $dir . '/' . $subdir . 'index.html';
        $target = $dir . '/' . $subdir;
        return $target;
    }

}
