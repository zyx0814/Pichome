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
    
    
    class dzz_io
    {
        protected function initIO($path)
        {
            $path = self::clean($path);
            $bzarr = explode(':', $path);
            $allowbz = C::t('connect')->fetch_all_bz();//array('baiduPCS','ALIOSS','dzz','JSS','disk');
            if (strpos($path, 'dzz::') !== false) {
                $classname = 'io_dzz';
            } elseif (strpos($path, 'attach::') !== false) {
                $classname = 'io_dzz';
            } elseif (strpos($path, 'TMP::') !== false) {
                $classname = 'io_dzz';
            } elseif (is_numeric($bzarr[0])) {
                $classname = 'io_dzz';
            } elseif (in_array($bzarr[0], $allowbz)) {
                $classname = 'io_' . $bzarr[0];
            } elseif (preg_match('/^\w{32}$/i', $path)) {
                $classname = 'io_dzz';
            } else {
                return false;
            }
            
            return new $classname($path);
        }
        
        function MoveToSpace($path, $attach, $ondup = 'overwrite')
        {
            $path = self::clean($path);
            if ($io = self::initIO($path)) {
                return $io->MoveToSpace($path, $attach, $ondup);
            } else {
                return false;
            }
        }
        
        function authorize($bz, $refer = '')
        {
            if ($io = self::initIO($bz)) {
                $io->authorize($refer);
            }
        }
        
        function getQuota($bz)
        {
            if ($io = self::initIO($bz)) {
                return $io->getQuota($bz);
            } else {
                return false;
            }
        }
        
        function chmod($path, $chmod, $son = 0)
        {
            if ($io = self::initIO($path)) {
                return $io->chmod($path, $chmod, $son);
            } else {
                return false;
            }
        }
        
        function parsePath($path)
        {
            $path = self::clean($path);
            if ($io = self::initIO($path)) {
                return $io->parsePath($path);
            } else {
                return false;
            }
        }
        
        function output_thumb($file, $mine = 'image/JPEG')
        {//根据文件地址，输出图像流
            global $_G;
            $last_modified_time = filemtime($file);
            if ($last_modified_time) {
                $etag = md5_file($file);
                header("Last-Modified: " . gmdate("D, d M Y H:i:s", $last_modified_time) . " GMT");
                header("Etag: $etag");
                if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time ||
                    trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag
                ) {
                    header("HTTP/1.1 304 Not Modified");
                    exit;
                }
            }
            /*if(!$last_modified_time) $last_modified_time = TIMESTAMP;*/
            @header('cache-control:public');
            header('Content-Type: ' . $mine);
            @ob_end_clean();
            if (getglobal('gzipcompress')) @ob_start('ob_gzhandler');
            @readfile($file);
            @flush();
            @ob_flush();
            exit();
        }

        /*将文件缓存到本地,并且返回本地的访问地址*/
        function cacheFile($data)
        {
            global $_G;
            $subdir = $subdir1 = $subdir2 = '';
            $subdir1 = date('Ym');
            $subdir2 = date('d');
            $subdir = $subdir1 . '/' . $subdir2 . '/';
            $target1 = 'dzzcache/' . $subdir . 'index.html';
            $target = 'dzzcache/' . $subdir . random(10);
            $target_attach = $_G['setting']['attachdir'] . $target1;
            $targetpath = dirname($target_attach);
            dmkdir($targetpath);
            if (file_put_contents($target, $data)) {
                return $target;
            } else {
                return false;
            }
        }
    
        public function clean($str) {//清除路径
			if(is_array($str)){
				foreach($str as $key=> $value){
					$str[$key]=self::clean_path(str_replace(array( "\n", "\r", '../'), '', $value));
				}
			}else{
				$str= self::clean_path(str_replace(array( "\n", "\r", '../'), '', $str));
			}

			return $str;
		}
		private function clean_path($str){
			if(preg_match("/\.\.\//",$str)){
				$str=str_replace('../','',$str);
				return self::clean_path($str);
			}else{
				return $str;
			}
		}
		
		
        
        public function getApps($path){
            $path = self::clean($path);
            if ($io = self::initIO($path)) {
                $return = $io->getApps($path);
                return $return;
            } else return false;
        }
        public function getAllFiles($path,$fid,$page=1,$limit=100){
            $path = self::clean($path);
            if ($io = self::initIO($path)) {
                $return = $io->getAllFiles($path,$fid,$page,$limit);
                return $return;
            } else return false;
        }
        public function getAlltag($path){
            $path = self::clean($path);
            if ($io = self::initIO($path)) {
                $return = $io->getAlltag($path);
                return $return;
            } else return false;
        }
        public function getAlltaggroup($path){
            $path = self::clean($path);
            if ($io = self::initIO($path)) {
                $return = $io->getAlltaggroup($path);
                return $return;
            } else return false;
        }
    }
