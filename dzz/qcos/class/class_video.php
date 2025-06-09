<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/21
 * Time: 16:05
 */
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
@ini_set('max_execution_time', 0);

class video
{
    private $secretId = '';
    private $secretKey = '';
    private $region = '';
    private $bucket = '';
    private $queueid = '';

    public function __construct($config = array())
    {
        $this->secretId = $config['secretId'];
        $this->secretKey = $config['secretKey'];
        $this->region = $config['region'];
        $this->bucket = $config['bucket'];
    }

    //检查是否开启视频转换功能
    public function check_videobucket()
    {
        if (!$this->secretId || !$this->secretKey || !$this->region || !$this->bucket) {
            return false;
        }
        $bucketlist = $this->get_videobucket();
        if (in_array($this->bucket, $bucketlist)) {
            return true;
        }
        return false;
    }

    //获取开启视频转换的bucket
    public function get_videobucket()
    {
        $StartTimestamp = time();
        $EndTimestamp = $StartTimestamp + 3600;
        //请求地址
        $queryurl = $this->bucket . '.ci.' . $this->region . '.myqcloud.com/mediabucket';
        $params = [];
        //请求头
        $headers = [];
        $fileUri = '/mediabucket';
        $authorization = $this->get_authorization($StartTimestamp, $EndTimestamp, $fileUri, $headers);
        $bucketdata = $this->get($queryurl, $params, $authorization);
        $bucketlist = [];
        if ($bucketdata['MediaBucketList']) {
            $count = $bucketdata['TotalCount'];
            if ($count == 1) {
                $bucketlist[] = $bucketdata['MediaBucketList']['Name'];
            } else {
                foreach ($bucketdata['MediaBucketList'] as $v) {
                    $bucketlist[] = $v['Name'];
                }
            }
        }
        return $bucketlist;
    }

    //获取队列id
    public function get_queueid()
    {
        $StartTimestamp = time();
        $EndTimestamp = $StartTimestamp + 3600;
        //请求地址
        $queryurl = $this->bucket . '.ci.' . $this->region . '.myqcloud.com/queue';
        $parseurl = parse_url($queryurl);
        $params = ['state' => 'Active'];
        $hostarr = explode('/', $parseurl['path']);
        //请求头
        $headers = [];
        $fileUri = '/' . $hostarr[1];
        $authorization = $this->get_authorization($StartTimestamp, $EndTimestamp, $fileUri, $headers);
        $queuedata = $this->get($queryurl, $params, $authorization);
        $this->queueid = $this->parse_queueid($queuedata);
    }

    /* //智能封面
     public function get_SmartCover()
     {
         if (!$this->queueid) $this->get_queueid();
         $xmldata = '
         <Request>
           <Tag>SmartCover</Tag>
           <Input>
                  <Region>$this->region</Region>
                  <Bucket>$this->bucket</Bucket>
                  <Object>dzz/202012/02/135938zb1yxzb2oej5l2o7.avi</Object>
           </Input>
           <Operation>
             <Output>
                      <Region>$this->region</Region>
                     <Bucket>$this->bucket</Bucket>
                     <Object>dzz/202012/02/my-new-cover-${Number}.jpg</Object>
             </Output>
           </Operation>
           <QueueId>' . $this->queueid . '</QueueId>
         </Request>';

         $StartTimestamp = time();
         $EndTimestamp = $StartTimestamp + 3600;
         //请求地址
         $queryurl = $this->bucket . '.ci.' . $this->region . '.myqcloud.com/jobs';
         $params = ['Tag' => 'SmartCover'];
         //请求头
         $headers = [];
         $fileUri = '/jobs';
         $authorization = $this->get_authorization($StartTimestamp, $EndTimestamp, $fileUri, $headers, 'post');
         $result = $this->get($queryurl, $params, $authorization, $xmldata);

     }
     */
    //解析队列id数据
    function parse_queueid($queuedata)
    {
        $queueid = '';
        if ($queuedata['TotalCount']) {
            $queueid = $queuedata['QueueList']['QueueId'];
        }
        return $queueid;
    }

