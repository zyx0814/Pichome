<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
@ini_set('max_execution_time', 0);
require_once(DZZ_ROOT . './dzz/class/class_encode.php');
require_once './core/class/class_Color.php';
require_once libfile('function/user', '', 'user');

class eagleexport
{
    private $path = '';//待执行数据path
    private $appid = 0;//库id
    private $uid = 0;//用户id
    private $username = null;//用户名
    private $filenum = 0;//总文件数
    private $checkpage = 1;
    private $checklimit = 1000;
    private $onceexportnum = 100;
    private $checknum = 0;
    private $eagledir = DZZ_ROOT . 'library';
    private $readtxt = DZZ_ROOT . './data/attachment/cache/';
    private $exportstatus = 0;
    private $donum = 0;
    private $lastid = '';
    private $defaultperm = 0;
    private $iscloud = false;
    private $processname = '';
    public $palette = [
        0xfff8e1, 0xf57c00, 0xffd740, 0xb3e5fc, 0x607d8b, 0xd7ccc8,
        0xff80ab, 0x4e342e, 0x9e9e9e, 0x66bb6a, 0xaed581, 0x18ffff,
        0xffe0b2, 0xc2185b, 0x00bfa5, 0x00e676, 0x0277bd, 0x26c6da,
        0x7c4dff, 0xea80fc, 0x512da8, 0x7986cb, 0x00e5ff, 0x0288d1,
        0x69f0ae, 0x3949ab, 0x8e24aa, 0x40c4ff, 0xdd2c00, 0x283593,
        0xaeea00, 0xffa726, 0xd84315, 0x82b1ff, 0xab47bc, 0xd4e157,
        0xb71c1c, 0x880e4f, 0x00897b, 0x689f38, 0x212121, 0xffff00,
        0x827717, 0x8bc34a, 0xe0f7fa, 0x304ffe, 0xd500f9, 0xec407a,
        0x6200ea, 0xffab00, 0xafb42b, 0x6a1b9a, 0x616161, 0x8d6e63,
        0x80cbc4, 0x8c9eff, 0xffeb3b, 0xffe57f, 0xfff59d, 0xff7043,
        0x1976d2, 0x5c6bc0, 0x64dd17, 0xffd600
    ];

    public function __construct($data = array())
    {
        if (strpos($data['path'], ':') === false) {
            $bz = 'dzz';
            $did = 1;
        } else {
            $patharr = explode(':', $data['path']);
            $bz = $patharr[0];
            $did = $patharr[1];

        }
        if ($bz == 'dzz') $did = 1;
        if (!is_numeric($did) || $did < 2) {
            $this->path = str_replace('/', BS, $data['path']);
            $this->path = str_replace('dzz::', '', $data['path']);
        } else {
            $this->iscloud = true;
            $this->path = $data['path'];
        }
        $this->appid = $data['appid'];
        $this->processname = 'PICHOMEVAPPISDEL_' . $data['appid'];

        $this->uid = $data['uid'];
        $this->username = $data['username'];
        $this->exportstatus = $data['state'];
        $this->donum = $data['donum'];
        $this->filenum = $data['filenum'];
        $this->lastid = $data['lastid'];
        $this->defaultperm = isset($data['perm']) ? intval($data['perm']) : 0;
    }

    public function getpathdata($folderdata, $appid, $pathdata = array())
    {
        foreach ($folderdata as $v) {
            $pathdata[$v['id'] . $appid] = $v['name'];
            if ($v['children']) {
                $tmpchild = $v['children'];
                $pathdata = $this->getpathdata($tmpchild, $appid, $pathdata);

            }
        }

        return $pathdata;
    }

