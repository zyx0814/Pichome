<?php
namespace dzz\qcos\classes;

use \core as C;
use \DB as DB;
use \IO as IO;
class thumb{

    private $video = null;
	public function run($meta){
	    global $_G;

        if(!$meta['ext'] || strpos($meta['bz'],'QCOS') === false){
            return '';
        }

        $connectdata = C::t('connect_storage')->fetch($meta['remoteid']);
        $hostarr = explode(':',$connectdata['hostname']);
        $config = [
            'secretId' => trim($connectdata['access_id']),
            'secretKey' => dzzdecode($connectdata['access_key'], 'QCOS'),
            'region' => $hostarr[1],
            'schema' => $hostarr[0],
            'bucket'=>trim($connectdata['bucket']),
        ];

        include_once DZZ_ROOT.'dzz'.BS.'qcos'.BS.'class'.BS.'class_video.php';
        $this->video = new \video($config);
       // $videoexts =  getglobal('config/qcosmedia') ? explode(',', getglobal('config/qcosmedia')):array('f4v','3gp','avi','flv','mp4','m3u8','mpg','asf','wmv','mkv','mov','ts','webm','mxf');
        $videoexts = array('f4v','3gp','avi','flv','mp4','m3u8','mpg','asf','wmv','mkv','mov','ts','webm','mxf');
        $officeexts = getglobal('config/qcosoffice') ? explode(',',getglobal('config/qcosoffice')):array('pptx','ppt','pot','potx','pps','ppsx','dps','dpt','pptm','potm','ppsm','doc','dot','wps','wpt','docx','dotx','docm','dotm','xls','xlt','et','ett','xlsx','xltx','csv','xlsb','xlsm','xltm','ets','pdf','lrc','c','cpp','h','asm','s','java','asp','bat','bas','prg','cmd','rtf','txt','log','xml','htm','html');
        $officestatus = $connectdata['docstatus'];
        //$videostatus = $connectdata['mediastatus'];
        $extraparams = $meta['extraparams'];
        $watermd5 = '';
        if($extraparams['watermarkstatus']){
            $watermd5 = !$extraparams['watermarktext'] ? $_G['setting']['watermd5']:($extraparams['watermarktext'] ? $extraparams['watermarktext']:$_G['setting']['watermarktext']);
        }

        //获取视频缩略图
        if(in_array($meta['ext'],$videoexts)){

            if($meta['duration']){
                $start=ceil($meta['duration']/5);
            }else{
                $start = 1;
            }
            $extraflag = '';

            if ($_G['setting']['watermarkstatus'] || $extraparams['position_text'] || $extraparams['position']) {
                $extraflag .= '_w';
            }
            if ($extraparams['watermarktype']) {
                $extraflag .= '_' . $extraparams['watermarktype'];
            }
            if ($extraparams['watermarktype']['watermarktext']) {
                $extraflag .= '_' . $extraparams['watermarktext'];
            }


            $thumbpath = $this->getthumbpath('pichomethumb');

            if($meta['aid']) $thumbname = md5($meta['aid'].$extraflag).'.webp';
            else $thumbname = md5($meta['rid'].$extraflag).'.webp';
            $thumbpath = $thumbpath.$thumbname;
            $defaultspace = $_G['setting']['defaultspacesetting'];
            $fpatharr = explode(':',$meta['path']);
            unset($fpatharr[0]);
            //$ofpath = implode('/',$fpatharr);
            $object =$fpatharr[2];
            $result = $this->video->get_Snapshot($object, $start,$thumbpath);
            //如果获取到缩略图
            if ($result['success']) {
                if($defaultspace['bz'].':'.$defaultspace['did'].':' != $meta['bz']){
                    $thumbpath = $meta['bz'].':'.$meta['remoteid'].':'.$result['success'];
                    $cloudpath = $defaultspace['bz'].':'.$defaultspace['did'] . ':' .$thumbpath;
                    $return = IO::moveThumbFile($cloudpath,$thumbpath);
                    if(isset($return['error'])){
                        return false;
                    }
                }else{
                    $cloudpath = $result['success'];
                }
                return array($cloudpath);
            }else{
                return '';
            }
        }elseif(in_array($meta['ext'],$officeexts)){//获取文档缩略图

            $result = $this->video->getDocthumb($meta);
            if ($result['success']) {
                return array($result['success']);
            }else{
                return '';
            }
        }

	}

    public function getthumbpath($dir = 'dzz'){
        $subdir = $subdir1 = $subdir2 = '';
        $subdir1 = date('Ym');
        $subdir2 = date('d');
        $subdir = $subdir1 . '/' . $subdir2 . '/';
        // $target1 = $dir . '/' . $subdir . 'index.html';
        $target = $dir . '/' . $subdir;
        return $target;
    }
}