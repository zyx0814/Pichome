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

    private function getPartInfo($content_range)
    {
        $arr = array();
        if (!$content_range) {
            $arr['ispart'] = false;
            $arr['iscomplete'] = true;
        } elseif (is_array($content_range)) {
            $arr['ispart'] = true;
            $partsize = isset($content_range[4])?intval($content_range[4]):getglobal('setting/maxChunkSize');
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

    public function uploadStream($file, $filename, $appid, $pfid = '', $relativePath = '', $content_range = array(), $params = array())
    {
        $data = array();
        //处理目录(没有分片或者最后一个分片时创建目录
        $arr = self::getPartInfo($content_range);
        // echo $pfid;die;
        if ($arr['iscomplete']) {
            if($relativePath && $relativePath != '.' && $relativePath != '..'){
                $fdata = C::t('pichome_folder')->createfolerbypath($appid, $relativePath, $pfid);
                if (isset($fdata['error'])) {
                    return array('error' => $data['error']);
                }
            }elseif($pfid){
                $folderdata = C::t('pichome_folder')->fetch($pfid);
                $fdata = ['fid'=>$pfid,'level'=>$folderdata['level']];
            }else{
                $fdata = ['fid'=>'','level'=>0];

            }

        }else{
            if($relativePath && $relativePath != '.' && $relativePath != '..'){
                $fdata = C::t('pichome_folder')->createfolerbypath($appid, $relativePath, $pfid);
                if (isset($fdata['error'])) {
                    return array('error' => $data['error']);
                }
            }elseif($pfid){
                $folderdata = C::t('pichome_folder')->fetch($pfid);
                $fdata = ['fid'=>$pfid,'level'=>$folderdata['level']];

            }else{
                $fdata = ['fid'=>'','level'=>0];

            }
        }
       /* if (substr($filename, -7) == '.folder') {
            if(!$relativePath){
                $patharr = explode('/',$filename);
                array_pop($patharr);
                $relativePath = ($patharr) ? implode('/',$patharr):'';
            }
            $fdata = C::t('pichome_folder')->createfolerbypath($appid, $relativePath, $pfid);
            var_dump($fdata);
            die;
            if (isset($fdata['error'])) {
                return array('error' => $data['error']);
            }
            return $fdata;
        }*/
        $arr['flag'] = $appid . '_' . $relativePath;
        //获取文件内容
        $fileContent = '';
        if (!$handle = fopen($file, 'rb')) {
            return array('error' => lang('open_file_error'));
        }
        while (!feof($handle)) {
            $fileContent .= fread($handle, 8192);
        }

        fclose($handle);
        if ($arr['ispart']) {
            $re = self::upload($fileContent, $appid, $fdata['fid'], $filename, $fdata['level'], $arr);

            if ($arr['iscomplete']) {
                if (empty($re['error'])) {
                    $data['icoarr'][] = $re;
                    $data['folder'] =C::t('pichome_folder')->fetch_allfolder_by_fid($fdata['fid']);

                    return $data;
                } else {
                    $data['error'] = $re['error'];
                    return $data;
                }
            } else {
                return true;
            }
        } else {

            $re = self::upload($fileContent, $appid, $fdata['fid'], $filename, $fdata['level']);
            if (empty($re['error'])) {
                if ($re['type'] == 'image' && $re['aid']) {
                    $re['imgpath'] = DZZSCRIPT . '?mod=io&op=thumbnail&path=' . dzzencode('attach::' . $re['aid']);
                }
                $re['monthdate'] = dgmdate($re['dateline'], 'm-d');
                $re['hourdate'] = dgmdate($re['dateline'], 'H:i');
                $re['pfid'] = $data['pfid'];
                $re['colect'] = 0;
                $data['icoarr'][] = $re;
                $data['folder'] =C::t('pichome_folder')->fetch_allfolder_by_fid($fdata['fid']);
                return $data;
            } else {
                $data['error'] = $re['error'];
                return $data;
            }
        }
    }

    public function upload_by_content($fileContent, $path, $fid = '', $filename, $level = 0, $partinfo = array())
    {
        return self::upload($fileContent, $path, $fid, $filename, $level = 0, $partinfo);
    }

    /**
     * 上传文件
     * 注意：此方法适用于上传不大于2G的单个文件。
     * @param string $fileContent 文件内容字符串
     * @param string $fid 上传文件的目标保存目录fid
     * @param string $fileName 文件名
     * @param string $ondup overwrite：表示覆盖同名文件；newcopy：表示生成文件副本并进行重命名，命名规则为“文件名_日期.后缀”。
     * @param boolean $isCreateSuperFile 是否分片上传
     * @return string
     */
    public function upload($fileContent, $appid, $fid = '', $filename, $level = 0, $partinfo = array(), $ondup = 'newcopy')
    {

        global $_G;
        $filename = self::name_filter($filename);
        /* if (($ondup == 'overwrite') && ($rid = self::getRepeatIDByName($filename, $fid))) {//如果目录下有同名文件
             return self::overwriteUpload($fileContent, $rid, $filename, $partinfo);//覆盖
         } else $nfilename = self::getFileName($filename, $fid); //重命名*/

        if ($partinfo['ispart']) {
            if ($partinfo['partnum'] == 1) {
                if ($target = self::getCache($partinfo['flag'] . '_' . md5($filename))) {
                    file_put_contents($_G['setting']['attachdir'] . $target, '');
                } else {
                    $pathinfo = pathinfo($filename);
                    $ext = strtolower($pathinfo['extension']);
                    $target = $this->getPath($ext ? ('.' . $ext) : '', 'dzz');
                    self::saveCache($partinfo['flag'] . '_' . md5($filename), $target);
                }
            } else {
                if(!$target = self::getCache($partinfo['flag'] . '_' . md5($filename))){
					  return array('error' => lang('cache_file_error'));
				}
            }
            /* if(!filesize($fileContent)){
                  return array('error' => lang('cache_file_error'));
             }*/

            if (file_put_contents($_G['setting']['attachdir'] . $target, $fileContent, FILE_APPEND) === false) {
                return array('error' => lang('cache_file_error'));
            }

            if (!$partinfo['iscomplete']) return true;
            else {
                self::deleteCache($partinfo['flag'] . '_' . md5($filename));

            }
        } else {

            $pathinfo = pathinfo($filename);
            $ext = strtolower($pathinfo['extension']);
            $target = $this->getPath($ext ? ('.' . $ext) : '', 'dzz');

            if (!empty($fileContent) && file_put_contents($_G['setting']['attachdir'] . $target, $fileContent) === false) {
                return array('error' => lang('cache_file_error'));
            }
        }

        /*//判断空间大小
        $gid = DB::result_first("select gid from %t where fid=%d", array('folder', $fid));
        if (!SpaceSize(filesize($_G['setting']['attachdir'] . $target), $gid)) {
            @unlink($_G['setting']['attachdir'] . $target);
            return array('error' => lang('inadequate_capacity_space'));
        }*/

        if ($attach = $this->save($target, $filename)) {
            if ($attach['error']) {
                return array('error' => $attach['error']);
            } else {

                return $this->uploadToattachment($attach, $appid, $fid, $level);
            }
        } else {
            return array('error' => 'Could not save uploaded file. The upload was cancelled, or server error encountered');
        }

    }

    public function getPath($ext, $dir = 'dzz')
    {
        global $_G;
        if ($ext && in_array(trim($ext, '.'), $_G['setting']['unRunExts'])) {
            $ext = '.dzz';
        }
        $subdir = $subdir1 = $subdir2 = '';
        $subdir1 = date('Ym');
        $subdir2 = date('d');
        $subdir = $subdir1 . '/' . $subdir2 . '/';
        $target1 = $dir . '/' . $subdir . 'index.html';
        $target = $dir . '/' . $subdir;
        $target_attach = $_G['setting']['attachdir'] . $target1;
        $targetpath = dirname($target_attach);
        dmkdir($targetpath);
        return $target . date('His') . '' . strtolower(random(16)) . $ext;
    }

     private static function getCache($path)
        {
            $cachekey = 'dzz_upload_' . md5($path);
			if(memory('check')){
				return memory('get',$cachekey);
			}else{
				if ($cache = C::t('cache')->fetch($cachekey)) {
					return $cache['cachevalue'];
				} else {
					return false;
				}
			}
        }

        private static function saveCache($path, $str)
        {
            global $_G;
            $cachekey = 'dzz_upload_' . md5($path);
			if(memory('check')){
				return memory('set',$cachekey,$str,60*60*24);
			}else{
				C::t('cache')->insert(array(
					'cachekey' => $cachekey,
					'cachevalue' => $str,
					'dateline' => $_G['timestamp'],
				), false, true);
			}
		}

        private static function deleteCache($path)
        {

            $cachekey = 'dzz_upload_' . md5($path);
			if(memory('check')){
				memory('rm',$cachekey);
			}else{
            	C::t('cache')->delete($cachekey);
			}
        }

    public function save($target, $filename = '')
    {
        global $_G;
        $filepath = $_G['setting']['attachdir'] . $target;
        $md5 = md5_file($filepath);
        $filesize = fix_integer_overflow(filesize($filepath));
        if ($md5 && $attach = DB::fetch_first("select * from %t where md5=%s and filesize=%d", array('attachment', $md5, $filesize))) {
            $attach['filename'] = $filename;
            $pathinfo = pathinfo($filename);
            $ext = $pathinfo['extension'] ? $pathinfo['extension'] : '';
            $attach['filetype'] = strtolower($ext);
            @unlink($filepath);
            // unset($attach['attachment']);
            return $attach;
        } else {
            $pathinfo = pathinfo($filename);
            $ext = $pathinfo['extension'] ? $pathinfo['extension'] : '';

            $pathinfo1 = pathinfo($target);
            $ext_dzz = strtolower($pathinfo1['extension']);
            if ($ext_dzz == 'dzz') {
                $unrun = 1;
            } else {
                $unrun = 0;
            }
            $filesize = filesize($filepath);
            $remote = 0;

            $attach = array(

                'filesize' => $filesize,
                'attachment' => $target,
                'filetype' => strtolower($ext),
                'filename' => $filename,
                'remote' => $remote,
                'copys' => 0,
                'md5' => $md5,
                'unrun' => $unrun,
                'dateline' => $_G['timestamp'],
            );
            if ($attach['aid'] = C::t('attachment')->insert($attach, 1)) {
                /* $remoteid = io_remote::getRemoteid($attach);
                 //主动模式生成缩略图
                 // if ($_G['setting']['thumb_active'] > 0) {
                 try {
                     foreach ($_G['setting']['thumbsize'] as $key => $value) {
                         if ($key != 'middle') getThumburl('attach::' . $attach['aid'], $key);
                     }
                 } catch (Exception $e) {
                     //   }
                 }

                 C::t('local_storage')->update_usesize_by_remoteid($attach['remote'], $attach['filesize']);*/
                //if ($remoteid > 1) dfsockopen($_G['localurl'] . 'misc.php?mod=movetospace&aid=' . $attach['aid'] . '&remoteid=0', 0, '', '', false, '', 0.1);
                //unset($attach['attachment']);
                return $attach;
            } else {
                return false;
            }
        }
    }

    public function uploadToattachment($attach, $appid, $fid = '', $level = 0)
    {

        global $_G;
        //如果当前库有该文件
        if ($rid = DB::result_first("select rid from %t where path = %s and appid = %s ", array('pichome_resources_attr', $attach['aid'], $appid))) {

            $resourcesdata = C::t('pichome_resources')->fetch($rid);
            if($resourcesdata['isdelete']){
                $rsetarr = [
                    'lastdate' => TIMESTAMP * 1000,
                    'appid' => $appid,
                    'uid' => $_G['uid'],
                    'username' => $_G['username'],
                    'apptype' => 3,
                    'size' => $resourcesdata['size'],
                    'type' => $resourcesdata['type'],
                    'ext' => $resourcesdata['ext'],
                    'mtime' => TIMESTAMP * 1000,
                    'dateline' => TIMESTAMP * 1000,
                    'btime' => TIMESTAMP * 1000,
                    'width' => $resourcesdata['width'],
                    'height' => $resourcesdata['height'],
                    'lastdate' => TIMESTAMP,
                    'level' => isset($folderdata['level']) ? $folderdata['level'] : 0,
                    'name' => $resourcesdata['name'],
                    'fids' => $folderdata['fid'] ? $folderdata['fid'] : ''
                ];

                if ($rsetarr['rid'] = C::t('pichome_resources')->insert_data($rsetarr)) {//插入主表
                    Hook::listen('lang_parse',$rsetarr,['saveResourcesLangData',[$rsetarr['rid']]]);
                    //获取附属表数据
                    $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                    $attrdata['rid'] = $rsetarr['rid'];
                    $attrdata['appid'] = $appid;
                    $attrdata['searchval'] = $rsetarr['name'];
                    C::t('attachment')->addcopy_by_aid($attrdata['path']);//增加图片使用数
                    C::t('pichome_resources_attr')->insert($attrdata);
                    //目录数据
                    if ($folderdata['fid']) {
                        $frsetarr = ['appid' => $appid, 'rid' => $rsetarr['rid'], 'fid' => $folderdata['fid']];
                        C::t('pichome_folderresources')->insert($frsetarr);
                        //C::t('pichome_folder')->add_filenum_by_fid($folderdata['fid'], 1);
                    }
                    //缩略图数据
                    $thumbrecorddata = C::t('thumb_record')->fetch($rid);
                    $thumbrecorddata['rid'] = $rsetarr['rid'];

                    C::t('thumb_record')->insert_data($thumbrecorddata);

                    //颜色数据
                    foreach (DB::fetch_all("select * from %t where rid = %s", array('pichome_palette', $rid)) as $v) {
                        $v['rid'] = $rsetarr['rid'];
                        unset($v['id']);
                        C::t('pichome_palette')->insert($v);
                    }
                    C::t('pichome_vapp')->addcopy_by_appid($appid);
                    $data = C::t('pichome_resources')->fetch_by_rid($rsetarr['rid']);
                    $data['addnum'] = 1;
                    $data['folder'] = C::t('pichome_folder')->fetch_allfolder_by_fid($folderdata['fid']);
                    return $data;
                }else{
                    return array('error' => lang('data_error'));
                }
            }else{
                $nfids = explode(',', $resourcesdata['fids']);
                $iscurrentfolder = 1;
                if (!in_array($fid, $nfids)) {
                    $iscurrentfolder = 0;
                    $nfids[] = $fid;
                }
                $icoarr = [
                    'lastdate' => TIMESTAMP * 1000,
                    'appid' => $appid,
                    'uid'=>$_G['uid'],
                    'username'=>$_G['username'],
                    'apptype' => 3,
                    'mtime' => TIMESTAMP * 1000,
                    'dateline' => TIMESTAMP * 1000,
                    'btime' => TIMESTAMP * 1000,
                    'lastdate' => TIMESTAMP,
                    'name' => $resourcesdata['name'],
                    'fids' => $nfids ? implode(',', $nfids) : '',
                ];

                if (C::t('pichome_resources')->update($rid, $icoarr)) {//插入主表
                    //目录数据
                    if (!$iscurrentfolder && $fid) {
                        $frsetarr = ['appid' => $appid, 'rid' => $rid, 'fid' => $fid];;
                        C::t('pichome_folderresources')->insert($frsetarr);
                       // C::t('pichome_folder')->add_filenum_by_fid($fid, 1);
                    }
                    $data = C::t('pichome_resources')->fetch_by_rid($rid);

                    $data['addnum'] = ($iscurrentfolder) ? 0:1;
                    $data['onlyfolderadd'] = 1;
                    $data['folder'] = C::t('pichome_folder')->fetch_allfolder_by_fid($fid);
                    return $data;
                }else{
                    return array('error' => lang('data_error'));
                }

            }

        }
        elseif ($rid = DB::result_first("select rid from %t where path = %s ", array('pichome_resources_attr',$attach['aid']))) {//如果当前库没有该文件，但其它库有
            //获取原文件基本数据
            $resourcesdata = C::t('pichome_resources')->fetch($rid);
            $rsetarr = [
                'lastdate' => TIMESTAMP * 1000,
                'appid' => $appid,
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'apptype' => 3,
                'size' => $resourcesdata['size'],
                'type' => $resourcesdata['type'],
                'ext' => $resourcesdata['ext'],
                'mtime' => TIMESTAMP * 1000,
                'dateline' => TIMESTAMP * 1000,
                'btime' => TIMESTAMP * 1000,
                'width' => $resourcesdata['width'],
                'height' => $resourcesdata['height'],
                'lastdate' => TIMESTAMP,
                'level' => isset($folderdata['level']) ? $folderdata['level'] : 0,
                'name' => $resourcesdata['name'],
                'fids' => $folderdata['fid'] ? $folderdata['fid'] : ''
            ];

            if ($rsetarr['rid'] = C::t('pichome_resources')->insert_data($rsetarr)) {//插入主表
                Hook::listen('lang_parse',$rsetarr,['saveResourcesLangData',[$rsetarr['rid']]]);
                //获取附属表数据
                $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                $attrdata['rid'] = $rsetarr['rid'];
                $attrdata['appid'] = $appid;
                $attrdata['searchval'] = $rsetarr['name'];
                C::t('attachment')->addcopy_by_aid($attrdata['path']);//增加图片使用数
                C::t('pichome_resources_attr')->insert($attrdata);
                //目录数据
                if ($folderdata['fid']) {
                    $frsetarr = ['appid' => $appid, 'rid' => $rsetarr['rid'], 'fid' => $folderdata['fid']];
                    C::t('pichome_folderresources')->insert($frsetarr);
                    C::t('pichome_folder')->add_filenum_by_fid($folderdata['fid'], 1);
                }
                //缩略图数据
                $thumbrecorddata = C::t('thumb_record')->fetch($rid);
                $thumbrecorddata['rid'] = $rsetarr['rid'];

                C::t('thumb_record')->insert_data($thumbrecorddata);

                //颜色数据
                foreach (DB::fetch_all("select * from %t where rid = %s", array('pichome_palette', $rid)) as $v) {
                    $v['rid'] = $rsetarr['rid'];
                    unset($v['id']);
                    C::t('pichome_palette')->insert($v);
                }
                C::t('pichome_vapp')->addcopy_by_appid($appid);
                $data = C::t('pichome_resources')->fetch_by_rid($rsetarr['rid']);
                $data['folder'] = C::t('pichome_folder')->fetch_allfolder_by_fid($folderdata['fid']);
                $data['addnum'] = 1;
                return $data;
            }else{
                return array('error' => lang('data_error'));
            }

        }else{
            $imginfo = getimagesize($_G['setting']['attachdir'] . $attach['attachment']);
            if (preg_match('/^(.*?)(\.[^.]+)$/', $attach['filename'], $matches)) {
                // 获取不带扩展名的文件名
                $filename = $matches[1];
            }else{
                $filename = $attach['filename'];
            }
            $setarr = [
                'appid' => $appid,
                'apptype' => 3,
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'size' => $attach['filesize'],
                'type' => getTypeByExt($attach['filetype']),
                'ext' => $attach['filetype'],
                'mtime' => TIMESTAMP * 1000,
                'dateline' => TIMESTAMP * 1000,
                'btime' => TIMESTAMP * 1000,
                'width' => isset($imginfo[0]) ? $imginfo[0] : 0,
                'height' => isset($imginfo[1]) ? $imginfo[1] : 0,
                'lastdate' => TIMESTAMP,
                'level' => $level ? $level : 0,
                'name' => $filename,
                'fids' => $fid
            ];

            if ($setarr['rid'] = C::t('pichome_resources')->insert_data($setarr)) {//插入主表
                Hook::listen('lang_parse',$setarr,['saveResourcesLangData',[$setarr['rid']]]);
                C::t('attachment')->update($attach['aid'], array('copys' => $attach['copys'] + 1));//增加图片使用数
                //属性表数据
                $attrdata = [
                    'rid' => $setarr['rid'],
                    'appid' => $appid,
                    'path' => $attach['aid'],
                    'searchval' => $setarr['name']
                ];

                C::t('pichome_resources_attr')->insert($attrdata);
                //目录数据
                if ($fid) {
                    $frsetarr = ['appid' => $appid, 'rid' => $setarr['rid'], 'fid' => $fid];
                    C::t('pichome_folderresources')->insert($frsetarr);
                }

                C::t('pichome_vapp')->addcopy_by_appid($appid);
            } else {
                return array('error' => 'upload failer');
            }

            if ($setarr['rid']) {
                C::t('pichome_vapp')->update($appid,['dateline'=>TIMESTAMP]);
                $setarr['fsize'] = formatsize($setarr['size']);
                $setarr['ftype'] = getFileTypeName($setarr['type'], $setarr['ext']);
                $setarr['fdateline'] = dgmdate($setarr['dateline']);
               /* $thumbparams = ['rid' => $setarr['rid'], 'hash' => VERHASH, 'download' => 1,
                    'hasthumb' => 0, 'lastdate' => $setarr['lastdate']];*/
                $setarr['icondata'] =  false;
                $setarr['width'] = ($setarr['width']) ? intval($setarr['width']):900;
                $setarr['height'] = ($setarr['height']) ? intval($setarr['height']):900;
                $setarr['aid'] = $attach['aid'];
                $setarr['dpath'] =  dzzencode($setarr['rid'], '', 0, 0);
                $setarr['realpath'] = IO::getStream('attach::'.$attach['aid']);
                $setarr['addnum'] = 1;
                return $setarr;
            } else {
                return array('error' => lang('data_error'));
            }
        }


    }

    //检查文件是否存在
    public function checkfileexists($path, $isdir = false)
    {
        if (!$isdir) {
            $path = IO::getStream($path);
            if(is_file($path)){
                return true;
            }else{
                if(!$handle = fopen($path,'r')){
                    fclose($handle);
                    return false;
                }else{
                    fclose($handle);
                }
                return true;
            }
        } else{
            $path = str_replace('dzz::','',$path);
            if(is_dir($path)) return true;
            elseif(is_dir(getglobal('setting/attachdir').$path)) return true;
            else return false;

        }
        return false;
    }

    public function getMeta($path, $getimagedata = 0)
    {
        if(strpos($path, 'attach::') === 0){
            global $Types;
            $attachment = C::t('attachment')->fetch(intval(str_replace('attach::', '', $path)));
            $bz = io_remote::getBzByRemoteid($attachment['remoteid']);
            $data = array(
                'name' => $attachment['filename'],
                'ext' => $attachment['filetype'],
                'size' => $attachment['filesize'],
                'dateline' => $attachment['dateline'],
                'remoteid'=>$attachment['remoteid'],
                'bz'=>$bz,
                'path'=>$bz.$attachment['attachment'],
                'aid'=>$attachment['aid']
            );
            $imginfo = array();
            if ($getimagedata && (in_array($attachment['filetype'], $Types['commonimage']) || in_array($attachment['filetype'], $Types['image']))) {
                //获取图片信息，以取得宽高
                $imgpath  =IO::getStream($path);
                $imgdata = @getimagesize($imgpath);
                $imginfo['width'] = isset($imgdata[0]) ? $imgdata[0] : 0;
                $imginfo['height'] = isset($imgdata[1]) ? $imgdata[1] : 0;
            }
            return array_merge($data, $imginfo);
        }elseif(preg_match('/^\w{32}$/i',$path)){
            $data = C::t('pichome_resources')->fetch_data_by_rid($path);
            Hook::listen('lang_parse',$data,['getResourcesLangData']);
            return $data;
        } else {
           // $path = $this->parsePath($path);
            global $Types;
            $pathinfo = pathinfo($path);
            $ext = strtolower($pathinfo['extension']);
            $fileinfo = array(
                'name' => $this->getbasename($path),
                'ext' => $ext,
                'size' => filesize($path),
                'bz'=>'dzz::',
                'remoteid'=>0,
                'dateline' => filemtime($path)
            );
            $imginfo = array();
            if ($getimagedata && (in_array($ext, $Types['commonimage']) || in_array($ext, $Types['image']))) {
                //获取图片信息，以取得宽高
                $imgdata = @getimagesize($path);
                $imginfo['width'] = isset($imgdata[0]) ? $imgdata[0] : 0;
                $imginfo['height'] = isset($imgdata[1]) ? $imgdata[1] : 0;
            }
            return array_merge($fileinfo, $imginfo);
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

        if ($obz == 'dzz::') {
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
    //查找目录下的同名文件
    //@param string $filename  文件名称
    //@param number $fid  目录id
    //@param bool $isfolder  查找同名目录
    //return icoid  返回icoid
    public function getRepeatIDByName($filename, $fid, $isfolder = false)
    {

        $sql = "pfid=%d and name=%s and isdelete<1";
        if ($isfolder) $sql .= " and type='folder'";
        else $sql .= " and type!='folder'";
        if ($rid = DB::result_first("select rid from %t where $sql ", array('resources', $fid, $filename))) {
            return $rid;
        } else return false;
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
        $path = str_replace('dzz::', '', $path);
        return $path;
    }


    //获取文件流地址
    public function getFileUri($path, $fop = '')
    {
        global $_G;
        if (strpos($path, 'attach::') === 0) {
            $attach = C::t('attachment')->fetch(intval(str_replace('attach::', '', $path)));
            if($attach['remote'] > 0){
                $bz = io_remote::getBzByRemoteid($attach['remote']);
                $uri =  IO::getFileUri($bz.$attach['attachment']);
            }else{
                $uri =   getglobal('siteurl').getglobal('setting/attachurl') . $attach['attachment'];
            }

        }elseif(preg_match('/^\w{32}$/',$path)){
            $resources = C::t('pichome_resources')->fetch_data_by_rid($path);
            $uri =  IO::getFileUri($resources['path']);
        }elseif(strpos($path, 'dzz::') === 0){
            $path = str_replace('dzz::','',$path);
            $uri =  getglobal('siteurl').getglobal('setting/attachurl') . $path;
        }else{
            $uri =  getglobal('siteurl').'index.php?mod=io&op=getfileStream&path='.dzzencode($path);
        }
        return $uri;
    }

    //获取文件的真实地址
    public function getStream($path, $fop = '')
    {
        global $_G;
        if (strpos($path, 'attach::') === 0) {
            $attach = C::t('attachment')->fetch(intval(str_replace('attach::', '', $path)));
            if($attach['remote'] > 0){
                $bz = io_remote::getBzByRemoteid($attach['remote']);
                return IO::getStream($bz.$attach['attachment']);
            }else{
                return getglobal('setting/attachdir') . $attach['attachment'];
            }

        }elseif(strpos($path, 'dzz::') === 0){
            $path = str_replace('dzz::','',$path);
            return getglobal('setting/attachdir') . $path;
        }elseif(preg_match('/^\w{32}$/',$path)){
            $resources = C::t('pichome_resources')->fetch_data_by_rid($path);
            return IO::getStream($resources['path']);
        }else{
            return $path;

        }
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
        $stream = IO::getStream($filepath);
        $handle = fopen($stream, 'rb');
        $succ = 1;
        while (!feof($handle)) {
            $fileContent = fread($handle, 8192);
            if (file_put_contents($path, $fileContent, FILE_APPEND) === false) {
                $succ = 0;
            }
            unset($fileContent);
        }
        fclose($handle);
        if (!$succ) {
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
                //print_r($params2);die;
                $image2 = new image($params2);
                $watermark2 = $image2->Watermark($fileurl);
            }
        }
        return true;
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
    function webpinfo($file) {
        if (!is_file($file)) {
            return false;
        } else {
            $file = realpath($file);
        }

        $fp = fopen($file, 'rb');
        if (!$fp) {
            return false;
        }

        $data = fread($fp, 90);

        fclose($fp);
        unset($fp);

        $header_format = 'A4Riff/' . // 获取4个字符的字符串
            'I1Filesize/' . // 获取一个整数（文件大小，但不是实际大小）
            'A4Webp/' . // 获取4个字符的字符串
            'A4Vp/' . // 获取4个字符的字符串
            'A74Chunk'; // 获取74个字符的字符串
        $header = unpack($header_format, $data);
        unset($data, $header_format);

        if (!isset($header['Riff']) || strtoupper($header['Riff']) !== 'RIFF') {
            return false;
        }
        if (!isset($header['Webp']) || strtoupper($header['Webp']) !== 'WEBP') {
            return false;
        }
        if (!isset($header['Vp']) || strpos(strtoupper($header['Vp']), 'VP8') === false) {
            return false;
        }

        if (
            strpos(strtoupper($header['Chunk']), 'ANIM') !== false ||
            strpos(strtoupper($header['Chunk']), 'ANMF') !== false
        ) {
            $header['Animation'] = true;
        } else {
            $header['Animation'] = false;
        }

        if (strpos(strtoupper($header['Chunk']), 'ALPH') !== false) {
            $header['Alpha'] = true;
        } else {
            if (strpos(strtoupper($header['Vp']), 'VP8L') !== false) {
                // 如果是VP8L，假设该图像会有透明度
                // 如Google文档中描述的WebP简单文件格式无损部分
                $header['Alpha'] = true;
            } else {
                $header['Alpha'] = false;
            }
        }

        unset($header['Chunk']);
        return $header;
    }
    public function createThumbByOriginal($path, $data, $width = 0, $height = 0, $thumbtype = 1, $original = 0, $extraparams = array(), $filesize = 0)
    {
        global $_G;
        //获取文件地址
        $fileuri = IO::getStream($path);
        $filedirpathinfo = pathinfo($path);
        $filedirextensionarr = explode('?', $filedirpathinfo['extension']);
        $ext = strtolower($filedirextensionarr[0]);

        if ($data['bz'] != 'dzz::') {
            $cachefile = $_G['setting']['attachdir'] . './cache/' . md5($data['path']) . '.' . $data['ext'];
            $handle = fopen($cachefile, 'w+');
            $fp = fopen($fileuri, 'rb');
            while (!feof($fp)) {
                fwrite($handle, fread($fp, 8192));
            }
            fclose($handle);
            fclose($fp);
            $fileuri = $cachefile;
        }
        $thumbpath = false;
        //如果服务器处理完成后，路径非图片类文件的时候，直接获取文件后缀对应的图片
        if (!in_array($ext, array('png', 'jpg', 'gif', 'jpeg','webp')) || !$imginfo = @getimagesize($fileuri)) {
            $thumbpath = false;
        } else {
            if($extraparams['istmp']){
                $targetpath =  'cache/'.md5($path).'_'.$width.'_'.$height.'.'.($extraparams['ext']?$extraparams['ext']:'webp');

                if(is_file($_G['setting']['attachdir'].$targetpath)){
                    return $targetpath;
                }
            }else{
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

                if(in_array($ext,array('gif'))) {
                    $thumbext = 'gif';
                }elseif($extraparams['ext']){
                    $thumbext=$extraparams['ext'];
                }else{
                    $thumbext = 'webp';
                }
                $thumbpathdir = self::getthumbpath('pichomethumb');
                if($data['aid']) $thumbname = md5($data['aid'].$data['thumbsign'].$extraflag).'_'.$data['thumbsign'].'.'.$thumbext;
                else $thumbname = md5($path.$data['thumbsign'].$extraflag).'_'.$data['thumbsign'].'.'.$thumbext;
                $targetpath = $thumbpathdir.$thumbname;
            }
            $target = $targetpath;

            if($ext == 'webp'){
                $info = $this->webpinfo($fileuri);
                if ($info !== false) {
                    if ($info['Animation'] || $info['Alpha']) {
                        $target_attach = $_G['setting']['attachdir'] . './' . $target;
                        $targetpath = dirname($target_attach);
                        dmkdir($targetpath);

                        if(copy($fileuri, $target_attach)){
                            $thumbpath = $target_attach;
                            $thumbpath =  $target;
                        }else{
                            $thumbpath = false;
                        }
                    }
                }else{
                    $thumbpath =  false;
                }
            }elseif($ext == 'gif'){
                $target_attach = $_G['setting']['attachdir'] . './' . $target;
                $targetpath = dirname($target_attach);
                dmkdir($targetpath);
                if(copy($fileuri, $target_attach)){
                    $thumbpath = $target_attach;
                    $thumbpath =  $target;
                }else{
                    $thumbpath = false;
                }
            }
            if(!$thumbpath){
                //图片小于最小水印最小设置时，不生成水印
                if ($extraparams['nomark'] ||($_G['setting']['IsWatermarkstatus'] == 0 || ($imginfo[0] < $_G['setting']['watermarkminwidth'] || $imginfo[1] < $_G['setting']['watermarkminheight']))) {
                    $nomark = 1;
                }
                //返回原图的时候 或者图片小于缩略图宽高的不生成直接返回原图
                if ($original || ($ext == 'wbep' && ($imginfo[0] < $width || $imginfo[1] < $height))) {
                    $target_attach = $_G['setting']['attachdir'] . './' . $target;
                    $targetpath = dirname($target_attach);
                    dmkdir($targetpath);

                    if(copy($fileuri, $target_attach)){
                        $thumbpath = $target_attach;
                        if (!$nomark) self::watermark($target_attach = $_G['setting']['attachdir'] . './' . $target, '', $extraparams);
                        $thumbpath =  $target;
                    }
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
                            if (!$nomark) self::watermark($target_attach = $_G['setting']['attachdir'] . './' . $target, '', $extraparams);
                            $thumbpath =  $target;
                        } else {
                            $thumbpath = false;
                        }
                    } catch (\Exception $e) {
                        $thumbpath = false;

                    }
                }
            }
            }


        //echo $thumbpath;die;
        if($cachefile){
            @unlink($cachefile);
        }
        //如果是生成临时图
        if($extraparams['istmp']){
            return $thumbpath;
        }
        if($thumbpath){
            $defaultspace = $_G['setting']['defaultspacesetting'];
            if($defaultspace['bz'] != 'dzz'){
                $cloudpath = $defaultspace['bz'].':'.$defaultspace['did'] . ':' .$thumbpath;
                $return = IO::moveThumbFile($cloudpath,$thumbpath);
                //$thumbpath = $return;
            }
            if(isset($return['error'])){
                return false;
            }
        }
        return $thumbpath;

    }

    //删除
    //当文件在回收站时，彻底删除；
    //finaldelete 真实删除，不放入回收站
    //$force 强制删除，不受权限控制
    public function Delete($path, $isdir = false)
    {
        if ($isdir) {
            $path = self::parsePath($path);
            removedirectory($path);
        } else {
            $filurl = IO::getStream($path);
            @unlink($filurl);
        }
        return true;
    }
    public function gettmpThumb($path, $width = 0, $height = 0, $returnurl = false, $thumbtype = 1, $extraparams = array()){
        global $_G;
        if (!$data = IO::getMeta($path)) return false;
        $filepath = $data['path'];

       //水印图md5或者水印文字
        $watermd5 = '';
        if($extraparams['watermarkstatus']){
            $watermd5 = !$extraparams['watermarktext'] ? $_G['setting']['watermd5']:($extraparams['watermarktext'] ? $extraparams['watermarktext']:$_G['setting']['watermarktext']);
        }
        $defaultspace = $_G['setting']['defaultspacesetting'];
        //如果文件为特殊格式类型，则先生成大图，再生成目标图片
        if (!in_array($data['ext'], array('jpg','png','jpeg','gif','webp'))) {
            //查找是否有原图
            $cachedata = [];
                //如果有aid从缓存表获取数据
                if($data['aid']){
                    //将数据插入缓存表
                    $thumbarr = [
                        'width'=>0,
                        'height'=>0,
                        'aid'=>$data['aid'],
                        'thumbtype'=>$thumbtype,
                        'watermd5'=>$watermd5
                    ];
                    $cachedata = C::t('thumb_cache')->fetch_data_by_thumbparam($thumbarr);
                }

                if($cachedata){
                    $bz = io_remote::getBzByRemoteid($cachedata['remoteid']);
                    if($data['rid']){
                        $thumbarr = [
                            'opath' => $bz.$cachedata['path'],
                            'oremoteid'=>$cachedata['remoteid'],
                            'ocacheid'=>$cachedata['id'],
                        ];
                        C::t('thumb_record')->update($data['rid'], $thumbarr);
                        $attr = array('width' => $cachedata['wdith'], 'height' => $cachedata['height']);
                        C::t('pichome_resources')->update($data['rid'], $attr);
                    }
                    $filepath = $bz.$cachedata['path'];
                }
                else{
                    $data['original'] = 1;
                    $data['thumbtype'] = $thumbtype;
                    $data['extraparams'] = $extraparams;
                    //获取原图
                    $thumbpathdata = Hook::listen('pichomethumb', $data, null, false, true);

                    $fileurl = $thumbpathdata[0];
                    if ($fileurl) {
                        //将原图计入缓存表
                        $defaultbz = io_remote::getBzByRemoteid($defaultspace['remoteid']);
                        $filepath = $defaultbz.$fileurl;
                        $infourl = IO::getStream($filepath);
                        $info = @getimagesize($infourl);
                        if($data['aid']){
                            $thumbarr = [
                                'width'=>isset($info[0]) ? intval($info[0]):0,
                                'height'=>isset($info[1]) ? intval($info[1]):0,
                                'aid'=>$data['aid'],
                                'thumbtype'=>$thumbtype,
                                'thumbsign'=>'original',
                                'watermd5'=>$watermd5,
                                'path'=>$fileurl,
                                'remoteid'=>$defaultspace['did']
                            ];
                            $cachedata = C::t('thumb_cache')->insertdata($thumbarr);
                        }
                        if($data['rid']) {
                            C::t('thumb_record')->update($data['rid'],['opath'=>$filepath,'ocacheid'=>$cachedata['id']]);
                           if($info) {
                               $attr = array('width' =>isset($info[0]) ? intval($info[0]):0, 'height' => isset($info[1]) ? intval($info[1]):0);
                               C::t('pichome_resources')->update($data['rid'], $attr);
                           }
                        }
                    }else{
                        $thumbpath=geticonfromext($data['ext'], $data['type']);
                        if($returnurl == 1){
                            return  $thumbpath;
                        }elseif($returnurl == 2){
                            return  $thumbpath;
                        }else {
                            IO::output_thumb($thumbpath);
                        }
                    }

                }
            }

        $extraparams['istmp'] = 1;
        $extraparams['nomark'] = 1;

        $thumbpath = IO::createThumbByOriginal($filepath, $data, $width, $height, $thumbtype, 0,  $extraparams);

        $bz = io_remote::getBzByRemoteid($defaultspace['remoteid']);
        if($thumbpath)$thumbpath = $bz.$thumbpath;
        if($thumbpath){
            if($returnurl == 1){
                return  IO::getFileUri($thumbpath);
            }elseif($returnurl == 2){
                return  $thumbpath;
            }else {
                $img = IO::getStream($thumbpath);
                IO::output_thumb($img);
            }
        }else{
            return false;
        }


    }
    //获取缩略图
    public function getThumb($path, $thumbsign='', $original = false, $returnurl = false, $create = 0,  $thumbtype = 1, $extraparams = array(), $filesize = 0)
    {
        global $_G;
        if (!$data = IO::getMeta($path)) return false;
        $filesize = $data['size'];
        $filepath = $data['path'];
        $wp = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarkstatus']:'';
        $wt = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarktype']:'';
        $wcontent = $_G['setting']['IsWatermarkstatus'] ? ($_G['setting']['watermarktype'] == 'png' ? $_G['setting']['waterimg']:$extraparams['position_text']):'';
        //水印图md5或者水印文字
        $watermd5 = '';
        if($extraparams['watermarkstatus']){
            $watermd5 = !$extraparams['watermarktext'] ? $_G['setting']['watermd5']:($extraparams['watermarktext'] ? $extraparams['watermarktext']:$_G['setting']['watermarktext']);
        }

        //水印参数处理
        $extraparams['position_text'] = $extraparams['position_text'] ? $extraparams['position_text']:$wcontent;
        $extraparams['position'] = $extraparams['position'] ? $extraparams['position']:$wp;
        $extraparams['watermarkstatus'] = $extraparams['watermarkstatus'] ?$extraparams['watermarkstatus']:$_G['setting']['IsWatermarkstatus'];
        $extraparams['watermarktype'] = $extraparams['watermarktype'] ?$extraparams['watermarktype']:$wt;
        $extraparams['watermarktext'] = $extraparams['watermarktext'] ? $extraparams['watermarktext']:'';
        //宽高值获取，原图默认为0
        $data['thumbsign'] = $thumbsign;
        $defaultspace = $_G['setting']['defaultspacesetting'];
        if($thumbsign){
            $width =   $_G['setting']['thumbsize'][$thumbsign]['width'];
            $height =   $_G['setting']['thumbsize'][$thumbsign]['height'];
        }else{
            $width = $height = 0;
        }
        $thumbpath = '';
        //如果是库文件，记录thumb_record表
        if($data['rid']){
            $thumbrecodearr = [
                'rid' => $data['rid'],
                'width' => $data['width'] ? intval($data['width']):0,
                'height' => $data['height'] ? intval($data['height']):0,
                'lwidth' => $_G['setting']['thumbsize']['large']['width'],
                'lheight' => $_G['setting']['thumbsize']['large']['height'],
                'swidth' => $_G['setting']['thumbsize']['small']['width'],
                'sheight' =>$_G['setting']['thumbsize']['small']['height'],
                'filesize' => $data['filesize'] ? $data['filesize'] : $data['size'],
                'ext' => $data['ext'],
                'lwaterposition'=>$wp,
                'lwatertype'=>$wt,
                'lwatercontent'=>$wcontent,
                'swaterposition'=>$wp,
                'swatertype'=>$wt,
                'swatercontent'=>$wcontent
            ];
            //缩略图记录表数据
            $thumbrecorddata = C::t('thumb_record')->insert_data($thumbrecodearr);
            if($original && $thumbrecorddata['opath']){//原图
                $thumbpath = $thumbrecorddata['opath'];
            } elseif($thumbsign == 'small' && $thumbrecorddata['sstatus']){//小图
                $thumbpath = $thumbrecorddata['spath'];
            }elseif($thumbsign == 'large' && $thumbrecorddata['lstatus']){//大图
                $thumbpath = $thumbrecorddata['lpath'];
            }
        }else{//如果是aid记录thumb_cache表
            $thumbarr = [
                'width'=>$width,
                'height'=>$height,
                'aid'=>$data['aid'],
                'thumbtype'=>$thumbtype,
                'watermd5'=>$watermd5
            ];
            $cachedata = C::t('thumb_cache')->fetch_data_by_thumbparam($thumbarr);
            if($cachedata){
                $bz = io_remote::getBzByRemoteid($cachedata['remoteid']);
                $thumbpath = $bz.$cachedata['path'];
            }
        }

        //如果没有强制生成根据查询结果返回
        if (!$create) {
            if(!$thumbpath) $thumbpath = geticonfromext($data['ext'], $data['type']);
            if ($returnurl) return $thumbpath;
            else IO::output_thumb($thumbpath);
        } else {

            if(!$thumbpath){
                $cachedata = [];
                //从缓存表获取数据
                if($data['aid']){
                    //尝试从缓存表获取数据
                    $thumbarr = [
                        'width'=>$width,
                        'height'=>$height,
                        'aid'=>$data['aid'],
                        'thumbtype'=>$thumbtype,
                        'watermd5'=>$watermd5,
                    ];

                    $cachedata = C::t('thumb_cache')->fetch_data_by_thumbparam($thumbarr);
                }

            }
            if($cachedata){
                $bz = io_remote::getBzByRemoteid($cachedata['remoteid']);
                $thumbpath = $bz.$cachedata['path'];
                if($thumbsign == 'small'){
                    $thumbarr = [
                        'spath'=>$thumbpath,
                        'sstatus'=>1,
                        'scacheid'=>$cachedata['id'],
                        'sremoteid'=>$cachedata['remoteid'],
                        'sdateline'=>TIMESTAMP,
                        'schk'=>0,
                        'schktimes'=>0
                    ];
                }elseif($thumbsign == 'large'){
                    $thumbarr = [
                        'lpath'=>$thumbpath,
                        'lstatus'=>1,
                        'lcacheid'=>$cachedata['id'],
                        'lremoteid'=>$cachedata['remoteid'],
                        'ldateline'=>TIMESTAMP,
                        'lchk'=>0,
                        'lchktimes'=>0
                    ];
                }
                //插入缩略图记录表
                C::t('thumb_record')->update($data['rid'], $thumbarr);
            }
            else{
                //如果文件为特殊格式类型，则先生成大图，再生成目标图片
                if (!in_array($data['ext'], array('jpg','png','jpeg','gif','webp'))) {
                    //查找是否有原图
                    $filepath = '';
                    //如果没有原图尝试生成原图
                    if (!$thumbrecorddata['opath']) {
                        //如果有aid从缓存表获取数据
                        if($data['aid']){
                            //将数据插入缓存表
                            $thumbarr = [
                                'width'=>0,
                                'height'=>0,
                                'aid'=>$data['aid'],
                                'thumbtype'=>$thumbtype,
                                'watermd5'=>$watermd5
                            ];
                            $cachedata = C::t('thumb_cache')->fetch_data_by_thumbparam($thumbarr);
                        }

                        if($cachedata){
                            $bz = io_remote::getBzByRemoteid($cachedata['remoteid']);
                            $thumbarr = [
                                'opath' => $bz.$cachedata['path'],
                                'oremoteid'=>$cachedata['remoteid'],
                                'ocacheid'=>$cachedata['id'],
                            ];
                            if($data['rid']){
                                C::t('thumb_record')->update($data['rid'], $thumbarr);
                                $attr = array('width' => $cachedata['wdith'], 'height' => $cachedata['height']);
                                C::t('pichome_resources')->update($data['rid'], $attr);
                            }
                            $filepath = $bz.$cachedata['path'];
                        }
                        else{
                            $data['original'] = 1;
                            $data['thumbtype'] = $thumbtype;
                            $data['extraparams'] = $extraparams;

                            //获取原图
                            $thumbpathdata = Hook::listen('pichomethumb', $data, null, false, true);

                            $fileurl = $thumbpathdata[0];
                            if ($fileurl) {
                                //将原图计入缓存表
                                $defaultbz = io_remote::getBzByRemoteid($defaultspace['remoteid']);
                                $filepath = $defaultbz.$fileurl;
                                $infourl = IO::getStream($filepath);
                                $info = @getimagesize($infourl);
                                $thumbarr = [
                                    'width'=>isset($info[0]) ? intval($info[0]):0,
                                    'height'=>isset($info[1]) ? intval($info[1]):0,
                                    'aid'=>$data['aid'],
                                    'thumbtype'=>$thumbtype,
                                    'thumbsign'=>'original',
                                    'watermd5'=>$watermd5,
                                    'path'=>$fileurl,
                                    'remoteid'=>$defaultspace['did']
                                ];
                                $cachedata = C::t('thumb_cache')->insertdata($thumbarr);
                                if($data['rid']) {
                                    C::t('thumb_record')->update($data['rid'],['opath'=>$filepath,'ocacheid'=>$cachedata['id']]);
                                    $attr = array('width' => $thumbarr['width'], 'height' => $thumbarr['height']);
                                    C::t('pichome_resources')->update($data['rid'], $attr);
                                }
                            }
                            else $thumbpath = geticonfromext($data['ext'], $data['type']);
                        }
                    }
                    else{
                        $filepath = $thumbrecorddata['opath'];
                    }
                }
            }
            //创建缩略图
            if(!$thumbpath){
                $cthumbpath = IO::createThumbByOriginal($filepath, $data, $width, $height, $thumbtype, $original, $extraparams, $filesize);
                $bz = io_remote::getBzByRemoteid($defaultspace['remoteid']);
                if($cthumbpath)$thumbpath = $bz.$cthumbpath;
                if($cthumbpath){
                    $cacheid = '';
                    if($data['aid']){
                        $thumbarr = [
                            'width'=>$width,
                            'height'=>$height,
                            'aid'=>$data['aid'],
                            'thumbtype'=>$thumbtype,
                            'watermd5'=>$watermd5,
                            'path'=>$cthumbpath,
                            'remoteid'=>$defaultspace['did']
                        ];
                        $cacheid = C::t('thumb_cache')->insertdata($thumbarr);
                    }

                    if($data['rid']){
                        $thumbdataarr = [];
                        if($thumbsign == 'small'){
                            $thumbdataarr = [
                                'spath'=>$thumbpath,
                                'sstatus'=>1,
                                'schk'=>0,
                                'sremoteid'=>$defaultspace['did'],
                                'scacheid'=>$cacheid
                            ];
                        }elseif($thumbsign == 'large'){
                            $thumbdataarr = [
                                'lpath'=>$thumbpath,
                                'lstatus'=>1,
                                'lchk'=>0,
                                'lremoteid'=>$defaultspace['did'],
                                'lcacheid'=>$cacheid
                            ];
                        }
                        C::t('thumb_record')->update($data['rid'],$thumbdataarr);
                    }
                }

            }
        }
        if ($thumbpath) {
            $img = ($returnurl) ? IO::getFileUri($thumbpath):IO::getStream($thumbpath);
        } else{
            $img = geticonfromext($data['ext'], $data['type']);
        }
        if ($returnurl) return $img;
        else IO::output_thumb($img);

    }
    public function getPreviewThumb($rdata, $thumbsign='', $original = false, $returnurl = false, $create = 0,  $thumbtype = 1, $extraparams = array(), $filesize = 0)
    {
        global $_G;
        if (!$data = IO::getMeta('attach::'.$rdata['aid'])) return false;
        $filesize = $data['size'];
        $filepath = $data['path'];
        $wp = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarkstatus']:'';
        $wt = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarktype']:'';
        $wcontent = $_G['setting']['IsWatermarkstatus'] ? ($_G['setting']['watermarktype'] == 'png' ? $_G['setting']['waterimg']:$extraparams['position_text']):'';
        //水印图md5或者水印文字
        $watermd5 = '';
        if($extraparams['watermarkstatus']){
            $watermd5 = !$extraparams['watermarktext'] ? $_G['setting']['watermd5']:($extraparams['watermarktext'] ? $extraparams['watermarktext']:$_G['setting']['watermarktext']);
        }
        //水印参数处理
        $extraparams['position_text'] = $extraparams['position_text'] ? $extraparams['position_text']:$wcontent;
        $extraparams['position'] = $extraparams['position'] ? $extraparams['position']:$wp;
        $extraparams['watermarkstatus'] = $extraparams['watermarkstatus'] ?$extraparams['watermarkstatus']:$_G['setting']['IsWatermarkstatus'];
        $extraparams['watermarktype'] = $extraparams['watermarktype'] ?$extraparams['watermarktype']:$wt;
        $extraparams['watermarktext'] = $extraparams['watermarktext'] ? $extraparams['watermarktext']:'';
        //宽高值获取，原图默认为0
        $data['thumbsign'] = $thumbsign;
        $defaultspace = $_G['setting']['defaultspacesetting'];
        if($thumbsign){
            $width =   $_G['setting']['thumbsize'][$thumbsign]['width'];
            $height =   $_G['setting']['thumbsize'][$thumbsign]['height'];
        }else{
            $width = $height = 0;
        }
        $thumbpath = '';
        if($thumbsign == 'small' && $rdata['sstatus']){//小图
            $thumbpath = $rdata['spath'];
        }elseif($thumbsign == 'large' && $rdata['lstatus']){//大图
            $thumbpath = $rdata['lpath'];
        }

        // //尝试从缓存表获取数据
        if(!$thumbpath){
            $thumbarr = [
                'width'=>$width,
                'height'=>$height,
                'aid'=>$data['aid'],
                'thumbtype'=>$thumbtype,
                'watermd5'=>$watermd5,
            ];
            $cachedata = C::t('thumb_cache')->fetch_data_by_thumbparam($thumbarr);
            if($cachedata){
                $bz = io_remote::getBzByRemoteid($cachedata['remoteid']);
                $thumbpath = $bz.$cachedata['path'];
                if($thumbsign == 'small'){
                    $thumbarr = [
                        'spath'=>$thumbpath,
                        'sstatus'=>1,
                        'scacheid'=>$cachedata['id'],
                        'sremoteid'=>$cachedata['remoteid'],
                        'sdateline'=>TIMESTAMP,
                        'schk'=>0,
                        'schktimes'=>0
                    ];
                }elseif($thumbsign == 'large'){
                    $thumbarr = [
                        'lpath'=>$thumbpath,
                        'lstatus'=>1,
                        'lcacheid'=>$cachedata['id'],
                        'lremoteid'=>$cachedata['remoteid'],
                        'ldateline'=>TIMESTAMP,
                        'lchk'=>0,
                        'lchktimes'=>0
                    ];
                }
                //插入缩略图记录表
                C::t('thumb_preview')->update($rdata['id'], $thumbarr);
            }  else{
                //如果没有强制生成根据查询结果返回
                if (!$create) {
                    $thumbpath = IO::getFileUri($rdata['opath']);
                } else {
                    //创建缩略图
                    $cthumbpath = IO::createThumbByOriginal($filepath, $data, $width, $height, $thumbtype, $original, $extraparams, $filesize);
                    $bz = io_remote::getBzByRemoteid($defaultspace['remoteid']);
                    if($cthumbpath)$thumbpath = $bz.$cthumbpath;
                    if($cthumbpath){
                        $cacheid = '';
                        if($rdata['aid']){
                            $thumbarr = [
                                'width'=>$width,
                                'height'=>$height,
                                'aid'=>$rdata['aid'],
                                'thumbtype'=>$thumbtype,
                                'watermd5'=>$watermd5,
                                'path'=>$cthumbpath,
                                'remoteid'=>$defaultspace['did']
                            ];
                            $cacheid = C::t('thumb_cache')->insertdata($thumbarr);
                        }

                        if($data['rid']){
                            $thumbdataarr = [];
                            if($thumbsign == 'small'){
                                $thumbdataarr = [
                                    'spath'=>$thumbpath,
                                    'sstatus'=>1,
                                    'schk'=>0,
                                    'sremoteid'=>$defaultspace['did'],
                                    'scacheid'=>$cacheid
                                ];
                            }elseif($thumbsign == 'large'){
                                $thumbdataarr = [
                                    'lpath'=>$thumbpath,
                                    'lstatus'=>1,
                                    'lchk'=>0,
                                    'lremoteid'=>$defaultspace['did'],
                                    'lcacheid'=>$cacheid
                                ];
                            }
                            C::t('thumb_preview')->update($rdata['id'],$thumbdataarr);
                        }
                    }

                }

            }

        }



        if ($thumbpath) {
            $img = IO::getFileUri($thumbpath);
        } else{
            $img = geticonfromext($data['ext'], $data['type']);
        }
        if ($returnurl) return $img;
        else IO::output_thumb($img);
    }


    //过滤文件名称
    public function name_filter($name)
    {
        return str_replace(array('/', '\\', ':', '*', '?', '<', '>', '|', '"', "\n"), '', $name);
    }

    //下载
    public static function download($paths, $filename = '', $checkperm = 'download', $param = array())
    {
        global $_G;
        //后续兼容多个文件或者目录
        $paths = (array)$paths;

        if (count($paths) > 1) {
            exit('暂不支持批量下载');
        } else {
            $path = $paths[0];
        }

        @set_time_limit(0);
        //获取文件信息
        $datas = IO::getMeta($path);
        $path = $datas['path'];
        if (!$datas) {
            topshowmessage(lang('attachment_nonexistence'));
        }
        $icoarr['name'] = $filename ? $filename : $datas['name'];
        $attachment = $icoarr;



        if ($attachment['ext'] && strpos(strtolower($attachment['name']), $attachment['ext']) === false) {
            $attachment['name'] .= '.' . $attachment['ext'];
        }

        $bz = io_remote::getBzByRemoteid($datas['remoteid']);
        if ($bz == 'dzz') {
            $patharr = [];
            foreach ($paths as $v) {
                if (!dzzdecode($v)) $patharr[] = dzzencode($v);
                else $patharr[] = $v;
            }
            $path = implode(',', $patharr);
            return $_G['siteurl'] . 'index.php?mod=io&op=download&path=' . $path;
            exit();
        } else {
            $attachurl = IO::getStream($path);
            $pathpre = md5($datas['attachment']);
            //下载区目标位置路径
            $cloudpath = $bz . '/tmpdownload/' . $pathpre . '/' . $attachment['name'];
            //原文件位置路径
            $fpath =$datas['path'];
            //检查下载记录表是否已存在对应数据，不存在则插入数据，返回记录表id
            $recordid = C::t('filedownload_record')->chkdownloadrecord($cloudpath, $fpath, $datas, $attachurl, $datas['remoteid']);
            if ($icoarr) {
                $param['position'] = ($param['position']) ? $param['position'] : $icoarr['relpath'];
                $indexarr = array('icoarr' => $icoarr, 'param' => $param);
                Hook::listen('downloadbefore_adddownloads', $indexarr);
            }
            return $recordid;
        }
    }

    public static function localdownload($paths, $filename = '', $checkperm = 'download', $param = array())
    {
        global $_G;

        if (count($paths) > 1) {
            self::zipdownload($paths, $filename, $checkperm, $param);
            exit();
        } else {
            $path = $paths[0];
        }
        @set_time_limit(0);
        $attachexists = FALSE;
        if (strpos($path, 'attach::') === 0) {
            $attachment = C::t('attachment')->fetch(intval(str_replace('attach::', '', $path)));
            $attachment['name'] = $filename ? $filename : $attachment['filename'];
            $path = getDzzPath($attachment);
            $attachurl = IO::getStream($path);
        } elseif (strpos($path, 'dzz::') === 0) {
            $attachment = array('attachment' => preg_replace("/^dzz::/i", '', $path), 'name' => $filename ? $filename : substr(strrpos($path, '/')));
            $attachurl = $_G['setting']['attachdir'] . $attachment['attachment'];
        } elseif (strpos($path, 'TMP::') === 0) {
            $tmp = str_replace('\\', '/', sys_get_temp_dir());
            $attachurl = str_replace('TMP::', $tmp . '/', $path);
            $pathinfo = pathinfo($attachurl);
            $attachment = array('attachment' => $attachurl, 'name' => $filename ? $filename : $pathinfo['basename']);

        } elseif (preg_match('/^\w{32}$/i', $path)) {
            $icoid = trim($path);
            $icoarr = C::t('resources')->fetch_by_rid($path);
            if (!$icoarr['rid']) {
                topshowmessage(lang('attachment_nonexistence'));
            }
            if (!$icoarr['aid']) {
                topshowmessage(lang('attachment_nonexistence'));
            }
            $icoarr['name'] = $filename ? $filename : $icoarr['name'];
            $attachment = $icoarr;
            $attachurl = IO::getStream($path);
            //添加事件
            if ($attachurl) {
                $eventdata = array('username' => getglobal('username'), 'dateline' => TIMESTAMP);

                $infos = C::t('resources')->fetch_info_by_rid($path);

                $path = C::t('resources_path')->fetch_pathby_pfid($infos['pfid']);
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($infos['pfid'], $infos['gid']);
                $eventdata['position'] = $icoarr['relpath'];

                $eventdata['files'] = $icoarr['name'];
                $eventdata['hash'] = $hash;
                if (!C::t('resources_event')->addevent_by_pfid($icoarr['pfid'], 'downfile', 'down', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name'])) {
                    return false;
                }
            }
        } elseif (preg_match('/^dzz:[gu]id_\d+:.+?/i', $path)) {
            $dir = dirname($path) . '/';
            if (!$pfid = C::t('resources_path')->fetch_fid_bypath($dir)) {
                return false;
            }
            $filename = preg_replace('/^.+[\\\\\\/]/', '', $path);
            //如果是文件夹
            if (!$filename) {
                $patharr = preg_split('/[\\\\\\/]/', $path);
                $patharr = array_filter($patharr);
                $filename = end($patharr);
            }

            if (!$rid = DB::result_first("select rid from %t where pfid = %d and name = %s", array('resources', $pfid, $filename))) {
                return false;
            }
            $icoarr = C::t('resources')->fetch_by_rid($rid);
            if (!$icoarr['rid']) {
                topshowmessage(lang('attachment_nonexistence'));
            } elseif ($icoarr['type'] == 'folder') {
                self::zipdownload($paths, $filename, $checkperm, $param);
                exit();
            }
            if (!$icoarr['aid']) {
                topshowmessage(lang('attachment_nonexistence'));
            }
            $attachment = $icoarr;
            $attachurl = IO::getStream($path);
            //添加事件
            if ($attachurl) {
                $eventdata = array('username' => getglobal('username'), 'dateline' => TIMESTAMP);

                $infos = C::t('resources')->fetch_info_by_rid($path);

                $path = C::t('resources_path')->fetch_pathby_pfid($infos['pfid']);
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($infos['pfid'], $infos['gid']);
                $eventdata['position'] = $icoarr['relpath'];

                $eventdata['files'] = $icoarr['name'];
                $eventdata['hash'] = $icoarr['hash'];
                if (!C::t('resources_event')->addevent_by_pfid($icoarr['pfid'], 'downfile', 'down', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name'])) {
                    return false;
                }
            }
        }
        if ($attachment['ext'] && strpos(strtolower($attachment['name']), $attachment['ext']) === false) {
            $attachment['name'] .= '.' . $attachment['ext'];
        }

        //如果remoteid大于0
        if ($attachment['remote']) {
            $filesize = filesize($attachurl);
        }
        $param['position'] = ($param['position']) ? $param['position'] : $icoarr['relpath'];
        $indexarr = array('icoarr' => $icoarr, 'param' => $param);
        Hook::listen('downloadbefore_adddownloads', $indexarr);
        $attachment['name'] = '"' . (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($attachment['name']) : ($attachment['name'])) . '"';
        $d = new FileDownload();
        $d->download($attachurl, $attachment['name'], $filesize, $attachment['dateline'], true);
        exit();
    }


}