    public function initFoldertag()
    {
        if (dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
        $jsonfile = ($this->iscloud) ? $this->path . '/metadata.json' : $this->path . BS . 'metadata.json';
        $appdatas = file_get_contents(IO::getStream($jsonfile));
        //解析出json数据
        $appdatas = json_decode($appdatas, true);

        //目录数据
        $folderdata = $appdatas['folders'];
        $efids = C::t('#eagle#eagle_folderrecord')->insert_folderdata_by_appid($this->appid, $folderdata, $this->defaultperm);
        $delids = [];
        foreach (DB::fetch_all("select id from %t where efid not in(%n) and appid = %s", array('eagle_folderrecord', $efids, $this->appid)) as $delid) {
            $delids[] = $delid['id'];
        }
        //删除多余目录
        foreach (DB::fetch_all("select  f.fid from %t f left join %t ef  on f.fid=ef.fid where f.appid = %s and ISNULL(ef.id)", array('pichome_folder', 'eagle_folderrecord', $this->appid)) as $dv) {
            C::t('pichome_folder')->delete_by_fids($dv);
        }
        //对比目录数据
        if ($delids) C::t('#eagle#eagle_folderrecord')->delete_by_ids($delids);
        //标签数据
        $tagdata = $appdatas['tagsGroups'];
        $currentcids = [];
        $tids = [];

        foreach ($tagdata as $v) {
            $taggroupdata = [
                'cid' => $v['id'] . $this->appid,
                'catname' => $v['name'],
                'appid' => $this->appid,
                'dateline' => TIMESTAMP
            ];
            //插入或更新标签分类数据
            $cid = C::t('pichome_taggroup')->insert($taggroupdata);
            $currentcids[] = $cid;
            foreach ($v['tags'] as $val) {
                $tid = C::t('pichome_tag')->insert($val, 1);
                $tids[] = $tid;
                if ($cid) {
                    $relasetarr = ['cid' => $cid, 'tid' => $tid, 'appid' => $this->appid];
                    C::t('pichome_tagrelation')->insert($relasetarr);
                }
            }
        }
        if ($tids) {
            //查询关系表中包含的不存在的标签关系
            $drids = [];
            foreach (DB::fetch_all("select id from %t where tid  not in(%n)  and appid = %s", array('pichome_tagrelation', $tids, $this->appid)) as $rv) {
                $drids[] = $rv['id'];
            }
            //删除不存在的标签关系数据
            C::t('pichome_tagrelation')->delete($drids);
        }
        $ocids = C::t('pichome_taggroup')->fetch_cid_by_appid($this->appid);
        $delcids = array_diff($ocids, $currentcids);
        C::t('pichome_taggroup')->delete_by_cids($delcids);
        return true;

    }

    //读取云存储数据
    public function readcloudDirdata($path, $force = 0)
    {
        $prepatharr = explode(':', $path);
        //获取mtime.json
        $mtimejsonfile = $this->path . '/mtime.json';
        $mtimedata = file_get_contents(IO::getStream($mtimejsonfile));
        if ($mtimedata) {
            $vappmtimedata = json_decode($mtimedata, true);
            unset($mtimedata);
            $this->filenum = $vappmtimedata['all'];
            unset($vappmtimedata);

        } else {
            return array('error' => '信息获取失败');
        }

        /* $prepath = $prepatharr[2];
         $returndata = IO::getFolderlist($path,$nextmarker,$by,$order,$limit,$force);
         if($returndata['error']){
             return array('error'=>$returndata['error']);
         }else{
             foreach($returndata['folder'] as $v){
                 if(dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
                 $v = str_replace($prepath,'',$v);
                 $v = trim($v,'/');
                 if(IO::checkfileexists($this->path.'/images/'.$v.'/metadata.json')){
                     $this->filenum++;
                     fwrite($thandle, $v . "\n");
                 }
             }
         }
         runlog('aaaaeagle',print_r($returndata,true));
         if($returndata['IsTruncated']){
             $istruncated = $returndata['NextMarker'];
             unset($returndata);
             $this->readcloudDirdata($thandle,$path,$istruncated,$by,$order,$limit,$force);
         }else{
             return array('success'=>true);
         }*/

    }

    //读取本地目录数据
    public function readLocalDirdata($thandle, $path)
    {
        if ($dch = opendir($path)) {
            while (($file = readdir($dch)) != false) {
                if (dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
                if ($file != '.' && $file != '..') {
                    $filePath = $path . '/' . $file;
                    //if (is_dir($filePath) && IO::checkfileexists($filePath . '/metadata.json')) {
                    $this->filenum++;
                    fwrite($thandle, $file . "\n");
                    //  }
                    unset($filePath);
                    unset($file);
                }
            }
            closedir($dch);
        } else {
            return array('error' => 'Read Dir Failer');
        }
    }

    //生成记录文件
    public function createReadTxt($readtxt)
    {
        $filedir = ($this->iscloud) ? $this->path . '/images' : $this->path . BS . 'images';
        $this->filenum = 0;

        if ($this->iscloud) {
            $readreturn = $this->readcloudDirdata($filedir);
        } else {
            $thandle = fopen($readtxt, 'w+');
            $readreturn = $this->readLocalDirdata($thandle, $filedir);
            fclose($thandle);
        }

        if ($readreturn['error']) {
            C::t('pichome_vapp')->update($this->appid, array('state' => 0));
            return array('error' => $readreturn['error']);
        }
        $this->initFoldertag();
        C::t('pichome_vapp')->update($this->appid, array('filenum' => $this->filenum, 'state' => 2));
        return array('success' => true);
    }

    //初始化导入
    public function initExport()
    {
        $readtxt = $this->readtxt . 'eagleexport' . md5($this->path) . '.txt';
        //如果导入时没有记录文件
        if (!is_file($readtxt)) {
            $readdata = $this->createReadTxt($readtxt);
        } else {
            //如果有记录文件，则对比记录文件生成时间和metadata.json时间,如果记录时间小于metadata.json时间重新生成记录文件
            $metapath = ($this->iscloud) ? $this->path . '/metadata.json' : $this->path . BS . 'metadata.json';
            $metadatainfo = IO::getMeta($metapath);
            if (filemtime($readtxt) < $metadatainfo['dateline']) {
                $readdata = $this->createReadTxt($readtxt);
            } else {
                $this->initFoldertag();
                C::t('pichome_vapp')->update($this->appid, array('state' => 2));
            }

        }
        if (isset($readdata) && $readdata['error']) {
            return array('error' => $readdata['error']);
        } else {
            return array('success' => true);
        }

    }

    //获取文件可访问的真实地址
    public function getFileRealFileName($filepath, $filename, $ext)
    {
        $charsetarr = ['GBK', 'GB18030'];
        $returnname = $filename;
        if (!is_file($filepath . BS . $filename . '.' . $ext)) {
            foreach ($charsetarr as $v) {
                $filemetadataname = diconv($filename, CHARSET, $v);
                if (is_file($filepath . BS . $filemetadataname . '.' . $ext)) {
                    $returnname = $filemetadataname;
                    break;
                }
            }
        }
        return $returnname;

    }

    //生成主键rid
    public function createrid()
    {
        $microtime = microtime();
        list($msec, $sec) = explode(' ', $microtime);
        $msec = $msec * 1000000;
        $idstr = md5($sec . $msec . random(6) . $this->appid);
        if (DB::result_first("select count(rid) from %t where rid = %s", array('pichome_resources', $idstr))) {
            $this->create_id();
        }
        return $idstr;
    }

    public function execExport($force = false)
    {
        if ($this->iscloud) {
            return $this->execExportCloud($force);
        } else {
            $filedir = ($this->iscloud) ? $this->path . '/images' : $this->path . BS . 'images';
            $readtxt = $this->readtxt . 'eagleexport' . md5($this->path) . '.txt';
            if (filesize($readtxt) == 0) {
                @unlink($readtxt);
                C::t('pichome_vapp')->update($this->appid, array('lastid' => 0, 'percent' => 100, 'donum' => 0, 'state' => 3, 'filenum' => $this->filenum));
                return array('success' => true);

            }
            if ($this->lastid) {
                $start = $this->lastid;
            } else $start = 0;
            $spl_object = new SplFileObject($readtxt, 'rb');
            $spl_object->seek($start);
            if ($this->lastid < $this->filenum && $this->exportstatus == 2) {
                $i = 0;
                while (is_file($readtxt) && !$spl_object->eof()) {
                    if (dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
                    $i++;
                    if ($i > $this->onceexportnum) {
                        break;

                    }

                    $file = $spl_object->current();
                    $file = trim($file);
                    $filePath = $filedir . '/' . $file;

                    $id = trim(str_replace('.info', '', $file));

                    //文件路径
                    $tmppath = $filePath;
                    unset($filePath);
                    //文件信息文件路径
                    $metadatajsonfile = ($this->iscloud) ? $tmppath . '/metadata.json' : $tmppath . BS . 'metadata.json';

                    //尝试获取记录表记录
                    $rdata = C::t('#eagle#eagle_record')->fetch_by_eid($id, $this->appid);
                    $rid = '';
                    if (!isset($rdata['rid'])) {
                        $orid = $id . $this->appid;//原来rid格式
                        if ($lastdate = DB::result_first("select lastdate from %t where rid = %s", array('pichome_resources', $orid))) {
                            $rid = $orid;
                        }

                    } else {
                        $rid = $rdata['rid'];
                        $lastdate = $rdata['dateline'];
                    }
                    //判断是否含有数据信息文件
                    if (IO::checkfileexists($metadatajsonfile)) {
                        $metadatajsonfileinfo = IO::getMeta($metadatajsonfile);
                        $flastdate = $metadatajsonfileinfo['dateline'];
                        $metadata = file_get_contents(IO::getStream($metadatajsonfile));
                        $filemetadata = json_decode($metadata, true);

                        //如果是删除状态，并且已有数据则执行删除
                        if ($filemetadata['isDeleted']) {
                            if ($rid) C::t('pichome_resources')->delete_by_rid($rid);
                        } else {
                            //如果不是新生成rid
                            if ($rid) {
                                $data = C::t('pichome_resources')->fetch($rid);
                                //判断最后更新时间
                                if ($force || $lastdate < $flastdate) {
                                    $filemetadataname = ($this->iscloud) ? $filemetadata['name'] : $this->getFileRealFileName($tmppath, $filemetadata['name'], $filemetadata['ext']);
                                    //文件名称
                                    $filename = $filemetadataname . '.' . $filemetadata['ext'];
                                    //缩略图名称
                                    $thumbname = $filemetadataname . '_thumbnail.png';
                                    //文件路径
                                    $file = ($this->iscloud) ? $tmppath . '/' . $filename : $tmppath . BS . $filename;
                                    //缩略图路径
                                    $thumbfile = ($this->iscloud) ? $tmppath . '/' . $thumbname : $tmppath . BS . $thumbname;
                                    $realfolderdata = [];
                                    if (!empty($filemetadata['folders'])) {
                                        $realfolderdata = C::t('#eagle#eagle_folderrecord')->fetch_fid_by_efid($filemetadata['folders'], $this->appid);
                                    }
                                    $currentperm = (!empty($realfolderdata)) ? max($realfolderdata) : $this->defaultperm;
                                    $realfids = array_keys($realfolderdata);

                                    $haspassword = false;
                                    if (!empty($realfids)) {
                                        //如果目录含有密码则不导入数据直接跳过
                                        $haspassword = C::t('pichome_folder')->check_haspasswrod($realfids, $this->appid);
                                    }
                                    if ($haspassword) {
                                        C::t('pichome_resources')->delete_by_rid($rid);
                                    } else {
                                        //如果mtime发生变化则删除原数据，重新导入
                                        if ($data['mtime'] < $filemetadata['mtime']) {
                                            C::t('pichome_resources')->delete_by_rid($rid);
                                            $filemetadata['rid'] = $this->createrid();
                                            $filemetadata['filename'] = $filemetadata['name'];
                                            $filemetadata['file'] = $file;
                                            $filemetadata['thumbfile'] = $thumbfile;
                                            $filemetadata['rid'] = $rid;
                                            $filemetadata['mtime'] = $filemetadata['mtime'] ? $filemetadata['mtime'] : $filemetadata['modificationTime'];
                                            $filemetadata['btime'] = $filemetadata['btime'] ? $filemetadata['btime'] : $filemetadata['modificationTime'];
                                            $filemetadata['dateline'] = $filemetadata['lastModified'];
                                            $filemetadata['lastdate'] = $flastdate;
                                            $filemetadata['folders'] = $realfids;
                                            $filemetadata['level'] = $currentperm;
                                            $this->exportfile($id, $filemetadata);
                                            unset($filemetadata);
                                        } else {
                                            //信息表数据记录
                                            $setarr = ['appid' => $this->appid];
                                            $setarr['searchval'] = $filemetadata['name'];
                                            //查询原数据中的属性信息
                                            $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                                            $filename = $filemetadata['name'] . '.' . $filemetadata['ext'];
                                            //检查reources数据变化
                                            $resourcesarr = [
                                                'name' => $filename,
                                                'dateline' => $filemetadata['lastModified'],
                                                'isdelete' => $filemetadata['isDeleted'],
                                                'grade' => $filemetadata['star'] ? intval($filemetadata['star']) : 0,
                                                'lastdate' => $flastdate,
                                                'width' => $filemetadata['width'] ? $filemetadata['width'] : 0,
                                                'height' => $filemetadata['height'] ? $filemetadata['height'] : 0,
                                                'appid' => $this->appid,
                                                'level' => $currentperm,
                                                'fids' => implode(',', $realfids)
                                            ];


                                            $file = str_replace('/', BS, $file);
                                            $attachment = str_replace($this->path . BS, '', $file);
                                            $path = str_replace('/', BS, $attachment);
                                            $thumb = IO::checkfileexists($thumbfile) ? 1 : 0;
                                            $thumbfile = str_replace(array(DZZ_ROOT, BS), array('', '/'), $thumbfile);
                                            $setarr['path'] = $path;
                                            $resourcesarr['hasthumb'] = $thumb;
                                            $resourcesarr['rid'] = $rid;
                                            if (C::t('#eagle#eagle_record')->insert_data($id, $resourcesarr)) {
                                                //缩略图数据
                                                /*  $thumbrecorddata = [
                                                      'rid'=>$rid,
                                                      'ext'=>$filemetadata['ext']
                                                  ];
                                                  if($thumb){
                                                      $imgdata = @getimagesize($filemetadata['thumbfile']);
                                                      $swidth = isset($imgdata[0]) ? $imgdata[0] : 0;
                                                      $sheight = isset($imgdata[1]) ? $imgdata[1] : 0;
                                                      $thumbrecorddata['spath'] = $filemetadata['thumbfile'];
                                                      $thumbrecorddata['sstatus'] = 1;
                                                      $thumbrecorddata['swidth'] = $swidth;
                                                      $thumbrecorddata['sheight'] = $sheight;
                                                  }
                                                  C::t('thumb_record')->insert_data($thumbrecorddata);*/
                                                //检查标签变化
                                                //标签数据
                                                $tags = $filemetadata['tags'];
                                                $setarr['searchval'] .= implode('', $tags);
                                                //现有标签
                                                $tagids = [];
                                                //原有标签
                                                $oldtids = [];
                                                if ($attrdata['tag']) $oldtids = explode(',', $attrdata['tag']);

                                                if (!empty($tags)) {
                                                    $tagids = $this->addtag($tags);
                                                    $setarr['tag'] = implode(',', $tagids);
                                                }
                                                $addtags = array_diff($tagids, $oldtids);
                                                $deltags = array_diff($oldtids, $tagids);

                                                if (!empty($deltags)) C::t('pichome_resourcestag')->delete_by_ridtid($rid, $deltags);
                                                foreach ($addtags as $tid) {
                                                    $rtag = ['appid' => $this->appid, 'rid' => $rid, 'tid' => $tid];
                                                    C::t('pichome_resourcestag')->insert($rtag);
                                                }

                                                //检查标注变化
                                                if (isset($filemetadata['comments'])) {
                                                    $cids = [];
                                                    foreach ($filemetadata['comments'] as $commentval) {
                                                        $tcommentval['id'] = $commentval['id'] . $this->appid;
                                                        $tcommentval['appid'] = $this->appid;
                                                        $tcommentval['rid'] = $rid;
                                                        $tcommentval['x'] = number_format($commentval['x'], 2);
                                                        $tcommentval['y'] = number_format($commentval['y'], 2);
                                                        $tcommentval['width'] = $commentval['width'];
                                                        $tcommentval['height'] = $commentval['height'];
                                                        $tcommentval['annotation'] = $commentval['annotation'];
                                                        $tcommentval['lastModified'] = $commentval['lastModified'];
                                                        try {
                                                            C::t('pichome_comments')->insert($tcommentval);
                                                            $setarr['searchval'] .= $tcommentval['annotation'];
                                                        } catch (Exception $e) {

                                                        }
                                                        $cids[] = $tcommentval['id'];
                                                        unset($tcommentval);

                                                    }
                                                    $ocids = C::t('pichome_comments')->fetch_id_by_rid($rid);
                                                    $delcids = array_diff($ocids, $cids);
                                                    if (!empty($delcids)) C::t('pichome_comments')->delete($delcids);
                                                } else {
                                                    C::t('pichome_comments')->delete_by_rid($rid);
                                                }

                                                $rfids = [];
                                                $orfids = C::t('pichome_folderresources')->fetch_id_by_rid($rid);
                                                C::t('pichome_folderresources')->delete($orfids);
                                                $setarr['searchval'] .= $resourcesarr['name'];


                                                //检查目录变化
                                                foreach ($realfids as $fv) {
                                                    $fid = $fv;
                                                    if (!C::t('pichome_folder')->check_password_byfid($fid)) {
                                                        $frsetarr = ['appid' => $this->appid, 'rid' => $rid, 'fid' => $fid];
                                                        C::t('pichome_folderresources')->insert($frsetarr);
                                                        // $fids[] = $fid;
                                                    }
                                                }
                                                //尝试更新属性表数据
                                                $setarr['link'] = $filemetadata['url'] ? trim($filemetadata['url']) : '';
                                                //描述数据
                                                $setarr['desc'] = $filemetadata['annotation'] ? $filemetadata['annotation'] : '';
                                                $setarr['searchval'] .= getstr($setarr['desc'],255) . $setarr['link'];
                                                if ($filemetadata['duration']) $setarr['duration'] = number_format($filemetadata['duration'], 2);
                                                $setarr['rid'] = $rid;
                                                C::t('pichome_resources_attr')->insert($setarr);
                                                unset($filemetadata);
                                                unset($setarr);
                                            }
                                        }
                                    }


                                } else {
                                    if (!$rdata) {
                                        $setarr = [
                                            'appid' => $this->appid,
                                            'rid' => $rid,
                                            'eid' => $id,
                                            'dateline' => $flastdate
                                        ];
                                        C::t('#eagle#eagle_record')->insert($setarr);
                                    }
                                }

                            } else {
                                $realfolderdata = [];
                                if (!empty($filemetadata['folders'])) {
                                    $realfolderdata = C::t('#eagle#eagle_folderrecord')->fetch_fid_by_efid($filemetadata['folders'], $this->appid);

                                }

                                $currentperm = (!empty($realfolderdata)) ? max($realfolderdata) : $this->defaultperm;
                                $realfids = array_keys($realfolderdata);
                                if (!empty($realfids)) {
                                    //如果目录含有密码则不导入数据直接跳过
                                    $haspassword = C::t('pichome_folder')->check_haspasswrod($realfids, $this->appid);
                                } else {
                                    $haspassword = false;
                                }

                                if (!$haspassword) {
                                    $filemetadataname = ($this->iscloud) ? $filemetadata['name'] : $this->getFileRealFileName($tmppath, $filemetadata['name'], $filemetadata['ext']);

                                    $filename = $filemetadataname . '.' . $filemetadata['ext'];
                                    $thumbname = $filemetadataname . '_thumbnail.png';

                                    $file = ($this->iscloud) ? $tmppath . '/' . $filename : $tmppath . BS . $filename;
                                    $thumbfile = ($this->iscloud) ? $tmppath . '/' . $thumbname : $tmppath . BS . $thumbname;
                                    $filemetadata['filename'] = $filemetadata['name'];
                                    $filemetadata['file'] = $file;
                                    unset($file);
                                    $filemetadata['thumbfile'] = $thumbfile;
                                    $filemetadata['folders'] = $realfids;
                                    $filemetadata['level'] = $currentperm;
                                    unset($thumbfile);
                                    $filemetadata['rid'] = $this->createrid();
                                    $filemetadata['mtime'] = $filemetadata['mtime'] ? $filemetadata['mtime'] : $filemetadata['modificationTime'];
                                    $filemetadata['btime'] = $filemetadata['btime'] ? $filemetadata['btime'] : $filemetadata['modificationTime'];
                                    $filemetadata['dateline'] = $filemetadata['lastModified'];
                                    $filemetadata['lastdate'] = $flastdate;

                                    $this->exportfile($id, $filemetadata);
                                    unset($filemetadata);
                                }
                            }

                        }
                    } else {
                        //如果已有数据删除，否则不做处理
                        if (!$rid) C::t('pichome_resources')->delete_by_rid($rid);
                    }


                    $this->donum += 1;
                    $percent = floor(($this->donum / $this->filenum) * 100);
                    //防止因获取文件总个数不准确百分比溢出
                    $percent = ($percent > 100) ? 100 : $percent;
                    $state = ($percent >= 100) ? 3 : 2;
                    if ($state == 3) {
                        $spl_object = false;
                        @unlink($this->readtxt . 'eagleexport' . md5($this->path) . '.txt');
                        $lastid = 0;
                        $percent = 0;
                        $this->donum = 0;
                    } else {
                        $lastid = $this->donum;
                    }
                    //记录导入起始位置，以备中断后从此处,更改导入状态为正在导入
                    C::t('pichome_vapp')->update($this->appid, array('lastid' => $lastid, 'percent' => $percent, 'donum' => $this->donum, 'state' => $state, 'filenum' => $this->filenum));
                    if ($spl_object) $spl_object->next();

                }


            }

            return array('success' => true);
        }


    }

    public function execExportCloud($force = false)
    {
        $filedir = $this->path . '/images';
        $prepatharr = explode(':', $filedir);

        $prepath = $prepatharr[2];
        if ($this->exportstatus == 2) {
            $nextmarker = $this->lastid ? $this->lastid : '';
            $returndata = IO::getFolderlist($filedir, $nextmarker, 'time', 'DESC', $this->onceexportnum);
            if ($returndata['error']) {
                return array('error' => $returndata['error']);
            } else {
                $i = 0;
                foreach ($returndata['folder'] as $v) {
                    if (dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
                    $v = str_replace($prepath, '', $v);
                    $v = trim($v, '/');

                    if (dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
                    $file = $v;
                    $file = trim($file);
                    $filePath = $filedir . '/' . $file;

                    $id = trim(str_replace('.info', '', $file));

                    //文件路径
                    $tmppath = $filePath;
                    unset($filePath);
                    //文件信息文件路径
                    $metadatajsonfile = ($this->iscloud) ? $tmppath . '/metadata.json' : $tmppath . BS . 'metadata.json';

                    //尝试获取记录表记录
                    $rdata = C::t('#eagle#eagle_record')->fetch_by_eid($id, $this->appid);
                    $rid = '';
                    if (!isset($rdata['rid'])) {
                        $orid = $id . $this->appid;//原来rid格式
                        if ($lastdate = DB::result_first("select lastdate from %t where rid = %s", array('pichome_resources', $orid))) {
                            $rid = $orid;
                        }

                    } else {
                        $rid = $rdata['rid'];
                        $lastdate = $rdata['dateline'];
                    }
                    //判断是否含有数据信息文件
                    if (IO::checkfileexists($metadatajsonfile)) {
                        $metadatajsonfileinfo = IO::getMeta($metadatajsonfile);
                        $flastdate = $metadatajsonfileinfo['dateline'];
                        $metadata = file_get_contents(IO::getStream($metadatajsonfile));
                        $filemetadata = json_decode($metadata, true);

                        //如果是删除状态，并且已有数据则执行删除
                        if ($filemetadata['isDeleted']) {
                            if ($rid) C::t('pichome_resources')->delete_by_rid($rid);
                        } else {
                            //如果不是新生成rid
                            if ($rid) {
                                $data = C::t('pichome_resources')->fetch($rid);
                                //判断最后更新时间
                                if ($force || $lastdate < $flastdate) {
                                    $filemetadataname = ($this->iscloud) ? $filemetadata['name'] : $this->getFileRealFileName($tmppath, $filemetadata['name'], $filemetadata['ext']);
                                    //文件名称
                                    $filename = $filemetadataname . '.' . $filemetadata['ext'];
                                    //缩略图名称
                                    $thumbname = $filemetadataname . '_thumbnail.png';
                                    //文件路径
                                    $file = ($this->iscloud) ? $tmppath . '/' . $filename : $tmppath . BS . $filename;
                                    //缩略图路径
                                    $thumbfile = ($this->iscloud) ? $tmppath . '/' . $thumbname : $tmppath . BS . $thumbname;
                                    $realfolderdata = [];
                                    if (!empty($filemetadata['folders'])) {
                                        $realfolderdata = C::t('#eagle#eagle_folderrecord')->fetch_fid_by_efid($filemetadata['folders'], $this->appid);
                                    }
                                    $currentperm = (!empty($realfolderdata)) ? max($realfolderdata) : $this->defaultperm;
                                    $realfids = array_keys($realfolderdata);

                                    $haspassword = false;
                                    if (!empty($realfids)) {
                                        //如果目录含有密码则不导入数据直接跳过
                                        $haspassword = C::t('pichome_folder')->check_haspasswrod($realfids, $this->appid);
                                    }
                                    if ($haspassword) {
                                        C::t('pichome_resources')->delete_by_rid($rid);
                                    } else {
                                        //如果mtime发生变化则删除原数据，重新导入
                                        if ($data['mtime'] < $filemetadata['mtime']) {
                                            C::t('pichome_resources')->delete_by_rid($rid);
                                            $filemetadata['rid'] = $this->createrid();
                                            $filemetadata['filename'] = $filemetadata['name'];
                                            $filemetadata['file'] = $file;
                                            $filemetadata['thumbfile'] = $thumbfile;
                                            $filemetadata['rid'] = $rid;
                                            $filemetadata['mtime'] = $filemetadata['mtime'] ? $filemetadata['mtime'] : $filemetadata['modificationTime'];
                                            $filemetadata['btime'] = $filemetadata['btime'] ? $filemetadata['btime'] : $filemetadata['modificationTime'];
                                            $filemetadata['dateline'] = $filemetadata['lastModified'];
                                            $filemetadata['lastdate'] = $flastdate;
                                            $filemetadata['folders'] = $realfids;
                                            $filemetadata['level'] = $currentperm;
                                            $this->exportfile($id, $filemetadata);
                                            unset($filemetadata);
                                        } else {
                                            //信息表数据记录
                                            $setarr = ['appid' => $this->appid];
                                            $setarr['searchval'] = $filemetadata['name'];
                                            //查询原数据中的属性信息
                                            $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                                            $filename = $filemetadata['name'] . '.' . $filemetadata['ext'];
                                            //检查reources数据变化
                                            $resourcesarr = [
                                                'name' => $filename,
                                                'dateline' => $filemetadata['lastModified'],
                                                'isdelete' => $filemetadata['isDeleted'],
                                                'grade' => $filemetadata['star'] ? intval($filemetadata['star']) : 0,
                                                'lastdate' => $flastdate,
                                                'width' => $filemetadata['width'] ? $filemetadata['width'] : 0,
                                                'height' => $filemetadata['height'] ? $filemetadata['height'] : 0,
                                                'appid' => $this->appid,
                                                'level' => $currentperm,
                                                'fids' => implode(',', $realfids)
                                            ];


                                            $file = str_replace('/', BS, $file);
                                            $attachment = str_replace($this->path . BS, '', $file);
                                            $path = str_replace('/', BS, $attachment);
                                            $thumb = IO::checkfileexists($thumbfile) ? 1 : 0;
                                            $thumbfile = str_replace(array(DZZ_ROOT, BS), array('', '/'), $thumbfile);
                                            $setarr['path'] = $path;
                                            $resourcesarr['hasthumb'] = $thumb;
                                            $resourcesarr['rid'] = $rid;
                                            if (C::t('#eagle#eagle_record')->insert_data($id, $resourcesarr)) {
                                                //缩略图数据
                                                /*  $thumbrecorddata = [
                                                      'rid'=>$rid,
                                                      'ext'=>$filemetadata['ext']
                                                  ];
                                                  if($thumb){
                                                      $imgdata = @getimagesize($filemetadata['thumbfile']);
                                                      $swidth = isset($imgdata[0]) ? $imgdata[0] : 0;
                                                      $sheight = isset($imgdata[1]) ? $imgdata[1] : 0;
                                                      $thumbrecorddata['spath'] = $filemetadata['thumbfile'];
                                                      $thumbrecorddata['sstatus'] = 1;
                                                      $thumbrecorddata['swidth'] = $swidth;
                                                      $thumbrecorddata['sheight'] = $sheight;
                                                  }
                                                  C::t('thumb_record')->insert_data($thumbrecorddata);*/
                                                //检查标签变化
                                                //标签数据
                                                $tags = $filemetadata['tags'];
                                                $setarr['searchval'] .= implode('', $tags);
                                                //现有标签
                                                $tagids = [];
                                                //原有标签
                                                $oldtids = [];
                                                if ($attrdata['tag']) $oldtids = explode(',', $attrdata['tag']);

                                                if (!empty($tags)) {
                                                    $tagids = $this->addtag($tags);
                                                    $setarr['tag'] = implode(',', $tagids);
                                                }
                                                $addtags = array_diff($tagids, $oldtids);
                                                $deltags = array_diff($oldtids, $tagids);

                                                if (!empty($deltags)) C::t('pichome_resourcestag')->delete_by_ridtid($rid, $deltags);
                                                foreach ($addtags as $tid) {
                                                    $rtag = ['appid' => $this->appid, 'rid' => $rid, 'tid' => $tid];
                                                    C::t('pichome_resourcestag')->insert($rtag);
                                                }

                                                //检查标注变化
                                                if (isset($filemetadata['comments'])) {
                                                    $cids = [];
                                                    foreach ($filemetadata['comments'] as $commentval) {
                                                        $tcommentval['id'] = $commentval['id'] . $this->appid;
                                                        $tcommentval['appid'] = $this->appid;
                                                        $tcommentval['rid'] = $rid;
                                                        $tcommentval['x'] = number_format($commentval['x'], 2);
                                                        $tcommentval['y'] = number_format($commentval['y'], 2);
                                                        $tcommentval['width'] = $commentval['width'];
                                                        $tcommentval['height'] = $commentval['height'];
                                                        $tcommentval['annotation'] = $commentval['annotation'];
                                                        $tcommentval['lastModified'] = $commentval['lastModified'];
                                                        try {
                                                            C::t('pichome_comments')->insert($tcommentval);
                                                            $setarr['searchval'] .= $tcommentval['annotation'];
                                                        } catch (Exception $e) {

                                                        }
                                                        $cids[] = $tcommentval['id'];
                                                        unset($tcommentval);

                                                    }
                                                    $ocids = C::t('pichome_comments')->fetch_id_by_rid($rid);
                                                    $delcids = array_diff($ocids, $cids);
                                                    if (!empty($delcids)) C::t('pichome_comments')->delete($delcids);
                                                } else {
                                                    C::t('pichome_comments')->delete_by_rid($rid);
                                                }

                                                $rfids = [];
                                                $orfids = C::t('pichome_folderresources')->fetch_id_by_rid($rid);
                                                C::t('pichome_folderresources')->delete($orfids);
                                                $setarr['searchval'] .= $resourcesarr['name'];


                                                //检查目录变化
                                                foreach ($realfids as $fv) {
                                                    $fid = $fv;
                                                    if (!C::t('pichome_folder')->check_password_byfid($fid)) {
                                                        $frsetarr = ['appid' => $this->appid, 'rid' => $rid, 'fid' => $fid];
                                                        C::t('pichome_folderresources')->insert($frsetarr);
                                                        // $fids[] = $fid;
                                                    }
                                                }
                                                //尝试更新属性表数据
                                                $setarr['link'] = $filemetadata['url'] ? trim($filemetadata['url']) : '';
                                                //描述数据
                                                $setarr['desc'] = $filemetadata['annotation'] ? $filemetadata['annotation'] : '';
                                                $setarr['searchval'] .= getstr($setarr['desc'],255) . $setarr['link'];
                                                if ($filemetadata['duration']) $setarr['duration'] = number_format($filemetadata['duration'], 2);
                                                $setarr['rid'] = $rid;
                                                C::t('pichome_resources_attr')->insert($setarr);
                                                unset($filemetadata);
                                                unset($setarr);
                                            }
                                        }
                                    }


                                } else {
                                    if (!$rdata) {
                                        $setarr = [
                                            'appid' => $this->appid,
                                            'rid' => $rid,
                                            'eid' => $id,
                                            'dateline' => $flastdate
                                        ];
                                        C::t('#eagle#eagle_record')->insert($setarr);
                                    }
                                }

                            } else {
                                $realfolderdata = [];
                                if (!empty($filemetadata['folders'])) {
                                    $realfolderdata = C::t('#eagle#eagle_folderrecord')->fetch_fid_by_efid($filemetadata['folders'], $this->appid);

                                }

                                $currentperm = (!empty($realfolderdata)) ? max($realfolderdata) : $this->defaultperm;
                                $realfids = array_keys($realfolderdata);
                                if (!empty($realfids)) {
                                    //如果目录含有密码则不导入数据直接跳过
                                    $haspassword = C::t('pichome_folder')->check_haspasswrod($realfids, $this->appid);
                                } else {
                                    $haspassword = false;
                                }

                                if (!$haspassword) {
                                    $filemetadataname = ($this->iscloud) ? $filemetadata['name'] : $this->getFileRealFileName($tmppath, $filemetadata['name'], $filemetadata['ext']);

                                    $filename = $filemetadataname . '.' . $filemetadata['ext'];
                                    $thumbname = $filemetadataname . '_thumbnail.png';

                                    $file = ($this->iscloud) ? $tmppath . '/' . $filename : $tmppath . BS . $filename;
                                    $thumbfile = ($this->iscloud) ? $tmppath . '/' . $thumbname : $tmppath . BS . $thumbname;
                                    $filemetadata['filename'] = $filemetadata['name'];
                                    $filemetadata['file'] = $file;
                                    unset($file);
                                    $filemetadata['thumbfile'] = $thumbfile;
                                    $filemetadata['folders'] = $realfids;
                                    $filemetadata['level'] = $currentperm;
                                    unset($thumbfile);
                                    $filemetadata['rid'] = $this->createrid();
                                    $filemetadata['mtime'] = $filemetadata['mtime'] ? $filemetadata['mtime'] : $filemetadata['modificationTime'];
                                    $filemetadata['btime'] = $filemetadata['btime'] ? $filemetadata['btime'] : $filemetadata['modificationTime'];
                                    $filemetadata['dateline'] = $filemetadata['lastModified'];
                                    $filemetadata['lastdate'] = $flastdate;

                                    $this->exportfile($id, $filemetadata);
                                    unset($filemetadata);
                                }
                            }

                        }
                    } else {
                        //如果已有数据删除，否则不做处理
                        if (!$rid) C::t('pichome_resources')->delete_by_rid($rid);
                    }


                    $this->donum += 1;
                    $percent = floor(($this->donum / $this->filenum) * 100);
                    //防止因获取文件总个数不准确百分比溢出
                    $percent = ($percent > 100) ? 100 : $percent;
                    C::t('pichome_vapp')->update($this->appid, array( 'percent' => $percent, 'donum' => $this->donum, 'state' =>2, 'filenum' => $this->filenum));
                }
            }
        }


        if ($returndata['IsTruncated']) {
            $lastid = $returndata['NextMarker'];
            $state = 2;
        } else {
            $state = 3;

        }
        C::t('pichome_vapp')->update($this->appid, array('lastid' => $lastid, 'percent' => $percent, 'donum' => $this->donum, 'state' => $state, 'filenum' => $this->filenum));
        return array('success' => true);
    }

    //校验文件
    public function execCheckFile()
    {

        if ($this->exportstatus == 3) {
            $total = DB::result_first("select count(rid) from %t where appid = %s ", array('pichome_resources', $this->appid));
            //校验文件
            $this->check_file($total);
        }
        return true;
    }

    public function check_file($total)
    {

        if ($this->lastid < 1) $this->lastid = 1;
        $limitsql = ($this->lastid - 1) * $this->checklimit . ',' . $this->checklimit;
        $delrids = [];
        $data = DB::fetch_all("select rid,isdelete from %t where appid = %s order by lastdate asc limit $limitsql ", array('pichome_resources', $this->appid));
        if (empty($data)) {
            C::t('pichome_vapp')->update($this->appid, array('percent' => 0, 'state' => 4, 'lastid' => 0, 'donum' => 0));
            //校验完成后更新目录文件数
            foreach (DB::fetch_all("select count(rf.id) as num,f.fid  from %t f left join %t rf on rf.fid=f.fid where f.appid = %s group by f.fid", array('pichome_folder', 'pichome_folderresources', $this->appid)) as $v) {
                C::t('pichome_folder')->update($v['fid'], array('filenum' => $v['num']));

            }
            //修正库中文件数
            $total = DB::result_first("select count(rid) from %t where appid = %s ", array('pichome_resources', $this->appid));
            $hascatnum = DB::result_first("SELECT count(DISTINCT rid) FROM %t where appid = %s", array('pichome_folderresources', $this->appid));
            $nosubfilenum = $total - $hascatnum;
            C::t('pichome_vapp')->update($this->appid, array('filenum' => $total, 'nosubfilenum' => $nosubfilenum));
            return true;
        }
        foreach ($data as $v) {
            if (dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
            if ($v['isdelete']) {
                $delrids[] = $v['rid'];
            } else {
                $id = C::t('#eagle#eagle_record')->fetch_eid_by_rid($v['rid'], $this->appid);
                if (!$id) {
                    $delrids[] = $v['rid'];
                } else {
                    $filejson = ($this->iscloud) ? $this->path . '/images/' . $id . '.info/metadata.json' : $this->path . BS . 'images' . BS . $id . '.info' . BS . 'metadata.json';
                    if (!IO::checkfileexists($filejson)) {
                        $delrids[] = $v['rid'];
                    }
                }

            }


        }
        if (!empty($delrids)) {
            //如果有需要删除的，删除后，则重新查询上一页数据
            C::t('pichome_resources')->delete_by_rid($delrids);
            if ($this->lastid == 1) {
                $percent = round(($this->checklimit / $total) * 100);
            } else {
                $percent = round((($this->lastid - 1) * $this->checklimit / $total) * 100);
            }
            C::t('pichome_vapp')->update($this->appid, array('lastid' => $this->lastid, 'percent' => $percent, 'state' => 3));
        } else {
            if ($this->lastid == 1) {
                $percent = round(($this->checklimit / $total) * 100);
            } else {
                $percent = round((($this->lastid - 1) * $this->checklimit / $total) * 100);
            }
            $percent = ($percent > 100) ? 100 : $percent;
            C::t('pichome_vapp')->update($this->appid, array('lastid' => $this->lastid + 1, 'percent' => $percent, 'state' => 3));
        }
    }

    public function exportfile($id, $filemetadata)
    {
        $rid = $filemetadata['rid'];

        if (!IO::checkfileexists($filemetadata['file'])) {
            return;
        }
        if (dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
        //$filemetadata['file'] = ($this->iscloud) ?  str_replace(BS, '/', $filemetadata['file']):str_replace('/', BS, $filemetadata['file']);
        $attachment = ($this->iscloud) ? str_replace(BS, '/', $filemetadata['file']) : str_replace('/', BS, $filemetadata['file']);
        $path = ($this->iscloud) ? str_replace($this->path . '/', '', $attachment) : str_replace($this->path . BS, '', $attachment);
        unset($attachment);
        $thumb = IO::checkfileexists($filemetadata['thumbfile']) ? 1 : 0;
        $filemetadata['thumbfile'] = str_replace(array(DZZ_ROOT, BS), array('', '/'), $filemetadata['thumbfile']);
        $type = getTypeByExt($filemetadata['ext']);
        $resourcesarr = [
            'rid' => $rid,
            'uid' => $this->uid,
            'username' => $this->username,
            'appid' => $this->appid,
            'width' => $filemetadata['width'] ? $filemetadata['width'] : 0,
            'height' => $filemetadata['height'] ? $filemetadata['height'] : 0,
            'name' => $filemetadata['filename'] . '.' . $filemetadata['ext'],
            'ext' => $filemetadata['ext'],
            'size' => $filemetadata['size'],
            'dateline' => $filemetadata['dateline'],
            'btime' => $filemetadata['btime'],
            'mtime' => $filemetadata['mtime'],
            'isdelete' => $filemetadata['isDeleted'],
            'hasthumb' => $thumb,
            'grade' => $filemetadata['star'] ? intval($filemetadata['star']) : 0,
            'type' => $type,
            'lastdate' => $filemetadata['lastdate'],
            'fids' => implode(',', $filemetadata['folders']),
            'level' => $filemetadata['level']
        ];
        unset($type);
        //插入文件表数据
        if (C::t('#eagle#eagle_record')->insert_data($id, $resourcesarr)) {
            /*  $thumbrecorddata = [
                  'rid'=>$rid,
                  'ext'=>$filemetadata['ext']
              ];
              if($resourcesarr['hasthumb']){
                  $imgdata = @getimagesize($filemetadata['thumbfile']);
                  $swidth = isset($imgdata[0]) ? $imgdata[0] : 0;
                  $sheight = isset($imgdata[1]) ? $imgdata[1] : 0;
                  $thumbrecorddata['spath'] = $filemetadata['thumbfile'];
                  $thumbrecorddata['sstatus'] = 1;
                  $thumbrecorddata['swidth'] = $swidth;
                  $thumbrecorddata['sheight'] = $sheight;
              }
              C::t('thumb_record')->insert_data($thumbrecorddata);*/
            DB::delete('pichome_folderresources', array('rid' => $rid));
            //获取属性表数据
            $setarr = [];
            $setarr['searchval'] = $resourcesarr['name'];
            //$fids = [];
            //插入目录关联表数据
            foreach ($filemetadata['folders'] as $fv) {
                $fid = $fv;
                if (!C::t('pichome_folder')->check_password_byfid($fid)) {
                    $frsetarr = ['appid' => $this->appid, 'rid' => $rid, 'fid' => $fid];
                    C::t('pichome_folderresources')->insert($frsetarr);
                    //$fids[] = $fid;
                    unset($frsetarr);
                }
            }
            /* if(!empty($fids)){
                 foreach(DB::fetch_all("select fname from %t where fid in(%n)",array('pichome_folder',$fids)) as $foldername){
                     $setarr['searchval'] .= $foldername['fname'];
                 }
             }*/

            //标签数据
            $tags = $filemetadata['tags'];
            $setarr['searchval'] .= implode('', $tags);
            if (!empty($tags)) {
                $tagids = $this->addtag($tags);
                unset($tags);
                foreach ($tagids as $tid) {
                    $rtag = ['appid' => $this->appid, 'rid' => $rid, 'tid' => $tid];
                    C::t('pichome_resourcestag')->insert($rtag);
                }
                $setarr['tag'] = implode(',', $tagids);
                unset($tagids);
            }
            //颜色数据
            if (isset($filemetadata['palettes'])) {
                $returndata = $this->getColor($filemetadata['palettes'], $resourcesarr['width'], $resourcesarr['height'], $rid);
                $setarr['colors'] = $returndata['colors'];
                $setarr['gray'] = $returndata['gray'];
                $setarr['shape'] = $returndata['shape'];
                unset($returndata);
            }
            //标注数据
            if (isset($filemetadata['comments'])) {
                foreach ($filemetadata['comments'] as $commentval) {
                    $tcommentval['id'] = $commentval['id'] . $this->appid;
                    $tcommentval['appid'] = $this->appid;
                    $tcommentval['rid'] = $rid;
                    $tcommentval['x'] = number_format($commentval['x'], 2);
                    $tcommentval['y'] = number_format($commentval['y'], 2);
                    $tcommentval['width'] = $commentval['width'];
                    $tcommentval['height'] = $commentval['height'];
                    $tcommentval['annotation'] = $commentval['annotation'];
                    $tcommentval['lastModified'] = $commentval['lastModified'];
                    C::t('pichome_comments')->insert($tcommentval);
                    $setarr['searchval'] .= $commentval['annotation'];
                    unset($tcommentval);
                }
            }
            //时长
            if ($filemetadata['duration']) $setarr['duration'] = number_format($filemetadata['duration'], 2);
            //链接数据
            $setarr['link'] = $filemetadata['url'] ? trim($filemetadata['url']) : '';
            //描述数据
            $setarr['desc'] = $filemetadata['annotation'] ? $filemetadata['annotation'] : '';
            $setarr['searchval'] .= getstr($setarr['desc'],255) . $setarr['link'];
            $setarr['rid'] = $rid;
            $setarr['appid'] = $this->appid;
            $setarr['path'] = $path;
            //echo '属性插入前缓存:'.memory_get_usage()/1024 . '<br>';
            //插入数据
            C::t('pichome_resources_attr')->insert($setarr);
            //echo '属性插入后缓存:'.memory_get_usage()/1024 . '<br>';
        } else {
            runlog('eagleexport', $rid);
        }
    }

    //判断图片纯色
    public function isgray($colors)
    {
        $i = 0;
        if (count($colors) < 1) return 0;
        foreach ($colors as $color) {
            $color = new Color($color);
            $rgb = $color->toRGB();
            unset($color);
            if (abs($rgb[0] - $rgb[1]) < 10 && abs($rgb[2] - $rgb[1]) < 10) {
                $i++;
            }
            unset($rgb);
        }
        if ($i == count($colors)) {
            return 1;
        } else {
            return 0;
        }
    }

    //获取颜色数据
    public function getColor($colors, $width, $height, $rid)
    {
        //echo '颜色处理前:'.memory_get_usage()/1024 . '<br>';
        $intcolorsarr = $palettesnum = $returndata = [];
        $i = 1;
        foreach ($colors as $c) {
            $color = new \Color($c['color']);
            //获取颜色整型值
            $intcolor = $color->toInt();
            $intcolorsarr[] = $intcolor;
            $palettesnum[] = $p = $this->getPaletteNumber($intcolor);
            $palattedataarr = ['rid' => $rid, 'color' => $intcolor, 'weight' => $c['ratio'],
                'r' => $c['color'][0], 'g' => $c['color'][1], 'b' => $c['color'][2], 'p' => $p];
            C::t('pichome_palette')->insert($palattedataarr);

            $i++;
        }
        unset($colors);
        //颜色整型值数据
        $returndata['colors'] = implode(',', $palettesnum);
        $returndata['gray'] = $this->isgray($intcolorsarr);
        $returndata['shape'] = round(($width / $height) * 100);
        unset($intcolorsarr);

        return $returndata;
    }

    public function getPaletteNumber($colors, $palette = array())
    {

        if (empty($palette)) $palette = $this->palette;
        $arr = array();

        if (is_array($colors)) {
            $isarray = 1;
        } else {
            $colors = (array)$colors;
            $isarray = 0;
        }

        foreach ($colors as $color) {
            $bestColor = 0x000000;
            $bestDiff = PHP_INT_MAX;
            $color = new Color($color);
            foreach ($palette as $key => $wlColor) {
                // calculate difference (don't sqrt)
                $diff = $color->getDiff($wlColor);
                // see if we got a new best
                if ($diff < $bestDiff) {
                    $bestDiff = $diff;
                    $bestColor = $wlColor;
                }
            }
            unset($color);
            $arr[] = array_search($bestColor, $palette);
        }
        return $isarray ? $arr : $arr[0];
    }

    //添加标签
    public function addtag($tags)
    {
        $tagids = [];
        foreach ($tags as $v) {
            if (!preg_match('/^\s*$/', $v)) {
                if ($tid = C::t('pichome_tag')->insert($v)) {
                    $tagids[] = $tid;
                }
            }

        }
        unset($tags);
        return $tagids;
    }
}