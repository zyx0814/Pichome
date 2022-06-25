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
    @set_time_limit(0);
    @ini_set('max_execution_time', 0);
    
    class io_dzz extends io_api
    {
        public function listFiles($rid, $by = 'name', $asc = 'DESC', $limit = 0, $force = 0)
        {
            $data = array();
            $icoarr = C::t('resources_path')->fetch($rid);
            switch ($by) {
                case 'name':
                    $orderby = 'name';
                    break;
                case 'size':
                    $orderby = 'size';
                    break;
                case 'type':
                    $orderby = array('type', 'ext');
                    break;
                case 'time':
                    $orderby = 'dateline';
                    break;
                
            }
            if ($limit) list($start, $perpage) = explode('-', $limit);
            foreach (C::t('resources')->fetch_all_by_pfid($icoarr['oid'], '', $perpage, $by, $asc, $start) as $value) {
                $data[$value['rid']] = $value;
            }
            return $data;
        }

        //检查文件是否存在
        public function checkfileexists($path,$isdir= false){
            $path = $this->parsePath($path);
            if(!$isdir && is_file($path)){
                return true;
            }elseif($isdir && is_dir($path)){
                return true;
            }
            return false;
        }
        public function getMeta($path,$getimagedata= 0){
            $path = $this->parsePath($path);
            if(!is_file($path)){
                $data = C::t('pichome_resources')->fetch_data_by_rid($path);
                return $data;
            }else{
                global $Types;
                $pathinfo = pathinfo($path);
                $ext = strtolower($pathinfo['extension']);
                $fileinfo = array(
                    'name' => $this->getbasename($path),
                    'ext' => $ext,
                    'size' => filesize($path),
                    'dateline'=>filemtime($path)
                );
                $imginfo = array();
                if($getimagedata && (in_array($ext,$Types['commonimage']) || in_array($ext,$Types['image']))){
                    //获取图片信息，以取得宽高
                    $imgdata = @getimagesize($path);
                    $imginfo['width'] = isset($imgdata[0]) ? $imgdata[0]:0;
                    $imginfo['height'] = isset($imgdata[1]) ? $imgdata[1]:0;
                }
                return array_merge($fileinfo,$imginfo);
            }


        }
        //兼容linux获取中文文件名问题
        public function getbasename($filename)
        {
            return preg_replace('/^.+[\\\\\\/]/', '', $filename);
        }
        /**
         * 获取空间配额信息
         * @return string
         */
        public function MoveToSpace($path, $attach = array())
        {
            global $_G;
            $obz = io_remote::getBzByRemoteid($attach['remote']);
            
            if ($obz == 'dzz') {
                return array('error' => lang('same_storage_area'));
            } else {
                $url = IO::getFileUri($obz . '/' . $attach['attachment']);
                if (is_array($url)) return array('error' => $url['error']);
                $target = $_G['setting']['attachdir'] . './' . $attach['attachment'];
                $targetpath = dirname($target);
                dmkdir($targetpath);
                try {
                    if (file_put_contents($target, fopen($url, 'rb')) === false) {
                        return array('error' => lang('error_occurred_written_local'));
                    }
                } catch (Exception $e) {
                    return array('error' => $e->getMessage());
                }
                if (md5_file($target) != $attach['md5']) {
                    return array('error' => lang('file_transfer_errors'));
                }
            }
            return true;
            
        }
        
        public function rename($rid, $text)
        {
            //查找当前目录下是否有同名文件
            $icoarr = C::t('resources')->fetch_info_by_rid($rid);
            /* $ext = '';
        $namearr = explode('.', $text);
        if (count($namearr) > 1) {
            $ext = $namearr[count($namearr) - 1];
            unset($namearr[count($namearr) - 1]);
            $ext = $ext ? ('.' . $ext) : '';
        }
        $tname = implode('.', $namearr);
        //如果有后缀名并且是文件
        if ($ext && $icoarr['ext']) {
            //如果后缀名和原后缀名不同,则加上原后缀名组成新的文件名
            if ($ext != '.' . $icoarr['ext']) {
                $text = $tname . $ext . '.' . $icoarr['ext'];
            } else {
                $text = $tname . $ext;
            }
        } elseif (!$ext && $icoarr['ext']) {
            $text = $tname . $ext . '.' . $icoarr['ext'];
        }*/
            /*$name=preg_replace("/\(\d+\)/i",'',$tname).'('.($i+1).')'.$ext;*/
            if ($icoarr['name'] != $text && ($ricoid = io_dzz::getRepeatIDByName($text, $icoarr['pfid'], ($icoarr['type'] == 'folder') ? true : false))) {//如果目录下有同名文件
                return array('error' => lang('filename_already_exists'));
            }
            if (!$arr = C::t('resources')->rename_by_rid($rid, $text)) {
                return array('error' => 'Not modified!');
            }
            $icoarr['name'] = $text;
            return $icoarr;
        }
        
        public function parsePath($path)
        {
            $path = str_replace('dzz::','',$path);
            return $path;
        }
        
        //根据路径获取目录树的数据；
        function getFolderDatasByPath($fid)
        {
            $fidarr = getTopFid($fid);
            $folderarr = array();
            foreach ($fidarr as $fid) {
                $folderarr[$fid] = C::t('folder')->fetch_by_fid($fid);
            }
            return $folderarr;
        }
        
        //获取文件流地址
        public function getStream($path, $fop = '')
        {
            $prepath = DZZ_ROOT;
            $path =   $this->parsePath($path);
            $handle = @fopen($path,'r');
            if($handle){
                fclose($handle);
                $path = str_replace('/',BS,$path);
                $path = str_replace($prepath,'',$path);
                $path = str_replace(BS,'/',$path);
            }else{
                $path = getglobal('setting/attachdir').$path;

            }
            return $path;

        }
        
        //获取文件的真实地址
        public function getFileUri($path, $fop = '')
        {
            $prepath = DZZ_ROOT;
            $path =   $this->parsePath($path);
            $handle = @fopen($path,'r');
            if($handle){
                fclose($handle);
             return $path;
            }else{
                $path = getglobal('setting/attachdir').$path;

            }
            return $path;
        }
        
        //根据路径移动文件到目标位置
        public function moveThumbFile($path, $filepath)
        {
            $path = IO::getStream($path);
            $pathdir = dirname($path);
            if (!is_dir($pathdir)) {
                mkdir($pathdir, 0777, true);
                chmod($pathdir, 0777);
            }
            if(!is_file($filepath)){
                $stream = IO::getStream($filepath);
            }else{
                $stream = $filepath;
            }
			$handle=fopen($stream,'rb');
			$succ=1;
			while (!feof($handle)) {
				$fileContent = fread($handle, 8192);
				if(file_put_contents($path, $fileContent, FILE_APPEND)===false){
					$succ=0;
				}
				unset($fileContent);
			}
			fclose($handle);
			if(!$succ){
				@unlink($path);
				return false;
			}
			return $path;
           
        }
        
        //获取文件内容
        public function getFileContent($path)
        {
            $url = self::getStream($path);
            return file_get_contents($url);
        }

        
        public function createThumbByOriginal($path,$data,$width = 0, $height = 0, $thumbtype = 1,$original=0,$tmpfile = 0,$extraparams=array(),$filesize=0)
        {
            global $_G;
            $path = self::parsePath($path);
            //取得原始文件路径
            if($this->checkfileexists($path)){
               if(!$tmpfile) $targetpath = 'pichomethumb/'.$data['appid'].'/'.md5($path.$data['thumbsign']).'.jpg';
               else $targetpath = 'cache/'.md5($path.$data['thumbsign']).'.jpg';
            }else{
               return false;
            }

            $filedirpathinfo = pathinfo($path);
            $filedirextensionarr = explode('?', $filedirpathinfo['extension']);
            $filedirextension = strtolower($filedirextensionarr[0]);
            //获取文件地址
            $fileuri = IO::getStream($path);

            $extraflag = '';

            $target = $targetpath;


            //如果服务器处理完成后，路径非图片类文件的时候，直接获取文件后缀对应的图片
            if (!in_array($filedirextension, array('png', 'jpg', 'gif', 'jpeg')) || !$imginfo = @getimagesize($fileuri)) {

                $thumbpath = false;
            } else {
                //图片小于最小水印最小设置时，不生成水印
                if ($_G['setting']['IsWatermarkstatus'] == 0 || ($imginfo[0] < $_G['setting']['watermarkminwidth'] || $imginfo[1] < $_G['setting']['watermarkminheight'])) {
                    $nomark = 1;
                }
                //返回原图的时候 或者图片小于缩略图宽高的不生成直接返回原图
                if ($original || ($imginfo[0] < $width || $imginfo[1] < $height)) {

                    $thumbpath = $path;

                } else {
                    //生成缩略图
                    include_once libfile('class/image');
                    $target_attach = $_G['setting']['attachdir'] . './' . $target;
                    $targetpath = dirname($target_attach);
                    dmkdir($targetpath);
                    $image = new image();
                    try {
                        $thumb = $image->Thumb($fileuri, $target, $width, $height, $thumbtype, 0, $extraparams);

                        if ($thumb) {

                            $thumbpath = $target;
                        } else {
                            $thumbpath = false;
                        }
                    } catch (\Exception $e) {
                        $thumbpath = false;
                       
                    }
                }
            }
            if($thumbpath){
                if(strpos($thumbpath,':') === false){
                    $bz = 'dzz';
                }else{
                    $patharr = explode(':',$thumbpath);
                    $bz = $patharr[0];
                    $did = $patharr[1];

                }
                if(!is_numeric($did) || $did < 2){
                    $bz = 'dzz';
                }
                if($bz == 'dzz'){
                    $rootpath = str_replace(BS,'/',DZZ_ROOT);
                    $thumbpath = str_replace($rootpath,'',$thumbpath);
                    $thumbpath = str_replace('./','/',$thumbpath);
                    $thumbpath = str_replace('//','/',$thumbpath);
                    $thumbpath = ltrim($thumbpath,'/');
                    $thumbpath = 'dzz::'.$thumbpath;
                }
            }

          /*  if (strpos($thumbpath, $_G['setting']['attachurl']) === 0) {
                    $thumbpath = str_replace(DZZ_ROOT, 'dzz::', $thumbpath);

                }*/
            return $thumbpath;
            
        }

        //删除
        //当文件在回收站时，彻底删除；
        //finaldelete 真实删除，不放入回收站
        //$force 强制删除，不受权限控制
        public function Delete($path, $isdir = false)
        {
            $path = self::parsePath($path);
            if($isdir){
                removedirectory($path);
            }else{
                @unlink($path);
            }
            return true;
        }
        
        //获取缩略图
        public function getThumb($path, $width = 0, $height = 0, $original = false, $returnurl = false, $create = 0,$tmpfile=0, $thumbtype = 1, $extraparams = array(),$filesize=0)
        {
            global $_G;
            if (!$data = IO::getMeta($path)) return false;
            if(!$tmpfile && !$original){
                //缩略图记录表
                $thumbrecodearr = [
                    'rid' =>$data['rid'],
                    'width' => $width,
                    'height' => $height,
                    'filesize' => $data['filesize'] ? $data['filesize'] : $data['size'],
                    'thumbtype' => $thumbtype,
                    'dateline' => TIMESTAMP,
                    'thumbsign'=>0,
                    'ext'=>$data['ext']
                ];
                //缩略图记录表数据
                $thumbrecorddata = C::t('thumb_record')->insert($thumbrecodearr, 1);
                //已存在缩略图不需要再生成
                if ($thumbrecorddata['thumbstatus'] > 0) {
                    $img = IO::getFileUri($thumbrecorddata['path']);
                    if ($returnurl) return $img;
                    else IO::output_thumb($img);
                }elseif($thumbrecorddata['error']){
                    return $thumbrecodearr['error'];
                }
            }
            //如果文件为特殊格式类型，则先生成大图，再生成目标图片
            if(!$original && in_array($data['ext'],explode(',',getglobal('config/pichomespecialimgext')))){
                //查找是否有原图
                $originalpath = DB::result_first("select path from %t where rid =%s and thumbsign = 1 and thumbstatus = 1",array('thumb_record',$data['rid']));
                //如果没有原图尝试生成原图
                if(!$originalpath){
                   IO::getThumb($path,getglobal('config/pichomethumlargwidth'),getglobal('config/pichomethumlargheight'),1,1,1);
                    $originalpath = DB::result_first("select path from %t where rid =%s and thumbsign = 1 and thumbstatus = 1",array('thumb_record',$data['rid']));
                }
                //如果有原图
                if($originalpath){
                    $thumbpath =  IO::createThumbByOriginal($originalpath,$data,$width,$height,$thumbtype,$original,$tmpfile,$extraparams,$filesize);
                    if($thumbpath) {
                        if (!$tmpfile) {
                            C::t('thumb_record')->update($thumbrecorddata['id'], array('thumbstatus' => 1, 'dateline' => time(), 'path' => $thumbpath));
                            C::t('pichome_resources')->update($data['rid'], array('hasthumb' => 1));
                        }
                    }
                }else{
                    $thumbpath = geticonfromext($data['ext'], $data['type']);
                    if (!$tmpfile) {
                        C::t('thumb_record')->update($thumbrecorddata['id'], array('thumbstatus' => -1, 'dateline' => time(), 'path' => $thumbpath));
                        C::t('pichome_resources')->update($data['rid'], array('hasthumb' => -1));
                    }
                }

                $img = IO::getFileUri($thumbpath);
                if ($returnurl) return $img;
                else IO::output_thumb($img);

            }
            if($original){
                $thumbrecodearr = [
                    'rid' =>$data['rid'],
                    'width' => $width,
                    'height' => $height,
                    'filesize' => $data['filesize'] ? $data['filesize'] : $data['size'],
                    'thumbtype' => $thumbtype,
                    'dateline' => TIMESTAMP,
                    'thumbsign'=>$original,
                    'ext'=>$data['ext']
                ];
                //缩略图记录表数据
                $thumbrecorddata = C::t('thumb_record')->insert($thumbrecodearr, 1);
                if($thumbrecorddata['thumbstatus'] > 0){
                    $img = IO::getFileUri($thumbrecorddata['path']);
                    if ($returnurl) return $img;
                    else IO::output_thumb($img);

                }
            }


            if(!$create){
                $img =  geticonfromext($data['ext'], $data['type']);
                if ($returnurl) return $img;
                else IO::output_thumb($img);
            }else{
                $filepath = $data['realpath'];
                $thumbpath =  IO::createThumbByOriginal($filepath,$data,$width,$height,$thumbtype,$original,$tmpfile,$extraparams,$filesize);
                if($thumbpath){
                    if(!$tmpfile) {
                        C::t('thumb_record')->update($thumbrecorddata['id'], array('thumbstatus' => 1, 'dateline' => time(), 'path' => $thumbpath));
                        if(!$original) C::t('pichome_resources')->update($data['rid'],array('hasthumb'=>1));
                    }
                    $img = IO::getFileUri($thumbpath);
                }else{
                    $data['tmpfile'] = $tmpfile;
                    $data['thumbwidth'] = $width;
                    $data['thumbheight'] = $height;
                    $data['original'] = $original;
                    $data['thumbtype'] = $thumbtype;
                    $data['extraparams'] = $extraparams;
                    //如果符合挂载点生成规则

                    $thumbpath = Hook::listen('pichomethumb',$data,null,false,true);
                    if(!$thumbpath[0]){
                        $img =geticonfromext($data['ext'], $data['type']);
                        if(!$tmpfile){
                            C::t('thumb_record')->update($thumbrecorddata['id'], array('thumbstatus' => -1, 'dateline' => time(), 'path' => $img));
                            C::t('pichome_resources')->update($data['rid'],array('hasthumb'=>-1));
                        }
                    }else{
                        $img = $thumbpath[0];
                        if($img){
                            if(strpos($img,':') === false){
                                $bz = 'dzz';
                            }else{
                                $patharr = explode(':',$img);
                                $bz = $patharr[0];
                                $did = $patharr[1];
                            }
                            if(!is_numeric($did) || $did < 2){
                                $bz = 'dzz';
                            }
                            if($bz == 'dzz'){
                                $rootpath = str_replace(BS,'/',DZZ_ROOT);
                                $img = str_replace($rootpath,'',$img);
                                $img = str_replace('./','/',$img);
                                $img = str_replace('//','/',$img);
                                $img = ltrim($img,'/');
                                $img = 'dzz::'.$img;
                            }

                            if(!$tmpfile){
                                C::t('thumb_record')->update($thumbrecorddata['id'], array('thumbstatus' => 1, 'dateline' => time(), 'path' => $img));
                                if(!$original) C::t('pichome_resources')->update($data['rid'],array('hasthumb'=>1));
                            }
                        }else{
                            echo 'aaaa';die;
                            if(!$tmpfile){
                                C::t('thumb_record')->update($thumbrecorddata['id'], array('thumbstatus' => -1, 'dateline' => time(), 'path' => $img));
                                if(!$original) C::t('pichome_resources')->update($data['rid'],array('hasthumb'=>-1));
                            }
                        }

                    }
                }
                if ($returnurl) return $img;
                else IO::output_thumb($img);
            }
        }
        
        private function watermark($fileurl, $target = '', $extraparams)
        {
            global $_G;
            if (!($_G['setting']['watermarkstatus'] || $extraparams['position_text'] || $extraparams['position'])) {
                return false;
            }
            if ($target) {
                $target_attach = $_G['setting']['attachdir'] . './' . $target;
                $targetpath = dirname($target_attach);
                dmkdir($targetpath);
                if (!file_put_contents($target_attach, file_get_contents($fileurl))) {
                    return false;
                }
                $fileurl = $target_attach;
            }
            
            if (!$extraparams['watermarktext'] && !$extraparams['watermarktype']) {//生成水印
                $image = new image();
                $watermark = $image->Watermark($fileurl);
            } else {
                if ($extraparams['watermarktext']) {//生成自定义文本的文本水印
                    $params1 = array();
                    $params1['watermarktype'] = 'text';
                    if ($extraparams['position_text']) {
                        $params2['watermarkstatus'] = intval($extraparams['position_text']);
                    }
                    
                    $params1['watermarktext'] = $_G['setting']['watermarktext'];
                    $params1['watermarktext']['text'] = $extraparams['watermarktext'];
                    $image1 = new image($params1);
                    $watermark1 = $image1->Watermark($fileurl);
                    
                }
                if ($extraparams['watermarktype']) {//生成图片水印
                    $params2 = array();
                    $params2['watermarktype'] = $extraparams['watermarktype'];
                    if ($extraparams['position']) {
                        $params2['watermarkstatus'] = intval($extraparams['position']);
                    }
                    $image2 = new image($params2);
                    $watermark2 = $image2->Watermark($fileurl);
                }
            }
            return true;
        }

        //检查名称是否重复
        public function check_name_repeat($name, $pfid)
        {
            return DB::result_first("select rid from " . DB::table('resources') . " where name='{$name}' and  pfid='{$pfid}'");
        }
        
        //过滤文件名称
        public function name_filter($name)
        {
            return str_replace(array('/', '\\', ':', '*', '?', '<', '>', '|', '"', "\n"), '', $name);
        }
        
        //获取不重复的目录名称
        public function getFolderName($name, $pfid)
        {
            static $i = 0;
            $name = self::name_filter($name);
            //echo("select COUNT(*) from ".DB::table('folder')." where fname='{$name}' and  pfid='{$pfid}'");
            if (DB::result_first("select COUNT(*) from %t where fname=%s and  pfid=%d and isdelete<1", array('folder', $name, $pfid))) {
                $name = preg_replace("/\(\d+\)/i", '', $name) . '(' . ($i + 1) . ')';
                $i += 1;
                return self::getFolderName($name, $pfid);
            } else {
                return $name;
            }
        }
        
        //获取不重复的文件名称
        public function getFileName($name, $pfid)
        {
            static $i = 0;
            $name = self::name_filter($name);
            if (DB::result_first("select COUNT(*) from %t where type!='folder' and name=%s and isdelete<1 and pfid=%d", array('resources', $name, $pfid))) {
                $ext = '';
                $namearr = explode('.', $name);
                if (count($namearr) > 1) {
                    $ext = $namearr[count($namearr) - 1];
                    unset($namearr[count($namearr) - 1]);
                    $ext = $ext ? ('.' . $ext) : '';
                }
                $tname = implode('.', $namearr);
                $name = preg_replace("/\(\d+\)/i", '', $tname) . '(' . ($i + 1) . ')' . $ext;
                $i += 1;
                return self::getFileName($name, $pfid);
            } else {
                return $name;
            }
        }
        
        
        //根据文件名创建顶级目录
        public function createTopFolderByFname($fname, $perm = 0, $params = array(), $ondup = 'newcopy')
        {
            global $_G;
            $folderparams = array('innav', 'fsperm', 'disp', 'iconview', 'display', 'flag', 'default', 'perm', 'gid');
            $data = array();
            if (($ondup == 'overwrite') && ($folder = C::t('folder')->fetch_topby_fname($fname))) {//如果目录下有同名目录
                $data['folderarr'] = $folder;
                return $data;
            } else $fname = self::getFolderName($fname, 0); //重命名
            
            $flag = $params['flag'] ? $params['flag'] : 'folder';
            $folder_set = array();
            if ($flag != 'folder' && ($fset = Hook::listen('foldertemplate_getfolder_by_flag', $flag, null, true))) {
                $folder_set = is_array($fset) ? $fset : array();
            }
            $top = array(
                'pfid' => 0,
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'perm' => $perm ? $perm : ($folder_set['perm'] ? $folder_set['perm'] : 0),
                'fsperm' => $folder_set['fsperm'] ? $folder_set['fsperm'] : 0,
                'fname' => $fname,
                'flag' => $flag,
                'disp' => $folder_set['disp'] ? $folder_set['disp'] : 0,
                'iconview' => $folder_set['iconview'] ? $folder_set['iconview'] : 4,
                'innav' => 0,
                'isdelete' => 0,
                'gid' => intval($params['gid']),
                'dateline' => TIMESTAMP
            );
            foreach ($params as $k => $v) {
                if (in_array($k, $folderparams)) {
                    $top[$k] = $v;
                }
            }
            if ($topfid = DB::result_first("select fid from " . DB::table('folder') . " where uid='{$_G[uid]}' and fname = '{$top[fname]}' and flag='{$top[flag]}' ")) {
                C::t('folder')->update($topfid, $top);
            } else {
                $appid = $params['appid'] ? $params['appid'] : 0;
                $folderattr = array();
                foreach ($params as $k => $v) {
                    if (in_array($k, $folderparams)) {
                        $top[$k] = $v;
                    } else {
                        $folderattr[$k] = $v;
                    }
                }
                $topfid = C::t('folder')->insert($top, $appid);
                if ($folderattr) {
                    C::t('folder_attr')->insert_data_by_fid($topfid, $folderattr);
                }
                
            }
            $data['folderarr'] = C::t('folder')->fetch_by_fid($topfid);
            return $data;
        }
        
        //创建目录
        public function CreateFolder($pfid, $fname, $perm = 0, $params = array(), $ondup = 'newcopy', $force = false)
        {
            global $_G, $_GET;
            
            $folderparams = array('innav', 'fsperm', 'disp', 'iconview', 'display', 'flag', 'default', 'perm', 'gid');
            if ($pfid == 0) {
                return self::createTopFolderByFname($fname, $perm, $params, $ondup);
            }
            $processname = 'CF_' . $pfid;
            process_waiting($processname);
            $fname = self::name_filter($fname);
            
            if (!$folder = C::t('folder')->fetch($pfid)) {//DB::fetch_first("select fid,pfid,iconview,disp,gid,perm_inherit from %t where fid=%d", array('folder', $pfid))) {
                dzz_process::unlock($processname);
                return array('error' => lang('parent_directory_not_exist'));
            }
            if (!$force && !perm_check::checkperm_Container($pfid, 'folder')) {
                dzz_process::unlock($processname);
                return array('error' => lang('no_privilege'));
            }
            if (($ondup == 'overwrite') && ($rid = self::getRepeatIDByName($fname, $pfid, true))) {//如果目录下有同名目录
                $data = array();
                $data['icoarr'] = C::t('resources')->fetch_by_rid($rid);
                $data['folderarr'] = self::getFolderByIcosdata($data['icoarr']);
                dzz_process::unlock($processname);
                return $data;
            } else $fname = self::getFolderName($fname, $pfid); //重命名
            $path = C::t('resources_path')->fetch_pathby_pfid($folder['fid']);
            //如果flag!=='folder'，使用此flag的默认设置
            //根据pfid找flag
            $isproject = 0;//是否是项目；
            if (empty($params['flag'])) {
                
                if (defined('VAPP_ROOTFID')) {//是虚拟应用
                    if (VAPP_ROOTFID == $folder['fid'] && $_G['vapp']['new'] > 0) {//是虚拟应用根目录时//是群组类时
                        
                        $tfids = $_G['vapp']['tfids_folder'];
                        $ft = array_shift($tfids);
                        if ($ft['flag']) $params['flag'] = $ft['flag'];
                        else $params['flag'] = $folder['flag'];
                        
                        //创建群组
                        $orgarr = array(
                            'orgname' => C::t('organization')->get_uniqueName_by_forgid($pfid, $fname),
                            'aid' => 0,
                            'desc' => '',
                            'type' => 1,
                            'dateline' => TIMESTAMP,
                            'maxspacesize' => 0,
                            'manageon' => 1,
                            'diron' => 1,
                            'forgid' => $folder['gid']
                        );
                        if ($gid = C::t('organization')->insert($orgarr, 1)) {
                            C::t('organization')->setPathkeyByOrgid($gid);
                            C::t('organization_user')->insert_by_orgid($gid, getglobal('uid'));
                            C::t('organization_admin')->insert(getglobal('uid'), $gid, 2);
                            $params['gid'] = $gid;
                            $isproject = 1;
                        }
                    } else {
                        $tfids = $_G['vapp']['ftfids_folder'];
                        $ft = array_shift($tfids);
                        
                        if ($ft['flag']) $params['flag'] = $ft['flag'];
                        else $params['flag'] = $folder['flag'];
                    }
                } elseif (class_exists('dzz\vapp\classes\vapplist')) {
                    $topgid = C::t('organization')->getTopOrgid($folder['gid']);
                    $vappid = DB::result_first("select appid from %t where rgid=%d", array('vapp', $topgid));
                    if ($vapp = C::t('#vapp#vapp')->fetch($vappid)) {
                        
                        if ($folder['pfid'] == 0 && $vapp['new'] > 0) {//是创建项目时
                            
                            //创建群组
                            $orgarr = array(
                                'orgname' => C::t('organization')->get_uniqueName_by_forgid($pfid, $fname),
                                'aid' => 0,
                                'desc' => '',
                                'type' => 1,
                                'dateline' => TIMESTAMP,
                                'maxspacesize' => 0,
                                'manageon' => 1,
                                'diron' => 1,
                                'forgid' => $folder['gid']
                            );
                            if ($gid = C::t('organization')->insert($orgarr, 1)) {
                                C::t('organization')->setPathkeyByOrgid($gid);
                                C::t('organization_user')->insert_by_orgid($gid, getglobal('uid'));
                                C::t('organization_admin')->insert(getglobal('uid'), $gid, 2);
                                $params['gid'] = $gid;
                                $isproject = 1;
                            }
                            
                            
                            $tfids = explode(',', $vapp['tfids']);
                            $ft = C::t('#foldertemplate#folder_template')->fetch($tfids[0]);
                            if ($ft['flag']) $params['flag'] = $ft['flag'];
                            else $params['flag'] = $folder['flag'];
                        } else {
                            $tfids = explode(',', $vapp['ftfids']);
                            $ft = C::t('#foldertemplate#folder_template')->fetch($tfids[0]);
                            if ($ft['flag']) $params['flag'] = $ft['flag'];
                            else $params['flag'] = $folder['flag'];
                        }
                    }
                }
            }
            $flag = $params['flag'] ? $params['flag'] : (($folder['flag'] == 'organization') ? 'folder' : $folder['flag']);
            
            $folder_set = array();
            if ($isproject && $flag != 'folder' && ($fset = Hook::listen('foldertemplate_getfolder_by_flag', $flag, null, true))) {
                $folder_set = is_array($fset) ? $fset : array();
                if ($folder_set['icon'] && !isset($params['icon'])) $params['icon'] = $folder_set['icon'];
            }
            $setarr = array('fname' => $fname,
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'pfid' => $folder['fid'],
                'disp' => isset($folder_set['disp']) ? $folder_set['disp'] : $folder['disp'],
                'iconview' => isset($folder_set['iconview']) ? $folder_set['iconview'] : $folder['iconview'],
                'perm' => $perm ? $perm : ($folder_set['perm'] ? $folder_set['perm'] : 0),
                'fsperm' => $folder_set['fsperm'] ? $folder_set['fsperm'] : 0,
                'flag' => $flag,
                'dateline' => $_G['timestamp'],
                'gid' => $folder['gid'],
            
            );
            
            $folderattr = array();
            foreach ($params as $k => $v) {
                if (in_array($k, $folderparams)) {
                    $setarr[$k] = $v;
                } else {
                    $folderattr[$k] = $v;
                }
            }
            if ($setarr['fid'] = C::t('folder')->insert($setarr)) {
                $setarr['perm'] = perm_check::getPerm($setarr['fid']);
                $setarr['perm1'] = perm_check::getPerm1($setarr['fid']);
                
                if ($folderattr) {
                    C::t('folder_attr')->insert_data_by_fid($setarr['fid'], $folderattr);
                }
                if ($isproject) {//是项目时，更新群组对应的fid
                    C::t('organization')->update($setarr['gid'], array('fid' => $setarr['fid']));
                }
                $setarr['title'] = $setarr['fname'];
                $setarr['ext'] = '';
                $setarr['size'] = 0;
                if (!$params['nosub']) {
                    $flagdata = array('pfid' => $setarr['fid'], 'flag' => $setarr['flag']);
                    Hook::listen('io_CreateFolder_after', $flagdata);
                }
                
                $setarr1 = array(
                    'uid' => $_G['uid'],
                    'username' => $_G['username'],
                    'oid' => $setarr['fid'],
                    'name' => $setarr['fname'],
                    'type' => 'folder',
                    'flag' => $setarr['flag'],
                    'dateline' => $_G['timestamp'],
                    'pfid' => intval($setarr['pfid']),
                    'gid' => intval($setarr['gid']),
                    'ext' => '',
                    'size' => 0,
                );
                if ($setarr1['rid'] = C::t('resources')->insert_data($setarr1)) {
                    $setarr1['relativepath'] = $path . $setarr1['name'] . '/';
                    $setarr1['path'] = $setarr1['rid'];
                    $setarr1['dpath'] = dzzencode($setarr1['rid']);
                    $setarr1['bz'] = '';
                    if ($fid = $setarr1['pfid']) {
                        $event = 'creat_folder';
                        $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                        $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($setarr1['pfid'], $setarr1['gid']);
                        $eventdata = array(
                            'foldername' => $setarr1['name'],
                            'fid' => $setarr1['oid'],
                            'username' => $setarr1['username'],
                            'uid' => $setarr1['uid'],
                            'path' => $setarr1['path'],
                            'position' => $path,
                            'hash' => $hash
                        );
                        C::t('resources_event')->addevent_by_pfid($setarr1['pfid'], $event, 'create', $eventdata, $setarr1['gid'], $setarr1['rid'], $setarr1['name']);
                    }
                    dzz_process::unlock($processname);
                    return array('icoarr' => C::t('resources')->fetch_by_rid($setarr1['rid']), 'folderarr' => $setarr);
                } else {
                    C::t('folder')->delete_by_fid($setarr['fid'], true);
                    dzz_process::unlock($processname);
                    return array('error' => lang('data_error'));
                }
            }
            
            dzz_process::unlock($processname);
            return false;
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


        //本地文件移动到本地其它区域
        public function FileMove($rid, $pfid, $first = true, $force = false)
        {
            global $_G, $_GET;
            @set_time_limit(0);
           
            //判断目标目录是否存在
            if (!$tfolder = C::t('folder')->fetch($pfid)) {
                return array('error' => lang('target_location_not_exist'));
            }
            //获取目标路径
            $targetpdata = C::t('resources_path')->fetch_pathby_pfid($pfid, true);//目标路径
            $targetpath = $targetpdata['path'];
            $targetarr = getpath($targetpath);
            $targetstr = implode('\\', $targetarr);//路径字符串
            
            //判断文件数据是否存在
            if ($icoarr = C::t('resources')->fetch($rid)) {
                //判断移动文件是否和目标文件在同一目录
                
                if ($icoarr['pfid'] == $tfolder['fid']) {
					$return['icoarr'] = C::t('resources')->fetch_by_rid($rid);
                    $return['icoarr']['monthdate'] = dgmdate($return['icoarr']['dateline'], 'm-d');
                    $return['icoarr']['hourdate'] = dgmdate($return['icoarr']['dateline'], 'H:i');
                    unset($icoarr);
                    return $return;
				}
				
				//判断有无删除权限
				if (!$force) {
					if ($icoarr['type'] == 'folder') {
						$return = C::t('resources')->check_folder_perm($icoarr, 'delete');
						if ($return['error']) {
							return array('error' => $return['error']);
						}
					} else {
						if (!perm_check::checkperm('delete', $icoarr)) {
							return array('error' => lang('privilege'));
						}
					}

					//判断有无新建权限,如果是文件夹判断是否有文件件新建权限
					if ($icoarr['type'] == 'folder' && !perm_check::checkperm_Container($pfid, 'folder')) {
						return array('error' => lang('privilege'));
					} elseif (!perm_check::checkperm_Container($pfid, 'upload')) {
						return array('error' => lang('privilege'));
					}
				}
                    
                
                //源文件路径
                $oldpath = C::t('resources_path')->fetch_pathby_pfid($icoarr['pfid'], true);
                $oldarr = getpath($oldpath['path']);
                $oldpathstr = implode('\\', $oldarr);
                $oldpathstr = preg_replace('/dzz:(.+?):/', '', $oldpathstr);
                
                //判断空间大小
                $ogid = $icoarr['gid'];
                $gid = $tfolder['gid'];
                $oldpfid = $icoarr['pfid'];
                $oldgid = $icoarr['gid'];
                //如果是文件夹类型
                if ($icoarr['type'] == 'folder') {
					if ($ogid != $gid){
						$contains = C::t('resources')->get_contains_by_fid($icoarr['oid'], true);
						if ($contains['size'] && !SpaceSize($contains['size'], $gid)) {
							return array('error' => lang('inadequate_capacity_space'));
						}
					}
                    
                    //如果是文件夹，并且目标目录中有同名文件夹，则执行合并
                    if ($currentfid = DB::result_first("select oid from %t where pfid = %d and `name` = %s and `type` = %s and isdelete < 1",
                        array('resources', $tfolder['fid'], $icoarr['name'], 'folder'))
                    ) {
                        //移动源文件夹数据到目标目录同名文件夹
                        foreach (C::t('resources')->fetch_basicinfo_by_pfid($icoarr['oid']) as $value) {
                            try {
                                self::FileMove($value['rid'], $currentfid, false, false);
                                unset($value);
                                unset($folder);
                            } catch (Exception $e) {
                            	return array('error' => lang('movement_error'));
                            }
                        }
                        //修改分享表状态
                        C::t('shares')->change_by_rid($icoarr['rid'], '-5');
                        //删除原文件夹数据
                        C::t('resources')->delete($icoarr['rid']);
                        //删除路径表数据
                        C::t('folder')->delete_by_fid($icoarr['oid']);
                       
                        
                        //添加事件
                        $oldhash = C::t('resources_event')->get_showtpl_hash_by_gpfid($oldpfid, $oldgid);
                        $eventdata1 = array('username' => $_G['username'], 'olderposition' => $oldpathstr, 'newposition' => $targetstr, 'foldername' => $icoarr['name'], 'hash' => $oldhash);
                        C::t('resources_event')->addevent_by_pfid($pfid, 'moved_folder', 'movedfolder', $eventdata1, $gid, $rid, $icoarr['name']);
                        $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($icoarr['pfid'], $ogid);
                        $eventdata2 = array('username' => $_G['username'], 'newposition' => $targetstr, 'foldername' => $icoarr['name'], 'hash' => $hash);
                        C::t('resources_event')->addevent_by_pfid($oldpfid, 'move_folder', 'movefolder', $eventdata2, $ogid, $rid, $icoarr['name']);
                    } else {
                        //查询源文件夹数据
                        
                        if ($folder = C::t('folder')->fetch($icoarr['oid'])) {
                            $icoarr['name'] = self::getFolderName($icoarr['name'], $tfolder['fid']);
                            $folder['uid'] = $_G['uid'];
                            $folder['username'] = $_G['username'];
                            $folder['gid'] = $gid;
                            $folder['pfid'] = $pfid;
                            $folder['fname'] = $icoarr['name'];
                            $updatefids = array();
                            $fids = C::t('resources_path')->fetch_folder_containfid_by_pfid($folder['fid']);
                            $folderinfo = array(
                                'uid' => $_G['uid'],
                                'username' => $_G['username'],
                                'gid' => $gid
                            );
                           /* $rids = array();
                            $oresources = array();
                            foreach (DB::fetch_all("select * from %t where pfid in(%n) or oid in(%n)", array('resources', $fids, $fids)) as $v) {
                                $rids[] = $v['rid'];
                                $oresources[] = $v;
                            }*/
                            
                            //修改文件夹表数据和resources表数据
                            // DB::update('resources', array('oid' => $folder['fid'], 'pfid' => $pfid, 'gid' => $gid, 'uid' => $_G['uid'], 'username' => $_G['username']), array('rid' => $rid)
                            if (C::t('folder')->update($folder['fid'], $folder) &&
                                C::t('resources')->update_by_rid($rid, array('oid' => $folder['fid'], 'pfid' => $pfid, 'gid' => $gid, 'uid' => $_G['uid'], 'username' => $_G['username']))
                            ) {
                                
                                //更改文件夹路径，此处使用模型表中更改solr路径
                                C::t('resources_path')->update_pathdata_by_fid($folder['fid'], $pfid);
                              
                                if ($fids) {
                                    //修改资源表数据
                                    //DB::update('resources', $folderinfo, "pfid IN(" . dimplode($fids) . ")");
                                    C::t('resources')->update_by_pfids($fids, $folderinfo);
                                    //更改动态表数据
                                    DB::update('resources_event', $folderinfo, "pfid IN(" . dimplode($fids) . ")");
                                    //更改folder表数据
                                    // DB::update('folder', $folderinfo, "pfid IN(" . dimplode($fids) . ")");
                                    C::t('folder')->update_by_pfids($fids, $folderinfo);
                                }
                                if ($ogid!=$gid && $contains['size'] > 0) {
                                    SpaceSize(-$contains['size'], $ogid, 1);
                                    SpaceSize($contains['size'], $gid, 1);
                                }
                                //修改分享表状态
                                //C::t('shares')->change_by_rid($rids, '-5');
                                //更改文件夹动态归属位置
                                DB::update('resources_event', array(
                                    'uid' => $_G['uid'],
                                    'username' => $_G['username'],
                                    'gid' => $gid,
                                    'pfid' => $pfid
                                ), array('pfid' => $folder['fid']));
                                
                                //添加事件
                                $oldhash = C::t('resources_event')->get_showtpl_hash_by_gpfid($oldpfid, $oldgid);
                                $eventdata1 = array('username' => $_G['username'], 'olderposition' => $oldpathstr, 'newposition' => $targetstr, 'foldername' => $icoarr['name'], 'hash' => $oldhash);
                                C::t('resources_event')->addevent_by_pfid($pfid, 'moved_folder', 'movedfolder', $eventdata1, $gid, $rid, $icoarr['name']);
                                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($icoarr['pfid'], $ogid);
                                $eventdata2 = array('username' => $_G['username'], 'newposition' => $targetstr, 'foldername' => $icoarr['name'], 'hash' => $hash);
                                C::t('resources_event')->addevent_by_pfid($oldpfid, 'move_folder', 'movefolder', $eventdata2, $ogid, $rid, $icoarr['name']);
                            }
                        } else {
                            return array('error', lang('folder_not_exist'));
                        }
                    }
                    
                } else {
                    $totalsize = 0;
                    if ($icoarr['vid'] > 0) {
                        $totalsize = DB::result_first("select sum(size) from %t where rid = %s", array('resources_version', $icoarr['rid']));
                    } else {
                        $totalsize = $icoarr['size'];
                    }
                    if ($ogid != $gid && $totalsize && !SpaceSize($totalsize, $gid)) {
                        return array('error' => lang('inadequate_capacity_space'));
                    }
                    
                    //如果不是文件夹判断文件名重复
                   
                        
                    $icoarr['name'] = self::getFileName($icoarr['name'], $tfolder['fid']);
                   
                    $icoarr['gid'] = $gid;
                    $icoarr['uid'] = $_G['uid'];
                    $icoarr['username'] = $_G['username'];
                    $icoarr['pfid'] = $pfid;
                    $icoarr['isdelete'] = 0;
                    if (C::t('resources')->update_by_rid($icoarr['rid'], $icoarr)) {
                        $solrindexdata = array('rid' => $icoarr['rid'], 'data' => $icoarr);
                        Hook::listen('movefile_after', $solrindexdata);
                        //更改文件动态归属位置
                        C::t('resources_event')->update_position_by_rid($icoarr['rid'], $icoarr['pfid'], $icoarr['gid']);
                        //修改分享表状态
                        C::t('shares')->change_by_rid($icoarr['rid'], '-5');
                        //添加移动文件动态
                        $oldhash = C::t('resources_event')->get_showtpl_hash_by_gpfid($oldpfid, $oldgid);
                        $eventdata1 = array('username' => $_G['username'], 'olderposition' => $oldpathstr, 'newposition' => $targetstr, 'filename' => $icoarr['name'], 'hash' => $oldhash);
                        C::t('resources_event')->addevent_by_pfid($pfid, 'moved_file', 'movedfile', $eventdata1, $gid, $rid, $icoarr['name']);
                        
                        $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($icoarr['pfid'], $ogid);
                        $eventdata2 = array('username' => $_G['username'], 'newposition' => $targetstr, 'filename' => $icoarr['name'], 'hash' => $hash);
                        C::t('resources_event')->addevent_by_pfid($oldpfid, 'move_file', 'movefile', $eventdata2, $ogid, $rid, $icoarr['name']);
                    }
                    if ($ogid != $gid) {
                        if ($totalsize > 0) {
                            SpaceSize(-$totalsize, $ogid, 1);
                            SpaceSize($totalsize, $gid, 1);
                        }
                    }
                    if (!$first) {
                        //addtoconfig($icoarr);
                    }
                }
                
            } /*else {
                C::t('resources')->update_by_rid($icoarr['rid'], array('isdelete' => 0, 'deldateline' => 0));
                //addtoconfig($icoarr);
            }*/
            if ($icoarr['type'] == 'folder') C::t('folder')->update($icoarr['oid'], array('isdelete' => 0));
            $return['icoarr'] = C::t('resources')->fetch_by_rid($icoarr['rid']);
            $return['icoarr']['monthdate'] = dgmdate($return['icoarr']['dateline'], 'm-d');
            $return['icoarr']['hourdate'] = dgmdate($return['icoarr']['dateline'], 'H:i');
            unset($icoarr);
            return $return;
            return array('error' => lang('movement_error') . '！');
        }
        
        //本地文件复制到本地其它区域
        public function FileCopy($rid, $pfid, $first = true, $force = false)
        {
            global $_G, $_GET;
            if (!$tfolder = DB::fetch_first("select * from " . DB::table('folder') . " where fid='{$pfid}'")) {
                return array('error' => lang('target_location_not_exist'));
            }
            if ($icoarr = C::t('resources')->fetch_by_rid($rid)) {
                
                unset($icoarr['rid']);
                //判断当前文件有没有拷贝权限；
                if (!$force) {
                    if ($icoarr['type'] == 'folder') {
                        $permcheck = C::t('resources')->check_folder_perm($icoarr, 'copy');
                        if ($permcheck['error']) {
                            return array('error' => $permcheck['error']);
                        }
                    } else {
                        if (!perm_check::checkperm('copy', $icoarr)) {
                            return array('error' => lang('privilege'));
                        }
                    }
                    
                    //判断当前目录有无添加权限
                    if (!perm_check::checkperm_Container($pfid, 'upload')) {
                        return array('error' => lang('privilege'));
                    }
                }
                $success = 0;
                $gid = DB::result_first("select gid from " . DB::table('folder') . " where fid='{$pfid}'");
                $targetpatharr = C::t('resources_path')->fetch_pathby_pfid($pfid, true);//目标路径
                $targetpath = $targetpatharr['path'];
                
                if ($icoarr['type'] == 'folder') {
                    $foldercontains = C::t('resources')->get_contains_by_fid($icoarr['oid']);
                    if (!SpaceSize($foldercontains['size'], $gid)) {
                        return array('error' => lang('inadequate_capacity_space'));
                    }
                    if ($icoarr['pfid'] == $pfid) {//判断源文件位置和目标位置是否相同,如果相同则生成副本
                        
                        $icoarr['name'] = $icoarr['name'] . lang('duplicate');
                        
                        if ($ricoid = self::getRepeatIDByName($icoarr['name'], $pfid, ($icoarr['type'] == 'folder') ? true : false)) {//如果目录下有同名文件
                            
                            $icoarr['name'] = self::getFolderName($icoarr['name'], $pfid);
                            
                        }
                    }
                    //查询原文件夹是否存在
                    if ($folder = C::t('folder')->fetch($icoarr['oid'])) {
                        
                        //如果目标目录中有同名文件夹，并且源文件位置和目标位置不在同一目录，则将源文件夹中文件放入该目录下
                        if ($icoarr['pfid'] != $pfid && $currentinfo = DB::fetch_first("select oid,rid from %t where pfid = %d and `name` = %s and `type` = %s and isdelete < 1",
                                array('resources', $tfolder['fid'], $icoarr['name'], 'folder'))
                        ) {
                            $currentfid = $currentinfo['oid'];
                            //复制源文件夹数据到目标目录同名文件夹
                            foreach (C::t('resources')->fetch_by_pfid($icoarr['oid']) as $value) {
                                try {
                                    self::FileCopy($value['rid'], $currentfid, false, $force);
                                } catch (Exception $e) {
									
                                }
                            }
                            $data = C::t('resources')->fetch_by_rid($currentinfo['rid']);
                            $return['folderarr'] = $data;
                            $icoarr['rid'] = $data['rid'];
                            
                        } else {//如果目标目录中不存在同名文件夹或者存在同名文件夹而源文件位置和目标位置在同一目录，执行创建
                            if ($data = self::createFolderByPath($icoarr['name'], $pfid)) {//根据文件夹名字和当前文件夹路径创建文件夹
                                foreach (C::t('resources')->fetch_by_pfid($folder['fid']) as $value) {//查询原文件夹中文件
                                    try {
                                        self::FileCopy($value['rid'], $data['pfid'], false, $force);//复制原文件夹中文件到新文件夹
                                    } catch (Exception $e) {
                                    }
                                }
                                $return['folderarr'] = $data['folderarr'][0];
                                
                                $icoarr['rid'] = $data['icoarr'][0]['rid'];
                            }
                        }
                        
                    } else {
                        return array('error', lang('folder_not_exist'));
                    }
                } else {
                    //判断空间大小是否足够
                    if (!SpaceSize($icoarr['size'], $gid)) {
                        return array('error' => lang('inadequate_capacity_space'));
                    }
                    //判断文件名重复
                    if ($icoarr['pfid'] == $pfid) {
                        $namestr = $icoarr['name'];
                        $ext = '';
                        $namearr = explode('.', $namestr);
                        if (count($namearr) > 1) {
                            $ext = $namearr[count($namearr) - 1];
                            unset($namearr[count($namearr) - 1]);
                            $ext = $ext ? ('.' . $ext) : '';
                        }
                        $tname = implode('.', $namearr);
                        $icoarr['name'] = $tname . lang('duplicate') . $ext;
                    }
                    
                    if ($ricoid = self::getRepeatIDByName($icoarr['name'], $pfid, ($icoarr['type'] == 'folder') ? true : false)) {//如果目录下有同名文件
                        
                        $icoarr['name'] = self::getFileName($icoarr['name'], $pfid);
                        
                    }
                    $setarr = array(
                        'name' => $icoarr['name'],
                        'oid' => $icoarr['oid'],
                        'uid' => getglobal('uid'),
                        'username' => getglobal('username'),
                        'pfid' => $pfid,
                        'gid' => $tfolder['gid'],
                        'type' => $icoarr['type'],
                        'dateline' => TIMESTAMP,
                        'ext' => $icoarr['ext'],
                        'size' => $icoarr['size'],
                        'vid' => 0,
                    );
					
				
                    //新建文件
                    if ($icoarr['rid'] = C::t('resources')->insert_data($setarr)) {
						
                        $sourceattrdata = array(
                            'postip' => $_G['clientip'],
                            'title' => $setarr['filename'],
                            'aid' => isset($icoarr['aid']) ? $icoarr['aid'] : '',
                            'img' => $icoarr['img'],
                        );
						
                        if (C::t('resources_attr')->insert_attr($icoarr['rid'], $setarr['vid'], $sourceattrdata)) {//插入属性表
                            if ($icoarr['aid']) {
                                $attach = C::t('attachment')->fetch($icoarr['aid']);
                                C::t('attachment')->update($icoarr['aid'], array('copys' => $attach['copys'] + 1));//增加使用数
                            }
							
                           /* if ($icoarr['oid']) {
                                $attach = C::t('collect')->fetch($icoarr['oid']);
                                C::t('collect')->update($icoarr['oid'], array('copys' => $attach['copys'] + 1));//增加使用数
                            }*/
                            $icoarr['path'] = $targetpath . $setarr['name'];
                            $event = 'creat_file';
                            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($pfid, $setarr['gid']);
                            $eventdata = array(
                                'title' => $setarr['name'],
                                'aid' => $icoarr['aid'],
                                'username' => $setarr['username'],
                                'uid' => $setarr['uid'],
                                'position' => preg_replace('/dzz:(.+?):/', '', $targetpath),
                                'hash' => $hash
                            );
                            C::t('resources_event')->addevent_by_pfid($pfid, $event, 'create', $eventdata, $setarr['gid'], $icoarr['rid'], $icoarr['name']);
                        } else {
                            C::t('resources')->delete_by_rid($icoarr['rid']);
                            return array('error' => lang('data_error'));
                        }
                    }
                }
                if ($icoarr['rid']) {
                    if ($icoarr['size'] > 0) {
                        SpaceSize($icoarr['size'], $gid, 1, $icoarr['uid']);
                    }
					$return['icoarr'] = C::t('resources')->fetch_by_rid($icoarr['rid']);
					$return['icoarr']['monthdate'] = dgmdate($return['icoarr']['dateline'], 'm-d');
					$return['icoarr']['hourdate'] = dgmdate($return['icoarr']['dateline'], 'H:i');
					Hook::listen('createafter_addindex_getvideo', $return['icoarr']);
					Hook::listen('createafter_addindex', $return['icoarr']);
                    return $return;
                   
                    
                } else {
                    return array('error' => lang('files_allowed_copy'));
                }
                return array('error' => 'copy error');
            }
        }

        public function shenpiCreateFile($fid, $path, $attach)
        {
            $data = self::createFolderByPath($path, $fid);;
            return self::uploadToattachment($attach, $data['pfid']);
        }
    }
