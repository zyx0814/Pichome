<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
class table_my_file extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'my_file';
        $this->_pk = 'id';
        parent::__construct();
    }

    public function delete_by_id($id){
        $data = parent::fetch($id);
        if(parent::delete($id)){
            C::t('attachment')->delete_by_aid($aid);
        }
        return true;
    }
    public function insert_by_fileurl($setarr){
        global $_G;
        //获取文件后缀
        $filedirpathinfo = pathinfo($setarr['fileurl']);
        $filedirextensionarr = explode('?', $filedirpathinfo['extension']);
        $ext = strtolower($filedirextensionarr[0]);

        //缓存文件位置
        $cachepath  = 'data/attachment/cache/'.md5($_G['uid'].$setarr['fileurl'].$setarr['type']).'.'.$ext;
        //将文件写入到缓冲区
        if($this->writeFileurlToFile($setarr['fileurl'],$cachepath)){
            $md5 = md5_file($cachepath);
            $filesize = filesize($cachepath);
            if(!$attachment = C::t('attachment')->fetch_by_md5($md5)){
                $filepath = $this->getPath($ext ? ('.' . $ext) : '', 'dzz');
                $remoteid = 0;
                if($_G['setting']['defaultspace']){
                    $defaultspace = $_G['setting']['defaultspace'];
                    $bz = $defaultspace['bz'].':'.$defaultspace['did'].':';
                    $remoteid = $defaultspace['did'];
                }else{
                    $bz = 'dzz::';
                }
                $savepath = $bz.$filepath;
                //移动文件成功后插入attachment表
                if(IO::moveThumbFile($savepath,$cachepath)){
                    @unlink($cachepath);
                    $attachment = [
                        'filename'=>$setarr['name'].'.'.$ext,
                        'filetype'=>$ext,
                        'filesize'=>$filesize,
                        'attachment'=>$filepath,
                        'remote'=>$remoteid,
                        'md5'=>$md5,
                        'dateline'=>TIMESTAMP
                    ];
                    if (!$attachment['aid'] = C::t('attachment')->insert($attachment, 1)){
                        IO::Delete($savepath);
                        return false;
                    }
                }else{
                    return false;
                }
            }
            else{
                return false;
            }

        }else{
            return false;
        }

        $filearr = [
            'uid'=>$_G['uid'],
            'username'=>$_G['username'],
            'source'=>$setarr['type'],
            'aid'=>$attachment['aid'],
            'filetype'=>$attachment['filetype'],
            'filename'=>$attachment['filename'],
            'dateline'=>TIMESTAMP
        ];
        if($id = DB::result_first("select id from %t where uid = %d and aid = %d",array($this->_table,$filearr['uid'],$filearr['aid']))){
            parent::update($id,$filearr);
            return $id;
        }
        if($id = parent::insert($filearr,1)){
            C::t('attachment')->addcopy_by_aid($attachment['aid']);
            return $id;
        }else{
            return false;
        }

    }
    public function insert_by_base64data($setarr){
        global $_G;
        //获取文件后缀
        if(preg_match("/data:(.+?);base64,/i",$setarr['base64'],$matches)){
            $mime=$matches[1];
        }
        $ext = dzz_mime::get_ext($mime);
        $base64Data = preg_replace("/data:(.+?);base64,/i",'',$setarr['base64']);
        $decodedData = base64_decode($base64Data);
        $md5 = md5($decodedData);
        $filesize = fix_integer_overflow(strlen($decodedData));
        if(!$attachment = C::t('attachment')->fetch_by_md5($md5)){
            //缓存文件位置
            $cachepath  = 'data/attachment/cache/'.md5($_G['uid'].$md5.$setarr['type']).'.'.$ext;

            //将文件写入到缓冲区
            if($this->writeContentToFile($decodedData,$cachepath)){
                $filepath = $this->getPath($ext ? ('.' . $ext) : '', 'dzz');
                $remoteid = 0;
                if($_G['setting']['defaultspace']){
                    $defaultspace = $_G['setting']['defaultspace'];
                    $bz = $defaultspace['bz'].':'.$defaultspace['did'].':';
                    $remoteid = $defaultspace['did'];
                }else{
                    $bz = 'dzz::';
                }
                $savepath = $bz.$filepath;
                //移动文件成功后插入attachment表
                if(IO::moveThumbFile($savepath,$cachepath)){
                    @unlink($cachepath);
                    $attachment = [
                        'filename'=>$setarr['name'],
                        'filetype'=>$ext,
                        'filesize'=>$filesize,
                        'attachment'=>$filepath,
                        'remote'=>$remoteid,
                        'md5'=>$md5,
                        'dateline'=>TIMESTAMP
                    ];
                    if (!$attachment['aid'] = C::t('attachment')->insert($attachment, 1)){
                        IO::Delete($savepath);
                        return false;
                    }
                }else{
                    return false;
                }
            }
            else{
                return false;
            }
        }
        $filearr = [
            'uid'=>$_G['uid'],
            'username'=>$_G['username'],
            'source'=>$setarr['type'],
            'aid'=>$attachment['aid'],
            'filetype'=>$attachment['filetype'],
            'filename'=>$attachment['filename'],
            'dateline'=>TIMESTAMP
        ];
        if($id = DB::result_first("select id from %t where uid = %d and aid = %d",array($this->_table,$filearr['uid'],$filearr['aid']))){
            parent::update($id,$filearr);
            return $id;
        }
        if($id = parent::insert($filearr,1)){
            C::t('attachment')->addcopy_by_aid($attachment['aid']);
            return $id;
        }else{
            return false;
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
        $target_attach =  $target1;
        $targetpath = $_G['setting']['attachdir'] . dirname($target_attach);
        dmkdir($targetpath);
        return $target . date('His') . '' . strtolower(random(16)) . $ext;
    }
    public function writeContentToFile($filecontent,$filepath){
        $filedir = dirname($filepath);
        dmkdir($filedir);
        $handle = fopen($filepath, 'w+');
        $chunkSize = 8192; // 每次读取的块大小
        $offset = 0;
        while ($offset < strlen($filecontent)) {
            $chunk = substr($filecontent, $offset, $chunkSize);
            fwrite($handle, $chunk);
            $offset += strlen($chunk);
        }
        fclose($handle);
        if(is_file($filepath)){
            return true;
        }else{
            return false;
        }
    }
    public function writeFileurlToFile($path, $filepath)
    {
        $filedir = dirname($filepath);
        dmkdir($filedir);
        $handle = fopen($path, 'rb');
        $succ = 1;
        while (!feof($handle)) {
            $fileContent = fread($handle, 8192);
            if (file_put_contents($filepath, $fileContent, FILE_APPEND) === false) {
                $succ = 0;
            }
            unset($fileContent);
        }
        fclose($handle);
        if (!$succ) {
            @unlink($filepath);
            return false;
        }
        return $filepath;

    }
}