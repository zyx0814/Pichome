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
        private $checklimit = 100;
        private $checknum = 0;
        private $eagledir =DZZ_ROOT.'library';
        private $exportstatus = 0;
        private $donum = 0;
        private $lastid = '';
    
        public function __construct($data = array())
        {
            //获取导入记录表基本数据
            $this->path = $data['path'];
            $this->appid = $data['appid'];
            $this->uid = $data['uid'];
            $this->username = $data['username'];
            $this->exportstatus = ($data['status'] == 1) ? 1:0;
           // $this->donum = $data['donum'];
            $this->eagledir = str_replace(BS,'/',$this->eagledir);
            $this->lastid = $data['lastid'];
        }
        
        public function exportAloneFile($rid,$force = 0)
        {
            $data = C::t('pichome_resources')->fetch($rid);
            $this->appid = $data['appid'];
            //获得文件对应目录id
            $id = str_replace($this->appid, '', $rid);
            $appdata = C::t('pichome_vapp')->fetch($this->appid);
            $path = $appdata['path'];
            $filepath = $this->eagledir .BS . $path . BS . 'images' . BS . $id . '.info';
            //文件信息文件路径
            $metadatajsonfile = $filepath . BS . 'metadata.json';
            if (!file_exists($metadatajsonfile)) {
                C::t('pichome_resources')->delete_by_rid($rid);
                return true;
            }
            
            $this->uid = $data['uid'];
            $this->username = $data['username'];
            //文件信息json文件
            $mjsonfile = $this->eagledir . BS . $path . BS . 'mtime.json';
            $allfiledata = file_get_contents($mjsonfile);
            //获取文件信息
            $allfiledata = json_decode($allfiledata, true);
            
            unset($allfiledata['all']);
            
            if ($force || $data['dateline'] < $allfiledata[$id]) {//当文件最后更新时间小于eagle时间时，需处理标签，颜色，目录等信息
                //获取文件信息数据
                $metadata = file_get_contents($metadatajsonfile);
                $filemetadata = json_decode($metadata, true);
                $p = new Encode_Core();
                $charset = $p->get_encoding($filepath);
                if(CHARSET != $charset)$filemetadataname= diconv($filemetadata['name'],CHARSET,$charset);
                //文件名称
                $filename = $filemetadataname . '.' . $filemetadata['ext'];
                //缩略图名称
                $thumbname =$filemetadataname . '_thumbnail.png';
                //文件路径
                $file = $filepath . BS . $filename;
                if (!file_exists($file)) {
                    return ;
                }
                //如果文件不存在删除
                if (!file_exists($file)) {
                    C::t('pichome_resources')->delete_by_rid($rid);
                    return true;
                }
                //缩略图路径
                $thumbfile = $filepath . BS . $thumbname;
                
                
                //检查md5是否发生变化
                $filemd5 = md5_file($file);
                //如果md5发生变化则删除原数据，重新导入
                if ($data['md5'] != $filemd5) {
                    C::t('pichome_resources')->delete_by_rid($rid);
                    $filemetadata['filename'] = $filemetadata['name'];
                    $filemetadata['file'] = $file;
                    $filemetadata['thumbfile'] = $thumbfile;
                    $filemetadata['md5'] = $filemd5;
                    $filemetadata['rid'] = $rid;
                    $filemetadata['dateline'] = $allfiledata[$id];
                    $this->exportfile($filemetadata);
                } else {
                    //查询原数据中的属性信息
                    $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                    //信息表数据记录
                    $setarr = [];
                    //检查标签变化
                    //标签数据
                    $tags = $filemetadata['tags'];
                    //现有标签
                    $tagids = [];
                    //原有标签
                    $oldtids=[];
                    if ($attrdata['tag']) $oldtids = explode(',', $attrdata['tag']);
                    if (!empty($tags)) {
                        $tagids = $this->addtag($tags);
                        $setarr['tag'] = implode(',',$tagids);
                    }
                    /* $addtags = array_diff($tagids,$oldtids);
                     $deltags = array_diff($oldtids,$tagids);
                    
                     if(!empty($deltags))C::t('pichome_resourcestag')->delete_by_ridtid($rid,$deltags);
                     foreach($addtags as $tid){
                         $rtag = ['appid' => $this->appid, 'rid' => $rid, 'tid' => $tid];
                         C::t('pichome_resourcestag')->insert($rtag);
                     }*/
                    //检查标注变化
                    if (isset($filemetadata['comments'])) {
                        $cids = [];
                        foreach ($filemetadata['comments'] as $commentval) {
                            $commentval['id'] = $commentval['id'] . $this->appid;
                            $commentval['appid'] = $this->appid;
                            $commentval['rid'] = $rid;
                            $commentval['x'] = number_format($commentval['x'], 2);
                            $commentval['y'] = number_format($commentval['y'], 2);
                            C::t('pichome_comments')->insert($commentval);
                            $cids[] = $commentval['id'];
                        }
                        $ocids = C::t('pichome_comments')->fetch_id_by_rid($rid);
                        $delcids = array_diff($ocids, $cids);
                        if (!empty($delcids)) C::t('pichome_comments')->delete($delcids);
                    } else {
                        C::t('pichome_comments')->delete_by_rid($rid);
                    }
                    
                    $thumb = file_exists($thumbfile) ? 1 : 0;
                    //检查reources数据变化
                    $resourcesarr = [
                        'name' => $filename,
                        'dateline' => $allfiledata[$id],
                        'isdelete' => $filemetadata['isDeleted'],
                        'hasthumb' => $thumb,
                        'grade' => $filemetadata['star'] ? intval($filemetadata['star']) : 0
                    ];
                    
                    //文件路径
                    $file = str_replace(BS,'/',$file);
                    $attachment = str_replace($this->eagledir.'/', '', $file);
                    $path = str_replace(BS, '/', $attachment);
                    $setarr['path'] = $path;
                    $resourcesarr['hasthumb'] = $thumb;
                    $resourcesarr['rid'] = $rid;
                    C::t('pichome_resources')->insert($resourcesarr);
                    $rfids = [];
                    //检查目录变化
                    foreach ($filemetadata['folders'] as $fv) {
                        $fid = $fv . $this->appid;
                        if (!C::t('pichome_folder')->check_password_byfid($fid)) {
                            $frsetarr = ['appid' => $this->appid, 'rid' => $rid, 'fid' => $fid];
                            $rfids[] = C::t('pichome_folderresources')->insert($frsetarr);
                        } else {
                            continue;
                        }
                    }
                    $orfids = C::t('pichome_folderresources')->fetch_id_by_rid($rid);
                    $delrfids = array_diff($orfids, $rfids);
                    C::t('pichome_folderresources')->delete($delrfids);
                    //尝试更新属性表数据
                    $setarr['link'] = $filemetadata['url'] ? trim($filemetadata['url']) : '';
                    //描述数据
                    $setarr['desc'] = $filemetadata['annotation'] ? htmlspecialchars($filemetadata['annotation']) : '';
                    if ($filemetadata['duration']) $setarr['duration'] = number_format($filemetadata['duration'], 2);
                    $setarr['rid'] = $rid;
                    C::t('pichome_resources_attr')->insert($setarr);
                    
                }
            }
            return true;
        }
        
        
        public function execExport($force = false)
        {
            if($this->exportstatus > 0 && $this->exportstatus<3 ) return ;
            C::t('pichome_vapp')->update($this->appid,array('state'=>1,'donum'=>0,'percent'=>0));
            $filedir = $this->eagledir . '/' . $this->path.'/images';
            if ($dch = opendir($filedir)) {
                while (($file = readdir($dch)) != false) {
                    if ($file != '.' && $file != '..') {
                        $filePath = $filedir . '/' . $file;
                        if (is_dir($filePath) && file_exists($filePath.'/metadata.json')) {
                            $this->filenum++;
                        }
                    }
                }
                closedir($dch);
            }else{
                C::t('pichome_vapp')->update($this->appid,array('state'=>0));
                return array('error' => 'Read Dir Failer');
            }
            if ($dh = opendir($filedir)) {
                while (($file = readdir($dh)) != false) {
                    if ($file != '.' && $file != '..') {
                        $filePath = $filedir . '/' . $file;
                        if (is_dir($filePath) && file_exists($filePath.'/metadata.json')) {
                            $id = str_replace('.info','',$file);
                            //组合文件id
                            $rid = $id . $this->appid;
                          //文件路径
                            $tmppath =$filePath;
                            //文件信息文件路径
                            $metadatajsonfile = $tmppath . BS . 'metadata.json';
                           
                            $data = C::t('pichome_resources')->fetch($rid);
                            //获取json文件最后修改时间
                            $lastdate = filemtime($metadatajsonfile);
                            //查询文件表是否有对应记录
                            if ($data ) {
                                //json文件不存在时删除文件，此情形基本不会出现(文件更新时，mtime会对应发生变化)
                                if (!file_exists($metadatajsonfile)) {
                                    //删除文件
                                    C::t('pichome_resources')->delete_by_rid($rid);
                                    continue;
                                }
                                elseif ($force || ($data['lastdate']< $lastdate)) {//当文件最后更新时间小于eagle时间时，需处理标签，颜色，目录等信息
                                    //获取文件信息数据
                                    $metadata = file_get_contents($metadatajsonfile);
                                    $filemetadata = json_decode($metadata, true);
                                    $p = new Encode_Core();
                                    $charset = $p->get_encoding($tmppath);
                                    if(CHARSET != $charset)$filemetadataname= diconv($filemetadata['name'],CHARSET,$charset);
                                    //文件名称
                                    $filename = $filemetadataname . '.' . $filemetadata['ext'];
                                    //缩略图名称
                                    $thumbname = $filemetadataname . '_thumbnail.png';
                                    //文件路径
                                    $file = $tmppath . BS . $filename;
                                    //缩略图路径
                                    $thumbfile = $tmppath . BS . $thumbname;
                                    //检查md5是否发生变化
                                    $filemd5 = md5_file($file);
                                    //如果md5发生变化则删除原数据，重新导入
                                    if ($data['md5'] != $filemd5) {
                                        C::t('pichome_resources')->delete_by_rid($rid);
                                        $filemetadata['filename'] = $filemetadata['name'];
                                        $filemetadata['file'] = $file;
                                        $filemetadata['thumbfile'] = $thumbfile;
                                        $filemetadata['md5'] = $filemd5;
                                        $filemetadata['rid'] = $rid;
                                        $filemetadata['dateline'] = $filemetadata['mtime'];
                                        $filemetadata['lastdate'] = $lastdate;
                                        $this->exportfile($filemetadata);
                                    }
                                    else {
                                        //查询原数据中的属性信息
                                        $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                                        //信息表数据记录
                                        $setarr = [];
                                        //检查标签变化
                                        //标签数据
                                        $tags = $filemetadata['tags'];
                                        //现有标签
                                        $tagids = [];
                                        //原有标签
                                        $oldtids=[];
                                        if ($attrdata['tag']) $oldtids = explode(',', $attrdata['tag']);
                                    
                                        if (!empty($tags)) {
                                            $tagids = $this->addtag($tags);
                                            $setarr['tag'] = implode(',',$tagids);
                                        }
                                        $addtags = array_diff($tagids,$oldtids);
                                        $deltags = array_diff($oldtids,$tagids);
                                        
                                        if(!empty($deltags))C::t('pichome_resourcestag')->delete_by_ridtid($rid,$deltags);
                                        foreach($addtags as $tid){
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
                                                try{
                                                    C::t('pichome_comments')->insert($tcommentval);
                                                }catch (Exception $e){
                                                
                                                }
                                                $cids[] = $tcommentval['id'];
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
                                            'lastdate'=>$lastdate
                                        ];
                                        $file = str_replace(BS,'/',$file);
                                        $attachment = str_replace($this->eagledir .'/', '', $file);
                                        $path = str_replace(BS, '/', $attachment);
                                        if (!file_exists($thumbfile)) {
                                            $thumb = (file_exists($filemetadata['thumbfile'])) ? 1 : 0;
                                        } else {
                                            $thumb = 1;
                                        }
                                        $setarr['path'] = $path;
                                        $resourcesarr['hasthumb'] = $thumb;
                                        $resourcesarr['rid'] = $rid;
                                        C::t('pichome_resources')->insert($resourcesarr);
                                        $rfids = [];
                
                                        //检查目录变化
                                        foreach ($filemetadata['folders'] as $fv) {
                                            $fid = $fv . $this->appid;
                                            if (!C::t('pichome_folder')->check_password_byfid($fid)) {
                                                $frsetarr = ['appid' => $this->appid, 'rid' => $rid, 'fid' => $fid];
                        
                                                $rfids[] = C::t('pichome_folderresources')->insert($frsetarr);
                                            } else {
                                                continue;
                                            }
                                        }
                                        $orfids = C::t('pichome_folderresources')->fetch_id_by_rid($rid);
                                        $delrfids = array_diff($orfids, $rfids);
                                        C::t('pichome_folderresources')->delete($delrfids);
                                        //尝试更新属性表数据
                                        $setarr['link'] = $filemetadata['url'] ? trim($filemetadata['url']) : '';
                                        //描述数据
                                        $setarr['desc'] = $filemetadata['annotation'] ? $filemetadata['annotation'] : '';
                                        if ($filemetadata['duration']) $setarr['duration'] = number_format($filemetadata['duration'], 2);
                                        $setarr['rid'] = $rid;
                                        C::t('pichome_resources_attr')->insert($setarr);
                                    }
                                }
                                else{
                                    $this->donum += 1;
                                    $percent = round(($this->donum/$this->filenum)*100);
                                    //防止因获取文件总个数不准确百分比溢出
                                    $percent = ($percent > 100) ? 100:$percent;
                                    //记录导入起始位置，以备中断后从此处,更改导入状态为正在导入
                                    C::t('pichome_vapp')->update($this->appid,array('lastid'=>$id,'percent'=>$percent,'donum'=>$this->donum,'state'=>1));
                                   continue;
                                }
                            }
                            else {//如果没有记录，则为新导入文件
                                if (!file_exists($metadatajsonfile)) {
                                    $this->filenum -= 1;
                                    continue;
                                }
                                //获取文件信息数据
                                $metadata = file_get_contents($metadatajsonfile);
                                $filemetadata = json_decode($metadata, true);
                                //文件目录信息
                                $filefolders = $filemetadata['folders'];
                                //如果目录含有密码则不导入数据直接跳过
                                $haspassword = C::t('pichome_folder')->check_haspasswrod($filefolders, $this->appid);
        
                                if ($haspassword) continue;
                                $p = new Encode_Core();
                                $charset = $p->get_encoding($tmppath);
                                if(CHARSET != $charset)$filemetadataname= diconv($filemetadata['name'],CHARSET,$charset);
                                $filename =$filemetadataname . '.' . $filemetadata['ext'];
                                $thumbname = $filemetadataname . '_thumbnail.png';
        
                                $file = $tmppath . BS . $filename;
                                $thumbfile = $tmppath . BS . $thumbname;
                                $filemd5 = md5_file($file);
                                $filemetadata['filename'] = $filemetadata['name'];
                                $filemetadata['file'] = $file;
                                $filemetadata['thumbfile'] = $thumbfile;
                                $filemetadata['md5'] = $filemd5;
                                $filemetadata['rid'] = $rid;
                                $filemetadata['mtime'] = $filemetadata['mtime'] ? $filemetadata['mtime']:$filemetadata['modificationTime'];
                                $filemetadata['btime'] = $filemetadata['btime'] ? $filemetadata['btime']:$filemetadata['modificationTime'];
                                $filemetadata['dateline'] = $filemetadata['lastModified'];
                                $filemetadata['lastdate'] = $lastdate;
                                $this->exportfile($filemetadata);
                            }
                            $this->donum += 1;
                            $percent = round(($this->donum/$this->filenum)*100);
                            //防止因获取文件总个数不准确百分比溢出
                            $percent = ($percent > 100) ? 100:$percent;
                            //记录导入起始位置，以备中断后从此处,更改导入状态为正在导入
                           C::t('pichome_vapp')->update($this->appid,array('lastid'=>$id,'percent'=>$percent,'donum'=>$this->donum,'state'=>1));
                        }
                    }
                }
                closedir($dh);
            }else{
                C::t('pichome_vapp')->update($this->appid,array('state'=>0));
                return array('error' => 'Read Dir Failer');
            }
            //更改状态为校验
            C::t('pichome_vapp')->update($this->appid,array('lastid'=>'','percent'=>100,'filenum'=>$this->filenum,'donum'=>0,'state'=>2));
            $total = DB::result_first("select count(rid) from %t where appid = %s ",array('pichome_resources',$this->appid));
            //校验文件
            $this->check_file($total);
            return array('success' => true);
            
        }
        public function check_file($total){
            
            $limitsql = ($this->checkpage -1)*$this->checklimit.','.$this->checklimit;
            $delrids = [];
            $data = DB::fetch_all("select rid from %t where appid = %s order by lastdate asc limit $limitsql ",array('pichome_resources',$this->appid));
            if(empty($data))  {
                C::t('pichome_vapp')->update($this->appid,array('percent'=>100,'state'=>3));
                return true;
            }
            foreach ( $data as $v){
                $id = str_replace($this->appid,'',$v['rid']);
                $filejson = $this->eagledir . '/' . $this->path.'/images/'.$id.'.info/metadata.json';
                if(!file_exists($filejson)){
                    $delrids[] = $v['rid'];
                }
                $this->checknum += 1;
                $percent = round(( $this->checknum/$total)*100);
                //记录导入起始位置，以备中断后从此处,更改导入状态为正在导入
                C::t('pichome_vapp')->update($this->appid,array('percent'=>$percent,'state'=>2));
            }
            if(!empty($delrids)){
                //如果有需要删除的，删除后，则重新查询上一页数据
                C::t('pichome_resources')->delete_by_rid($delrids);
                $ctotal = DB::result_first("select count(rid) from %t where appid = %s ",array('pichome_resources',$this->appid));
                if($ctotal)$this->check_file($total);
            }else{
                $this->checkpage += 1;
                $this->check_file($total);
            }
        }
        public function exportfile($filemetadata)
        {
            $rid = $filemetadata['rid'];
            if (!file_exists($filemetadata['file'])) {
                return ;
            }
            
            $filemetadata['file'] = str_replace(BS,'/',$filemetadata['file']);
            $attachment = str_replace(BS, '/', $filemetadata['file']);
            $path = str_replace($this->eagledir.'/', '', $attachment);
            if (!file_exists($filemetadata['thumbfile'])) {
                $thumb = (file_exists($filemetadata['thumbfile'])) ? 1 : 0;
            } else {
                $thumb = 1;
            }
            $ext = $filemetadata['ext'];
            $type = getTypeByExt($ext);
            $resourcesarr = [
                'rid' => $rid,
                'uid' => $this->uid,
                'username' => $this->username,
                'appid' => $this->appid,
                'width' => $filemetadata['width'] ? $filemetadata['width'] : 0,
                'height' => $filemetadata['height'] ? $filemetadata['height'] : 0,
                'name' => $filemetadata['filename'].'.'.$filemetadata['ext'],
                'ext' => $filemetadata['ext'],
                'size' => $filemetadata['size'],
                'dateline' => $filemetadata['dateline'],
                'btime' => $filemetadata['btime'],
                'mtime' => $filemetadata['mtime'],
                'isdelete' => $filemetadata['isDeleted'],
                'md5' => $filemetadata['md5'],
                'hasthumb' => $thumb,
                'grade' => $filemetadata['star'] ? intval($filemetadata['star']) : 0,
                'type' => $type
            ];
            //插入文件表数据
            if (C::t('pichome_resources')->insert($resourcesarr)) {
                //插入目录关联表数据
                foreach ($filemetadata['folders'] as $fv) {
                    $fid = $fv . $this->appid;
                    if (!C::t('pichome_folder')->check_password_byfid($fid)) {
                        $frsetarr = ['appid' => $this->appid, 'rid' => $rid, 'fid' => $fid];
                        C::t('pichome_folderresources')->insert($frsetarr);
                    } else {
                        continue;
                    }
                }
                //获取属性表数据
                $setarr = [];
                //标签数据
                $tags = $filemetadata['tags'];
                if (!empty($tags)) {
                    $tagids = $this->addtag($tags);
                    //$setarr['tag'] = implode(',', $tagids);
                    foreach ($tagids as $tid) {
                        $rtag = ['appid' => $this->appid, 'rid' => $rid, 'tid' => $tid];
                        C::t('pichome_resourcestag')->insert($rtag);
                    }
                    $setarr['tag'] = implode(',',$tagids);
                }
                //颜色数据
                if (isset($filemetadata['palettes'])) {
                    $returndata = $this->getColor($filemetadata['palettes'], $resourcesarr['width'], $resourcesarr['height'], $rid);
                    $setarr['colors'] = $returndata['colors'];
                    $setarr['gray'] = $returndata['gray'];
                    $setarr['shape'] = $returndata['shape'];
                }
                //标注数据
                if (isset($filemetadata['comments'])) {
                    foreach ($filemetadata['comments'] as $commentval) {
                        $tcommentval['id'] = $commentval['id'] . $this->appid;
                        $tcommentval['appid'] = $this->appid;
                        $tcommentval['rid'] = $rid;
                        $tcommentval['x'] = number_format($commentval['x'], 2);
                        $t['y'] = number_format($commentval['y'], 2);
                        C::t('pichome_comments')->insert($tcommentval);
                    }
                }
                //时长
                if ($filemetadata['duration']) $setarr['duration'] = number_format($filemetadata['duration'], 2);
                //链接数据
                $setarr['link'] = $filemetadata['url'] ? trim($filemetadata['url']) : '';
                //描述数据
                $setarr['desc'] = $filemetadata['annotation'] ? $filemetadata['annotation'] : '';
                $setarr['rid'] = $rid;
                $setarr['appid'] = $this->appid;
                $setarr['path'] = $path;
                //插入数据
                C::t('pichome_resources_attr')->insert($setarr);
            }else{
                runlog('eagleexport',$rid);
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
                if (abs($rgb[0] - $rgb[1]) < 10 && abs($rgb[2] - $rgb[1]) < 10) {
                    $i++;
                }
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
            $intcolorsarr = $returndata = $palattedataarr = [];
            foreach ($colors as $c) {
                $color = new \Color($c['color']);
                //获取颜色整型值
                $intcolor = $color->toInt();
                $palattedataarr = ['rid' => $rid, 'color' => $intcolor, 'weight' => $c['ratio'], 'r' => $c['color'][0], 'g' => $c['color'][1], 'b' => $c['color'][2]];
                $intcolorsarr[] = $intcolor;
                C::t('pichome_palette')->insert($palattedataarr);
            }
            //颜色整型值数据
            // $intcolorsarr= array_keys($palattedataarr);
            $returndata['colors'] = implode(',', $intcolorsarr);
            $returndata['gray'] = $this->isgray($intcolorsarr);
            $returndata['shape'] = round(($width / $height) * 100);
            return $returndata;
        }
        
        //添加标签
        public function addtag($tags)
        {
            $tagids = [];
            foreach ($tags as $v) {
                if (preg_match('/^\s*$/', $v)) continue;
                if ($tid = C::t('pichome_tag')->insert($v)) {
                    $tagids[] = $tid;
                } else {
                    continue;
                }
            }
            return $tagids;
        }
    }