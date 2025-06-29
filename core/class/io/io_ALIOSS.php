<?php
    /*
     * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
     * @license     https://www.oaooa.com/licenses/
     *
     * @link        https://www.oaooa.com
     * @author      zyx(zyx@oaooa.com)
     */
    
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    include_once DZZ_ROOT . './core/api/oss_sdk/autoload.php';
    
    use OSS\Core\OssException;
    use OSS\OssClient;
    
    @set_time_limit(0);
    @ini_set('max_execution_time', 0);
    
    class io_ALIOSS extends io_api
    {
        const T = 'connect_storage';
        const BZ = 'ALIOSS';
        private $icosdatas = array();
        private $bucket = '';
        private $_root = '';
        private $_rootname = '';
        private $hostname = '';
        private $perm = 0;
        private $alc = '';
        
        public function __construct($path)
        {
            $arr = DB::fetch_first("SELECT root,name FROM %t WHERE bz=%s", array('connect', self::BZ));
            $this->_root = $arr['root'];
            $this->_rootname = $arr['name'];
            //$this->perm = perm_binPerm::getMyPower();
            //self::init($path);
        }
        
        public function MoveToSpace($path, $attach)
        {
            global $_G;
            /*
         *移动附件	 *
         */
            $filename = substr($path, strrpos($path, '/') + 1);;
            $fpath = substr($path, 0, strrpos($path, '/')) . '/';
          /*  if ($re = $this->makeDir($fpath)) { //创建目录
                if ($re['error']) return $re;
            }*/
            $obz = io_remote::getBzByRemoteid($attach['remote']);
            if ($obz == 'dzz') {
                $opath = 'attach::' . $attach['aid'];
            } else {
                $opath = $obz . '/' . $attach['attachment'];
            }
            if ($re = $this->multiUpload($opath, $fpath, $filename, $attach, 'overwrite')) {
                if ($re['error']) return $re;
                else {
                    return true;
                }
            }
            return false;
        }
        
        public function createFolderByPath($path, $pfid = '', $noperm = false)
        {
            $data = array();
            if (self::makeDir($path)) {
                $data = self::getMeta($path);
            }
            return $data;
        }
        
        public function makeDir($path)
        {
            $arr = $this->parsePath($path);
            $patharr = explode('/', trim($arr['object'], '/'));
            $folderarr = array();
            $p = $arr['bz'] . $arr['bucket'];
            foreach ($patharr as $value) {
                $p .= '/' . $value;
                $re = $this->_makeDir($p);
                if (isset($re['error'])) {
                    return $re;
                } else {
                    continue;
                }
                
            }
            return true;
        }
        
        protected function _makeDir($path)
        {
            global $_G;
            $arr = self::parsePath($path);
            try {
                $oss = self::init($path);
                if (is_array($oss) && $oss['error']) return $oss;
                try {
                    $oss->putObject($arr['bucket'], $arr['object'], '');
                } catch (OssException $e) {
                    return array('error' => $e->getMessage());
                }
                
                return true;
            } catch (Exception $e) {
                //var_dump($e);
                return array('error' => $e->getMessage());
            }
            
        }
        
        /*
        *初始化OSS 返回oss 操作符
        */
        public function init($bz, $isguest = 0)
        {
            global $_G;
            $bzarr = explode(':', $bz);
            $id = trim($bzarr[1]);
            if (!$root = DB::fetch_first("select * from " . DB::table(self::T) . " where  id='{$id}'")) {
                return array('error' => 'need authorize to ' . $bzarr[0]);
            }
            if (!$isguest && $root['uid'] > 0 && $root['uid'] != $_G['uid']) return array('error' => 'need authorize to ALIOSS');
            
            $access_id = dzzdecode($root['access_id'], 'ALIOSS');
            if (empty($access_id)) $access_id = $root['access_id'];
            $access_key = dzzdecode($root['access_key'], 'ALIOSS');
            if ($root['cloudname']) {
                $this->_rootname = $root['cloudname'];
            } else {
                $this->_rootname .= ':' . ($root['bucket'] ? $root['bucket'] : cutstr($root['access_id'], 4, $dot = ''));
            }
            $this->bucket = $root['bucket'];
            //默认内网不可用
            $isuseinternal = 0;
            //如果存在内网地域，检测内网是否可用
            if (!$isguest && isset($root['internalhostname'])) {
                $isuseinternal = $this->checkInternal($access_id, $access_key, $root['internalhostname']);
            }
            $this->hostname = $isuseinternal ? $root['internalhostname'] : $root['hostname'];
            
            try {
                /*echo $access_id.'<br>';
                echo $access_key.'<br>';
                echo $this->hostname;*/
                //die;
                return new OssClient($access_id, $access_key, $this->hostname);
            } catch (Exception $e) {
                return array('error' => $e->getMessage());
            }
        }
        
        //检测内网地址是否可用并缓存结果1小时，缓存状态值为1为可用，2为不可用
        public function checkInternal($access_id, $access_key, $hostname)
        {
            //内网可用判断缓存键
            $cachekey = "aliossinternalcheck_isallowuse";
            //获取内网可用状态值
            $memorydata = memory('get', $cachekey);
            if ($memorydata === 1) {
                return true;
            } elseif ($memorydata === 2) {
                return false;
            } else {
                try {
                    //尝试使用内网地域实例化操作，以检测内网是否可用，可用即缓存1，不可用缓存2
                    $filename = 'testinternal.txt';
                    $fileContent = '123';
                    $ossclient = new OssClient($access_id, $access_key, $hostname);
                    $ret = $ossclient->putObject($this->bucket, $filename, $fileContent);
                    $ossclient->deleteObject($this->bucket, $filename);;
                    memory('set', $cachekey, 1, 3600);
                    return true;
                } catch (OssException $e) {
                    //  print_r($e);die;
                    memory('set', $cachekey, 2, 3600);
                    return false;
                }
            }
            
        }
        public function uploadByStream($path,$filename,$file,$pfid,$relativePath='',$nohook=0){
            global $_G;
            $arr = self::parsePath($path);
            $bzarr = explode(':',$arr['bz']);
            $data['remoteid']=DB::result_first("select remoteid from %t  where did = %d",array('local_storage',$bzarr[1]));
            //获取上传的目标目录
            $dirarr = explode('/', $relativePath);
            //如果有不存在的目录则创建之
            $relativepath = !empty($dirarr) ? implode('/', $dirarr) : '';
            $datas = array();
            $datas['pfid'] = $pfid;
            if ($relativepath) {
                $datas = IO::createFolderByPath($relativepath, $pfid);
                $pfid = $datas['pfid'];
            }
            //获取文件md5
            $md5 =  md5_file($file);
            
            if($md5){
                $attachment = C::t('attachment')->fetch_by_md5($md5);
            }
            //如果文件已经在附件表存在，调用秒传，返回结果
            if($attachment){
                $data['icoarr'][]  = io_dzz::secondUpload($filename,$attachment,$pfid,1,1);
                if(isset($data['icoarr']['error'])){
                    return array('errormsg'=>$data['icoarr']['error']);
                }else{
                 return $data;
                }
            }
            else{
                $data['md5'] = $md5;
                $data['size'] = filesize($file);
    
                $pathinfo = pathinfo($path);
                $ext = strtolower($pathinfo['extension']);
                $filepath = $arr['object'];
                $attachment = array(
                    'filesize' => $data['size'],
                    'attachment' => $filepath,
                    'filetype' => $ext,
                    'filename' => $filename,
                    'remote' => $data['remoteid'],
                    'copys' => 0,
                    'md5' => $data['md5'],
                    'unrun' => 0,
                    'dateline' => $_G['timestamp'],
                );
                //获取文件路由对应位置
                $routeremoteid = C::t('local_router')->getRemoteId($attachment);
                //如果路由位置和当前文件位置不同,则移动临时区文件到路由所指定位置
                if ($routeremoteid && $routeremoteid != $data['remoteid']) {
                    $localdata = C::t('local_storage')->fetch($routeremoteid);
                    //如果路由位置为本地则直接赋值路径
                    if($localdata['bz'] == 'dzz') {
                        $path = 'dzz::'.$filepath;
                    } else {
                        $did = $localdata['did'];
                        $connectdata = C::t('connect_storage')->fetch($did);
                        $path = $localdata['bz'] . ':' . $did . ':' . $connectdata['bucket'] . '/' . $filepath;
                    }
                    $attachment['remote'] = $routeremoteid;
                  /*  //如果不是阿里云则调用对应位置方法
                    if($localdata['bz'] != 'ALIOSS'){
                        return IO::uploadByStream($path,$filename,$file,$pfid,$relativePath,$nohook);
                    }*/
                }
          
                $return = IO::moveThumbFile($path,$file);
                if(!isset($return['error'])){
                    $attachment['attachment'] = $arr['object'];
                    //第三个参数代表由服务器端上传，控制挂载点调用
                    $attachment['aid'] = C::t('attachment')->insert($attachment, 1,$nohook);
                    //文件上传至目标位置后的数据库数据处理
                    $datas['icoarr'][] = self::uploadToattachment($attachment, $pfid,$nohook);
                    return $datas;
                }else{
                    return $return;
                }
              
            }
        
        }
      
        public function getBucketALC($path)
        {
            $arr = self::parsePath($path);
            $oss = self::init($path, 1);
            if (is_array($oss) && $oss['error']) return $oss;
            $cachekey = "ossbucketpermcheck_" . $arr['bucket'];
            $memorydata = memory('get', $cachekey);
            if ($memorydata) return $this->alc = $memorydata;
            else {
                $response = $oss->getBucketAcl($arr['bucket']);
                memory('set', $cachekey, $response, 3600);
                return $this->alc = $response;
            }
            
        }
        
        public function getBucketList($access_id, $access_key)
        {
            $re = array();
            if (!$access_id || !$access_key) return array();
            try {
                //requireOnce DZZ_ROOT.'./core/api/oss_sdk/sdk.class.php';
                $oss = new ALIOSS($access_id, $access_key);
                $response = $oss->listBuckets();
            } catch (OssException $e) {
                return array();
            }
            if (is_array($oss) && $oss['error']) return $oss;
            
            $bucket = $response;
            foreach ($bucket['ListAllMyBucketsResult']['Buckets']['Bucket'] as $key => $value) {
                if (is_array($value) && $value['Name']) {
                    $re[] = $value['Name'];
                } else {
                    $re[] = $bucket['ListAllMyBucketsResult']['Buckets']['Bucket']['Name'];
                    break;
                }
            }
            return $re;
        }
        
        public function authorize($refer)
        {
            global $_G, $_GET, $clouds;
            if (empty($_G['uid'])) {
                dsetcookie('_refer', rawurlencode(BASESCRIPT . '?mod=connect&op=oauth&bz=ALIOSS'));
                showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
            }
            if (submitcheck('alisubmit')) {
                $access_id = $_GET['access_id'];
                $access_key = $_GET['access_key'];
                $hostname = $_GET['hostname'];
                $internalhostname = $_GET['internalhostname'];
                $bucket = $_GET['bucket'];
                $rolearn = trim($_GET['extra']);
                $host = trim($_GET['host']);
                $internalhost = trim($_GET['internalhost']);
                if (!$access_id || !$access_key) {
                    showmessage(lang('input_aliyun_acc_sec') . 'Access Key ID and Access Key Secret', dreferer());
                }
                if (!$bucket || !$hostname) showmessage('select_bucket_node_address', dreferer());
                
                $oss = new OssClient($access_id, $access_key, $hostname);
                try {
                    $response = $oss->listbuckets();
                    
                } catch (OssException $e) {
                    showmessage($e->getMessage(), dreferer());
                }
                $type = 'ALIOSS';
                $uid = defined('IN_ADMIN') ? 0 : $_G['uid'];
                $setarr = array('uid' => $uid,
                    'access_id' => $access_id,
                    'access_key' => dzzencode($access_key, $type,0),
                    'bz' => $type,
                    'bucket' => $bucket,
                    'host' => $host,
                    'internalhost' => $internalhost,
                    'hostname' => $hostname,
                    'internalhostname' => $internalhostname,
                    'dateline' => TIMESTAMP,
                    'extra' => $rolearn
                );
                
                $checkdata = $this->checkalioss($setarr);
                if (isset($checkdata['error'])) {
                    showmessage('do_failed', BASESCRIPT . '?mod=cloud&op=space');
                }
                
                if ($id = DB::result_first("select id from " . DB::table(self::T) . " where uid='{$uid}' and access_id='{$access_id}' and bucket='{$bucket}'")) {
                    DB::update(self::T, $setarr, "id ='{$id}'");
                } else {
                    $id = DB::insert(self::T, $setarr, 1);
                }
                if (defined('IN_ADMIN')) {
                    $setarr = array('name' => $clouds[$type]['name'] . ':' . ($bucket ? $bucket : cutstr($access_id, 4, '')),
                        'bz' => $type,
                        'isdefault' => 0,
                        'dname' => self::T,
                        'did' => $id,
                        'dateline' => TIMESTAMP
                    );
                    if (!DB::result_first("select COUNT(*) from %t where did=%d and dname=%s ", array('local_storage', $id, self::T))) {
                        C::t('local_storage')->insert($setarr);
                    }
                    showmessage('do_success', BASESCRIPT . '?mod=cloud&op=space');
                } else {
                    showmessage('do_success', $refer ? $refer : BASESCRIPT . '?mod=connect');
                }
            } else {
                include template('oauth_ALIOSS');
            }
        }
        
        public function checkalioss($aliossconfig)
        {
            $bucket = $aliossconfig['bucket'];
            $alioss = new OssClient(
                $aliossconfig['access_id'],
                dzzdecode($aliossconfig['access_key'], 'ALIOSS'),
                $aliossconfig['hostname']
            );
            $foldername = 'tmpupload/';
            
            $filename = $foldername . 'testapi.txt';
            
            $filecontent = '123';
            try {
                $ret = $alioss->putObject($bucket, $filename, $filecontent);
            } catch (OssException $e) {
                return array('error' => $e->getMessage());
            }
            
            $return = $alioss->signUrl($bucket, $filename, 60 * 2);
            if (isset($return['error'])) {
                return array('error' => $return['error']);
            } else {
                if (file_get_contents($return) !== $filecontent) {
                    return array('error' => 'read is error');
                }
            }
            try {
                $response = $alioss->deleteObject($bucket, $filename);
                
                if ($response === false) {
                    return array('error' => 'delete error');
                }
            } catch (OssException $e) {
                return array('error' => $e->getMessage());
            }
            return true;
        }
        
        public function getBzByPath($path)
        {
            $bzarr = explode(':', $path);
            return $bzarr[0] . ':' . $bzarr[1] . ':';
        }
        
        public function getFileUri($path)
        {
            $arr = self::parsePath($path);
            $oss = self::init($path);
            if (is_array($oss) && $oss['error']) return $oss;
            if (empty($this->alc)) {
                try {
                    $this->alc = $this->getBucketALC($path);
                } catch (Exception $e) {
                    return array('error' => $e->getMessage());
                }
            }
            //echo $this->alc;die;
            if ($this->alc == 'public-read') {
                $scheme = (strpos($this->hostname, 'https://') === 0) ? 'https://' : 'http://';
                $host = str_replace(array('http://', 'https://', '-internal.aliyuncs.com'), array('', '', '.aliyuncs.com'), $this->hostname);
                $url = $scheme . $arr['bucket'] . '.' . $host . '/' . $arr['object'];
                return $url;
            } else {
                
                $url = $oss->signUrl($arr['bucket'], $arr['object'], 60 * 60 * 2);
                $url = str_replace('-internal.aliyuncs.com', '.aliyuncs.com', $url);
                return $url;
            }
        }
        
        public function deleteThumb($path)
        {
            global $_G;
            $imgcachePath = './imgcache/';
            $cachepath = str_replace('//', '/', str_replace(':', '/', $path));
            
            foreach ($_G['setting']['thumbsize'] as $value) {
                $target = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_1.jpeg';
                $target1 = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_2.jpeg';
                @unlink($_G['setting']['attachdir'] . $target);
                @unlink($_G['setting']['attachdir'] . $target1);
            }
            
        }
        
        public function base64url_encode($data)
        {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }
        
        //阿里缩略图参数
        public function parseThumbParams($path, $width, $height, $thumbtype)
        {
            $streamurl = IO::getStream($path);
            $imginfo = @getimagesize($streamurl);
            $imgwidth = $imginfo[0];
            $imgheight = $imginfo[1];
            if ($imgwidth > 9999 || $imgheight > 9999) {
                return false;
            }
            //如果缩略图宽高大于等于原图宽高则不生成缩略图
            if ($width >= $imgwidth || $height >= $imgheight) {
                return '';
            }
            $oscale = $imgwidth / $imgheight;
            $nscale = $width / $height;
            
            if ($oscale > $nscale) {
                $width = $width;
                $height = $height / $oscale;
            } else {
                $height = $height;
                $width = $width * $oscale;
            }
            if ($width > 4096) $width = 4096;
            else $width = intval($width);
            if ($height > 4096) $height = 4096;
            else $height = intval($height);
            if ($thumbtype == 1) {
                if ($oscale < $nscale) {
                    //按高度度等比剪裁
                    return 'image/resize,h_' . $height . ',m_lfit/auto-orient,1/quality,q_90/format,jpg';
                } else {
                    //按宽度等比剪裁
                    return 'image/resize,l_' . $width . ',m_mfit/auto-orient,1/quality,q_90/format,jpg';
                }
            } else {
                return 'image/resize,w_' . $width . ',h_' . $height . ',m_fill/auto-orient,1/quality,q_90/format,jpg';
            }
        }
        
        //解析水印参数
        public function parseWaterParams($path, $width, $height)
        {
            global $_G;
            if (!$_G['setting']['IsWatermarkstatus']) return false;
            $streamurl = IO::getStream($path);
            //横图按高度给比例值 竖图按宽度比例给值
            $imginfo = @getimagesize($streamurl);
            $imgwidth = $imginfo[0];
            $imgheight = $imginfo[1];
            if ($imgwidth > 9999 || $imgheight > 9999) {
                return false;
            }
            //如果缩略图宽高大于等于原图宽高则不生成缩略图
           /* if ($width >= $imgwidth && $height >= $imgheight) {
                return '';
            }*/
            //1920 1080 3600 720  640 360   1920/1080
            $oscale = $imgwidth / $imgheight;

            $nscale = $width / $height;
            
            if ($oscale > $nscale) {
                $this->width = $width;
                $this->height = $height / $oscale;
            } else {
                $this->height = $height;
                $this->width = $width * $oscale;
            }
            
            if (!($_G['setting']['watermarkstatus'] || $extraparams['position_text'] || $extraparams['position'])) {
                return '';
            }
            if (($_G['setting']['watermarkminwidth'] && $this->width <= $_G['setting']['watermarkminwidth'])
                || ($_G['setting']['watermarkminheight'] && $this->height <= $_G['setting']['watermarkminheight'])) {
                return '';
            }
            $watermarktype = ($extraparams['watermarktype']) ? $extraparams['watermarktype'] : $_G['setting']['watermarktype'];
            $watermarktext = ($extraparams['watermarktext']) ? $extraparams['watermarktext'] : $_G['setting']['watermarktext'];
            $gravity = 'g_se';
            switch ($_G['setting']['watermarkstatus']) {
                /* 右下角水印 */
                case 9:
                    $gravity = 'g_se';
                    break;
                /* 左下角水印 */
                case 7:
                    $gravity = 'g_sw';
                    break;
                /* 左上角水印 */
                case 1:
                    $gravity = 'g_nw';
                    break;
                /* 右上角水印 */
                case 3:
                    $gravity = 'g_ne';
                    break;
                /* 居中水印 */
                case 5:
                    $gravity = 'g_center';
                    break;
                /* 下居中水印 */
                case 8:
                    $gravity = 'g_south';
                    break;
                /* 右居中水印 */
                case 6:
                    $gravity = 'g_east';
                    break;
                /* 上居中水印 */
                case 2:
                    $gravity = 'g_north';
                    break;
                /* 左居中水印 */
                case 4:
                    $gravity = 'g_west';
                    break;
                default:
                    $gravity = 'g_se';
            }
            $dx = $watermarktext['skewx'] ? $watermarktext['skewx'] : 0;
            $dy = $watermarktext['skewy'] ? $watermarktext['skewy'] : 0;
            if ($watermarktype != 'text') {
               // $imgurl = $this->base64url_encode(\IO::getStream($_G['setting']['aliosswaterimg']));
                $imgurl = $this->base64url_encode('static/waterimg/water.png');
                return '/watermark,image_' . $imgurl . ',t_90,' . $gravity . ',x_' . $dx . ',y_' . $dy;
            } else {
                $text = $watermarktext['textfull'];
                $text = $this->base64url_encode($text);
                $colorarr = explode(',', $watermarktext['color']);
                if (!empty($colorarr)) {
                    $color = 'rgb(' . $colorarr[0] . ',' . $colorarr[1] . ',' . $colorarr[2] . ')';
                    $color = $this->RGBToHex($color);
                    $dissolve = isset($colorarr[3]) ? $colorarr[3] : 90;
                } else {
                    $color = $watermarktext['color'];
                }
                //$color = base64_encode($color);
                $fontsize = $watermarktext['size'];
                return '/watermark,type_d3F5LXplbmhlaQ,size_' . $fontsize .
                    ',text_' . $text . ',color_' . $color . ',t_90,' . $gravity . ',x_' . $dx . ',y_' . $dy;
            }
            
        }
        function RGBToHex($rgb){
            $regexp = "/^rgb\(([0-9]{0,3})\,\s*([0-9]{0,3})\,\s*([0-9]{0,3})\)/";
            $re = preg_match($regexp, $rgb, $match);
            $re = array_shift($match);
            $hexColor = "#";
            $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
            for ($i = 0; $i < 3; $i++) {
                $r = null;
                $c = $match[$i];
                $hexAr = array();
                while ($c > 16) {
                    $r = $c % 16;
                    $c = ($c / 16) >> 0;
                    array_push($hexAr, $hex[$r]);
                }
                array_push($hexAr, $hex[$c]);
                $ret = array_reverse($hexAr);
                $item = implode('', $ret);
                $item = str_pad($item, 2, '0', STR_PAD_LEFT);
                $hexColor .= $item;
            }
            return $hexColor;
        }
        //创建缩略图
        public function createThumbByOriginal($path, $width = 0, $height = 0, $thumbtype = 1, $original = 0, $extraparams = array(),$filesize = 0)
        {
            global $_G;
            $filedirpathinfo = pathinfo($path);
            $filedirextensionarr = explode('?', $filedirpathinfo['extension']);
            $filedirextension = strtolower($filedirextensionarr[0]);
            $arr = self::parsePath($path);
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
    
            $cachepath = str_replace('//', '/', str_replace(':', '/', $arr['object']));
    
            $cachethumbpath = ($cachepath) . '.' . $width . '_' . $height . '_' . $thumbtype . $extraflag . '.jpeg';
            if($filesize > 1024*1024*20){
                $url =  io_dzz::createThumbByOriginal($path, $width, $height ,$thumbtype, $original , $extraparams,$filesize);
            }
            else{
                $aliossimageexts = array('jpeg','jpg','bmp','tiff', 'png', 'gif', 'webp');
                if(!in_array($filedirextension,$aliossimageexts)){
                    return false;
                }
                if (!$original) {
                    $params = $this->parseThumbParams($path, $width, $height, $thumbtype);
                }
                $waterparams = '';
                //水印参数
                $waterparams = $this->parseWaterParams($path, $width, $height);
                if (!$params && !$waterparams) {
                    $style = '';
                } else {
                    $style = ($params?$params:'image') . (($waterparams) ?  $waterparams : '');
                }
    
                if (!$style) return $path;
               
                $alithumdata = $this->getThumbWaterUrl($path, $style, $cachethumbpath);
                if (isset($alithumdata['error'])) {
                    return false;
                } else {
                    return $arr['bz'] . $arr['bucket'] . '/' . $alithumdata;
                }
            }
            if(!$url) return false;
            $cloudpath = $arr['bz'] . $arr['bucket'] . '/' . $cachethumbpath;
            $return = $this->moveThumbFile($cloudpath,$url);
            if(isset($return['error'])){
                return false;
            }else{
                return $cloudpath;
            }
          
        }
        
        public function  getFileSzie($path){
            $arr = self::parsePath($path);
            $oss = self::init($path);
            try {
                // 获取文件的全部元信息。
                $objectMeta = $oss->getObjectMeta($arr['bucket'], $arr['object']);
                return $objectMeta['content-length'];
            } catch (OssException $e) {
                return array('error'=>$e->getMessage());
            }
    }
        //获取文件流；
        //$path: 路径
        function getStream($path,$checkexist=false)
        {
         	if($checkexist) {
                $filesize = $this->getFileSzie($path);
                if (isset($filesize['error']) || $filesize < 1) {
                    return array('error' => 'file not found');
                }
            }
            $arr = self::parsePath($path);
            $oss = self::init($path);
            if (is_array($oss) && $oss['error']) return $oss;
            if (empty($this->alc)) {
                try {
                    $this->alc = $this->getBucketALC($path);
                } catch (Exception $e) {
                
                }
            }
            //runlog('aaaaawwww',$this->alc);
            if ($this->alc == 'public-read') {
                $scheme = (strpos($this->hostname, 'https://') === 0) ? 'https://' : 'http://';
                $host = str_replace(array('http://', 'https://'), array('', ''), $this->hostname);
                $url = $scheme . $arr['bucket'] . '.' . $host . '/' . $arr['object'];
               
            } else {
                $url=$oss->signUrl($arr['bucket'], $arr['object'], 60 * 60 * 2);
            }
	
			return $url;
        }
        
        public function parsePath($path)
        {
            $arr = explode(':', $path);
            $bz = $arr[0] . ':' . $arr[1] . ':';
            $arr1 = explode('/', $arr[2]);
            //if(count($arr1)>1){
            $bucket = $arr1[0];
            unset($arr1[0]);
            //}else $bucket='';
            //if(!$bucket) return array('error'=>'bucket不能为空');
            $object = implode('/', $arr1);
            return array('bucket' => $bucket, 'object' => $object, 'bz' => $bz);
        }
        //重写文件内容
        //@param number $path  文件的路径
        //@param string $data  文件的新内容
        public function setFileContent($path, $data)
        {
            $patharr = explode('/', $path);
            $filename = $patharr[count($patharr) - 1];
            unset($patharr[count($patharr) - 1]);
            $path1 = implode('/', $patharr) . '/';
            $icoarr = self::upload_by_content($data, $path1, $filename, 0, 'overwrite');
            if ($icoarr['type'] == 'image') {
                self::deleteThumb($path);
                $icoarr['img'] .= '&t=' . TIMESTAMP;
            }
            return $icoarr;
        }
        
        /**
         * 上传文件
         * @param string $fileContent 文件内容字符串
         * @param string $path 上传文件的目标保存路径
         * @param string $fileName 文件名
         * @param string $newFileName 新文件名
         * @param string $ondup overwrite：目前只支持覆盖。
         * @return string
         */
        function upload_by_content($fileContent, $path, $filename, $ondup = 'overwrite')
        {
            global $_G;
            $path .= $filename;
            $arr = self::parsePath($path);
            try {
                $oss = self::init($path);
                if (is_array($oss) && $oss['error']) return $oss;
                $upload_fileOptions = array(
                    'content' => $fileContent,
                    'length' => strlen($fileContent)
                );
                try {
                    $response = $oss->putObject($arr['bucket'], $arr['object'], $fileContent);
                } catch (OssException $e) {
                    return array('error' => $e->getMessage());
                }
                if (md5($fileContent) != strtolower(trim($response->header['etag'], '"'))) { //验证上传是否完整
                    return array('error' => lang('upload_file_incomplete'));
                }
                $meta = array(
                    'Key' => $arr['object'],
                    'Size' => strlen($fileContent),
                    'LastModified' => $response->header['date'],
                );
                
                $icoarr = self::_formatMeta($meta, $arr);
                return $icoarr;
            } catch (Exception $e) {
                return array('error' => $e->getMessage());
            }
        }
        
        /**
         * 获取当前用户空间配额信息
         * @return string
         */
        public function getQuota($bz)
        {
            return 0;
        }
        
        /**
         * 获取指定文件夹下的文件列表
         * @param string $path 文件路径
         * @param string $by 排序字段，缺省根据文件类型排序，time（修改时间），name（文件名），size（大小，注意目录无大小）
         * @param string $order asc或desc，缺省采用降序排序
         * @param string $limit 返回条目控制，参数格式为：n1-n2。返回结果集的[n1, n2)之间的条目，缺省返回所有条目。n1从0开始。
         * @param string $force 读取缓存，大于0：忽略缓存，直接调用api数据，常用于强制刷新时。
         * @return icosdatas
         */
        function listFiles($path, $by = 'time', $marker = '', $limit = 100, $force = 0)
        {
            global $_G, $_GET, $documentexts, $imageexts;
            $arr = self::parsePath($path);
            
            $icosdata = array();
            $oss = self::init($path, 1);
            if (is_array($oss) && $oss['error']) return $oss;
            if (!$arr['bucket']) {
                $response = $oss->listBuckets();
                $bucket = $response->getBody();
                $icosdata = array();
                foreach ($bucket['ListAllMyBucketsResult']['Buckets']['Bucket'] as $value) {
                    $arr['bucket'] = $value['Name'];
                    $value['Key'] = '';
                    $value['LastModified'] = $value['CreationDate'];
                    $value['isdir'] = true;
                    $value['nextMarker'] = '';
                    $value['IsTruncated'] = false;
                    $icoarr = self::_formatMeta($value, $arr);
                    $icosdata[$icoarr['icoid']] = $icoarr;
                }
                //print_r($arr);exit($path);
                //print_r($folderarr);exit('ddddd');
            } else {
                $response = $oss->listObject($arr['bucket'], array('prefix' => $arr['object'], 'marker' => $marker, 'max-keys' => $limit));
                $data = $response->getBody();
                if ($data['ListBucketResult']['Contents']) $icos = $data['ListBucketResult']['Contents'];
                if ($data['ListBucketResult']['CommonPrefixes']) $folders = $data['ListBucketResult']['CommonPrefixes'];
                $value = array();
                
                foreach ($icos as $key => $value) {
                    if (is_array($value)) {
                        $icoarr = self::_formatMeta($value, $arr);
                        $icosdata[$icoarr['icoid']] = $icoarr;
                    } else {
                        $icoarr = self::_formatMeta($icos, $arr);
                        $icosdata[$icoarr['icoid']] = $icoarr;
                        break;
                    }
                }
                $value = array();
                foreach ($folders as $key => $value) {
                    
                    if (is_array($value)) {
                        $value['isdir'] = true;
                        $value['Key'] = $value['Prefix'];
                        $value['LastModified'] = '';
                        $icoarr = self::_formatMeta($value, $arr);
                        $icosdata[$icoarr['icoid']] = $icoarr;
                    } else {
                        $folders['isdir'] = true;
                        $folders['Key'] = $folders['Prefix'];
                        $icoarr = self::_formatMeta($folders, $arr);
                        $icosdata[$icoarr['icoid']] = $icoarr;
                        break;
                    }
                }
                
                $value = array();
                $value['isdir'] = true;
                $value['Key'] = $data['ListBucketResult']['Prefix'] ? $data['ListBucketResult']['Prefix'] : '';
                $value['nextMarker'] = $data['ListBucketResult']['NextMarker'];
                $value['IsTruncated'] = $data['ListBucketResult']['IsTruncated'];
                
                $icoarr = self::_formatMeta($value, $arr);
                if ($icosdata[$icoarr['icoid']]) {
                    $icosdata[$icoarr['icoid']]['nextMarker'] = $icoarr['nextMarker'];
                    $icosdata[$icoarr['icoid']]['IsTruncated'] = $icoarr['IsTruncated'];
                } else {
                    $icosdata[$icoarr['icoid']] = $icoarr;
                }
            }
            
            /*print_r($data);
            print_r($icosdata);
            exit('dfdsf');*/
            return $icosdata;
        }
        
        /**
         * 获取指定文件夹下的文件列表
         * @param string $path 文件路径
         * @param string $by 排序字段，缺省根据文件类型排序，time（修改时间），name（文件名），size（大小，注意目录无大小）
         * @param string $order asc或desc，缺省采用降序排序
         * @param string $limit 返回条目控制，参数格式为：n1-n2。返回结果集的[n1, n2)之间的条目，缺省返回所有条目。n1从0开始。
         * @param string $force 读取缓存，大于0：忽略缓存，直接调用api数据，常用于强制刷新时。
         * @return icosdatas
         */
        function listFilesAll(&$oss, $path, $limit = '1000', $marker = '', $icosdata = array())
        {
            //static $icosdata=array();
            $arr = self::parsePath($path);
            $response = $oss->listObject($arr['bucket'], array('prefix' => $arr['object'], 'marker' => $marker, 'max-keys' => $limit));
            $data = $response->getBody();
            if ($data['ListBucketResult']['Contents']) $icos = $data['ListBucketResult']['Contents'];
            if ($data['ListBucketResult']['CommonPrefixes']) $folders = $data['ListBucketResult']['CommonPrefixes'];
            $value = array();
            
            foreach ($icos as $key => $value) {
                if (is_array($value)) {
                    $icoarr = self::_formatMeta($value, $arr);
                    $icosdata[$icoarr['icoid']] = $icoarr;
                } else {
                    $icoarr = self::_formatMeta($icos, $arr);
                    $icosdata[$icoarr['icoid']] = $icoarr;
                    break;
                }
            }
            $value = array();
            foreach ($folders as $key => $value) {
                
                if (is_array($value)) {
                    $value['isdir'] = true;
                    $value['Key'] = $value['Prefix'];
                    $value['LastModified'] = '';
                    $icoarr = self::_formatMeta($value, $arr);
                    $icosdata[$icoarr['icoid']] = $icoarr;
                } else {
                    $folders['isdir'] = true;
                    $folders['Key'] = $folders['Prefix'];
                    $icoarr = self::_formatMeta($folders, $arr);
                    $icosdata[$icoarr['icoid']] = $icoarr;
                    break;
                }
            }
            
            $value = array();
            $value['isdir'] = true;
            $value['Key'] = $data['ListBucketResult']['Prefix'] ? $data['ListBucketResult']['Prefix'] : '';
            $value['nextMarker'] = $data['ListBucketResult']['NextMarker'];
            $value['IsTruncated'] = $data['ListBucketResult']['IsTruncated'];
            
            $icoarr = self::_formatMeta($value, $arr);
            //print_r($icoarr);print_r($data);exit('ddddd');
            if ($icosdata[$icoarr['icoid']]) {
                $icosdata[$icoarr['icoid']]['nextMarker'] = $icoarr['nextMarker'];
                $icosdata[$icoarr['icoid']]['IsTruncated'] = $icoarr['IsTruncated'];
            } else {
                $icosdata[$icoarr['icoid']] = $icoarr;
            }
            
            //exit($data['ListBucketResult']['IsTruncated']);
            if ($data['ListBucketResult']['IsTruncated'] == 'true') {
                $icosdata = self::listFilesAll($oss, $path, 1000, $data['ListBucketResult']['nextMarker'], $icosdata);
                //self::getFolderObjects($path,1000,$data['ListBucketResult']['nextMarker']);
            }
            return $icosdata;
        }
        
        /*
         *获取文件的meta数据
         *返回标准的icosdata
         *$force>0 强制刷新，不读取缓存数据；
        */
        function getMeta($path, $force = 0)
        {
            global $_G, $_GET, $documentexts, $imageexts;
            $arr = self::parsePath($path);
            
            $icosdata = array();
            $oss = self::init($path, 1);
            if (is_array($oss) && $oss['error']) return $oss;
            if (empty($arr['object']) || empty($arr['bucket'])) {
                $meta = array(
                    'Key' => '',
                    'Size' => 0,
                    'LastModified' => '',
                    'isdir' => true
                );
            } else {
                try {
                    $response = $oss->getObjectMeta($arr['bucket'], $arr['object'], array('Content-Type' => 'application/octet-stream'));
                } catch (Exception $e) {
                    return array('error' => $e->getMessage());
                }
                
                $return = $response->header;
                if (!$return['content-length']) {
                    $headers = get_headers(self::getStream($path), 1);
                    $return['content-length'] = $headers['Content-Length'];
                }
                $meta = array(
                    'Key' => str_replace($arr['bz'] . $arr['bucket'] . '/', '', $path),
                    'Size' => $return['content-length'],
                    'LastModified' => $return['last-modified'],
                );
            }
            $icosdata = self::_formatMeta($meta, $arr);
            return $icosdata;
        }
        
        //将api获取的meta数据转化为icodata
        function _formatMeta($meta, $arr)
        {
            global $_G, $documentexts, $imageexts;
            $icosdata = array();
            ///print_r($meta);print_r($arr);exit($this->bucket);
            
            
            if (strrpos($meta['Key'], '/') == (strlen($meta['Key']) - 1)) $meta['isdir'] = true;
            
            if ($meta['isdir']) {
                if (!$meta['Key']) {
                    if ($this->bucket) {
                        $name = $this->bucket;
                        $pfid = 0;
                        $pf = '';
                        $flag = self::BZ;
                    } elseif ($arr['bucket']) {
                        $name = $arr['bucket'];
                        $pfid = md5($arr['bz']);
                        $pf = '';
                        $flag = self::BZ;
                    } else {
                        $name = $this->_rootname;
                        $pfid = 0;
                        $pf = '';
                        $flag = self::BZ;
                    }
                    if ($arr['bucket']) $arr['bucket'] .= '/';
                } else {
                    if ($arr['bucket']) $arr['bucket'] .= '/';
                    $namearr = explode('/', $meta['Key']);
                    $name = $namearr[count($namearr) - 2];
                    $pf = '';
                    for ($i = 0; $i < (count($namearr) - 2); $i++) {
                        $pf .= $namearr[$i] . '/';
                    }
                    $pf = $arr['bucket'] . $pf;
                    $pfid = md5($arr['bz'] . $pf);
                    $flag = '';
                }
                //print_r($arr);
                //print_r($namearr);
                
                $icoarr = array(
                    'icoid' => md5(($arr['bz'] . $arr['bucket'] . $meta['Key'])),
                    'path' => $arr['bz'] . $arr['bucket'] . $meta['Key'],
                    'dpath' => dzzencode($arr['bz'] . $arr['bucket'] . $meta['Key']),
                    'bz' => ($arr['bz']),
                    'gid' => 0,
                    'name' => $name,
                    'username' => $_G['username'],
                    'uid' => $_G['uid'],
                    'oid' => md5($arr['bz'] . $arr['bucket'] . $meta['Key']),
                    'img' => 'dzz/images/default/system/folder.png',
                    'type' => 'folder',
                    'ext' => '',
                    'pfid' => $pfid,
                    'ppath' => $arr['bz'] . $pf,
                    'size' => 0,
                    'dateline' => strtotime($meta['LastModified']),
                    'flag' => $flag,
                    'nextMarker' => $meta['nextMarker'],
                    'IsTruncated' => $meta['IsTruncated'],
                );
                
                $icoarr['fsize'] = formatsize($icoarr['size']);
                $icoarr['ftype'] = getFileTypeName($icoarr['type'], $icoarr['ext']);
                $icoarr['fdateline'] = dgmdate($icoarr['dateline']);
                $icosdata = $icoarr;
                /*print_r($icosdata);
                exit($meta['Key']);*/
            } else {
                if ($arr['bucket']) $arr['bucket'] .= '/';
                $namearr = explode('/', $meta['Key']);
                $name = $namearr[count($namearr) - 1];
                $pf = '';
                for ($i = 0; $i < count($namearr) - 1; $i++) {
                    $pf .= $namearr[$i] . '/';
                }
                $ext = strtoupper(substr(strrchr($meta['Key'], '.'), 1));
                if (in_array($ext, $imageexts)) $type = 'image';
                elseif (in_array($ext, $documentexts)) $type = 'document';
                else $type = 'attach';
                if ($type == 'image') {
                    $img = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=thumbnail&size=small&path=' . dzzencode($arr['bz'] . $arr['bucket'] . $meta['Key']);
                    $url = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=thumbnail&size=large&path=' . dzzencode($arr['bz'] . $arr['bucket'] . $meta['Key']);
                } else {
                    $img = geticonfromext($ext, $type);
                    $url = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=getStream&path=' . dzzencode($arr['bz'] . $arr['bucket'] . $meta['Key']);;
                }
                
                $icoarr = array(
                    'icoid' => md5(($arr['bz'] . $arr['bucket'] . $meta['Key'])),
                    'path' => ($arr['bz'] . $arr['bucket'] . $meta['Key']),
                    'dpath' => dzzencode($arr['bz'] . $arr['bucket'] . $meta['Key']),
                    'bz' => ($arr['bz']),
                    'gid' => 0,
                    'name' => $name,
                    'username' => $_G['username'],
                    'uid' => $_G['uid'],
                    'oid' => md5(($arr['bz'] . $arr['bucket'] . $meta['Key'])),
                    'img' => $img,
                    'url' => $url,
                    'type' => $type,
                    'ext' => strtolower($ext),
                    'pfid' => md5($arr['bz'] . $arr['bucket'] . $pf),
                    'ppath' => $arr['bz'] . $arr['bucket'] . $pf,
                    'size' => $meta['Size'],
                    'dateline' => strtotime($meta['LastModified']),
                    'flag' => ''
                );
                $icoarr['fsize'] = formatsize($icoarr['size']);
                $icoarr['ftype'] = getFileTypeName($icoarr['type'], $icoarr['ext']);
                $icoarr['fdateline'] = dgmdate($icoarr['dateline']);
                $icosdata = $icoarr;
            }
            
            return $icosdata;
        }
        
        //根据路径获取目录树的数据；
        public function getFolderDatasByPath($path)
        {
            $bzarr = self::parsePath($path);
            $oss = self::init($path, 1);
            $spath = $bzarr['object'];
            
            if (!$this->bucket && $bzarr['bucket']) {
                $spath = $bzarr['bucket'] . '/' . $spath;
                $bzarr['bucket'] = '';
            } else {
                $bzarr['bucket'] .= '/';
            }
            $spath = trim($spath, '/');
            $patharr = explode('/', $spath);
            $folderarr = array();
            $path1 = $bzarr['bz'] . $bzarr['bucket'];
            if ($arr = self::getMeta($path1)) {
                if (!isset($arr['error'])) {
                    $folder = self::getFolderByIcosdata($arr);
                    $folderarr[$folder['fid']] = $folder;
                }
            }
            for ($i = 0; $i < count($patharr); $i++) {
                $path1 = $bzarr['bz'] . $bzarr['bucket'];
                for ($j = 0; $j <= $i; $j++) {
                    $path1 .= $patharr[$j] . '/';
                }
                if ($arr = self::getMeta($path1)) {
                    if (isset($arr['error'])) continue;
                    $folder = self::getFolderByIcosdata($arr);
                    $folderarr[$folder['fid']] = $folder;
                }
            }
            return $folderarr;
        }
        
        //通过icosdata获取folderdata数据
        function getFolderByIcosdata($icosdata)
        {
            global $_GET;
            $folder = array();
            //通过path判断是否为bucket
            $path = $icosdata['path'];
            $arr = self::parsePath($path);
            if (!$arr['bucket']) { //根目录
                $fsperm = perm_FolderSPerm::flagPower('ALIOSS_root');
            } else {
                $fsperm = perm_FolderSPerm::flagPower('ALIOSS');
            }
            if ($icosdata['type'] == 'folder') {
                $folder = array('fid' => $icosdata['oid'],
                    'path' => $icosdata['path'],
                    'fname' => $icosdata['name'],
                    'uid' => $icosdata['uid'],
                    'pfid' => $icosdata['pfid'],
                    'ppath' => $icosdata['ppath'],
                    'iconview' => $_GET['iconview'] ? intval($_GET['iconview']) : 0,
                    'disp' => $_GET['disp'] ? intval($_GET['disp']) : 0,
                    'perm' => $this->perm,
                    'hash' => $icosdata['hash'],
                    'bz' => $icosdata['bz'],
                    'gid' => $icosdata['gid'],
                    'fsperm' => $fsperm,
                    'icon' => $icosdata['flag'] ? ('dzz/images/default/system/' . $icosdata['flag'] . '.png') : '',
                    'nextMarker' => $icosdata['nextMarker'],
                    'IsTruncated' => $icosdata['IsTruncated'],
                );
                //print_r($folder);
            }
            return $folder;
        }
        
        //获得文件内容；
        function getFileContent($path)
        {
            $arr = self::parsePath($path);
            $url = self::getFileUri($path);
            return file_get_contents($url);
        }
        
        //打包下载文件
        public function zipdownload($paths, $filename)
        {
            global $_G;
            $paths = (array)$paths;
            set_time_limit(0);
            
            if (empty($filename)) {
                $meta = self::getMeta($paths[0]);
                $filename = $meta['name'] . (count($paths) > 1 ? lang('wait') : '');
            }
            $filename = (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($filename) : $filename);
            include_once libfile('class/ZipStream');
            $zip = new ZipStream($filename . ".zip");
            $data = self::getFolderInfo($paths, '', $zip);
            /*if($data['error']){
                topshowmessage($data['error']);
                exit();
            }*/
            /*foreach($data as $value){
                 $zip->addLargeFile(fopen($value['url'],'rb'), $value['position'], $value['dateline']);
            }*/
            $zip->finalize();
        }
        
        public function getFolderInfo($paths, $position = '', $zip)
        {
            static $data = array();
            try {
                foreach ($paths as $path) {
                    $arr = IO::parsePath($path);
                    $oss = self::init($path, 1);
                    if (is_array($oss) && $oss['error']) return $oss;
                    $meta = self::getMeta($path);
                    switch ($meta['type']) {
                        case 'folder':
                            $lposition = $position . $meta['name'] . '/';
                            $contents = self::listFilesAll($oss, $path);
                            $arr = array();
                            foreach ($contents as $key => $value) {
                                if ($value['path'] != $path) {
                                    $arr[] = $value['path'];
                                }
                            }
                            if ($arr) self::getFolderInfo($arr, $lposition, $zip);
                            break;
                        default:
                            $meta['url'] = self::getStream($meta['path']);
                            $meta['position'] = $position . $meta['name'];
                            //$data[$meta['icoid']]=$meta;
                            $zip->addLargeFile(@fopen($meta['url'], 'rb'), $meta['position'], $meta['dateline']);
                    }
                }
                
            } catch (Exception $e) {
                //var_dump($e);
                $data['error'] = $e->getMessage();
                return $data;
            }
            return $data;
        }
        
        //下载文件
        public function download($paths, $filename)
        {
            global $_G;
            $paths = (array)$paths;
            if (count($paths) > 1) {
                self::zipdownload($paths, $filename);
                exit();
            } else {
                $path = $paths[0];
            }
            $path = rawurldecode($path);
            
            //header("location: $url");
            try {
                $url = self::getStream($path);
                // Download the file
                $file = self::getMeta($path);
                if ($file['type'] == 'folder') {
                    self::zipdownload($path);
                    exit();
                }
                if (!$fp = @fopen($url, 'rb')) {
                    topshowmessage(lang('file_not_exist1'));
                }
                
                
                $chunk = 10 * 1024 * 1024;
                //$file['data'] = self::getFileContent($path);
                //if($file['data']['error']) topshowmessage($file['data']['error']);
                $file['name'] = '"' . (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($file['name']) : $file['name']) . '"';
                $d = new FileDownload();
                $d->download($url, $file['name'], $file['size'], $file['dateline'], true);
                exit();
                dheader('Date: ' . gmdate('D, d M Y H:i:s', $file['dateline']) . ' GMT');
                dheader('Last-Modified: ' . gmdate('D, d M Y H:i:s', $file['dateline']) . ' GMT');
                dheader('Content-Encoding: none');
                dheader('Content-Disposition: attachment; filename=' . $file['name']);
                dheader('Content-Type: application/octet-stream');
                dheader('Content-Length: ' . $file['size']);
                @ob_end_clean();
                if (getglobal('gzipcompress')) @ob_start('ob_gzhandler');
                while (!feof($fp)) {
                    echo fread($fp, $chunk);
                    @ob_flush();  // flush output
                    @flush();
                }
                fclose($fp);
                exit();
            } catch (Exception $e) {
                // The file wasn't found at the specified path/revision
                //echo 'The file was not found at the specified path/revision';
                topshowmessage($e->getMessage());
            }
        }
        
        
        //获取目录的所有下级和它自己的object
        public function getFolderObjects(&$oss, $path, $limit = '1000', $marker = '')
        {
            static $objects = array();
            $arr = self::parsePath($path);
            //echo( $path.'---------');
            
            $response = $oss->listObject($arr['bucket'], array('prefix' => $arr['object'], 'marker' => $marker, 'max-keys' => $limit, 'delimiter' => ''));
            $data = $response->getBody();
            if ($data['ListBucketResult']['Contents']) $icos = $data['ListBucketResult']['Contents'];
            if ($data['ListBucketResult']['CommonPrefixes']) $folders = $data['ListBucketResult']['CommonPrefixes'];
            error_reporting(E_ERROR);
            $value = array();
            foreach ($icos as $key => $value) {
                if (is_array($value)) {
                    $objects[] = $value['Key'];
                } else {
                    $objects[] = $icos['Key'];
                    break;
                }
            }
            $value = array();
            foreach ($folders as $key => $value) {
                if (is_array($value)) {
                    $objects[] = $value['Prefix'];
                } else {
                    $objects[] = $folders['Prefix'];
                    break;
                }
            }
            //exit('dddddd='.$data['ListBucketResult']['IsTruncated']);
            if ($data['ListBucketResult']['IsTruncated'] == 'true') {
                /*if($objs=self::getFolderObjects($oss,$path,1000,$data['ListBucketResult']['nextMarker'])){
                    $objects=array_merge($objects,$objs);
                }*/
                self::getFolderObjects($path, 1000, $data['ListBucketResult']['nextMarker']);
            }
            
            return $objects;
        }
        
        //删除原内容
        //$path: 删除的路径
        //$bz: 删除的api;
        //$data：可以删除的id数组（当剪切的时候，为了保证数据不丢失，目标位置添加成功后将此id添加到data数组，
        //删除时如果$data有数据，将会只删除id在$data中的数据；
        //如果删除的是目录或下级有目录，需要判断此目录内是否所有元素都在删除的id中，如果有未删除的元素，则此目录保留不会删除；
        //
        public function Delete($path, $force = false)
        {
            //global $dropbox;
            $arr = self::parsePath($path);
            try {
                $oss = self::init($path, $force);
                if (is_array($oss) && $oss['error']) return $oss;
                //判断删除的对象是否为文件夹
                if (strrpos($arr['object'], '/') == (strlen($arr['object']) - 1)) { //是文件夹
                    $objects = self::getFolderObjects($oss, $path);
                    $response = $oss->deleteObjects($arr['bucket'], $objects, array('quiet' => true));
                } else {
                    try {
                        $oss->deleteObject($arr['bucket'], $arr['object']);
                    } catch (OssException $e) {
                        return array('error' => $e->getMessage());
                    }
                    //$response = $oss->deleteObject($arr['bucket'],$arr['object']);
                }
                
                
                return array('icoid' => md5(($path)),
                    'name' => substr(strrchr($path, '/'), 1),
                );
            } catch (Exception $e) {
                return array('icoid' => md5($path), 'error' => $e->getMessage());
            }
        }
        //添加目录
        //$fname：目录路径;
        //$container：目标容器
        //$bz：api;
        public function CreateFolder($path, $fname)
        {
            global $_G;
            $arr = self::parsePath($path);
            //exit('createrfolder==='.$fname.'===='.$path1.'===='.$bz);
            //exit($path.$fname.'vvvvvvvvvvv');
            $return = array();
            try {
                $oss = self::init($path);
                if (is_array($oss) && $oss['error']) return $oss;
                try {
                    $response = $oss->putObject($arr['bucket'], $arr['object'] . $fname);
                } catch (OssException $e) {
                    return array('error' => $e->getMessage());
                }
                
                $meta = array('isdir' => true,
                    'Key' => $arr['object'] . $fname . '/',
                    'Size' => 0,
                    'LastModified' => $response->header['date'],
                );
                $icoarr = self::_formatMeta($meta, $arr);
                
                $folderarr = self::getFolderByIcosdata($icoarr);
                $return = array('folderarr' => $folderarr, 'icoarr' => $icoarr);
            } catch (Exception $e) {
                //var_dump($e);
                $return = array('error' => $e->getMessage());
            }
            return $return;
        }
        
        //获取不重复的目录名称
        public function getFolderName($name, $path)
        {
            static $i = 0;
            if (!$this->icosdatas) $this->icosdatas = self::listFiles($path);
            $names = array();
            foreach ($this->icosdatas as $value) {
                $names[] = $value['name'];
            }
            if (in_array($name, $names)) {
                $name = str_replace('(' . $i . ')', '', $name) . '(' . ($i + 1) . ')';
                $i += 1;
                return self::getFolderName($name, $path);
            } else {
                return $name;
            }
        }
        
        private function getCache($path)
        {
            $cachekey = 'ALIOSS_uploadID_' . md5($path);
            $cache = C::t('cache')->fetch($cachekey);
            return unserialize($cache['cachevalue']);
        }
        
        private function saveCache($path, $data)
        {
            global $_G;
            $cachekey = 'ALIOSS_uploadID_' . md5($path);
            C::t('cache')->insert(array(
                'cachekey' => $cachekey,
                'cachevalue' => serialize($data),
                'dateline' => $_G['timestamp'],
            ), false, true);
        }
        
        private function deleteCache($path)
        {
            $cachekey = 'ALIOSS_uploadID_' . md5($path);
            C::t('cache')->delete($cachekey);
        }
        
        private function getPartInfo($content_range)
        {
            $arr = array();
            if (!$content_range) {
                $arr['ispart'] = false;
                $arr['iscomplete'] = true;
            } elseif (is_array($content_range)) {
                $arr['ispart'] = true;
                $partsize = getglobal('setting/maxChunkSize');
                $arr['partnum'] = ceil(($content_range[2] + 1) / $partsize);
                if (($content_range[2] + 1) >= $content_range[3]) {
                    $arr['iscomplete'] = true;
                } else {
                    $arr['iscomplete'] = false;
                }
            } else {
                return false;
            }
            return $arr;
        }
        
        public function uploadStream($file, $filename, $path, $relativePath, $content_range)
        {
            $data = array();
            $arr = self::getPartInfo($content_range);
            //echo ($relativePath).'vvvvvvvv';
            //if($arr['partnum']>1) print_r($arr);
            if ($relativePath && ($arr['iscomplete'])) {
                $path1 = $path;
                $patharr = explode('/', $relativePath);
                //print_r($patharr);
                foreach ($patharr as $key => $value) {
                    if (!$value) {
                        continue;
                    }
                    //	echo $path1.'---'.$value.'------';
                    $re = self::CreateFolder($path1, $value);
                    if (isset($re['error'])) {
                        return $re;
                    } else {
                        if ($key == 0) {
                            $data['icoarr'][] = $re['icoarr'];
                            $data['folderarr'][] = $re['folderarr'];
                        }
                    }
                    $path1 = $path1 . $value . '/';
                }
            }
            $path .= $relativePath;
            if ($arr['ispart']) {
                if ($re1 = self::upload($file, $path, $filename, $arr)) {
                    if ($re1['error']) {
                        return $re1;
                    }
                    if ($arr['iscomplete']) {
                        if (empty($re1['error'])) {
                            $data['icoarr'][] = $re1;
                            return $data;
                        } else {
                            $data['error'] = $re1['error'];
                            return $data;
                        }
                    } else {
                        return true;
                    }
                }
            } else {
                $re1 = self::upload($file, $path, $filename);
                if (empty($re1['error'])) {
                    $data['icoarr'][] = $re1;
                    return $data;
                } else {
                    $data['error'] = $re1['error'];
                    return $data;
                }
            }
        }
        
        function upload($file, $path, $filename, $partinfo = array(), $ondup = 'overwrite')
        {
            global $_G;
            $path .= $filename;
            $arr = self::parsePath($path);
            
            try {
                $oss = self::init($path);
                if (is_array($oss) && $oss['error']) return $oss;
                $upload_fileOptions = array(
                    'fileUpload' => $file,
                );
                if ($partinfo['partnum']) {
                    $upload_fileOptions['partNumber'] = $partinfo['partnum'];
                    if ($partinfo['partnum'] == 1) {//第一个分块时 初始化分块上传得到$uploadID;并缓存住，留以后分块使用
                        //初始化分块
                        try {
                            $response = $oss->initiateMultipartUpload($arr['bucket'], $arr['object']);
                        } catch (OssException $e) {
                            return array('error' => $e->getMessage());
                        }
                        
                        $body = $response;
                        $upload_id = $body['InitiateMultipartUploadResult']['UploadId'];
                        
                        //上传分块
                        try {
                            $response = $oss->uploadPart($arr['bucket'], $arr['object'], $upload_id, $upload_fileOptions);
                        } catch (OssException $e) {
                            return array('error' => $e->getMessage());
                        }
                        
                        if (md5_file($file) != strtolower(trim($response->header['etag'], '"'))) { //验证上传是否完整
                            return array('error' => lang('upload_file_incomplete'));
                        }
                        
                        
                        $data = array();
                        $data['upload_id'] = $upload_id;
                        $data['filesize'] = filesize($file);
                        $data['partnum'] = 1;
                        $data['path'] = $path;
                        $data['parts'][$data['partnum']] = array('PartNumber' => $data['partnum'], 'ETag' => $response->header['etag']);
                        
                        self::saveCache($path, $data);
                    } else {
                        $cache = self::getCache($path);
                        $upload_id = $cache['upload_id'];
                        $cache['partnum'] += 1;
                        //上传分块
                        try {
                            $response = $oss->uploadPart($arr['bucket'], $arr['object'], $upload_id, $upload_fileOptions);
                        } catch (OssException $e) {
                            return array('error' => $e->getMessage());
                        }
                        
                        if (md5_file($file) != strtolower(trim($response->header['etag'], '"'))) { //验证上传是否完整
                            return array('error' => lang('upload_file_incomplete'));
                        }
                        
                        //print_r($cache);
                        $cache['filesize'] += filesize($file);
                        
                        $cache['parts'][$partinfo['partnum']] = array('PartNumber' => $cache['partnum'], 'ETag' => $response->header['etag']);
                        //print_r($cache);exit('dddd');
                        self::saveCache($path, $cache);
                    }
                    if ($partinfo['iscomplete']) {
                        $cache = self::getCache($path);
                        try {
                            $response = $oss->completeMultipartUpload($arr['bucket'], $arr['object'], $cache['upload_id'], $cache['parts']);
                            
                        } catch (OssException $e) {
                            return array('error' => $e->getMessage());
                        }
                        
                        
                        self::deleteCache($path);
                        $meta = array(
                            'Key' => $arr['object'],
                            'Size' => $cache['filesize'],
                            'LastModified' => $response->header['date'],
                        );
                        
                        $icoarr = self::_formatMeta($meta, $arr);
                        
                        return $icoarr;
                    } else {
                        return true;
                    }
                } else {
                    try {
                        $response = $oss->putObject($arr['bucket'], $arr['object'], $file);
                    } catch (OssException $e) {
                        return array('error' => $e->getMessage());
                    }
                    
                    
                    if (md5_file($file) != strtolower(trim($response->header['etag'], '"'))) { //验证上传是否完整
                        return array('error' => lang('upload_file_incomplete'));
                    }
                    $meta = array(
                        'Key' => $arr['object'],
                        'Size' => filesize($file),
                        'LastModified' => $response->header['date'],
                    );
                    
                    $icoarr = self::_formatMeta($meta, $arr);
                    
                    return $icoarr;
                }
            } catch (Exception $e) {
                return array('error' => $e->getMessage());
            }
            
        }
        
        public function rename($path, $name)
        {//重命名
            $arr = self::parsePath($path);
            //判断是否为目录
            $patharr = explode('/', $arr['object']);
            $arr['object1'] = '';
            if (strrpos($path, '/') == (strlen($path) - 1)) {//是目录
                return array('error' => lang('folder_not_allowed_rename'));
            } else {
                $ext = strtolower(substr(strrchr($arr['object'], '.'), 1));
                foreach ($patharr as $key => $value) {
                    if ($key >= count($patharr) - 1) break;
                    $arr['object1'] .= $value . '/';
                }
                $arr['object1'] .= $ext ? (preg_replace("/\.\w+$/i", '.' . $ext, $name)) : $name;
            }
            if ($arr['object'] != $arr['object1']) {
                $oss = self::init($path);
                if (is_array($oss) && $oss['error']) return $oss;
                try {
                    $response = $oss->copyObject($arr['bucket'], $arr['object'], $arr['bucket'], $arr['object1']);
                } catch (OssException $e) {
                    return array('error' => $e->getMessage());
                }
                
                $response = $oss->deleteObject($arr['bucket'], $arr['object']);
            }
            return self::getMeta($arr['bz'] . $arr['bucket'] . '/' . $arr['object1']);
        }
        
        /**
         * 移动文件到目标位置
         * @param string $opath 被移动的文件路径
         * @param string $path 目标位置（可能是同一api内或跨api，这两种情况分开处理）
         * @return icosdatas
         */
        public function CopyTo($opath, $path, $iscopy)
        {
            static $i = 0;
            $i++;
            $oarr = self::parsePath($opath);
            $arr = IO::parsePath($path);
            
            $oss = self::init($opath);
            if (is_array($oss) && $oss['error']) return $oss;
            try {
                $data = self::getMeta($opath);
                switch ($data['type']) {
                    case 'folder'://创建目录
                        //exit($arr['path'].'===='.$data['name']);
                        if ($re = IO::CreateFolder($path, $data['name'])) {
                            if (isset($re['error']) && intval($re['error_code']) != 31061) {
                                $data['success'] = $arr['error'];
                            } else {
                                
                                $data['newdata'] = $re['icoarr'];
                                $data['success'] = true;
                                //echo $opath.'<br>';
                                $contents = self::listFilesAll($oss, $opath);
                                $value = array();
                                foreach ($contents as $key => $value) {
                                    if ($value['path'] != $opath) {
                                        $data['contents'][$key] = self::CopyTo($value['path'], $re['folderarr']['path']);
                                    }
                                    $value = array();
                                }
                            }
                        } else {
                            $data['success'] = 'create folder failure';
                        }
                        
                        break;
                    
                    default:
                        if ($arr['bz'] == $oarr['bz']) {//同一个api时
                            $arr = self::parsePath($path . $data['name']);
                            try {
                                $response = $oss->copyObject($oarr['bucket'], $oarr['object'], $arr['bucket'], $arr['object']);
                            } catch (OssException $e) {
                                $data['success'] = $e->getMessage();
                            }
                            
                            $meta = array(
                                'Key' => $arr['object'],
                                'Size' => $data['size'],
                                'LastModified' => $response->header['date'],
                            );
                            $data['newdata'] = self::_formatMeta($meta, $arr);
                            
                            $data['success'] = true;
                        } else {
                            
                            if ($re = IO::multiUpload($opath, $path, $data['name'])) {
                                if ($re['error']) $data['success'] = $re['error'];
                                else {
                                    $data['newdata'] = $re;
                                    $data['success'] = true;
                                }
                            }
                        }
                        break;
                }
                //	}
            } catch (Exception $e) {
                //var_dump($e);
                $data['success'] = $e->getMessage();
                return $data;
            }
            return $data;
        }
        
        public function multiUpload($opath, $path, $filename, $attach = array(), $ondup = "newcopy")
        {
            global $_G;
            
            $partsize = 1024 * 1024 * 5; //分块大小5M
            if ($attach) {
                $data = $attach;
                $data['size'] = $attach['filesize'];
            } else {
                $data = IO::getMeta($opath);
                if ($data['error']) return $data;
            }
            
            $size = $data['size'];
            if (is_array($filepath = IO::getStream($opath))) {
                return array('error' => $filepath['error']);
            }
            return $this->uploadbymulti($filepath, $path . $filename, $size);
            /*if ($size < $partsize) {
                //获取文件内容
                $fileContent = '';
                if (!$handle = fopen($filepath, 'rb')) {
                    return array('error' => lang('open_file_error'));
                }
                while (!feof($handle)) {
                    $fileContent .= fread($handle, 8192);
                    //if(strlen($fileContent)==0) return array('error'=>'文件不存在');
                }
                fclose($handle);
                //exit('upload');
                return self::upload_by_content($fileContent, $path, $filename);
            }
            else { //分片上传
    
                $partinfo = array('ispart' => true, 'partnum' => 0, 'iscomplete' => false);
                if (!$handle = fopen($filepath, 'rb')) {
                    return array('error' => lang('open_file_error'));
                }
    
                $cachefile = $_G['setting']['attachdir'] . './cache/' . md5($opath) . '.dzz';
                $fileContent = '';
                while (!feof($handle)) {
                    $fileContent .= fread($handle, 8192);
                    if (strlen($fileContent) == 0) return array('error' => lang('file_not_exist1'));
                    if (strlen($fileContent) >= $partsize) {
                        if ($partinfo['partnum'] * $partsize + strlen($fileContent) >= $size) $partinfo['iscomplete'] = true;
                        $partinfo['partnum'] += 1;
                        file_put_contents($cachefile, $fileContent);
                        $re = self::upload($cachefile, $path, $filename, $partinfo);
                        if ($re['error']) return $re;
                        if ($partinfo['iscomplete']) {
                            @unlink($cachefile);
                            return $re;
                        }
                        $fileContent = '';
                    }
                }
                fclose($handle);
                if (!empty($fileContent)) {
                    $partinfo['partnum'] += 1;
                    $partinfo['iscomplete'] = true;
                    file_put_contents($cachefile, $fileContent);
                    $re = self::upload($cachefile, $path, $filename, $partinfo);
                    if ($re['error']) return $re;
                    if ($partinfo['iscomplete']) {
                        @unlink($cachefile);
                        return $re;
                    }
                }
            }*/
        }
        
        public function getPath($ext, $dir = 'dzz')
        {
            global $_G;
            if ($ext && in_array(trim($ext, '.'), $_G['setting']['unRunExts'])) {
                $ext = 'dzz';
            }
            $subdir = $subdir1 = $subdir2 = '';
            $subdir1 = date('Ym');
            $subdir2 = date('d');
            $subdir = $subdir1 . '/' . $subdir2 . '/';
            $target1 = $dir . '/' . $subdir . 'index.html';
            $target = $dir . '/' . $subdir;
            return ($ext) ? $target . date('His') . '' . strtolower(random(16)) . '.' . $ext : $target . date('His') . '' . strtolower(random(16));
        }
        
        public function path_info($filepath)
        {
            $path_parts = array();
            $path_parts ['dirname'] = rtrim(substr($filepath, 0, strrpos($filepath, '/')), "/") . "/";
            $path_parts ['basename'] = ltrim(substr($filepath, strrpos($filepath, '/')), "/");
            $path_parts ['extension'] = substr(strrchr($filepath, '.'), 1);
            $path_parts ['filename'] = ltrim(substr($path_parts ['basename'], 0, strrpos($path_parts ['basename'], '.')), "/");
            return $path_parts;
        }
        
        //移动临时上传区域文件到上传目标位置
        public function movetmpdataToattachment($path, $data)
        {
            global $_G;
            //获取上传的目标目录
            $pathinfo = $this->path_info($data['Key']);
            $dirname = $pathinfo['dirname'];
            $dirarr = explode('/', $dirname);
            $pfid = $dirarr[1];
            unset($dirarr[0]);
            unset($dirarr[1]);
            //如果有不存在的目录则创建之
            $relativepath = !empty($dirarr) ? implode('/', $dirarr) : '';
            $datas = array();
            $datas['pfid'] = intval($pfid);
            if ($relativepath) {
                $datas = IO::createFolderByPath($relativepath, $pfid);
                $pfid = $datas['pfid'];
            }
            if (!isset($data['md5'])) {
                $file = self::getStream($path);
                $data['md5'] = md5_file($file);
            }
            $filename = $pathinfo['basename'];
            $pathinfo = pathinfo($path);
            $ext = strtolower($pathinfo['extension']);
            $filepath = self::getPath($ext);
            $attachment = array(
                'filesize' => $data['size'],
                'attachment' => $filepath,
                'filetype' => $ext,
                'filename' => $filename,
                'remote' => $data['remoteid'],
                'copys' => 0,
                'md5' => $data['md5'],
                'unrun' => 0,
                'dateline' => $_G['timestamp'],
            );
            //获取文件路由对应位置
            $routeremoteid = C::t('local_router')->getRemoteId($attachment);
            //如果路由位置和当前文件位置不同,则移动临时区文件到路由所指定位置
            if ($routeremoteid && $routeremoteid != $data['remoteid']) {
                $localdata = C::t('local_storage')->fetch($routeremoteid);
                $did = $localdata['did'];
                $connectdata = C::t('connect_storage')->fetch($did);
                $topath = $localdata['bz'] . ':' . $did . ':' . $connectdata['bucket'] . '/' . $filepath;
                $arr = self::parsePath($path);
                $attachment['attachment'] = $arr['object'];
                //$attachment['attachment'] = $routeremoteid;
                $movereturn = IO::MoveToSpace($topath, $attachment);
                //移除临时区文件
                $oss = self::init($path);
                if ($movereturn && !$movereturn['error']) {
                    $attachment['attachment'] = $filepath;
                    $attachment['remote'] = $routeremoteid;
                    $attachment['aid'] = C::t('attachment')->insert($attachment, 1);
                    try {
                        $response = $oss->deleteObject($arr['bucket'], $arr['object']);
                    } catch (\Exception $e) {
                        runlog('aliossupload', $connectdata['bucket'] . '/' . $filepath . ' delete tmp file failed');
                        // return array('error' => $e->getMessage());
                    }
                } else {
                    try {
                        $response = $oss->deleteObject($arr['bucket'], $arr['object']);
                    } catch (\Exception $e) {
                        runlog('aliossupload', $connectdata['bucket'] . '/' . $filepath . ' delete tmp file failed');
                        // return array('error' => $e->getMessage());
                    }
                    //如果移动文件失败，返回错误
                    return array('error' => $movereturn['error']);
                }
            } else {
                $attachment['aid'] = C::t('attachment')->insert($attachment, 1);
                
                $topath = $data['bz'] . ':' . $data['did'] . ':' . $data['Bucket'] . '/' . $attachment['attachment'];
                $arr = self::parsePath($path);
                $oarr = self::parsePath($topath);
                if ($path != $topath) {
                    $oss = self::init($path);
                    if (is_array($oss) && $oss['error']) return $oss;
                        try {
                            $result = $oss->copyObject($arr['bucket'], $arr['object'], $oarr['bucket'], $oarr['object']);
                        } catch (\Exception $e) {
                            return array('error' => $e->getMessage());
                        }
                        
                    try {
                        $response = $oss->deleteObject($arr['bucket'], $arr['object']);
                    } catch (\Exception $e) {
                        return array('error' => $e->getMessage());
                    }
                }
            }
            $datas['icoarr'][] = self::uploadToattachment($attachment, $pfid);
            return $datas;
        }
        
        //分片拷贝
        public function copyMultiData($path, $topath, $options = array())
        {
            // 根据实际情况设置分片大小。
            $part_size = 100 * 1024 * 1024;
            $arr = self::parsePath($path);
            $oarr = self::parsePath($topath);
            $oss = self::init($path);
            if ($arr['bz'] == $oarr['bz'] && $arr['bucket'] == $oarr['bucket']){
                try{
                    $oss->copyObject($arr['bucket'] , $arr['object'],$oarr['bucket'] , $oarr['object']);
                    return true;
                }catch (OssException $e){
                    return array('error' => $e->getMessage());
                }
            }else{
                try {
                    if (is_array($oss) && $oss['error']) return $oss;
                    //$ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        
                    $objectMeta = $oss->getObjectMeta($arr['bucket'], $arr['object']);
        
                    $length = $objectMeta['content-length'] + 0;
        
                    // 初始化分片。
                    $upload_id = $oss->initiateMultipartUpload($oarr['bucket'], $oarr['object'], $options);
        
                    // 逐个分片拷贝。
                    $pieces = $oss->generateMultiuploadParts($length, $part_size);
                    $response_upload_part = array();
                    $copyId = 1;
                    $upload_position = 0;
        
                    foreach ($pieces as $i => $piece) {
                        $from_pos = $upload_position + (integer)$piece['seekTo'];
                        $to_pos = (integer)$piece['length'] + $from_pos - 1;
                        $up_options = array(
                            'start' => $from_pos,
                            'end' => $to_pos,
                        );
                        $response_upload_part[] = $oss->uploadPartCopy($arr['bucket'], $arr['object'], $oarr['bucket'], $oarr['object'], $copyId, $upload_id, $up_options);
                        $copyId = $copyId + 1;
                    }
        
                    // 完成分片拷贝。
                    $upload_parts = array();
                    foreach ($response_upload_part as $i => $etag) {
                        $upload_parts[] = array(
                            'PartNumber' => ($i + 1),
                            'ETag' => $etag,
                        );
                    }
                    $result = $oss->completeMultipartUpload($oarr['bucket'], $oarr['object'], $upload_id, $upload_parts);
                    return true;
                } catch (OssException $e) {
        
                    return array('error' => $e->getMessage());
                }
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
        
        public function uploadToattachment($attach, $fid,$nohook=0)
        {
            global $_G, $documentexts, $space, $docexts;
            if ( !perm_check::checkperm_Container($fid, 'upload')) {
                return array('error' => lang('no_privilege'));
            }
            $gid = DB::result_first("select gid from %t where fid=%d", array('folder', $fid));
            
            $attach['filename'] = io_dzz::getFileName($attach['filename'], $fid);
            
            $path = C::t('resources_path')->fetch_pathby_pfid($fid);
            
            $imgexts = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
            //图片文件时
            if (in_array(strtolower($attach['filetype']), $imgexts)) {
                $icoarr = array(
                    'uid' => $_G['uid'],
                    'username' => $_G['username'],
                    'name' => $attach['filename'],
                    'dateline' => $_G['timestamp'],
                    'pfid' => intval($fid),
                    'type' => 'image',
                    'flag' => '',
                    'vid' => 0,
                    'gid' => intval($gid),
                    'ext' => $attach['filetype'],
                    'size' => $attach['filesize']
                );
                if ($icoarr['rid'] = C::t('resources')->insert_data($icoarr)) {//插入主表
                    $sourceattrdata = array(
                        'postip' => $_G['clientip'],
                        'title' => $attach['filename'],
                        'aid' => $attach['aid']
                    );
                    $rbz = io_remote::getBzByRemoteid($attach['remote']);
                    $streamurl = IO::getStream($rbz.'/'.$attach['attachment']);
                    if ($imagesize = getimagesize($streamurl)) {
                        $sourceattrdata['width'] = $imagesize[0];
                        $sourceattrdata['height'] = $imagesize[1];
                    }
            
                    if (C::t('resources_attr')->insert_attr($icoarr['rid'], $icoarr['vid'], $sourceattrdata)) {//插入属性表
                        C::t('attachment')->update($attach['aid'], array('copys' => $attach['copys'] + 1));//增加图片使用数
                        $icoarr = array_merge($attach, $icoarr, $sourceattrdata);
                        //$icoarr['img'] = DZZSCRIPT . '?mod=io&op=thumbnail&size=small&path=' . dzzencode($icoarr['rid']);
                        $icoarr['img'] = geticonfromext($icoarr['ext'], $icoarr['type']);
                        $icoarr['thumbstatus'] = 0;
                        $icoarr['url'] = DZZSCRIPT . '?mod=io&op=thumbnail&size=large&create=1&path=' . dzzencode('attach::' . $icoarr['aid']);
                        $icoarr['bz'] = '';
                        $icoarr['rbz'] =$rbz;
                        $icoarr['relativepath'] = $path . $icoarr['name'];
                        $icoarr['path'] = $icoarr['rid'];
                        $icoarr['dpath'] = dzzencode($icoarr['rid']);
                        $icoarr['apath'] = dzzencode('attach::' . $attach['rid']);
                        $event = 'creat_file';
                        $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                        $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fid, $icoarr['gid']);
                        $eventdata = array(
                            'title' => $icoarr['name'],
                            'aid' => $icoarr['aid'],
                            'username' => $icoarr['username'],
                            'uid' => $icoarr['uid'],
                            'path' => $icoarr['path'],
                            'position' => $path,
                            'hash' => $hash
                        );
                        
                        C::t('resources_event')->addevent_by_pfid($fid, $event, 'create', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name']);
                    } else {
                        C::t('resources')->delete_by_rid($icoarr['rid']);
                        return array('error' => lang('data_error'));
                    }
                }
                
            }
            elseif (in_array(strtoupper($attach['filetype']), $documentexts)) {//文档文件时
                $icoarr = array(
                    'uid' => $_G['uid'],
                    'username' => $_G['username'],
                    'name' => $attach['filename'],
                    'type' => ($attach['filetype'] == 'dzzdoc') ? 'dzzdoc' : 'document',
                    'dateline' => $_G['timestamp'],
                    'pfid' => intval($fid),
                    'flag' => '',
                    'vid' => 0,
                    'gid' => intval($gid),
                    'ext' => $attach['filetype'],
                    'size' => $attach['filesize']
                );
                if ($icoarr['rid'] = C::t('resources')->insert_data($icoarr)) {
                    C::t('attachment')->update($attach['aid'], array('copys' => $attach['copys'] + 1));//增加文档使用数
                    $sourcedata = array(
                        'title' => $attach['filename'],
                        'desc' => '',
                        'aid' => $attach['aid'],
                        'img' => geticonfromext($icoarr['ext'], $icoarr['type'])
                    );
                    
                    if (C::t('resources_attr')->insert_attr($icoarr['rid'], $icoarr['vid'], $sourcedata)) {
                        
                        $icoarr = array_merge($attach, $sourcedata, $icoarr);
                        $icoarr['img'] = geticonfromext($icoarr['ext'], $icoarr['type']);
                        $icoarr['url'] = DZZSCRIPT . '?mod=io&op=getStream&path=' . dzzencode($icoarr['rid']);
                        $icoarr['bz'] = '';
                        $icoarr['rbz'] = io_remote::getBzByRemoteid($attach['remote']);;
                        $icoarr['relativepath'] = $path . $icoarr['name'];
                        $icoarr['path'] = $icoarr['rid'];
                        $icoarr['dpath'] = dzzencode($icoarr['rid']);
                        $icoarr['apath'] = dzzencode('attach::' . $attach['aid']);
                        $event = 'creat_file';
                        $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                        $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fid, $icoarr['gid']);
                        $eventdata = array(
                            'title' => $icoarr['name'],
                            'aid' => $icoarr['aid'],
                            'username' => $icoarr['username'],
                            'uid' => $icoarr['uid'],
                            'path' => $icoarr['path'],
                            'position' => $path,
                            'hash' => $hash
                        );
                        C::t('resources_event')->addevent_by_pfid($fid, $event, 'create', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name'], $icoarr['name']);
                    } else {
                        C::t('resources')->delete_by_rid($icoarr['rid']);
                        return array('error' => lang('data_error'));
                    }
                }
                
            }
            else {//附件
                $icoarr = array(
                    'uid' => $_G['uid'],
                    'username' => $_G['username'],
                    'name' => $attach['filename'],
                    'type' => 'attach',
                    'flag' => '',
                    'vid' => 0,
                    'dateline' => $_G['timestamp'],
                    'pfid' => intval($fid),
                    'gid' => intval($gid),
                    'ext' => $attach['filetype'],
                    'size' => $attach['filesize']
                
                );
                
                if ($icoarr['rid'] = C::t('resources')->insert_data($icoarr)) {
                    $sourcedata = array(
                        'title' => $attach['filename'],
                        'desc' => '',
                        'aid' => $attach['aid'],
                        'img' => geticonfromext($icoarr['ext'], $icoarr['type'])
                    );
                    C::t('attachment')->update($attach['aid'], array('copys' => $attach['copys'] + 1));
                    if (C::t('resources_attr')->insert_attr($icoarr['rid'], $icoarr['vid'], $sourcedata)) {
                        $icoarr = array_merge($attach, $sourcedata, $icoarr);
                        $icoarr['img'] = geticonfromext($icoarr['ext'], $icoarr['type']);
                        $icoarr['url'] = DZZSCRIPT . '?mod=io&op=getStream&path=' . dzzencode($icoarr['rid']);
                        $icoarr['bz'] = '';
                        $icoarr['rbz'] = io_remote::getBzByRemoteid($attach['remote']);
                        $icoarr['relativepath'] = $path . $icoarr['name'];
                        $icoarr['path'] = $icoarr['rid'];
                        $icoarr['dpath'] = dzzencode($icoarr['rid']);
                        $icoarr['apath'] = dzzencode('attach::' . $attach['aid']);
                        $event = 'creat_file';
                        $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                        $eventdata = array(
                            'title' => $icoarr['name'],
                            'aid' => $icoarr['aid'],
                            'username' => $icoarr['username'],
                            'uid' => $icoarr['uid'],
                            'path' => $icoarr['path'],
                            'position' => $path
                        );
                        
                        C::t('resources_event')->addevent_by_pfid($fid, $event, 'create', $eventdata, $icoarr['gid'], $icoarr['rid']);
                    } else {
                        C::t('resources')->delete_by_rid($icoarr['rid']);
                        return array('error' => lang('data_error'));
                    }
                }
            }
            $videoexts = ['mp4', 'ts', 'flv', 'wmv', 'asf', 'rm', 'rmvb', 'mpg', 'mpeg', '3gp', 'mov', 'webm', 'mkv', 'avi'];
            if ($icoarr['rid'] ) {
                //如果不调用挂载点，则此处暂不处理缩略图数据交由调用方法处处理，避免计划任务执行导致的缩略图生成不相符
                if(!$nohook){
                    $icoarr['thumbstatus'] = 0;
                    $settingthumbsize = $_G['setting']['thumbsize'];
                    //主动模式生成缩略图
                    if (in_array($icoarr['ext'], $videoexts)) {
                        $thumbrecodearr = [
                            'aid' => $icoarr['aid'],
                            'width' => $_G['setting']['thumbsize']['small']['width'],
                            'height' => $_G['setting']['thumbsize']['small']['height'],
                            'filesize' => $icoarr['filesize'],
                            'waterstatus' => ($_G['setting']['IsWatermarkstatus']) ? $_G['setting']['watermarkstatus'] : 0,
                            'thumbtype' => 1,
                            'dateline' => TIMESTAMP
                        ];
                        //缩略图记录id
                        $thumbrecorddata = C::t('thumb_record')->insert($thumbrecodearr, 1);
                        //已存在缩略图不需要再生成
                        if (isset($thumbrecorddata['thumbstatus']) && $thumbrecorddata['thumbstatus'] && $thumbrecorddata['path']) {
                            $icoarr['img'] = IO::getFileUri($thumbrecorddata['path']);
                            $icoarr['thumbstatus'] = 1;
                        }
                    }
                    else {
                        // if ($_G['setting']['thumb_active'] > 0) {
                        try {
                            unset($settingthumbsize['middle']);
                            foreach ($settingthumbsize as $key => $value) {
                                // IO::createThumb($icoarr['rid'], $key);
                                //缩略图记录表
                                $thumbrecodearr = [
                                    'aid' => $icoarr['aid'],
                                    'width' => $_G['setting']['thumbsize'][$key]['width'],
                                    'height' => $_G['setting']['thumbsize'][$key]['height'],
                                    'filesize' => $icoarr['filesize'],
                                    'thumbtype' => 1,
                                    'waterstatus' => ($_G['setting']['IsWatermarkstatus']) ? $_G['setting']['watermarkstatus'] : 0,
                                    'dateline' => TIMESTAMP
                                ];
                                //缩略图记录id
                                $thumbrecorddata = C::t('thumb_record')->insert($thumbrecodearr, 1);
                                //已存在缩略图不需要再生成
                                if (isset($thumbrecorddata['thumbstatus']) && $thumbrecorddata['thumbstatus'] && $thumbrecorddata['path']) {
                                    if ($key == 'small') {
                                        $icoarr['img'] = IO::getStream($thumbrecorddata['path']);
                                        $icoarr['thumbstatus'] = 1;
                                    }
                                    if ($key == 'large') $icoarr['url'] = IO::getStream($thumbrecorddata['path']);
                                }
                            }
                        } catch (Exception $e) {
                        }
                        //}
                    }
                }
               
                
                if ($icoarr['size']) SpaceSize($icoarr['size'], $gid, true);
                $icoarr['fsize'] = formatsize($icoarr['size']);
                $icoarr['ftype'] = getFileTypeName($icoarr['type'], $icoarr['ext']);
                $icoarr['fdateline'] = dgmdate($icoarr['dateline']);
                $icoarr['sperm'] = perm_FileSPerm::typePower($icoarr['type'], $icoarr['ext']);
                return $icoarr;
            }
            else {
                return array('error' => lang('data_error'));
            }
            
        }
        
        public function uploadbymulti($uploadFile, $path, $filesize = 0, $options = array())
        {
            global $_G;
            $arr = self::parsePath($path);
            $ossClient = self::init($path);
            if (is_array($ossClient) && $ossClient['error']) return $ossClient;
            try {
                $uploadId = $ossClient->initiateMultipartUpload($arr['bucket'], $arr['object'], $options);
            } catch (OssException $e) {
                return array('error' => $e->getMessage());
            }
            $partSize = 10 * 1024 * 1024;
            if (!$handle = fopen($uploadFile, 'rb')) {
                return array('error' => lang('open_file_error'));
            }
            $hascachfile = 0;
            //判断文件是否可以fseek指针移动，不可指针移动则缓存到本地上传
            if (@fseek($handle, 0, SEEK_END) === -1) {
                $cachefile = $_G['setting']['attachdir'] . './cache/' . md5($uploadFile) . '.dzz';
                $fileContent = '';
                if (!file_exists($cachefile)) {
                    $whandle = fopen($cachefile, 'a+');
                    while (!feof($handle)) {
                        $fileContent .= fread($handle, 8192);
                        fwrite($whandle, $fileContent);
                        $fileContent = '';
                    }
                    fclose($whandle);
                }
                if (file_exists($cachefile)) {
                    $uploadFile = $cachefile;
                    $hascachfile = 1;
                }
            }
            fclose($handle);
            $uploadFileSize = ($filesize) ? $filesize : filesize($uploadFile);
            $pieces = $ossClient->generateMultiuploadParts($uploadFileSize, $partSize);
            $responseUploadPart = array();
            $uploadPosition = 0;
            $isCheckMd5 = false;
            foreach ($pieces as $i => $piece) {
                $fromPos = $uploadPosition + (integer)$piece[$ossClient::OSS_SEEK_TO];
                $toPos = (integer)$piece[$ossClient::OSS_LENGTH] + $fromPos - 1;
                $upOptions = array(
                    // 上传文件。
                    $ossClient::OSS_FILE_UPLOAD => $uploadFile,
                    // 设置分片号。
                    $ossClient::OSS_PART_NUM => ($i + 1),
                    // 指定分片上传起始位置。
                    $ossClient::OSS_SEEK_TO => $fromPos,
                    // 指定文件长度。
                    $ossClient::OSS_LENGTH => $toPos - $fromPos + 1,
                    // 是否开启MD5校验，true为开启。
                    $ossClient::OSS_CHECK_MD5 => $isCheckMd5,
                );
                // 开启MD5校验。
                if ($isCheckMd5) {
                    $contentMd5 = OssUtil::getMd5SumForFile($uploadFile, $fromPos, $toPos);
                    $upOptions[$ossClient::OSS_CONTENT_MD5] = $contentMd5;
                }
                try {
                    // 上传分片。
                    $responseUploadPart[] = $ossClient->uploadPart($arr['bucket'], $arr['object'], $uploadId, $upOptions);
                } catch (OssException $e) {
                    
                    return array('error' => $e->getMessage());
                }
                
            }
            // $uploadParts是由每个分片的ETag和分片号（PartNumber）组成的数组。
            $uploadParts = array();
            foreach ($responseUploadPart as $i => $eTag) {
                $uploadParts[] = array(
                    'PartNumber' => ($i + 1),
                    'ETag' => $eTag,
                );
            }
            
            try {
                // 执行completeMultipartUpload操作时，需要提供所有有效的$uploadParts。OSS收到提交的$uploadParts后，会逐一验证每个分片的有效性。当所有的数据分片验证通过后，OSS将把这些分片组合成一个完整的文件。
                $ossClient->completeMultipartUpload($arr['bucket'], $arr['object'], $uploadId, $uploadParts);
                if ($hascachfile) @unlink($cachefile);
            } catch (OssException $e) {
                return array('error' => $e->getMessage());
            }
            return true;
        }
        
        //移动文件到下载缓冲区并下载
        public function moveFileToDownload($path, $filepath)
        {
            $oss = self::init($path);
            $arr = self::parsePath($path);
            $brr = self::parsePath($filepath);
            $options = array(
                OssClient::OSS_HEADERS => array(
                    'Content-Type' => 'application/octet-stream',
                    'Content-Disposition' => 'attachment'
                ));
            if ($arr['bz'] == $brr['bz'] && $arr['bucket'] == $brr['bucket']) {
                try {
                    $result = $this->copyMultiData($filepath, $path, $options);
                } catch (ErrorException $e) {
                    return array('error' => $e->getMessage());
                }
                
            } else {
                
                $stream = IO::getstream($filepath);
                $filesize = filesize($stream);
                if ($filesize >= 100 * 1024 * 1024) {
                    $result = $this->uploadbymulti($stream, $path, $filesize, $options);
                    if ($result['error']) {
                        return array('error' => $result['error']);
                    }
                } else {
                    try {
                        $r = $oss->putObject($arr['bucket'], $arr['object'], file_get_contents($stream), $options);
                    } catch (ErrorException $e) {
                        return array('error' => $e->getMessage());
                    }
                }
            }
            try {
                $result = self::getStream($path);
                return $result;
                // 请求成功
            } catch (\Exception $e) {
                // 请求失败
                return array('error' => $e->getMessage());
            }
        }
        
        public function getThumbWaterUrl($path, $style, $download_file)
        {
            global $_G;
            $oss = self::init($path);
            $arr = self::parsePath($path);
            $process = $style .
                '|sys/saveas' .
                ',o_' . $this->base64url_encode($download_file) .
                ',b_' . $this->base64url_encode($arr['bucket']);
            try {
                $result = $oss->processObject($arr['bucket'], $arr['object'], $process);
                $result = json_decode($result, true);
                return $result['object'];
            } catch (OssException $e) {
                return array('error' => $e->getMessage());
            }
            
        }
        
        //移动缩略图到目标位置
        public function moveThumbFile($path, $filepath)
        {
            $oss = self::init($path);
            $arr = self::parsePath($path);
            $brr = self::parsePath($filepath);
            if ($arr['bz'] == $brr['bz'] && $arr['bucket'] == $brr['bucket']) {
                if (is_array($oss) && $oss['error']) return $oss;
                    try {
                        $result = $oss->copyObject($brr['bucket'], $brr['object'], $arr['bucket'], $arr['object']);
                    } catch (\Exception $e) {
                        return array('error' => $e->getMessage());
                    }
               
            } else {
                if($arr['bz'] == $brr['bz']){
                    $return = $this->copyMultiData($filepath, $path);
                    if (isset($return['error'])) {
                        return array('error' => $return['error']);
                    }
                }else{
                    if(strpos($filepath,getglobal('setting/attachdir')) === 0){
                        $stream = $filepath;
                    }
                    else{
                        $stream = IO::getstream($filepath);
                    }
                    $filesize = filesize($stream);
                    if ($filesize >= 100 * 1024 * 1024) {
                        $result = $this->uploadbymulti($stream, $path, $filesize);
                        if ($result['error']) {
                            return array('error' => $result['error']);
                        }
                    }
                    else {
                        try {
                            $result = $oss->putObject($arr['bucket'], $arr['object'], file_get_contents($stream));
                        } catch (ErrorException $e) {
                            return array('error' => $e->getMessage());
                        }
                    }
                }
              
            }
            try {
                $result = self::getStream($path);
                return $result;
                // 请求成功
            } catch (\Exception $e) {
                // 请求失败
                return array('error' => $e->getMessage());
            }
        }
    }

?>
