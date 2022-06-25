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
    include_once DZZ_ROOT . './core/api/Qcos/vendor/autoload.php';
    @set_time_limit(0);
    @ini_set('max_execution_time', 0);
    
    class io_Qcos extends io_api
    {
        const T = 'connect_storage';
        const BZ = 'QCOS';
        private $icosdatas = array();
        private $bucket = '';
        private $_root = '';
        private $_rootname = '';
        private $perm = 0;
        private $alc = '';
        private $getaclpermtime=0;
        private  $getaclurl = '';
        
        // qcos配置信息
        private $qcos_config = [];
        
        public function __construct($path='')
        {
            $arr = DB::fetch_first("SELECT root,name FROM %t WHERE bz=%s", array('connect', self::BZ));
            
            $this->_root = $arr['root'];
            $this->_rootname = $arr['name'];
            //$this->perm = perm_binPerm::getMyPower();
            
            //self::init($path);
        }
        
        /*
         * 初始化qcos 返回 qcos对象实例
         */
        public function init($bz, $isguest = 1)
        {
            global $_G;
            $bzarr = explode(':', $bz);
            $id = trim($bzarr[1]);
            if (!$root = DB::fetch_first("select * from " . DB::table(self::T) . " where  id='{$id}'")) {
                return array('error' => 'need authorize to ' . $bzarr[0]);
            }
            if (!$isguest && $root['uid'] > 0 && $root['uid'] != $_G['uid']) return array('error' => 'need authorize to qcos');
            
            //$access_id = authcode($root['access_id'], 'DECODE', $root['bz']);
            $access_id = dzzdecode($root['access_id'],  $root['bz']);
            if (empty($access_id)) $access_id = $root['access_id'];
            //$access_key = authcode($root['access_key'], 'DECODE', $root['bz']);
            $access_key = dzzdecode($root['access_key'], $root['bz']);
            $this->bucket = $root['bucket'];
            $hostnamearr = explode(':', $root['hostname']);
            $schema = isset($hostnamearr[0]) ? $hostnamearr[0] : 'http';
            $region = isset($hostnamearr[1]) ? $hostnamearr[1] : 'ap-beijing';
            try {
                
                $this->qcos_config = [
                    'credentials' => array(
                        'secretId' => $access_id,
                        'secretKey' => $access_key
                    ),
                    'region' => $hostnamearr[1],
                    'schema' => $hostnamearr[0],

                ];
                $this->host = $root['host'];
                $qcos = new Qcloud\Cos\Client($this->qcos_config);
                return $qcos;
            } catch (Exception $e) {
                return array('error' => $e->getMessage());
            }
        }
       public  function get_authorization($secretKey,$secretId,$StartTimestamp, $EndTimestamp, $fileUri, $headers, $method = 'get')
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
            $signKey = hash_hmac('sha1', $signTime, $secretKey);
            $httpString = "$httpMethod\n$httpUri\n$httpParameters\n$headerString\n";
            $sha1edHttpString = sha1($httpString);
            $stringToSign = "sha1\n$signTime\n$sha1edHttpString\n";
            $signature = hash_hmac('sha1', $stringToSign, $signKey);
//组合结果
            $authorization = "q-sign-algorithm=sha1&q-ak=$secretId&q-sign-time=$qSignTime&q-key-time=$qKeyTime&q-header-list=$header_list&q-url-param-list=$url_param_list&q-signature=$signature";
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
        
        public  function getapidatas($bucket,$region,$url, $params, $authheader, $xmldata = '')
        {
            $arr_header[] = "Content-Type:application/xml";
            $arr_header[] = "Authorization: " . $authheader;
            $arr_header[] = "Host:".$bucket.".cos.".$region.".myqcloud.com";
            //$arr_header[] = "x-cos-acl:private";
            $ch = curl_init();
            $url .= '?' . http_build_query($params);
            $url = str_replace('0=','',$url);
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
        //获取bucket读取权限
        public function getAclPerm(){
            $readperm = 1;
            $apparr = explode('-',$this->bucket);
            $appid = $apparr[1];
            $bucket = $apparr[0];
            $secret_id = $this->qcos_config['credentials']['secretId'];
            $secret_key = $this->qcos_config['credentials']['secretKey'];
            $aclpermcache = $this->bucket.'_readPerm';
            if($readperm = memory('get',$aclpermcache)){
                    return $readperm;
            }else {
                $StartTimestamp = time();
                $EndTimestamp = $StartTimestamp + 3600;
                //请求地址
                $queryurl = $this->bucket . '.cos.' . $this->qcos_config['region'] . '.myqcloud.com/';
                $params = ['acl'];
                //请求头
                $headers = [];
                $fileUri = '/';
                $authorization = $this->get_authorization($secret_key,$secret_id,$StartTimestamp, $EndTimestamp, $fileUri, $headers);
                $data = $this->getapidatas($this->bucket,$this->qcos_config['region'],$queryurl, $params, $authorization);
                $result =$data['AccessControlList']['Grant'];
                foreach($result as $v){
                    if($v['Permission'] == 'READ') $readperm= 2;
                }
                memory('set',$aclpermcache,$readperm,3600);
                return $readperm;
            }
        }
        
        
        /**
         * 移动附件
         * @param $path
         * @param $attach
         *
         * @return array|bool|void
         */
        public function MoveToSpace($path, $attach)
        {
            $filename = substr($path, strrpos($path, '/') + 1);;
            $fpath = substr($path, 0, strrpos($path, '/')) . '/';
            if ($re = $this->makeDir($fpath)) { //创建目录
                if ($re['error']) return $re;
            }
            $obz = io_remote::getBzByRemoteid($attach['remote']);
            $save_path = array();
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
        
        /**
         * 根据文件路径创建文件夹
         * @param        $path
         * @param string $pfid
         * @param bool $noperm
         */
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
                }
            }
            return true;
        }
        
        protected function _makeDir($path)
        {
            global $_G;
            $arr = self::parsePath($path);
            
            $qcos = self::init($path);
            if (is_array($qcos) && $qcos['error']) return $qcos;
            try {
                $ret = $qcos->putObject(array('Bucket' => $arr['bucket'], 'Key' => $arr['object'] . '/', 'Body' => ''));
            } catch (ErrorException $e) {
                return array('error' => $e->getMessage());
            }
            
            return true;
            
        }
        
        
        //获取权限
        public function getBucketALC($path)
        {
            $arr = self::parsePath($path);
            $qcos = self::init($path, 1);
            if (is_array($qcos) && $qcos['error']) return $qcos;
            $this->alc = $qcos->getObjectAcl(array($arr['bucket'], $arr['object']));
            return $this->alc;
        }
        
        //获取存储桶列表
        public function getBucketList($access_id, $access_key, $region)
        {
            $re = array();
            $config = [
                'credentials' => array(
                    'secretId' => $access_id,
                    'secretKey' => $access_key
                ),
                'region' => $region,
                'schema' => 'http',
            ];
            $qcos = new \Qcloud\Cos\Client($config);
            try {
                //请求成功
                if ($list = $qcos->listBuckets()) {
                    foreach ($list['Buckets'][0] as $value) {
                        $re[] = $value['Name'];
                    }
                } else {
                    return array();
                }
            } catch (\Exception $e) {
                //请求失败
                //echo($e);
                return array('error' => $e->getMessage());
            }
            return $re;
        }
        
        public function authorize($refer = '')
        {
            
            global $_G, $_GET, $clouds;

            if (empty($_G['uid'])) {
                dsetcookie('_refer', rawurlencode(BASESCRIPT . '?mod=connect&op=oauth&bz=qcos'));

            }
            if (submitcheck('addspace')) {
                $access_id = trim($_GET['access_id']);
                $access_key = trim($_GET['access_key']);
                $region = trim($_GET['region']);
                $bucket = trim($_GET['bucket']);
                $host = trim($_GET['host']);
                $appid = trim($_GET['extra']);
                $cloudname = trim($_GET['coludname']);
                $urlarr = parse_url($host);
                if (!$access_id || !$access_key) {
                   exit(json_encode(array('success'=>false,'msg'=>lang('input_QCOS_acc_sec') . 'Access Key ID and Access Key Secret')));
                }
                if (!$bucket)   exit(json_encode(array('success'=>false,'msg'=>'select_bucket_node_address')));
                
                
                $qcos_config = [
                    'credentials' => array(
                        'secretId' => $access_id,
                        'secretKey' => $access_key),
                    'region' => $region,
                    'schema' =>$urlarr['scheme'] ? $urlarr['scheme']:'http'
                ];

                $checkdata = $this->checkqcos($qcos_config,$bucket);

                if(isset($checkdata['error'])){
                    exit(json_encode(array('success'=>false,'msg'=>$checkdata['error'])));
                }
                $cosClient = new Qcloud\Cos\Client($qcos_config);
                $type = 'QCOS';
                $uid = defined('IN_ADMIN') ? 0 : $_G['uid'];
                $setarr = array(
                    'cloudname'=>$cloudname,
                    'uid' => $uid,
                    'access_id' => $access_id,
                    'access_key' => dzzencode($access_key,  $type,0),
                    'bz' => $type,
                    'bucket' => $bucket,
                    'dateline' => TIMESTAMP,
                    'hostname' => $qcos_config['schema'] . ':' . $region,
                    'host'=>$host,
                    'extra'=>$appid
                );
                if ($id = DB::result_first("select id from " . DB::table(self::T) . " where uid='{$uid}' and access_id='{$access_id}' and bucket='{$bucket}'")) {
                    DB::update(self::T, $setarr, "id ='{$id}'");
                } else {
                    $setarr['cloudname'] = C::t('connect_storage')->getcloudname($setarr['cloudname']);
                    $id = DB::insert(self::T, $setarr, 1);
                }
                exit(json_encode(array('success'=>true)));
            }
        }
        //验证腾讯云可用性
        public function checkqcos($config,$bucket){
            //实例化类
            $qcosclient = new Qcloud\Cos\Client($config);
            $foldername = 'tmppichomethumb/';
            //创建目录
            try{
                $return =  $qcosclient->putObject(array('Bucket'=>$bucket,'Key'=>$foldername,'Body'=>''));;
            } catch (Exception $e) {
                return array('error' => $e->getMessage());
            }
            if(isset($return['error'])){
                return array( 'error' =>$return['error']);
            }
           
            //创建测试文件
            $filename = $foldername.'/testapi.txt';
            $filecontent = '123';
            try {
                $ret = $qcosclient->Upload($bucket,  $filename, $filecontent);
            } catch (Exception $e) {
                return array('error' => $e->getMessage());
            }
            //获取文件地址
            try {
                $return = $qcosclient->getObjectUrl($bucket,$filename,'+120 minutes');;
            }catch (Exception $e) {
                return array('error' => $e->getMessage());
            }
       
            if(isset($return['error'])){
                return array( 'error' =>$return['error']);
            }else{
                //对比文件内容
                if(file_get_contents($return) !== $filecontent){
                    return array( 'error' =>'read is error');
                }
            }
         
            //删除文件
            try {
                $response = $qcosclient->deleteObject(array('Bucket' =>$bucket, 'Key' =>$filename));
            } catch (Exception $e) {
                return array( 'error' => $e->getMessage());
            }
            
            return true;
        }
        public function getBzByPath($path)
        {
            $bzarr = explode(':', $path);
            return $bzarr[0] . ':' . $bzarr[1] . ':';
        }
        
        //获取文件地址
        public function getFileUri($path)
        {
            $arr = self::parsePath($path);
            $qcos = self::init($path, 1);
            if (is_array($qcos) && $qcos['error']) return $qcos;
            return $qcos->getObjectUrl($arr['bucket'], $arr['object'], '+120 minutes');
            
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
        public function parseparams($signedUrl, $width, $height, $thumbtype)
        {
            //横图按高度给比例值 竖图按宽度比例给值
            $imginfo = @getimagesize($signedUrl);
            $imgwidth = $imginfo[0];
            $imgheight = $imginfo[1];
            if ($imgwidth > 9999 || $imgheight > 9999) {
                return false;
            }
            //如果缩略图宽高大于等于原图宽高则不生成缩略图
            if($width>= $imgwidth || $height>= $imgheight){
                return '';
            }
            $oscale = $imgwidth / $imgheight;
            $nscale = $width /$height;
            
           
            if ($thumbtype == 2) {
              /*  if ($oscale > $nscale) {
                    $width = $width/$oscale;
                    $height= $height;
                } else {
                    $width = $width;
                    $height = $height*$oscale;
                }*/
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
                    $height= $height/$oscale;
                } else {
                    $height = $height;
                    $width = $width*$oscale;
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
                $imgurl = base64_encode(\IO::getStream($_G['setting']['qcoswaterimg']));
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
            
            //http://examples-1251000004.cos.ap-shanghai.myqcloud.com/sample.jpeg?watermark/2/text/6IW-6K6v5LqRwrfkuIfosaHkvJjlm74/fill/IzNEM0QzRA/fontsize/20/dissolve/50/gravity/northeast/dx/20/dy/20/batch/1/degree/45
            //return 'watermark/2/text/'.$text.'/fill/IzNEM0QzRA/fontsize/20/gravity/center';
            
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

        public function createThumbByOriginal($path, $data,$width = 0, $height = 0, $thumbtype = 1, $original = 0, $tmpfile=0,$extraparams = array(),$filesize=0)
        {
           
            global $_G;
            $filedirpathinfo = pathinfo($path);
            $filedirextensionarr = explode('?', $filedirpathinfo['extension']);
            $filedirextension = strtolower($filedirextensionarr[0]);
            $patharr = explode(':',$path);
            $did = $patharr[1];
            $connectdata = C::t('connect_storage')->fetch($did);
            //如果文件大小超过32M,尝试使用本地缩略图转换处理
            if(!$connectdata['imagestatus'] || $filesize > 1024*1024*32){
               //$url =  io_dzz::createThumbByOriginal($path, $width, $height ,$thumbtype, $original , $extraparams,$filesize);
                return false;
            }
            else{
                $qcosimageexts = getglobal('config/qcosimage') ? explode(',',getglobal('config/qcosimage')):array('jpeg','jpg', 'png', 'gif', 'webp','bmp');
                if(!in_array($filedirextension,$qcosimageexts)){
                    return false;
                }
                $signedUrl = $this->getStream($path);
                $params = '';
                if(!$original){
                    $params = $this->parseparams($signedUrl, $width, $height, $thumbtype);
                }
                $waterparams = '';
                //水印参数
                $waterparams = $this->parsewatermarkparams($signedUrl,$extraparams,$width, $height);
    
                if(!$params && !$waterparams){
                    return $path;
                }else{
                    $signedUrl = explode('?',$signedUrl);
                    $url = $signedUrl[0].'?'.($params ? $params:'').($waterparams ? '|'.$waterparams.'&':'&').$signedUrl[1];
                }
            }
            if(!$url) return false;
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
    
            $cachethumbpath = 'tmppichomethumb/'.$data['appid'].'/'.md5($path.$data['thumbsign']). '.jpg';
            $cloudpath = $arr['bz'] . '/' .$cachethumbpath;
            if(!$tmpfile) {
                $return = $this->moveThumbFile($cloudpath,$url);
                if(isset($return['error'])){
                    return false;
                }else{
                    return $cloudpath;
                }
            }
            else return $url;



        }

        //判断文件或目录是否存在
        function checkfileexists($path,$isdir = false){
            $arr = self::parsePath($path);
            $qcos = self::init($path, 1);
            if (is_array($qcos) && $qcos['error']) return array('error'=>$qcos);
            try{

                $object = ($isdir &&  substr($arr['object'], -1) != '/') ? $arr['object'].'/':$arr['object'];
                $result = $qcos->doesObjectExist($arr['bucket'],$object);
                if(!$result){
                    if($isdir){
                        try{
                            $result = $qcos->listObjects(
                                ['Bucket' => $arr['bucket'],
                                    'Delimiter'=>'/',
                                    'Marker' => '',
                                    'Prefix' => $object,
                                    'MaxKeys' => 1]
                            );
                        }catch (Exception $e){
                            return false;
                        }
                        if($result['Contents']){
                            return true;
                        }
                    }
                    return false;
                }
            }catch (Exception $e){
                return false;
            }
            return true;
        }
        
        //获取文件流；
        //$path: 路径
        function getStream($path,$fileexists = false)
        {
            $arr = self::parsePath($path);
            $qcos = self::init($path, 1);
            if (is_array($qcos) && $qcos['error']) return array('error'=>$qcos);
            if($fileexists){
                try{
                    $result = $qcos->doesObjectExist($arr['bucket'],$arr['object']);
                    if(!$result){
                        return array('error'=>'文件不存在');//后面修改为语言包
                    }
                }catch (Exception $e){
                    return array('error'=>$e->getMessage());
                }
            }
            
            $readperm = self::getAclPerm();

            if($readperm == 2){
                $d = $this->host .'/'. $arr['object'];
            }else{
                $d = $qcos->getObjectUrl($arr['bucket'], $arr['object'], '+120 minutes');
            }
            return $d;
        }
        
        public function parsePath($path)
        {
            $path = str_replace(BS,'/',$path);
            $arr = explode(':', $path);
            $bz = $arr[0] . ':' . $arr[1] . ':';
            $arr1 = explode('/', $arr[2]);
            $bucket = DB::result_first("select bucket from %t where id = %d",array('connect_storage',$arr[1]));
            unset($arr1[0]);
            $object = implode('/', $arr1);
            return array('bucket' => $bucket, 'object' => $object, 'bz' => $bz);
        }
        //重写文件内容
        //@param number $path  文件的路径
        //@param string $data  文件的新内容
        public function setFileContent($path, $data)
        {
            $arr = self::parsePath($path);
            
            $qcos = self::init($path);
            if (is_array($qcos) && $qcos['error']) return $qcos;
            try {
                $ret = $qcos->Upload($arr['bucket'], $arr['object'], $data);
                $meta = array(
                    'Key' => $arr['object'],
                    'Size' => strlen($data),
                    'LastModified' => dgmdate(TIMESTAMP, 'Y-m-d H:i:s'),
                );
                
                $icoarr = self::_formatMeta($meta, $arr);
                return $icoarr;
            } catch (ErrorException $e) {
                return array('error' => $e->getMessage());
            }
        }
        
        /**
         * 上传文件
         *
         * @param string $fileContent 文件内容字符串
         * @param string $path 上传文件的目标保存路径
         * @param string $fileName 文件名
         * @param string $ondup overwrite：目前只支持覆盖。
         *
         * @return string
         */
        function upload_by_content($fileContent, $path, $filename, $ondup = 'overwrite')
        {
            global $_G;
            $path .= $filename;
            $arr = self::parsePath($path);
            
            $qcos = self::init($path);
            if (is_array($qcos) && $qcos['error']) return $qcos;
            try {
                $ret = $qcos->Upload($arr['bucket'], $arr['object'] . '/' . $filename, $fileContent);
                $meta = array(
                    'Key' => $arr['object'] . '/' . $filename,
                    'Size' => strlen($fileContent),
                    'LastModified' => dgmdate(TIMESTAMP, 'Y-m-d H:i:s'),
                );
                
                $icoarr = self::_formatMeta($meta, $arr);
                return $icoarr;
            } catch (Exception $e) {
                return array('error' => $e->getMessage());
            }
        }
        
        /**
         * 获取当前用户空间配额信息
         *
         * @return string
         */
        public function getQuota($bz)
        {
            return 0;
        }
        function getFolderlist($path,  $nextmarker = '',$by = 'time',$order = 'DESC', $limit = 1000, $force = 0){
            $arr = self::parsePath($path);
            $folderdatas = $folders =  [];
            $folderdatas['IsTruncated'] = false;
            $folderdatas['NextMarker'] = '';
            $qcos = self::init($path);
            if (is_array($qcos) && $qcos['error']) return $qcos;

            try {
                $querydata = array(
                    'Bucket' => $arr['bucket'],
                    'Delimiter' => '/',
                    'Marker' => ($nextmarker) ? $nextmarker:$arr['object'],
                    'MaxKeys' => $limit,
                );
                if($arr['object']) $querydata['Prefix'] =  $arr['object'].'/';
                $data = $qcos->listObjects($querydata);
            } catch (ErrorException $e) {
                return array('error' => $e->getMessage());
            }
            foreach ($data['CommonPrefixes'] as $v) {
                if($v['Prefix'] != 'tmppichomethumb/'){
                    $folders[] = $v['Prefix'];
                }

            }
            if($data['IsTruncated']){
                $folderdatas['IsTruncated'] = true;
                $folderdatas['NextMarker'] = $data['NextMarker'];
            }
            $folderdatas['folder'] = $folders;
           return $folderdatas;
        }
        /**
         * 获取指定文件夹下的文件列表
         *
         * @param string $path 文件路径
         * @param string $by 排序字段，缺省根据文件类型排序，time（修改时间），name（文件名），size（大小，注意目录无大小）
         * @param string $order asc或desc，缺省采用降序排序
         * @param string $limit 返回条目控制，参数格式为：n1-n2。返回结果集的[n1, n2)之间的条目，缺省返回所有条目。n1从0开始。
         * @param string $force 读取缓存，大于0：忽略缓存，直接调用api数据，常用于强制刷新时。
         *
         * @return icosdatas
         */
        function listFiles($path,  $nextmarker = '',$by = 'time',$order = 'DESC', $limit = 1000, $force = 0)
        {
            $arr = self::parsePath($path);
            $datas = $folders = $files =  [];
            $datas['IsTruncated'] = false;
            $datas['NextMarker'] = '';
            $qcos = self::init($path);
            if (is_array($qcos) && $qcos['error']) return $qcos;

            try {
                $querydata = array(
                    'Bucket' => $arr['bucket'],
                    'Delimiter' => '/',
                    'Marker' => ($nextmarker) ? $nextmarker:$arr['object'],
                    'MaxKeys' =>  $limit,
                );
                if($arr['object']) $querydata['Prefix'] =  $arr['object'].'/';
                //if($nextmarker) print_r($querydata);die;
                $data = $qcos->listObjects($querydata);
            } catch (ErrorException $e) {
                return array('error' => $e->getMessage());
            }

            foreach($data['Contents'] as $v){
                if($v['Size'] > 0){
                    $files[] = $v['Key'];
                }
            }
            foreach ($data['CommonPrefixes'] as $v) {
                if($v['Prefix'] != 'tmppichomethumb/'){
                    $folders[] = $v['Prefix'];
                }

            }
            if($data['IsTruncated']){
                $datas['IsTruncated'] = true;
                $datas['NextMarker'] = $data['NextMarker'];
            }
            $datas['folder'] = $folders;
            $datas['file'] = $files;
            return $datas;
        }

        //获取图片信息
      function getImagedatabyPath($path){


              $ch = curl_init();
              $url = $this->getStream($path);
              $url .= '?imageInfo';
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              if (!empty($arr_header)) {
                  curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_header);
              }

              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
              curl_setopt($ch, CURLOPT_REFERER, '');
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
              $response = curl_exec($ch);
              $data = json_decode($response,true);
              curl_close($ch);
              return $data;


      }
        
        /*
     *获取文件的meta数据
     *返回标准的icosdata
     *$force>0 强制刷新，不读取缓存数据；
    */
        function getMeta($path, $getimagedata = 0)
        {

            $arr = self::parsePath($path);
            $icosdata = array();
            $qcos = self::init($path, 1);
            if (is_array($qcos) && $qcos['error']) return $qcos;
            if (empty($arr['object']) || empty($arr['bucket'])) {
                $meta = array(
                    'Key' => '',
                    'Size' => 0,
                    'LastModified' => '',
                    'isdir' => true
                );
            } else {
                try {
                    $meta = $qcos->headObject(array('Bucket' => $arr['bucket'], 'Key' => $arr['object']));
                    if($getimagedata && strpos($meta['ContentType'],'image') === 0){
                       $imagedata =  $this->getImagedatabyPath($path);
                    }else{
                        $imagedata = array();
                    }
                    $ext = substr(strrchr($meta['Key'], '.'), 1);
                    $ext = strtolower($ext);
                    $metatmp = array(
                        'dateline'=>strtotime($meta['LastModified']),
                        'size'=>$meta['ContentLength'],
                        'filename'=>basename($meta['Key']),
                        'ext'=>$ext
                    );
                    $meta = array_merge($metatmp,$imagedata);
                } catch (Exception $e) {
                    return array('error' => $e->getMessage());
                }
            }
            return $meta;

        }
        
        //将api获取的meta数据转化为icodata
        function _formatMeta($meta, $arr)
        {
            global $_G, $documentexts, $imageexts;
            $icosdata = array();
            ///print_r($meta);print_r($arr);exit($this->bucket);
            
            
            if (substr($meta['Key'], -1) == '/') $meta['isdir'] = true;
            
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
                if (in_array($ext, $imageexts)) $type = 'image'; elseif (in_array($ext, $documentexts)) $type = 'document';
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
                $fsperm = 0;
            } else {
                $fsperm = 0;
            }
            if ($icosdata['type'] == 'folder') {
                $folder = array(
                    'fid' => $icosdata['oid'],
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
                    'icon' => '',
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
        public function getFolderObjects(&$qcos, $path, $limit = '1', $marker = '')
        {
            static $objects = array();
            $arr = self::parsePath($path);
            try {
                $querydata = array(
                    'Bucket' => $arr['bucket'],
                    'Prefix'=>$arr['object'],
                    'Marker' => ($marker) ? $marker:$arr['object'],
                    'MaxKeys' => $limit
                );
                print_r($querydata);
                $data = $qcos->listObjects($querydata);
            } catch (ErrorException $e) {
                return array('error' => $e->getMessage());
            }
            foreach ($data['Contents'] as $v) {
                    $objects[] = $v['Key'];
            }
            if ($data['IsTruncated'] == 'true') {

                 self::getFolderObjects($path, 1, $data['nextMarker']);

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
        public function Delete($path, $isdir=false)
        {
            $arr = self::parsePath($path);

            try {
                $qcos = self::init($path);

                if (is_array($qcos) && $qcos['error']) return $qcos;

                //判断删除的对象是否为文件夹
                if ($isdir || substr($arr['object'], -1) == '/') { //是文件夹
                    $cos_prefix = $arr['object'];

                    $nextMarker = '';
                    $isTruncated = true;
                    while ( $isTruncated ) {
                        try {
                            $result = $qcos->listObjects(
                                [
                                    'Delimiter'=>'/',
                                    'Bucket' => $arr['bucket'],
                                    'Marker' => $nextMarker,
                                    'Prefix' => $cos_prefix,
                                    'MaxKeys' => 1000]
                            );

                            $isTruncated = $result['IsTruncated'];
                            $nextMarker = $result['NextMarker'];
                            foreach ($result['Contents'] as $content ) {
                                $cos_file_path = $content['Key'];
                                try {
                                    $qcos->deleteObject(array(
                                        'Bucket' => $arr['bucket'],
                                        'Key' => $cos_file_path,
                                    ));
                                } catch ( \Exception $e ) {
                                    return array('error' => $e->getMessage());
                                }
                            }

                        } catch ( \Exception $e ) {
                            return array('error' => $e->getMessage());
                        }
                    }
                } else {
                    $response = $qcos->deleteObject(array('Bucket' => $arr['bucket'], 'Key' => $arr['object']));
                }
                if ($response === false) {
                    return array('error' =>  'error');
                }
                
                return true;
            } catch (Exception $e) {
                return array( 'error' => $e->getMessage());
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
                $qcos = self::init($path);
                if (is_array($qcos) && $qcos['error']) return $qcos;
                
                $ret = $qcos->putObjectByContent($arr['object'] . '/' . $fname . '/', '');
                if ($ret === false) {
                    return array('error' => self::get_error_info($qcos));
                }
                $meta = array(
                    'isdir' => true,
                    'Key' => $arr['object'] . $fname . '/',
                    'Size' => 0,
                    'LastModified' => $ret->header['date'],
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
            $cachekey = 'qcos_uploadID_' . md5($path);
            $cache = C::t('cache')->fetch($cachekey);
            return unserialize($cache['cachevalue']);
        }
        
        private function saveCache($path, $data)
        {
            global $_G;
            $cachekey = 'qcos_uploadID_' . md5($path);
            C::t('cache')->insert(array(
                'cachekey' => $cachekey,
                'cachevalue' => serialize($data),
                'dateline' => $_G['timestamp'],
            ), false, true);
        }
        
        private function deleteCache($path)
        {
            $cachekey = 'qcos_uploadID_' . md5($path);
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
        public function uploadbymulti($uploadFile, $path, $filesize = 0, $options = array())
        {
            global $_G;
            $arr = self::parsePath($path);
            $oss= self::init($path);
            if (is_array($oss) && $oss['error']) return $oss;
            $partsize = 1024 * 1024 * 5; //分块大小5M
            if(is_file($uploadFile)){
                $stream = $uploadFile;
            }else{
                $stream = IO::getstream($uploadFile);
            }
            $filesize = ($filesize) ? $filesize:filesize($stream);
            if ($filesize < $partsize) {
                try {
                    $source =  fopen($stream, 'rb');
                    if(!$source){
                        return array('error'=>'file is empty ');
                    }
                    $result = $oss->putObject(array(
                        'Bucket' => $arr['bucket'],
                        'Key' => $arr['object'],
                        'Body' =>$source,
                    ));
                    return $result;
                } catch (ErrorException $e) {
                    return array('error' => $e->getMessage());
                }
            } else { //分片上传
                $partinfo = array('ispart' => true, 'partnum' => 0, 'iscomplete' => false);
                if (!$handle = fopen($stream, 'rb')) {
                    return array('error' => lang('open_file_error'));
                }
          
                $fileContent = '';
                while (!feof($handle)) {
                    $fileContent .= fread($handle, 8192);
                    if (strlen($fileContent) == 0) return array('error' => lang('file_not_exist1'));
                    if (strlen($fileContent) >= $partsize) {
                        if ($partinfo['partnum'] * $partsize + strlen($fileContent) >= $filesize) $partinfo['iscomplete'] = true;
                        $partinfo['partnum'] += 1;
                        //file_put_contents($cachefile, $fileContent);
                        if ($partinfo['partnum'] == 1){//第一个分块时 初始化分块上传得到$uploadID;并缓存住，留以后分块使用
                            //初始化分块
                        try {
                            $response = $oss->CreateMultipartUpload(array('Bucket' => $arr['bucket'], 'Key' => $arr['object']));
                        } catch (ErrorException $e) {
                            return array('error' => $e->getMessage());
                        }
                        $upload_id = $response['UploadId'];
                        }
                        try {
                            //上传分块
                            $response = $oss->UploadPart(array(
                                'Bucket' => $arr['bucket'], 'Body'=>$fileContent,
                                'Key' => $arr['object'], 'UploadId' => $upload_id,
                                'PartNumber' => $partinfo['partnum'] ));
                          
                        } catch (ErrorException $e) {
                          return   array('error' => $e->getMessage());
                        }
                        if ($partinfo['iscomplete']) {
                            //获取已上传分块列表信息
                            try {
                                $partlists = $oss->listParts(array(
                                    'Bucket' => $arr['bucket'], //格式：BucketName-APPID
                                    'Key' => $arr['object'],
                                    'UploadId' => $upload_id,
                                ));
                            } catch (\Exception $e) {
                                return   array('error' => $e->getMessage());
                            }
                            try{
                                $re = $oss->completeMultipartUpload(array(
                                    'Bucket' =>  $arr['bucket'], //格式：BucketName-APPID
                                    'Key' => $arr['object'],
                                    'UploadId' =>$upload_id,
                                    'Parts' =>$partlists['Parts']
                                ));
                            }catch (ErrorException $e) {
                                return   array('error' => $e->getMessage());
                            }
                            return $re;
                        }
                        $fileContent = '';
                    }
                }
                fclose($handle);
                if (!empty($fileContent)) {
                    $partinfo['partnum'] += 1;
                    $partinfo['iscomplete'] = true;
                    try {
                        //上传最后一个分块
                        $response = $oss->UploadPart(array(
                            'Bucket' => $arr['bucket'], 'Body'=>$fileContent,
                            'Key' => $arr['object'], 'UploadId' => $upload_id,
                            'PartNumber' => $partinfo['partnum'] ));
                      
                    } catch (ErrorException $e) {
                        return   array('error' => $e->getMessage());
                    }
                    //获取已上传分块列表信息
                    try {
                        $partlists = $oss->listParts(array(
                            'Bucket' => $arr['bucket'], //格式：BucketName-APPID
                            'Key' => $arr['object'],
                            'UploadId' => $upload_id,
                        ));
                    } catch (\Exception $e) {
                        return   array('error' => $e->getMessage());
                    }
                    try{
                        $re = $oss->completeMultipartUpload(array(
                            'Bucket' =>  $arr['bucket'], //格式：BucketName-APPID
                            'Key' => $arr['object'],
                            'UploadId' =>$upload_id,
                            'Parts' =>$partlists['Parts']
                        ));
                    }catch (ErrorException $e) {
                        return   array('error' => $e->getMessage());
                    }
                  return $re;
                }
            }
      
        }
        function CreateMultipartUpload($path)
        {
            $arr = self::parsePath($path);
            $oss = self::init($path);
            if (is_array($oss) && $oss['error']) return $oss;
            $ret = $oss->createMultipartUpload(array('Bucket' => $arr['bucket'], 'Key' => $arr['object']));
            
        }
        
        function upload($file, $path, $filename, $partinfo = array(), $ondup = 'overwrite')
        {
            global $_G;
            $path .= $filename;
            $arr = self::parsePath($path);
            
            try {
                $oss = self::init($path);
                if (is_array($oss) && $oss['error']) return $oss;
                $upload_file_options = array(
                    'SourceFile' => $file,
                );
                if ($partinfo['partnum']) {
                    $upload_file_options['PartNumber'] = $partinfo['partnum'];
                    if ($partinfo['partnum'] == 1) {//第一个分块时 初始化分块上传得到$uploadID;并缓存住，留以后分块使用
                        //初始化分块
                        try {
                            $response = $oss->CreateMultipartUpload(array('Bucket' => $arr['bucket'], 'Key' => $arr['object']));
                        } catch (ErrorException $e) {
                            return array('error' => $e->getMessage());
                        }
                        
                        $upload_id = $response['UploadId'];
                        
                        try {
                            //上传分块
                            $response = $oss->UploadPart(array('Bucket' => $arr['bucket'], 'Key' => $arr['object'], 'UploadId' => $upload_id, 'PartNumber' => $upload_file_options));
                        } catch (ErrorException $e) {
                            array('error' => $e->getMessage());
                        }
                        
                        if (md5_file($file) != strtolower(trim($response['ETag'], '"'))) { //验证上传是否完整
                            return array('error' => lang('upload_file_incomplete'));
                        }
                        
                        
                        $data = array();
                        $data['upload_id'] = $upload_id;
                        $data['filesize'] = filesize($file);
                        $data['partnum'] = 1;
                        $data['path'] = $path;
                        $data['parts'][$data['partnum']] = array(
                            'PartNumber' => $data['partnum'],
                            'ETag' => $response['ETag']
                        );
                        
                        self::saveCache($path, $data);
                    } else {
                        $cache = self::getCache($path);
                        $upload_id = $cache['upload_id'];
                        $cache['partnum'] += 1;
                        try {
                            //上传分块
                            $response = $oss->UploadPart(array('Bucket' => $arr['bucket'], 'Key' => $arr['object'], 'UploadId' => $upload_id, 'PartNumber' => $upload_file_options));
                        } catch (ErrorException $e) {
                            array('error' => $e->getMessage());
                        }
                        
                        if (md5_file($file) != strtolower(trim($response['ETag'], '"'))) { //验证上传是否完整
                            return array('error' => lang('upload_file_incomplete'));
                        }
                        
                        //print_r($cache);
                        $cache['filesize'] += filesize($file);
                        
                        $cache['parts'][$partinfo['partnum']] = array(
                            'PartNumber' => $cache['partnum'],
                            'ETag' => $response['ETag']
                        );
                        //print_r($cache);exit('dddd');
                        self::saveCache($path, $cache);
                    }
                    if ($partinfo['iscomplete']) {
                        $cache = self::getCache($path);
                        try {
                            $response = $oss->completeMultipartUpload(array('Bucket' => $arr['bucket'], 'Key' => $arr['object'], 'UploadId' => $cache['upload_id'], 'Parts' => $cache['parts']));
                        } catch (ErrorException $e) {
                            array('error' => $e->getMessage());
                        }
                        
                        self::deleteCache($path);
                        $meta = array(
                            'Key' => $arr['object'],
                            'Size' => $cache['filesize'],
                            'LastModified' => $response['LastModified']->format('Y-m-d H:i:s'),
                        );
                        
                        $icoarr = self::_formatMeta($meta, $arr);
                        
                        return $icoarr;
                    } else {
                        return true;
                    }
                } else {
                    $response = $oss->putObjectBySavePath($file, $arr['object']);
                    
                    if ($response === false) {
                        return array('error' => self::get_error_info($oss));
                    }
                    
                    $meta = array(
                        'Key' => $arr['object'],
                        'Size' => filesize($file),
                        'LastModified' => $response['LastModified'],
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
                
                $CopySource = $arr['bucket'] . '.cos.' . $this->qcos_config ['region'] . '.myqcloud.com/' . $arr['object'];
                try {
                    $result = $oss->copyObject(array('Bucket' => $arr['bucket'], 'Key' => $arr['object1'], 'CopySource' => $CopySource));
                } catch (ErrorException $e) {
                    return array('error' => $e->getMessage());
                }
                try {
                    $response = $oss->deleteObject(array('Bucket' => $arr['bucket'], 'Key' => $arr['object']));
                } catch (ErrorException $e) {
                    return array('error' => $e->getMessage());
                }
                
            }
            return self::getMeta($arr['bz'] . $arr['bucket'] . '/' . $arr['object1']);
        }
        
        /**
         * 移动文件到目标位置
         *
         * @param string $opath 被移动的文件路径
         * @param string $path 目标位置（可能是同一api内或跨api，这两种情况分开处理）
         *
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
                            
                            $CopySource = $arr['bucket'] . '.cos.' . $this->qcos_config ['region'] . '.myqcloud.com/' . $arr['object'];
                            try {
                                $result = $oss->copyObject(array('Bucket' => $arr['bucket'], 'Key' => $oarr['object'], 'CopySource' => $CopySource));
                            } catch (ErrorException $e) {
                                $data['success'] = $e->getMessage();
                            }
                            
                            
                            $meta = array(
                                'Key' => $arr['object'],
                                'Size' => $data['size'],
                                'LastModified' => $result['LastModified'],
                            );
                            $data['newdata'] = self::_formatMeta($meta, $arr);
                            
                            $data['success'] = true;
                        } else {
                            
                            if ($re = IO::multiUpload($opath, $path, $data['name'])) {
                                if ($re['error']) $data['success'] = $re['error']; else {
                                    $data['newdata'] = $re;
                                    $data['success'] = true;
                                }
                            }
                        }
                        break;
                }
                
            } catch (Exception $e) {
                $data['success'] = $e->getMessage();
                return $data;
            }
            return $data;
        }
        //移动缩略图到目标位置
        public function moveThumbFile($path,$filepath){
            $oss = self::init($path);
            $arr = self::parsePath($path);
            $brr = self::parsePath($filepath);
            if ($arr['bz'] == $brr['bz'] && $arr['bucket'] == $brr['bucket']) {
                try {
                    $CopySource = $brr['bucket'] . '.cos.' . $this->qcos_config ['region'] . '.myqcloud.com/' . $brr['object'];
                    $result = $oss->copyObject(array('Bucket' => $arr['bucket'], 'Key' => $arr['object'], 'CopySource' => $CopySource, 'MetadataDirective' => 'Replaced'));
                } catch (ErrorException $e) {
                    return array('error' => $e->getMessage());
                }
            } else {
                $filepath = IO::getStream($filepath);
                if (!$handle = fopen($filepath, 'rb')) {
                    return array('error' => lang('open_file_error'));
                }
                $hascachfile = 0;
                //判断是否是文件,如果不是文件读取地址文件内容，写入缓存
                if (!is_file($filepath)) {
                    $cachefile = getglobal('setting/attachdir') . './cache/' . md5($filepath) . '.dzz';
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
                        $filepath = $cachefile;
                        $hascachfile = 1;
                    }
                }
                fclose($handle);
                $filesize = filesize($filepath);
                if($filesize >= 20*1024*1024){
                    $return = $this->uploadbymulti($filepath,$path,$filesize);
                    if($hascachfile) @unlink($cachefile);
                    if($return['error']) return array('error' =>$return['error']);
                }else{
                    try {
                        $source =  fopen($filepath, 'rb');
                        if(!$source){
                            return array('error'=>'file is empty ');
                        }
                        $result = $oss->putObject(array(
                            'Bucket' => $arr['bucket'],
                            'Key' => $arr['object'],
                            'Body' =>$source,
                        ));
                    } catch (ErrorException $e) {
                        if($hascachfile) @unlink($cachefile);
                        return array('error' => $e->getMessage());
                    }
                }
                }
            if($hascachfile) @unlink($cachefile);
            try {
                $result = $oss->getObjectUrl($arr['bucket'], $arr['object'], '+120 minutes');
                return $result;
                // 请求成功
            } catch (\Exception $e) {
                // 请求失败
                return array('error' => $e->getMessage());
            }
        }
        public function multiUpload($opath, $path, $filename, $attach = array(), $ondup = "newcopy")
        {
            global $_G;
            
            
            $partsize = 1024 * 1024 * 20; //分块大小2M
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

        }
        
        //移动文件到下载缓冲区并下载
        public function moveFileToDownload($path, $filepath)
        {
            $oss = self::init($path);
            $arr = self::parsePath($path);
            $brr = self::parsePath($filepath);
            if ($arr['bz'] == $brr['bz'] && $arr['bucket'] == $brr['bucket']) {
                try {
                    $CopySource = $brr['bucket'] . '.cos.' . $this->qcos_config ['region'] . '.myqcloud.com/' . $brr['object'];
                    $result = $oss->copyObject(array('Bucket' => $arr['bucket'], 'Key' => $arr['object'], 'CopySource' => $CopySource, 'MetadataDirective' => 'Replaced',
                        'ContentDisposition' =>'attachment'));
                } catch (ErrorException $e) {
                    return array('error' => $e->getMessage());
                }
                
            } else {
                try {
                    $stream = IO::getstream($filepath);
                    $result = $oss->putObject(array(
                        'Bucket' => $arr['bucket'],
                        'Key' => $arr['object'],
                        'Body' => fopen($stream, 'rb'),
                        'ContentDisposition'=>'attachment'
                    ));
                } catch (ErrorException $e) {
                    return array('error' => $e->getMessage());
                }
            }
            try {
                $result = $oss->getObjectUrl($arr['bucket'], $arr['object'], '+120 minutes');
                return $result;
                // 请求成功
            } catch (\Exception $e) {
                // 请求失败
                return array('error' => $e->getMessage());
            }
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
            return ($ext) ? $target . date('His') . '' . strtolower(random(16))  .'.'.$ext:$target . date('His') . '' . strtolower(random(16));
        }
        public function path_info($filepath)
        {
            $path_parts = array();
            $path_parts ['dirname'] = rtrim(substr($filepath, 0, strrpos($filepath, '/')),"/")."/";
            $path_parts ['basename'] = ltrim(substr($filepath, strrpos($filepath, '/')),"/");
            $path_parts ['extension'] = substr(strrchr($filepath, '.'), 1);
            $path_parts ['filename'] = ltrim(substr($path_parts ['basename'], 0, strrpos($path_parts ['basename'], '.')),"/");
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
                'attachment' =>$filepath,
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
            if($routeremoteid && $routeremoteid != $data['remoteid']){
                $localdata  = C::t('local_storage')->fetch($routeremoteid);
                $did = $localdata['did'];
                $connectdata = C::t('connect_storage')->fetch($did);
                $topath = $localdata['bz'].':'.$did.':'.$connectdata['bucket'].'/'.$filepath;
                $arr = self::parsePath($path);
                $attachment['attachment'] = $arr['object'];
                //$attachment['attachment'] = $routeremoteid;
                $movereturn = IO::MoveToSpace($topath,$attachment);
         
                //移除临时区文件
                $oss = self::init($path);
                if($movereturn && !$movereturn['error'] ){
                    $attachment['attachment'] = $filepath;
                    $attachment['remote'] = $routeremoteid;
                    $attachment['aid'] = C::t('attachment')->insert($attachment, 1);
                    try {
                        $response = $oss->deleteObject(array('Bucket' => $arr['bucket'], 'Key' => $arr['object']));
                    } catch (\Exception $e) {
                        runlog('qcosupload',$connectdata['bucket'].'/'.$filepath.' delete tmp file failed');
                        // return array('error' => $e->getMessage());
                    }
                }else{
                    try {
                        $response = $oss->deleteObject(array('Bucket' => $arr['bucket'], 'Key' => $arr['object']));
                    } catch (\Exception $e) {
                        runlog('qcosupload',' delete tmp file failed');
                        // return array('error' => $e->getMessage());
                    }
                    //如果移动文件失败，返回错误
                    return array('error' => $movereturn['error']);
                }
            }
            else{
                $attachment['aid'] = C::t('attachment')->insert($attachment, 1);
    
                $topath = $data['bz'] . ':' . $data['did'] . ':' . $data['Bucket'] . '/' . $attachment['attachment'];
                $arr = self::parsePath($path);
                $oarr = self::parsePath($topath);
                if ($path != $topath) {
                    $oss = self::init($path);
                    if (is_array($oss) && $oss['error']) return $oss;
                    $filename = $this->get_basename($arr['object']);
                    $CopySource = $arr['bucket'] . '.cos.' . $this->qcos_config ['region'] . '.myqcloud.com/' . dirname($arr['object']).'/'.urlencode($filename);
                    try {
                        $result = $oss->copyObject(array('Bucket' => $oarr['bucket'], 'Key' => $oarr['object'], 'CopySource' => $CopySource));
                    } catch (\Exception $e) {
                        runlog('qcosupload',$CopySource.' move to folder failed');
                        return array('error' => $e->getMessage());
                    }
        
                    try {
                        $response = $oss->deleteObject(array('Bucket' => $arr['bucket'], 'Key' => $arr['object']));
                    } catch (\Exception $e) {
                        runlog('qcosupload',$CopySource.' delete tmp file failed');
                        return array('error' => $e->getMessage());
                    }
                }
                //C::t('attachment')->addcopy_by_aid($attachment['aid']);
            }
            $datas['icoarr'][] = self::uploadToattachment($attachment, $pfid);
            return $datas;
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
                    
                    if ($imagesize = getimagesize($_G['setting']['attachdir'] . $attach['attachment'])) {
                        $sourceattrdata['width'] = $imagesize[0];
                        $sourceattrdata['height'] = $imagesize[1];
                    }
                    if (C::t('resources_attr')->insert_attr($icoarr['rid'], $icoarr['vid'], $sourceattrdata)) {//插入属性表
                        C::t('attachment')->update($attach['aid'], array('copys' => $attach['copys'] + 1));//增加图片使用数
                        $icoarr = array_merge($attach, $icoarr, $sourceattrdata);
                        //$icoarr['img'] = DZZSCRIPT . '?mod=io&op=thumbnail&size=small&path=' . dzzencode($icoarr['rid']);
                        $icoarr['img'] =  geticonfromext($icoarr['ext'], $icoarr['type']);
                        $icoarr['thumbstatus'] = 0;
                        $icoarr['url'] =    DZZSCRIPT . '?mod=io&op=thumbnail&size=large&create=1&path=' . dzzencode('attach::' . $icoarr['aid']);;
                        $icoarr['bz'] = '';
                        $icoarr['rbz'] = io_remote::getBzByRemoteid($attach['remote']);
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
                
            } elseif (in_array(strtoupper($attach['filetype']), $documentexts)) {//文档文件时
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
                
            } else {//附件
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
            $videoexts = ['mp4','ts','flv','wmv','asf','rm','rmvb','mpg','mpeg','3gp','mov','webm','mkv','avi'];
            if ($icoarr['rid']) {
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
            } else {
                return array('error' => lang('data_error'));
            }
            
        }
        public function getThumburl($path,$size,$thumbtype=1,$original = 0,$extparams=array(),$width = 0,$height = 0){
            global $_G;
            $size=trim($size);
            $size=in_array($size,array_keys($_G['setting']['thumbsize']))?$size:'large';
            if(!$width) $width=$_G['setting']['thumbsize'][$size]['width'];
            if(!$height) $height=$_G['setting']['thumbsize'][$size]['height'];
            return IO::getThumb($path,$width,$height,$original,true,$thumbtype,$extparams);
        }

        public function upload_content($path,$content){
            $arr = self::parsePath($path);
            $qcos = self::init($path);
            if (is_array($qcos) && $qcos['error']) return $qcos;
            try {
                $ret = $qcos->Upload($arr['bucket'], $arr['object'],$content);
                return $ret;
            } catch (Exception $e) {
                return array('error' => $e->getMessage());
            }
        }
        
    }