    //发起转码任务
    function create_mediajobs($filepath, $outputpath,$videoquality=0, $watertplids = [])
    {
        //获取任务id
        if (!$this->queueid) $this->get_queueid();
        //获取转码模板id
        $templateid = $this->get_mediatpl($videoquality);
        if(!$templateid){
            $templateid = $this->createTransTemplate($videoquality);
        }
        //备用水印模板变量，将来用系统水印模板数据替代
        //$watertpldata = [];
        $xmlwaterdata = '';
        if (!empty($watertplids)) {
            $haswater = 1;
            foreach ($watertplids as $v) {
                $xmlwaterdata .= '<WatermarkTemplateId>' . $v . '</WatermarkTemplateId>';
            }
        } else {
            $haswater = 0;
            //$xmlwaterdata = '<WatermarkTemplateId>t13b3128bc0ea24d479645e92a30434ff0</WatermarkTemplateId>';
        }
        $xmldata = <<<EOF
                <Request>
                     <Tag>Transcode</Tag>
                    <Input>
                            <Region>$this->region</Region>
                            <Bucket>$this->bucket</Bucket>
                            <Object>$filepath</Object>
                     </Input>
                    <Operation>
                         <TemplateId>$templateid</TemplateId>
                        $xmlwaterdata
                        <Output>
                                 <Region>$this->region</Region>
                                 <Bucket>$this->bucket</Bucket>
                                <Object>$outputpath</Object>
                        </Output>
                    </Operation>
                     <QueueId>$this->queueid</QueueId>
            </Request>
EOF;

        $StartTimestamp = time();
        $EndTimestamp = $StartTimestamp + 3600;
        //请求地址
        $queryurl = $this->bucket . '.ci.' . $this->region . '.myqcloud.com/jobs';
        $params = [];
        //请求头
        $headers = [];
        $fileUri = '/jobs';
        $authorization = $this->get_authorization($StartTimestamp, $EndTimestamp, $fileUri, $headers, 'post');
        $jobdata = $this->get($queryurl, $params, $authorization, $xmldata);
        $data = $this->parase_mediajobdata($jobdata);
        return $data;
    }
    public function getVideoQuality($videoquality = 0){
        $templatename = '';
        switch($videoquality){
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
        return array($templatename,$width,$height,$bitrate);
    }
    //创建转码模板
    public function createTransTemplate($videoquality = 0){

        $format = $this->getVideoQuality($videoquality);
        list($templatename,$width,$height,$bitrate) = $format;

        $xmldata = <<<EOF
        <Request>
    <Tag>Transcode</Tag>
    <Name>$templatename</Name>
    <Container>
        <Format>mp4</Format>
    </Container>
    <Video>
        <Codec>H.264</Codec>
        <Profile>high</Profile>
        <Bitrate>$bitrate</Bitrate>
        <Crf></Crf>
        <Width>$width</Width>
        <Height></Height>
        <Fps></Fps>
        <Gop></Gop>
        <Preset>medium</Preset>
        <ScanMode></ScanMode>
    </Video>
    <Audio>
        <Codec>mp3</Codec>
        <Samplerate>44100</Samplerate>
        <Bitrate>128</Bitrate>
        <Channels>2</Channels>
    </Audio>
    <TransConfig>
        <AdjDarMethod>scale</AdjDarMethod>
        <IsCheckReso>false</IsCheckReso>
        <ResoAdjMethod>1</ResoAdjMethod>
    </TransConfig>
    <TimeInterval>
        <Start>0</Start>
        <Duration>0</Duration>
    </TimeInterval>
</Request>
EOF;
        $StartTimestamp = time();
        $EndTimestamp = $StartTimestamp + 3600;
        //请求地址
        $queryurl = $this->bucket . '.ci.' . $this->region . '.myqcloud.com/template';
        $params = [];
        //请求头
        $headers = [];
        $fileUri = '/template';
        $authorization = $this->get_authorization($StartTimestamp, $EndTimestamp, $fileUri, $headers, 'post');
        $tmplatedata = $this->get($queryurl, $params, $authorization, $xmldata);
       return $tmplatedata['Template']['TemplateId'];

    }
    //解析任务数据
    public function parase_mediajobdata($jobdata)
    {
        if ($jobdata['JobsDetail']['Code'] == 'Success') {
            return array(
                'success' => true,
                'jobid' => $jobdata['JobsDetail']['JobId'],
                'templateid' => $jobdata['JobsDetail']['Operation']['TemplateId'],
                'waterid' => isset($jobdata['JobsDetail']['Operation']['WatermarkTemplateId']) ? $jobdata['JobsDetail']['Operation']['WatermarkTemplateId'] : '',
                'status' => 1,
                'dateline' => TIMESTAMP
            );
        } else {
            return array('error' => $jobdata['Message']);
        }
    }

    //获取媒体转码模板
    public function get_mediatpl($videoquality = 0)
    {
        $format = $this->getVideoQuality($videoquality);
        list($templatename,$width,$height,$bitrate) = $format;
        $StartTimestamp = time();
        $EndTimestamp = $StartTimestamp + 3600;
        //请求地址
        $queryurl = $this->bucket . '.ci.' . $this->region . '.myqcloud.com/template';
        $params = ['tag' => 'Transcode', 'category' => 'Custom', 'name' => $templatename];
        //请求头
        $headers = [];
        $fileUri = '/template';
        $authorization = $this->get_authorization($StartTimestamp, $EndTimestamp, $fileUri, $headers);
        $mediatpldata = $this->get($queryurl, $params, $authorization);

        $tpldata = $this->parse_mediatpl($mediatpldata);

        return $tpldata;
    }

//解析媒体模板数据
    public function parse_mediatpl($mediatpldata)
    {

        if ($mediatpldata['TotalCount'] == 1) {
           return $mediatpldata['TemplateList']['TemplateId'];
        }
        return false;
    }

    //请求接口结果
    public function get($url, $params, $authheader, $xmldata = '')
    {
        $arr_header[] = "Content-Type:application/xml";
        $arr_header[] = "Authorization: " . $authheader;
        $arr_header[] = "Host:" . $this->bucket . ".ci." . $this->region . ".myqcloud.com";
        //$arr_header[] = "x-cos-acl:private";
        $ch = curl_init();
        $url .= '?' . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (!empty($arr_header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_header);
        }
        if ($xmldata) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_REFERER, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $response = curl_exec($ch);
        $xmlstring = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
        curl_close($ch);
        $value_array = json_decode(json_encode($xmlstring), true);
        return $value_array;
    }

    //兼容linux下获取文件名
    public function get_basename($filename)
    {
        if ($filename) {
            return preg_replace('/^.+[\\\\\\/]/', '', $filename);
        }
        return '';

    }

    //计算签名
    public function get_authorization($StartTimestamp, $EndTimestamp, $fileUri, $headers, $method = 'get')
    {
        $qSignTime = "$StartTimestamp;$EndTimestamp";
        $qKeyTime = $qSignTime;

        $header_list = $this->get_q_header_list($headers);
        //如果 Uri 中带有 ?的请求参数，该处为数组排序后的字符串组合
        $url_param_list = '';

        $httpMethod = $method;
        $httpUri = $fileUri;

        //与 q-url-param-list 相同
        $httpParameters = $url_param_list;

        //将自定义请求头分解为 & 连接的字符串
        $headerString = $this->get_http_header_string($headers);
        // 计算签名中的 signature 部分
        $signTime = $qSignTime;
        $signKey = hash_hmac('sha1', $signTime, $this->secretKey);
        $httpString = "$httpMethod\n$httpUri\n$httpParameters\n$headerString\n";
        $sha1edHttpString = sha1($httpString);
        $stringToSign = "sha1\n$signTime\n$sha1edHttpString\n";
        $signature = hash_hmac('sha1', $stringToSign, $signKey);
        //组合结果
        $authorization = "q-sign-algorithm=sha1&q-ak=$this->secretId&q-sign-time=$qSignTime&q-key-time=$qKeyTime&q-header-list=$header_list&q-url-param-list=$url_param_list&q-signature=$signature";
        return $authorization;
    }

    //组合header数据
    public function get_q_header_list($headers)
    {
        if (!is_array($headers)) {
            return false;
        }
        try {
            $tmpArray = array();
            foreach ($headers as $key => $value) {
                array_push($tmpArray, strtolower($key));
            }
            sort($tmpArray);
            return implode(';', $tmpArray);
        } catch (Exception $error) {
            return false;
        }
    }

    //获取header字符串
    public function get_http_header_string($headers)
    {
        if (!is_array($headers)) {
            return false;
        }
        try {
            $tmpArray = array();
            foreach ($headers as $key => $value) {
                $tmpKey = strtolower($key);
                $tmpArray[$tmpKey] = urlencode($value);
            }
            ksort($tmpArray);
            $headerArray = array();
            foreach ($tmpArray as $key => $value) {
                array_push($headerArray, "$key=$value");
            }
            return implode('&', $headerArray);
        } catch (Exception $error) {
            return false;
        }
    }

    public function get_jobdata($jobid)
    {
        $StartTimestamp = time();
        $EndTimestamp = $StartTimestamp + 3600;
        //请求地址
        $queryurl = $this->bucket . '.ci.' . $this->region . '.myqcloud.com/jobs/' . $jobid;
        $parseurl = parse_url($queryurl);
        $params = [];
        $hostarr = explode('/', $parseurl['path']);
        //请求头
        //$headers = ['content-length' => 0, 'content-type' => 'application/xml', 'host' => $hostarr[0], 'x-cos-acl' => 'private'];
        $headers = [];
        $fileUri = '/jobs/' . $jobid;
        $authorization = $this->get_authorization($StartTimestamp, $EndTimestamp, $fileUri, $headers);
        $jobdata = $this->get($queryurl, $params, $authorization);
        if ($jobdata['JobsDetail']['State'] == 'Success') {
            return 2;
        } elseif ($jobdata['JobsDetail']['State'] == 'Running') {
            return 1;
        } elseif ($jobdata['JobsDetail']['State'] == 'Failed') {
            return -1;
        } elseif ($jobdata['JobsDetail']['State'] == 'Pause') {
            return 3;
        } elseif ($jobdata['JobsDetail']['State'] == 'Cancel') {
            return 4;
        }
    }

    //视频截帧
    public function get_Snapshot($filepath, $start = 5, $outputpath = '')
    {
        if (!$outputpath) $outputpath = 'tmpthumbpath' . '/' . $this->get_basename($filepath) . '.webp';
        $xmldata = <<<EOF
                <Request>
                     <Input>
                          <Region>$this->region</Region>
                          <Bucket>$this->bucket</Bucket>
                          <Object>$filepath</Object>
                      </Input>
                      <Time>$start</Time>
                      <Width>640</Width>
                     <Mode>keyframe</Mode>
                     <Format>jpg</Format>
                     <Output>
                          <Region>$this->region</Region>
                          <Bucket>$this->bucket</Bucket>
                         <Object>$outputpath</Object>
                  </Output>
                </Request>
EOF;

        $StartTimestamp = time();
        $EndTimestamp = $StartTimestamp + 3600;
        //请求地址
        $queryurl = $this->bucket . '.ci.' . $this->region . '.myqcloud.com/snapshot';
        $params = [];
        //请求头
        $headers = [];
        $fileUri = '/snapshot';
        $authorization = $this->get_authorization($StartTimestamp, $EndTimestamp, $fileUri, $headers, 'post');
        $result = $this->get($queryurl, $params, $authorization, $xmldata);
        if ($result['Message']) {
            return array('error' => $result['Message']);
        } else {
            return array('success'=>$result['Output']['Object']);
        }
    }

    public function get_mediainfo($filepath)
    {
        $xmldata = '
                <Request>
                  <Input>
                    <Object>' . $filepath . '</Object>
                  </Input>
                </Request>';

        $StartTimestamp = time();
        $EndTimestamp = $StartTimestamp + 3600;
        //请求地址
        $queryurl = $this->bucket . '.ci.' . $this->region . '.myqcloud.com/mediainfo';
        $params = [];
        //请求头
        $headers = [];
        $fileUri = '/mediainfo';
        $authorization = $this->get_authorization($StartTimestamp, $EndTimestamp, $fileUri, $headers, 'post');
        $mediadata = $this->get($queryurl, $params, $authorization, $xmldata);
        $data = $this->parse_mediadata($mediadata['MediaInfo']);
        return $data;
    }

    public function parse_mediadata($mediadata)
    {
        $data = [];
        if (!empty($mediadata)) {
            $data = array(
                'width' => intval($mediadata['Stream']['Video']['Width']),
                'height' => intval($mediadata['Stream']['Video']['Height']),
                'avg_frame_rate' => round($mediadata['Stream']['Video']['Fps'], 2),
                'bit_rate' => intval($mediadata['Format']['Bitrate']) . 'kbps',
                'duration' => round($mediadata['Format']['Duration'], 2) . 's',
                'format_name' => $mediadata['Format']['FormatName'],
            );
        }
        return $data;
    }

    //获取开启文档处理的bucket
    public function get_officebucket()
    {
        $StartTimestamp = time();
        $EndTimestamp = $StartTimestamp + 3600;
        //请求地址
        $queryurl = $this->bucket . '.ci.' . $this->region . '.myqcloud.com/docbucket';
        $params = [];
        //请求头
        $headers = [];
        $fileUri = '/docbucket';
        $authorization = $this->get_authorization($StartTimestamp, $EndTimestamp, $fileUri, $headers);
        $bucketdata = $this->get($queryurl, $params, $authorization);
        $bucketlist = [];
        if ($bucketdata['DocBucketList']) {
            $count = $bucketdata['TotalCount'];
            if ($count == 1) {
                $bucketlist[] = $bucketdata['DocBucketList']['Name'];
            } else {
                foreach ($bucketdata['DocBucketList'] as $v) {
                    $bucketlist[] = $v['Name'];
                }
            }
        }
        return $bucketlist;
    }

    //检查是否开启文档处理功能
    public function check_docbucket()
    {
        if (!$this->secretId || !$this->secretKey || !$this->region || !$this->bucket) {
            return false;
        }
        $bucketlist = $this->get_officebucket();

        if (in_array($this->bucket, $bucketlist)) {
            return true;
        }
        return false;
    }

    public function getDocthumb($data)
    {
        global $_G;
        if($data['aid']){
            $attachment = IO::getMeta('attach::'.$data['aid']);
        }else{
            $attachment = IO::getMeta($data['rid']);
        }

        $signedUrl = IO::getStream($data['path']);

        if (!$data['original']) {
            $params = $this->parseparams($signedUrl, $data['width'], $data['height'], $data['thumbtype']);
        }
        $extraparams = $data['extraparams'];
        if($extraparams['watermarkstatus'] && !$extraparams['watermarktext']){
            //获取水印图片地址
            $extraparams['waterimg'] = $attachment['bz'].'static/waterimg/water.png';
        }
        //水印参数
        $waterparams = $this->parsewatermarkparams($signedUrl, $data['extraparams'], $data['width'], $data['height']);

        $signedUrl = explode('?', $signedUrl);
        $url = $signedUrl[0] . '?ci-process=doc-preview&page=1&dstType=webp&' . ($params ? $params : '') . ($waterparams ? '|' . $waterparams . '&' : '&') . $signedUrl[1];
        if (!$url) return false;
        //组合缩略图地址

        $watermd5 = '';
        if($extraparams['watermarkstatus']){
            $watermd5 = !$extraparams['watermarktext'] ? $_G['setting']['watermd5']:($extraparams['watermarktext'] ? $extraparams['watermarktext']:$_G['setting']['watermarktext']);
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
        if($data['aid']) $thumbname = md5($data['aid'].$extraflag).'.webp';
        else $thumbname = md5($data['path'].$extraflag).'.webp';
        $thumbpath = $thumbpath.$thumbname;
        $defaultspace = $_G['setting']['defaultspacesetting'];
        $cloudpath = $defaultspace['bz'].':'.$defaultspace['did'] . ':/' .$thumbpath;
        //如果获取到缩略图
        if (!$data['tmpfile']) {
            $return = IO::moveThumbFile($cloudpath, $url);
            if (isset($return['error'])) {
                return false;
            } else {
                //插入缩略图记录表
                return array('success'=>$thumbpath);
            }
        } else return array('success'=>$url);
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
    public function parseparams($signedUrl, $width, $height, $thumbtype)
    {
        $imgwidth = 842;
        $imgheight = 595;
        //横图按高度给比例值 竖图按宽度比例给值
        if ($imgwidth > 9999 || $imgheight > 9999) {
            return false;
        }
        //如果缩略图宽高大于等于原图宽高则不生成缩略图
        if ($width >= $imgwidth || $height >= $imgheight) {
            return '';
        }
        $oscale = $imgwidth / $imgheight;
        $nscale = $width / $height;


        if ($thumbtype == 2) {
            if ($oscale > $nscale) {
                //按高度度等比剪裁
                return 'imageMogr2/thumbnail/x' . $height . '/|imageMogr2/gravity/center/crop/' . $width . 'x' . $height . '/interlace/0';
            } else {
                //按宽度等比剪裁
                return 'imageMogr2/thumbnail/' . $width . 'x' . '/|imageMogr2/gravity/north/crop/' . $width . 'x' . $height . '/interlace/0';
            }
        } else {
            if ($oscale > $nscale) {
                $width = $width;
                $height = $height / $oscale;
            } else {
                $height = $height;
                $width = $width * $oscale;
            }
            if ($oscale > $nscale) {
                //按宽度等比缩放
                return 'imageMogr2/thumbnail/' . $width . 'x' . '/interlace/0 ';
            } else {
                //按高度度等比缩放
                return 'imageMogr2/thumbnail/x' . $height . '/interlace/0 ';
            }
        }

    }

    public function parsewatermarkparams($signedUrl,$extraparams,$width,$height){
        global  $_G;
        if(!$_G['setting']['IsWatermarkstatus']) return false;

        //横图按高度给比例值 竖图按宽度比例给值
        $imginfo = @getimagesize($signedUrl);
        $imgwidth = $imginfo[0];
        $imgheight = $imginfo[1];
        if ($imgwidth > 9999 || $imgheight > 9999) {
            return false;
        }
        //如果缩略图宽高大于等于原图宽高则不生成缩略图
        /* if($width>= $imgwidth && $height>= $imgheight){
             return '';
         }*/
        //1920 1080 3600 720  640 360   1920/1080
        $oscale = $imgwidth / $imgheight;
        $nscale = $width /$height;

        if ($oscale > $nscale) {
            $width = $width;
            $height= $height/$oscale;
        } else {
            $height = $height;
            $width = $width*$oscale;
        }

        if (!($_G['setting']['watermarkstatus'] || $extraparams['position_text'] || $extraparams['position'])) {
            return '';
        }
        if(($_G['setting']['watermarkminwidth'] && $width <= $_G['setting']['watermarkminwidth'])
            || ($_G['setting']['watermarkminheight'] && $height <= $_G['setting']['watermarkminheight'])){
            return '';
        }
        $watermarktype = ($extraparams['watermarktype']) ? $extraparams['watermarktype']:$_G['setting']['watermarktype'];
        $watermarktext = ($extraparams['watermarktext']) ? $extraparams['watermarktext']:$_G['setting']['watermarktext'];
        $gravity = 'center';
        switch ($_G['setting']['watermarkstatus']) {
            /* 右下角水印 */
            case 9:
                $gravity = 'southeast';
                break;
            /* 左下角水印 */
            case 7:
                $gravity = 'southwest';
                break;
            /* 左上角水印 */
            case 1:
                $gravity = 'northwest';
                break;
            /* 右上角水印 */
            case 3:
                $gravity = 'northeast';
                break;
            /* 居中水印 */
            case 5:
                $gravity = 'center';
                break;
            /* 下居中水印 */
            case 8:
                $gravity = 'south';
                break;
            /* 右居中水印 */
            case 6:
                $gravity = 'east';
                break;
            /* 上居中水印 */
            case 2:
                $gravity = 'north';
                break;
            /* 左居中水印 */
            case 4:
                $gravity = 'west';
                break;
            default:
                $gravity = 'center';
        }
        $dx = $watermarktext['skewx']? $watermarktext['skewx']:0;
        $dy = $watermarktext['skewy'] ? $watermarktext['skewy']:0 ;
        if($watermarktype != 'text'){
            $imgurl = IO::getwaterimg($extraparams['waterimg']);
            $imgurl = str_replace('https','http',$imgurl);
            $imgurl = base64_encode($imgurl);
            $imgurl = str_replace(array('+','/'),array('-','_'),$imgurl);
            return 'watermark/1/image/'.$imgurl.'/gravity/'.$gravity.'/dx/'.$dx.'/dy/'.$dy;
        }else{
            $text =$watermarktext['textfull'];
            $text = base64_encode($text);
            $colorarr = explode(',',$watermarktext['color']);
            if(!empty($colorarr)){
                $color = 'rgb('.$colorarr[0].','.$colorarr[1].','.$colorarr[2].')';
                $color = $this->RGBToHex($color);
                $dissolve = isset($colorarr[3]) ? $colorarr[3]:90;
            }else{
                $color = $watermarktext['color'];
            }
            $color = base64_encode($color);
            $fontsize = $watermarktext['size'];
            return 'watermark/2/text/'.$text.'/fill/'.$color.'/fontsize/'.$fontsize.'/gravity/'.$gravity.'/dx/'.$dx.'/dy/'.$dy;
        }

    }
}