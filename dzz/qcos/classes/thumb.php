<?php
namespace dzz\qcos\classes;

use \core as C;
use \DB as DB;
use \IO as IO;
class thumb{

    private $video = null;
	public function run($meta){
        if(strpos($meta['realpath'],':') === false){
            $bz = 'dzz';
        }else{
            $patharr = explode(':', $meta['realpath']);
            $bz = $patharr[0];
            $did = $patharr[1];

        }
        if(!is_numeric($did) || $did < 2){
            $bz = 'dzz';
        }
        if(!$meta['ext'] || $bz != 'QCOS'){
            return '';
        }
        $connectdata = C::t('connect_storage')->fetch($did);
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
        $videoexts =  getglobal('config/qcosmedia') ? explode(',', getglobal('config/qcosmedia')):array('3gp','avi','flv','mp4','m3u8','mpg','asf','wmv','mkv','mov','ts','webm','mxf');
        $officeexts = getglobal('config/qcosoffice') ? explode(',',getglobal('config/qcosoffice')):array('pptx','ppt','pot','potx','pps','ppsx','dps','dpt','pptm','potm','ppsm','doc','dot','wps','wpt','docx','dotx','docm','dotm','xls','xlt','et','ett','xlsx','xltx','csv','xlsb','xlsm','xltm','ets','pdf','lrc','c','cpp','h','asm','s','java','asp','bat','bas','prg','cmd','rtf','txt','log','xml','htm','html');
        $officestatus = $connectdata['docstatus'];
        $videostatus = $connectdata['mediastatus'];

        //获取视频缩略图
        if($videostatus && in_array($meta['ext'],$videoexts)){
            if($meta['duration']){
                $start=ceil($meta['duration']/5);
            }else{
                $start = 1;
            }

            $outputpath = 'tmppichomethumb/'.$meta['appid'].'/'.md5($meta['realpath'].$meta['thumbsign']).'.jpg';
            $fpatharr = explode('/',$meta['realpath']);
            unset($fpatharr[0]);
            $ofpath = implode('/',$fpatharr);
            $object = str_replace(BS,'/',$ofpath);
            $result = $this->video->get_Snapshot($object, $start,$outputpath);
            //如果获取到缩略图
            if ($result['success']) {
                return array($bz.':'.$did.':/'.$result['success']);
            }else{
                return '';
            }
        }elseif($officestatus && in_array($meta['ext'],$officeexts)){//获取文档缩略图

            $result = $this->video->getDocthumb($meta);
            if ($result['success']) {
                return array($result['success']);
            }else{
                return '';
            }
        }

	}
}