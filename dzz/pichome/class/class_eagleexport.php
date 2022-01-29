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
    private $onceexportnum = 1000;
    private $checknum = 0;
    private $eagledir = DZZ_ROOT . 'library';
    private $readtxt = DZZ_ROOT . './data/attachment/cache/';
    private $exportstatus = 0;
    private $donum = 0;
    private $lastid = '';

    public function __construct($data = array())
    {
        //获取导入记录表基本数据
        $this->path = str_replace('/', BS, $data['path']);;
        $this->appid = $data['appid'];
        $this->uid = $data['uid'];
        $this->username = $data['username'];
        $this->exportstatus = $data['state'];
        $this->donum = $data['donum'];
        $this->filenum = $data['filenum'];
        $this->lastid = $data['lastid'];
    }
    public function getpathdata($folderdata,$appid, $pathdata = array())
    {
        foreach ($folderdata as $v) {
            $pathdata[$v['id'].$appid] =  $v['name'];
            if ($v['children']) {
                $tmpchild = $v['children'];
                $pathdata = $this->getpathdata($tmpchild, $appid,$pathdata);

            }
        }

        return $pathdata;
    }
    public function initFoldertag(){
        $jsonfile = $this->path . BS . 'metadata.json';
        $mtime = filemtime($jsonfile);
        $appdatas = file_get_contents($jsonfile);
        //解析出json数据
        $appdatas = json_decode($appdatas, true);

        //目录数据
        $folderdata = $appdatas['folders'];

        C::t('pichome_folder')->insert_folderdata_by_appid($this->appid,$folderdata);
        //对比目录数据
        $folderarr = $this->getpathdata($folderdata,$this->appid);
        $folderfids = array_keys($folderarr);
        $delfids = [];
        foreach(DB::fetch_all("select fid from %t where fid not in(%n) and appid = %s",array('pichome_folder',$folderfids,$this->appid)) as $v){
            $delfids[] = $v['fid'];
        }
        C::t('pichome_folder')->delete($delfids);
        //标签数据
        $tagdata = $appdatas['tagsGroups'];
        $currentcids = [];
        $tids = [];
        foreach($tagdata as $v){
            $taggroupdata = [
                'cid'=>$v['id'].$this->appid,
                'catname'=>$v['name'],
                'appid'=>$this->appid,
                'dateline'=>TIMESTAMP
            ];
            //插入或更新标签分类数据
            $cid = C::t('pichome_taggroup')->insert($taggroupdata);
            $currentcids[] = $cid;

            foreach($v['tags'] as $val){
                $tid = C::t('pichome_tag')->insert($val,1);
                $tids[] = $tid;
                if($cid){
                    $relasetarr = ['cid'=>$cid,'tid'=>$tid,'appid'=>$this->appid];
                    C::t('pichome_tagrelation')->insert($relasetarr);
                }
            }
        }
        if($tids){
            //查询关系表中包含的不存在的标签关系
            $drids = [];
            foreach(DB::fetch_all("select id from %t where tid  not in(%n)  and appid = %s",array('pichome_tagrelation',$tids,$this->appid)) as $rv){
                $drids[] = $rv['id'];
            }
            //删除不存在的标签关系数据
            C::t('pichome_tagrelation')->delete($drids);
        }
        $ocids = C::t('pichome_taggroup')->fetch_cid_by_appid($this->appid);
        $delcids = array_diff($ocids,$currentcids);
        C::t('pichome_taggroup')->delete_by_cids($delcids);
        C::t('pichome_vapp')->update($this->appid,array('dateline'=>$mtime));
        return true;

    }
    public function initExport()
    {
        $filedir =  $this->path . BS.'images';
        $readtxt = $this->readtxt . 'eagleexport' . md5($this->path) . '.txt';
        $filenum = 0;
        if (!is_file($readtxt) || filemtime($readtxt) < filemtime( $this->path . BS.'metadata.json')) {
            C::t('pichome_vapp')->update($this->appid, array( 'state' => 1));
            if ($dch = opendir($filedir)) {
                $thandle = fopen($readtxt, 'w+');
                while (($file = readdir($dch)) != false) {
                    if ($file != '.' && $file != '..') {
                        $filePath = $filedir . '/' . $file;
                        if (is_dir($filePath) && is_file($filePath . '/metadata.json')) {
                            $filenum++;
                            fwrite($thandle, $file . "\n");
                        }
                        unset($filePath);
                        unset($file);
                    }
                }
                fclose($thandle);
                closedir($dch);
                if ($filenum) $this->filenum = $filenum;
            } else {
                C::t('pichome_vapp')->update($this->appid, array('state' => 0));
                return array('error' => 'Read Dir Failer');
            }

            C::t('pichome_vapp')->update($this->appid, array('filenum' => $this->filenum, 'state' => 2));
        }
        C::t('pichome_vapp')->update($this->appid, array('state' => 2));
        $this->initFoldertag();
        return array('success' => true);
    }

    //获取文件可访问的真实地址
    public function getFileRealFileName($filepath,$filename,$ext){
        $charsetarr = ['GBK','GB18030'];
        $returnname = $filename;
        if(!is_file($filepath.BS.$filename.'.'.$ext)){
            foreach ($charsetarr as $v){
                $filemetadataname = diconv($filename, CHARSET, $v);
                if(is_file($filepath.BS.$filemetadataname.'.'.$ext)){
                    $returnname = $filemetadataname;
                    break;
                }
            }
        }
        return $returnname;

    }
    public function execExport($force = false)
    {
        $filedir = $this->path . BS.'images';
        $readtxt = $this->readtxt . 'eagleexport' . md5($this->path) . '.txt';
        if(filesize($readtxt) == 0){
            @unlink($readtxt);
            C::t('pichome_vapp')->update($this->appid, array('lastid' => 0, 'percent' => 100, 'donum' => 0, 'state' => 3,'filenum'=>$this->filenum));
            return array('success'=>true);

        }
        if ($this->lastid) {
            $start = $this->lastid * 19;
        } else $start = 0;
        if ($this->lastid < $this->filenum && $this->exportstatus == 2) {
            $tfile = fopen($readtxt, 'r');
            $i = 0;
            fseek($tfile, $start);
            while ($tfile && !feof($tfile)) {
                ++$i;
                if ($i > $this->onceexportnum) {
                    break;
                }
                $file = trim(fgets($tfile));

                $filePath = $filedir . '/' . $file;

                $id = str_replace('.info', '', $file);
                //组合文件id
                $rid = $id . $this->appid;
                //文件路径
                $tmppath = $filePath;
                unset($filePath);
                //文件信息文件路径
                $metadatajsonfile = $tmppath . BS . 'metadata.json';

                $data = C::t('pichome_resources')->fetch($rid);
                //获取json文件最后修改时间
                $lastdate = filemtime($metadatajsonfile);
                $metadata = file_get_contents($metadatajsonfile);
                $filemetadata = json_decode($metadata, true);
                //查询文件表是否有对应记录
                if ($data) {

                    //json文件不存在时删除文件，此情形基本不会出现(文件更新时，mtime会对应发生变化)
                    if (!is_file($metadatajsonfile)) {
                        //删除文件
                        C::t('pichome_resources')->delete_by_rid($rid);

                    }
                    elseif ($force || ($data['lastdate'] < $lastdate)) {//当文件最后更新时间小于eagle时间时，需处理标签，颜色，目录等信息
                        if($filemetadata['isDeleted']){
                            C::t('pichome_resources')->delete_by_rid($rid);
                        }
                        else{
                            $filemetadataname = $this->getFileRealFileName($tmppath,$filemetadata['name'],$filemetadata['ext']);
                            //文件名称
                            $filename = $filemetadataname . '.' . $filemetadata['ext'];
                            //缩略图名称
                            $thumbname = $filemetadataname . '_thumbnail.png';
                            //文件路径
                            $file = $tmppath . BS . $filename;
                            //缩略图路径
                            $thumbfile = $tmppath . BS . $thumbname;

                            //如果mtime发生变化则删除原数据，重新导入
                            if ($data['mtime'] < $filemetadata['mtime']) {
                                C::t('pichome_resources')->delete_by_rid($rid);
                                $filemetadata['filename'] = $filemetadata['name'];
                                $filemetadata['file'] = $file;
                                $filemetadata['thumbfile'] = $thumbfile;
                                $filemetadata['rid'] = $rid;
                                $filemetadata['mtime'] = $filemetadata['mtime'] ? $filemetadata['mtime'] : $filemetadata['modificationTime'];
                                $filemetadata['btime'] = $filemetadata['btime'] ? $filemetadata['btime'] : $filemetadata['modificationTime'];
                                $filemetadata['dateline'] = $filemetadata['lastModified'];
                                $filemetadata['lastdate'] = $lastdate;
                                $this->exportfile($filemetadata);
                                unset($filemetadata);
                            } else {
                                //信息表数据记录
                                $setarr = [];
                                $setarr['searchval'] = $filemetadata['name'];
                                //查询原数据中的属性信息
                                $attrdata = C::t('pichome_resources_attr')->fetch($rid);

                                //检查标签变化
                                //标签数据
                                $tags = $filemetadata['tags'];
                                $setarr['searchval'] .= implode('',$tags);
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
                                        $tentval['appid'] = $this->appid;
                                        $tcommentval['rid'] = $rid;
                                        $tcommentval['x'] = number_format($commentval['x'], 2);
                                        $tcommentval['y'] = number_format($commentval['y'], 2);
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

                                $filename = $filemetadata['name'] . '.' . $filemetadata['ext'];
                                //检查reources数据变化
                                $resourcesarr = [
                                    'name' => $filename,
                                    'dateline' => $filemetadata['lastModified'],
                                    'isdelete' => $filemetadata['isDeleted'],
                                    'grade' => $filemetadata['star'] ? intval($filemetadata['star']) : 0,
                                    'lastdate' => $lastdate
                                ];
                                $file = str_replace('/', BS, $file);
                                $attachment = str_replace($this->path . BS, '', $file);
                                $path = str_replace('/', BS, $attachment);
                                $thumb = (is_file($thumbfile)) ? 1 : 0;
                                $setarr['path'] = $path;
                                $resourcesarr['hasthumb'] = $thumb;
                                $resourcesarr['rid'] = $rid;
                                C::t('pichome_resources')->insert($resourcesarr);
                                $rfids = [];
                                $orfids = C::t('pichome_folderresources')->fetch_id_by_rid($rid);
                                C::t('pichome_folderresources')->delete($orfids);
                                $setarr['searchval'] .= $resourcesarr['name'];
                                //$fids = [];
                                //检查目录变化
                                foreach ($filemetadata['folders'] as $fv) {
                                    $fid = trim($fv) . $this->appid;
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
                                $setarr['searchval'] .= $setarr['desc'].$setarr['link'];
                                if ($filemetadata['duration']) $setarr['duration'] = number_format($filemetadata['duration'], 2);
                                $setarr['rid'] = $rid;
                                C::t('pichome_resources_attr')->insert($setarr);
                                unset($filemetadata);
                                unset($setarr);
                            }
                        }

                    }
                    else {
                        unset($data);
                    }
                }
                else {//如果没有记录，则为新导入文件

                    if (is_file($metadatajsonfile)) {
                        if($filemetadata['isDeleted']){
                            //$this->filenum -= 1;
                        }else{
                            //如果目录含有密码则不导入数据直接跳过
                            $haspassword = C::t('pichome_folder')->check_haspasswrod($filemetadata['folders'], $this->appid);

                            if (!$haspassword){
                                $filemetadataname = $this->getFileRealFileName($tmppath,$filemetadata['name'],$filemetadata['ext']);
                                //echo $filemetadataname;die;
                                $filename = $filemetadataname . '.' . $filemetadata['ext'];
                                $thumbname = $filemetadataname . '_thumbnail.png';
                                //echo $i.'middle:'.memory_get_usage()/1024 . '<br>';
                                $file = $tmppath . BS . $filename;
                                $thumbfile = $tmppath . BS . $thumbname;
                                //$filemd5 = md5_file($file);
                                $filemetadata['filename'] = $filemetadata['name'];
                                $filemetadata['file'] = $file;
                                unset($file);
                                $filemetadata['thumbfile'] = $thumbfile;
                                unset($thumbfile);
                                // $filemetadata['md5'] = $filemd5;
                                $filemetadata['rid'] = $rid;
                                $filemetadata['mtime'] = $filemetadata['mtime'] ? $filemetadata['mtime'] : $filemetadata['modificationTime'];
                                $filemetadata['btime'] = $filemetadata['btime'] ? $filemetadata['btime'] : $filemetadata['modificationTime'];
                                $filemetadata['dateline'] = $filemetadata['lastModified'];
                                $filemetadata['lastdate'] = $lastdate;

                                $this->exportfile($filemetadata);
                                unset($filemetadata);
                            }
                        }


                    }

                }
                $this->donum += 1;
                $percent = floor(($this->donum / $this->filenum) * 100);
                //防止因获取文件总个数不准确百分比溢出
                $percent = ($percent > 100) ? 100 : $percent;
                $state = ($percent >= 100) ? 3 : 2;
                if ($state == 3) {
                    fclose($tfile);
                    @unlink($this->readtxt . 'eagleexport' . md5($this->path) . '.txt');
                    $tfile = false;
                    $lastid = 0;
                } else {
                    $lastid = $this->donum;
                }
                //记录导入起始位置，以备中断后从此处,更改导入状态为正在导入
                C::t('pichome_vapp')->update($this->appid, array('lastid' => $lastid, 'percent' => $percent, 'donum' => $this->donum, 'state' => $state,'filenum'=>$this->filenum));

            }


        }

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
            foreach(DB::fetch_all("select count(rf.id) as num,f.fid  from %t f left join %t rf on rf.fid=f.fid where f.appid = %s group by f.fid",array('pichome_folder','pichome_folderresources',$this->appid)) as $v){
                C::t('pichome_folder')->update($v['fid'],array('filenum'=>$v['num']));

            }
            //修正库中文件数
            $total = DB::result_first("select count(rid) from %t where appid = %s ", array('pichome_resources', $this->appid));
            $hascatnum = DB::result_first("SELECT count(DISTINCT rid) FROM %t where appid = %s",array('pichome_folderresources',$this->appid));
            $nosubfilenum = $total - $hascatnum;
            C::t('pichome_vapp')->update($this->appid,array('filenum'=>$total,'nosubfilenum'=>$nosubfilenum));
            return true;
        }

        foreach ($data as $v) {
            if($v['isdelete']){
                $delrids[] = $v['rid'];
            }else{
                $id = str_replace($this->appid, '', $v['rid']);
                $filejson =  $this->path . BS.'images'.BS . $id . '.info'.BS.'metadata.json';
                if (!is_file($filejson)) {
                    $delrids[] = $v['rid'];
                }
            }


        }
        if (!empty($delrids)) {
            //如果有需要删除的，删除后，则重新查询上一页数据
            C::t('pichome_resources')->delete_by_rid($delrids);
            if($this->lastid == 1){
                $percent = round(($this->checklimit / $total) * 100);
            }else{
                $percent = round((($this->lastid - 1) * $this->checklimit / $total) * 100);
            }
            C::t('pichome_vapp')->update($this->appid, array('lastid' => $this->lastid, 'percent' => $percent, 'state' => 3));
        } else {
            if($this->lastid == 1){
                $percent = round(($this->checklimit / $total) * 100);
            }else{
                $percent = round((($this->lastid - 1) * $this->checklimit / $total) * 100);
            }
            $percent = ($percent > 100) ? 100:$percent;
            C::t('pichome_vapp')->update($this->appid, array('lastid' => $this->lastid + 1, 'percent' => $percent, 'state' => 3));
        }
    }

    public function exportfile($filemetadata)
    {
        $rid = $filemetadata['rid'];
        if (!is_file($filemetadata['file'])) {
            return;
        }

        $filemetadata['file'] = str_replace('/', BS, $filemetadata['file']);
        $attachment = str_replace('/', BS, $filemetadata['file']);
        $path = str_replace($this->path . BS, '', $attachment);
        unset($attachment);
        $thumb = (is_file($filemetadata['thumbfile'])) ? 1 : 0;
        //echo 'middle1:'.memory_get_usage()/1024 . '<br>';
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
            'lastdate' => $filemetadata['lastdate']
        ];
        unset($type);
        //插入文件表数据
        if (C::t('pichome_resources')->insert($resourcesarr,1)) {
            DB::delete('pichome_folderresources',array('rid'=>$rid));
            //获取属性表数据
            $setarr = [];
            $setarr['searchval'] = $resourcesarr['name'];
            //$fids = [];
            //插入目录关联表数据
            foreach ($filemetadata['folders'] as $fv) {
                $fid = $fv . $this->appid;
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
            $setarr['searchval'] .= implode('',$tags);
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
                    $t['y'] = number_format($commentval['y'], 2);
                    C::t('pichome_comments')->insert($commentval);
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
            $setarr['searchval'] .= $setarr['desc'].$setarr['link'];
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
        $intcolorsarr = $returndata = [];
        $i = 1;
        foreach ($colors as $c) {
            $color = new \Color($c['color']);
            //获取颜色整型值
            $intcolor = $color->toInt();
            $palattedataarr = ['rid' => $rid, 'color' => $intcolor, 'weight' => $c['ratio'], 'r' => $c['color'][0], 'g' => $c['color'][1], 'b' => $c['color'][2]];
            $intcolorsarr[] = $intcolor;
            //echo "颜色处理中前 $i :".memory_get_usage()/1024 . '<br>';
            C::t('pichome_palette')->insert($palattedataarr);
            //echo "颜色处理中后 $i :".memory_get_usage()/1024 . '<br>';
            $i++;
        }
        unset($colors);
        //颜色整型值数据
        // $intcolorsarr= array_keys($palattedataarr);
        $returndata['colors'] = implode(',', $intcolorsarr);
        $returndata['gray'] = $this->isgray($intcolorsarr);
        $returndata['shape'] = round(($width / $height) * 100);
        unset($intcolorsarr);
        //echo '颜色处理后缓存：'.memory_get_usage()/1024 . '<br>';
        return $returndata;
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