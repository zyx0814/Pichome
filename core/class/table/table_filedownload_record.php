<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 *
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_filedownload_record extends dzz_table
{
    public function __construct() {

        $this->_table = 'filedownload_record';
        $this->_pk    = 'id';

        parent::__construct();
    }
    //通过md5获取记录表数据
    public function  fetch_by_md5($md5){
        return   DB::fetch_first("select * from %t  where md5 = %s", array($this->_table,$md5));
    }

    //计算文件大小最大的空间
    public function getautodownloadspace($filedatas)
    {
        $remotesumarr = [];
        foreach ($filedatas as $v) {
            if (isset($v['remote'])) {
                $v['remote']  = ($v['remote'] == 0) ? 1:$v['remote'];
                if (!isset($remotesumarr[$v['remote']])) $remotesumarr[$v['remote']] = 0;
                $remotesumarr[$v['remote']] += $v['size'];
            }
        }
        $defaultremoteid = array_search(max($remotesumarr), $remotesumarr);
        return $defaultremoteid;
    }

    //获取下载设置值，如果下载设置值未设置，返回空，否则返回remoteid，remoteid可能为0代表的本地下载
    public function getdownloadspace()
    {
        //获取下载位置设置值
        $defaultdownloadremoteid = 0;
        if (!isset($_G['setting']['downloadsetting'])) {
            $setting = C::t('setting')->fetch_all('downloadsetting');
            $defaultdownloadremoteid = $setting['downloadsetting'];
        } else {
            $defaultdownloadremoteid = $_G['setting']['downloadsetting'];
        }
        return $defaultdownloadremoteid;
    }
    public function chkdownloadrecord($cloudpath,$fpath,$attachment,$attachurl,$defaultdownloadremoteid){
        global $_G;
        //下载记录表数据
        $drecorddata = [
            'filename' => $attachment['name'],
            'ext' => $attachment['ext'],
            'md5' => $attachment['md5'],
            'dateline' => time(),
            'filesize' => $attachment['filesize'] ? $attachment['filesize'] : filesize($attachurl),
            'status' => 3,
            'remoteid' => $defaultdownloadremoteid
        ];
        $ishasdownfile = false;
        $cloudurl = '';
        //判断下载缓冲区文件是否已经存在
        $checkdata = C::t('downfile_record')->fetch_by_md5($drecorddata['md5']);
        //如果已经存在则不需复制或上传
        if ($checkdata) {
            //尝试获取文件地址，并判断文件是否已被删除
            if ($checkdata['path']) $retrun = IO::getstream($checkdata['path'], true);
            if (!$checkdata['path'] || isset($retrun['error'])) {
                //下载文件不存在时删除记录
                C::t('downfile_record')->delete($checkdata['id']);
            } else {
                $cloudurl = $retrun;
                $ishasdownfile = true;
            };
        }
        //如果文件存在记录，并且下载文件存在
        if ($ishasdownfile ) {
            //如果文件名相同直接下载
            if ($checkdata['filename'] == $drecorddata['filename']) {
                C::t('downfile_record')->update($checkdata['id'], array('dateline' => time()));
                return $checkdata['id'];
            } else {
                //如果文件名不同,将下载区文件地址赋予源文件地址，即将拷贝下载区文件至目标位置
                $fpath = $cloudurl;
            }
        }
        $filedata = ['path'=>$cloudpath,'fpath'=>$fpath];
        $drecorddata['filedata'] = serialize($filedata);
        //如果文件不存在记录或者下载文件不存在插入下载记录表数据
        $recordid = C::t('downfile_record')->insert($drecorddata, 1);
        //异步执行将文件迁移到下载缓冲区
        dfsockopen($_G['localurl'] . 'misc.php?mod=movefiletodownload&rdid=' . $recordid, 0, '', '', false, '', 0.1);
        return $recordid;
    }

    //预处理文件数据
    public function zippackfilepack($zippath, $paths, $position, $checkperm, $params)
    {
        global $_G;
        $filedatas = $this->getfolderinfo($paths, $position, $checkperm, $params);
        $pfidpaths = [];
        foreach($filedatas as $v){
            $tmparr =  explode('/',$v['relpath']);
            if(empty($pfidpaths)) $pfidpaths = $tmparr;
            else{
                $t=$pfidpaths;
                $t1=array();
                foreach($tmparr as $k => $v){
                    if($v && $t[$k]==$v) {
                        $t1[$k]=$v;
                    }else{
                        break;
                    }
                }
                $pfidpaths = $t1;
            }
        }

        $rootpath = trim(implode('/',$pfidpaths),'/');
        $pfid = DB::result_first("select fid from %t where path REGEXP %s ",array('resources_path','^dzz:.+:'.$rootpath.'/$'));

        //获取设置的默认下载空间
        $defaultdownloadremoteid = $this->getdownloadspace();
        //如果默认下载空间未设置或者为自适应时,按文件大小所占比重计算出下载空间
        if (!$defaultdownloadremoteid) {
            $defaultdownloadremoteid = $this->getautodownloadspace($filedatas);
        }
        //获取总大小和md5值以计算合并md5值
        $totalsize = 0;
        $md5arr = [];
        $positionarr = [];
        foreach ($filedatas as $v) {
            if ($v['size']) $totalsize += $v['size'];
            if ($v['md5']) {
                $md5arr[] = md5($v['md5'].$v['position']);
            }
        }
        //计算合并md5值
        arsort($md5arr);
        $md5str =implode(',', $md5arr);
        //获取压缩包名字
        $filename = $this->get_basename($zippath);
        $filedatas['pfid'] = $pfid;
        $filedatas['pathmd5'] = md5($md5str);
        $recorddata = [];
        foreach ($filedatas as $path => $val) {
            if ($path != 'pfid' && $path != 'pathmd5') {
                $recorddata[$path] = [
                    'size' => $val['size'],
                    'path' => $path,
                    'fileurl' => $val['fileurl'],
                    'position' => $val['position']
                ];
            } else {
                $recorddata[$path] = $val;
            }

        }
        //组合下载记录表数据
        $drecorddata = [
            'filename' => $filename,
            'filesize' => $totalsize,
            'md5' => md5($md5str),
            'ext' => 'zip',
            'dateline' => TIMESTAMP,
            'filedata' => serialize($recorddata),
            'rate' => 90,
            'remoteid' => $defaultdownloadremoteid
        ];
        //判断是否已有对应的下载记录数据
        $checkdata = $this->fetch_by_md5( $drecorddata['md5']);

        //如果有对应的下载记录数据
        if (!empty($checkdata)) {
            //如果设定有下载空间，且当前设定下载空间和记录值空间不一致时，将下载包复制到设定下载空间下载
            if ($defaultdownloadremoteid   && $defaultdownloadremoteid  != $checkdata['remoteid']) {
                //判断下载记录对应下载包文件是否存在，如果不存在删除下载记录，重新记录下载记录，存在则移动当前下载包到设定下载空间
                $retrun = IO::getstream($checkdata['path'], true);
                if (isset($retrun['error']) || !$checkdata['path']) {
                    //下载文件不存在时删除记录,继续执行后续操作
                    C::t('downfile_record')->delete($checkdata['id']);
                } else {
                    //下载包存在时，移动下载包到对应下载空间
                    $bz = io_remote::getBzByRemoteid($defaultdownloadremoteid);
                    if ($bz == 'dzz') {
                        $cloudpath = 'dzz::' . 'cache/downloadtmp/' . $drecorddata['md5'] . '/'.$drecorddata['filename'];
                    } else {
                        $cloudpath = $bz . '/downloadtmp/' . $drecorddata['md5'] .'/' .$drecorddata['filename'];
                    }
                    $filedata = ['path'=>$cloudpath,'fpath'=>$checkdata['path']];

                    //如果文件不存在记录或者下载文件不存在插入下载记录表数据
                    $recordid = C::t('downfile_record')->update($checkdata['id'], array('filedata'=>serialize($filedata)));
                    //异步执行将文件迁移到下载缓冲区
                    dfsockopen($_G['localurl'] . 'misc.php?mod=movefiletodownload&rdid=' . $checkdata['id'], 0, '', '', false, '', 0.1);
                    return $checkdata['id'];

                }
            }
            else {
                //如果未设置下载空间，则直接下载记录值中下载包
                //尝试获取文件地址，如果获取失败则该文件已被删除
                $retrun = IO::getstream($checkdata['path'], true);
                if (isset($retrun['error']) || !$checkdata['path']) {
                    //下载文件不存在时删除记录
                    C::t('downfile_record')->delete($checkdata['id']);
                } else {
                    C::t('downfile_record')->update($checkdata['id'], array('dateline' => time()));
                    return $checkdata['id'];
                    exit();
                }
            }

        }
        else {
            //如果没有对应记录则需拉取打包并上传至下载区
            $recordid = C::t('downfile_record')->insert($drecorddata, 1);
            dfsockopen($_G['localurl'] . 'misc.php?mod=packdownload&rdid=' . $recordid, 0, '', '', false, '', 0.1);
            return $recordid;
        }



    }

    //获取文件数据
    public  function getfolderinfo($paths, $position = '', $checkperm = '', $params = array())
    {
        static $data = array();
        //static $pfid = 0;
        try {
            foreach ($paths as $path) {
                $meta = IO::getMeta($path);
                //if ($pfid == 0) $pfid = $meta['pfid'];
                switch ($meta['type']) {
                    case 'folder':
                        $lposition = $meta['relpath'] . $meta['name'] . '/';
                        $contents = C::t('resources')->fetch_by_pfid($meta['oid'], '', $checkperm);
                        foreach ($contents as $key => $value) {
                            $this->getFolderInfo(array($value['rid']), $lposition, $checkperm, $params);
                        }
                        break;
                    case 'discuss':
                    case 'dzzdoc':
                    case 'shortcut':
                    case 'user':
                    case 'link':
                    case 'topic':
                    case 'app'://这些内容不能移动到api网盘内；
                        break;
                    default:
                        $metaname = ($meta['ext'] ? (preg_replace("/\." . $meta['ext'] . "$/i", '', $meta['name']) . '.' . $meta['ext']) : $meta['name']);
                        //$metaname = $metaname;
                        $meta['position'] = $meta['relpath'] . $metaname;
                        $icoarr = $meta;
                        $icoarr['fileurl'] = IO::getStream($path);
                        $icoarr['rid'] = $path;
                        //  $icoarr['pfid'] = $data['pfid'];
                        $data[$path] = $icoarr;
                        $params['position'] = ($params['position']) ? $params['position'] : $position;
                        $indexarr = array('icoarr' => $icoarr, 'param' => $params);
                        Hook::listen('downloadbefore_adddownloads', $indexarr);
                }
            }
        } catch
        (Exception $e) {
            $data['error'] = $e->getMessage();
            return $data;
        }
        // $data['pfid'] = $pfid;
        return $data;
    }

    public function get_basename($filename)
    {
        if ($filename) {
            return preg_replace('/^.+[\\\\\\/]/', '', $filename);
        }
        return '';

    }

}
