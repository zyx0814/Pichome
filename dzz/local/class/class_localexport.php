<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
@ini_set('max_execution_time', 0);
require_once(DZZ_ROOT . './dzz/class/class_encode.php');
require_once libfile('function/user', '', 'user');

use \core as C;
use \DB as DB;
use \IO as IO;
use \ImagePalette as ImagePalette;
use \Color as Color;

class localexport
{
    public $palette = array(
        0x111111, 0xFFFFFF, 0x9E9E9E, 0xA48057, 0xFC85B3, 0xFF2727, 0xFFA34B, 0xFFD534, 0x47C595, 0x51C4C4, 0x2B76E7, 0x6D50ED
    );
    private $path = '';//待执行数据path
    private $appid = 0;//库id
    private $uid = 0;//用户id
    private $username = null;//用户名
    private $filenum = 0;//总文件数
    private $getinfosizelimit = 100;//限制获取信心文件大小，单位M
    private $checklimit = 1000;
    private $onceexportnum = 100;
    private $oncegetinfonum = 1000;
    private $getinfonum = 0;
    private $readtxt = DZZ_ROOT . './data/attachment/cache/';
    private $exportstatus = 0;
    private $donum = 0;
    private $lastid = '';
    private $charset = 'UTF-8';
    private $allowext = '';
    private $notallowext = '';
    private $notallowdir = '';
    private $getinfo = 0;
    private $defaultperm = 0;
    private $processname = '';


    public function __construct($data = array())
    {
        global $Defaultallowext;
        //获取导入记录表基本数据
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
        $this->processname = 'PICHOMEVAPPISDEL_'.$this->appid;
        $this->uid = $data['uid'];
        $this->username = $data['username'];
        $this->exportstatus = $data['state'];
        $this->donum = $data['donum'];
        $this->filenum = $data['filenum'];
        $this->lastid = $data['lastid'];
        $this->getinfonum = $data['getinfonum'];
        $this->getinfo = $data['getinfo'];
        $data['allowext'] = ($data['allowext']) ? $data['allowext']:getglobal('setting/pichomeimportallowext');
        if($data['perm']) $this->defaultperm = $data['perm'];
        //允许导入后缀
        if ($data['allowext']) {
            $allowext = str_replace(array('.', ',','+','$',"'",'^','(',')','[',']','{','}'), array('\.', '|','\+','\$',"'",'\^','\(',')','\[','\]','\{','\}'),$data['allowext']);
            $this->allowext = str_replace('*', '.*', $allowext);

        }
        $data['notallowext'] = ($data['notallowext']) ? $data['notallowext']:getglobal('setting/pichomeimportnotallowext');
        //禁止导入后缀
        if ($data['notallowext']) {
            $notallowext = str_replace(array('.', ',','+','$',"'",'^','(',')','[',']','{','}'), array('\.', '|','\+','\$',"'",'\^','\(',')','\[','\]','\{','\}'),$data['notallowext']);
            $this->notallowext = str_replace('*', '.*', $notallowext);

        }
        //获取pichome设置的默认禁止导入目录
        $notallowdir = getglobal('setting/pichomeimportnotdir') ? getglobal('setting/pichomeimportnotdir'):implode(',',$Defaultallowext);
        //禁止导入目录
        if ($notallowdir) {
            $notallowdir = str_replace(array('.', ',','+','$',"'",'^','(',')','[',']','{','}'), array('\.', '|','\+','\$',"'",'\^','\(',')','\[','\]','\{','\}'), $notallowdir);
            $this->notallowdir = str_replace('*', '.*', $notallowdir);

        }
        if ($data['charset']) $this->charset = $data['charset'];
    }

    //获取文件名
    public function getbasename($filename)
    {
        return preg_replace('/^.+[\\\\\\/]/', '', $filename);
    }


    public function initExport()
    {
        $filedir = $this->path;
        $readtxt = $this->readtxt . 'loaclexport' . md5($this->path) . '.txt';

        if (!is_file($readtxt) || !filesize($readtxt)) {

            $this->filenum = 0;
            if(dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
            C::t('pichome_vapp')->update($this->appid, array( 'state' => 1));
            $thandle = fopen($readtxt, 'w+');
            if($this->iscloud){
                $fileinfo = $this->readClouddata($thandle,$filedir);
            }else{
                $fileinfo = $this->readdir($filedir, $thandle, $filenum);
            }
            fclose($thandle);

            if ($fileinfo['error']) {

                C::t('pichome_vapp')->update($this->appid, array('state' => -1));
                return $fileinfo;
            } else {

                C::t('pichome_vapp')->update($this->appid, array('filenum' => $this->filenum, 'state' => 2,'percent'=>0,'lastid'=>0,'donum'=>0));
            }
        }else{

            if($this->filenum)C::t('pichome_vapp')->update($this->appid, array('state' => 2,'percent'=>0,'lastid'=>0,'donum'=>0));
            else{
                C::t('pichome_vapp')->update($this->appid, array('state' => -1));
                @unlink($readtxt);
                return array('error'=>'导入失败，请重试');
            }

        }
        $a = C::t('pichome_vapp')->fetch($this->appid);
        return array('success' => true);
    }
    //读取云存储数据
    public function readClouddata($thandle,$path,  $nextmarker = '',$by = 'time',$order = 'DESC', $limit = 1000, $force = 0){

        $prepatharr = explode(':',$path);

        $prepath = $prepatharr[2];

        $returndata = IO::listfiles($path,$nextmarker,$by,$order,$limit,$force);
        if($returndata['error']){
            return array('error'=>$returndata['error']);
        }else{

            foreach($returndata['file'] as $v){
                if(dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
                $v = str_replace($prepath,'',$v);
                $v = trim($v,'/');
                if (($this->allowext && !preg_match('/^(' . $this->allowext . ')$/i', $v)) || ($this->notallowext && preg_match('/^(' . $this->notallowext . ')$/i', $v))) {
                    continue;
                }
                $this->filenum++;
                $newpath = $path.'/'.$v;
                fwrite($thandle, $newpath . "\n");
            }
            foreach($returndata['folder'] as $v){
                if(dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
                $v = str_replace($prepath,'',$v);
                $v = trim($v,'/');
                if (!$v||(preg_match('/^(' . $this->notallowdir . ')$/i', $v))) {
                    continue;
                }
                $newpath = $path.'/'.$v;
                $this->readClouddata($thandle,$newpath);
            }

        }

        if($returndata['IsTruncated']){
            $this->readClouddata($thandle,$path,$returndata['NextMarker'],$by,$order,$limit,$force);
        }else{
            return array('success'=>true);
        }

    }
    public function readdir($path, $thandle, &$filenum)
    {

        if (!IO::checkfileexists($path,1)) {

            return [];
        }

        $handle = dir($path);
        if ($handle) {
            while (($filename = $handle->read()) !== false) {
                if(dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
                if ($this->charset != CHARSET) $convertfilename = diconv($filename, $this->charset, CHARSET);
                else $convertfilename = $filename;
                $newPath = $path . BS . $filename;
                if (is_dir($newPath) && $filename != '.' && $filename != '..' && $filename != '@eaDir') {
                    if ((preg_match('/^(' . $this->notallowdir . ')$/i', $convertfilename))) {
                        continue;
                    }
                    $this->readdir($newPath, $thandle, $filenum);
                } elseif (is_file($newPath)) {
                    if(dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
                    if (($this->allowext && !preg_match('/^(' . $this->allowext . ')$/i', $convertfilename)) || ($this->notallowext && preg_match('/^(' . $this->notallowext . ')$/i', $convertfilename))) {
                        continue;
                    }
                    $this->filenum++;
                    fwrite($thandle, $newPath . "\n");
                }
            }
        } else {
            return array('error' => 'Read Dir Failer');

        }

        $handle->close();
        return array('success' =>true);

    }

    public function execExport($force = false)
    {
        $filedir = $this->path;

        $filedir = $this->iscloud ? $filedir:str_replace(array('/', './', '\\'), BS, $filedir);

        $readtxt = $this->readtxt . 'loaclexport' . md5($this->path) . '.txt';

        //如果txt为空直接进入下一步
        if(filesize($readtxt) == 0){
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
            if(dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
            $i = 0;
            while (is_file($readtxt) && !$spl_object->eof()) {
                $i++;
                if ($i > $this->onceexportnum) {

                    break;

                }
                $file = $spl_object->current();

                $file = trim($file);

                $file = $this->iscloud ? $file:str_replace(array('/', './', '\\'), BS, $file);
                $filearr = explode("\t", $file);
                $filerelativepath = $filearr[0];
                $filepath = $this->iscloud ? str_replace($filedir .'/', '', $filerelativepath):str_replace($filedir . BS, '', $filerelativepath);
                $p = new Encode_Core();
                $this->charset = $p->get_encoding($filepath);
                //如果是目录直接执行目录导入
                if (isset($filearr[1]) && $filearr[1] == 'folder') {
                    if ($this->charset != CHARSET) $filepath = diconv($filepath, $this->charset, CHARSET);
                    // $fid = $this->createfolerbypath($filepath);
                    $fdata = C::t('pichome_folder')->createfolerbypath($this->appid,$filepath,'');
                    $spl_object->next();
                    continue;
                }
                else {

                    $hasrid = 0;

                    //保存路径，用于之后获取文件使用
                    $savepath = str_replace(array('/', './', '\\'), '/', $filearr[0]);
                    if(!$this->iscloud) $this->path = str_replace(BS,'/',$this->path);
                    $savepath = str_replace($this->path . '/', '', $savepath);
                    $pathmd5 = md5($savepath.$this->appid);
                    if($recorddata = DB::fetch_first("select * from %t where id = %s",array('local_record',$pathmd5))){
                        $rid = $recorddata['rid'];
                        $hasrid = 1;
                    }else{
                        //生成rid
                        $rid = $this->createRid();
                    }
                    $realfilepath = $filedir.BS.$filepath;

                    //如果文件不存在则删除记录
                    if (!IO::checkfileexists($realfilepath)) {
                        if($hasrid)C::t('pichome_resources')->delete_by_rid($rid);
                        $this->filenum -= 1;
                    } else {
                        $filedata = IO::getMeta($realfilepath,1);

                        //修改时间
                        $mtime = $filedata['dateline'];
                        //创建时间
                        $ctime =  $filedata['dateline'];
                        //获取文件后缀

                        $ext =  $filedata['ext'];
                        //获取文件类型
                        $type = getTypeByExt($ext);
                        //获取文件大小
                        $size = $filedata['size'];




                        //去掉库路径，以便获取文件相对目录
                        $filepath = $this->iscloud ? str_replace($filedir . '/', '', $filepath):str_replace($filedir . BS, '', $filepath);


                        if ($this->charset != CHARSET) $filepath = diconv($filepath, $this->charset, CHARSET);
                        //获取文件名
                        $filename = $this->getbasename($filepath);
                        if(dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
                        //不符合导入规则文件不允许导入，并减少总数
                        if (($this->allowext && !preg_match('/^(' . $this->allowext . ')$/i', $filename)) || ($this->notallowext && preg_match('/^(' . $this->notallowext . ')$/i', $filename))) {
                            if($hasrid) C::t('pichome_resources')->delete_by_rid($rid);
                            $this->filenum -= 1;
                        } else {

                            if (!$hasrid) {
                                $setarr = [
                                    'rid' => $rid,
                                    'name' => $filename,
                                    'lastdate' => $mtime,
                                    'appid' => $this->appid,
                                    'apptype' => 1,
                                    'size' => $size,
                                    'type' => $type,
                                    'ext' => $ext,
                                    'width' => ($filedata['width']) ? $filedata['width'] : 0,
                                    'height' => ($filedata['height']) ? $filedata['height']: 0,
                                    'mtime' => $ctime * 1000,
                                    'dateline' => $mtime * 1000,
                                    'btime' => TIMESTAMP * 1000,
                                    'editdate'=>$mtime,
                                    'path'=>$savepath
                                ];

                                if (C::t('#local#local_record')->insert_data($setarr)) {
                                    $attrdata = [
                                        'rid' => $rid,
                                        'appid' => $this->appid,
                                        'isget' => 0,
                                        'path' => $savepath,
                                        'searchval'=>$filename,

                                    ];
                                    $thumbrecorddata = [
                                        'rid' => $rid,
                                        'ext' => $setarr['ext'],
                                        'filesize'=>$size,
                                        'width'=>$setarr['width'],
                                        'height'=>$setarr['height']
                                    ];
                                    C::t('thumb_record')->insert_data($thumbrecorddata);
                                    // dfsockopen(getglobal('localurl') . 'misc.php?mod=thumbconvertrecord&rid='.$data['rid'], 0, '', '', false, '',0.01);
                                    C::t('pichome_resources_attr')->insert($attrdata);
                                    $dirstr = dirname($filepath);
                                    if ($dirstr != '.' && $dirstr != '..' && $dirstr) {
                                        $dirstr = str_replace(BS,'/',$dirstr);
                                        // $fdata = $this->createfolerbypath($dirstr);
                                        $fdata = C::t('pichome_folder')->createfolerbypath($this->appid,$dirstr,'');
                                        if ($fdata['fid']) {
                                            $frsetarr = ['appid' => $this->appid, 'rid' => $rid, 'fid' => $fdata['fid']];
                                            C::t('pichome_folderresources')->insert($frsetarr);
                                            C::t('pichome_resources')->update($rid,['level'=>$fdata['level'],'fids'=>$fdata['fid']]);
                                        }
                                    }else{
                                        C::t('pichome_resources')->update($rid,['level'=>$this->defaultperm]);
                                    }
                                    $setarr['realpath']  = $this->path.'/'.$savepath;
                                    //视频转换数据
                                    Hook::listen('pichomeconvert',$setarr,null,false,true);
                                }
                            }
                            else {
                                //查询是否已有数据
                                if ($mtime > $recorddata['dateline'] || $force) {
                                    $setarr = [
                                        'rid'=>$rid,
                                        'lastdate' => $mtime,
                                        'appid' => $this->appid,
                                        'apptype' => 1,
                                        'size' => $size,
                                        'type' => $type,
                                        'ext' => $ext,
                                        'mtime' => $ctime * 1000,
                                        'dateline' => $mtime * 1000,
                                        'btime' => TIMESTAMP * 1000,
                                        'width' => isset($filedata['width']) ? $filedata['width'] : 0,
                                        'height' => isset($filedata['height']) ? $filedata['height'] : 0,
                                        'editdate'=>$mtime
                                    ];
                                    if (C::t('#local#local_record')->insert_data($setarr)) {
                                        $attrdata = [
                                            'rid' => $rid,
                                            'appid' => $this->appid,
                                            'isget' => 0,
                                            'path' => $savepath,
                                            'searchval'=>$filename,

                                        ];
                                        C::t('pichome_resources_attr')->insert($attrdata);
                                        $thumbrecorddata = [
                                            'rid' => $rid,
                                            'ext' => $setarr['ext'],
                                            'filesize'=>$size,
                                            'width'=>$setarr['width'],
                                            'height'=>$setarr['height'],
                                        ];
                                        C::t('thumb_record')->insert_data($thumbrecorddata);
                                        //dfsockopen(getglobal('localurl') . 'misc.php?mod=thumbconvertrecord&rid='.$data['rid'], 0, '', '', false, '',0.01);
                                        $dirstr = dirname($filepath);
                                        if ($dirstr != '.' && $dirstr != '..' && $dirstr) {
                                            // $fdata = $this->createfolerbypath($dirstr);
                                            $dirstr = str_replace(BS,'/',$dirstr);
                                            $fdata = C::t('pichome_folder')->createfolerbypath($this->appid,$dirstr,'');
                                            runlog('aaaexportafter',print_r($fdata,true));
                                            if ($fdata['fid']) {
                                                $frsetarr = ['appid' => $this->appid, 'rid' => $rid, 'fid' => $fdata['fid']];
                                                C::t('pichome_folderresources')->insert($frsetarr);
                                                C::t('pichome_resources')->update($rid,['level'=>$fdata['level'],'fids'=>$fdata['fid']]);
                                            }
                                        } else{
                                            C::t('pichome_resources')->update($rid,['level'=>$this->defaultperm]);
                                        }
                                        //如果文件被替换强制插入获取信息数据
                                        $hookdata = ['appid'=>$this->appid,'rid'=>$rid,'ext'=>$ext,'isforce'=>1,'realpath'=>$this->path.'/'.$savepath];
                                        Hook::listen('pichomeconvert',$hookdata,null,false,true);

                                    }

                                }else{
                                    $hookdata = ['appid'=>$this->appid,'rid'=>$rid,'ext'=>$ext,'realpath'=>$this->path.'/'.$savepath];
                                    //如果文件已存在，尝试插入获取信息数据
                                    Hook::listen('pichomeconvert',$hookdata,null,false,true);
                                }
                            }
                            $this->donum += 1;
                        }

                    }
                    $percent = floor(($this->donum / $this->filenum) * 100);
                    $percent = ($percent > 100) ? 100 : $percent;
                    $state = ($percent >= 100) ? 3 : 2;
                    if ($state == 3) {
                        @unlink($readtxt);
                        $lastid = 0;
                    } else {
                        $lastid = $this->donum;
                    }
                    //记录导入起始位置，以备中断后从此处,更改导入状态为正在导入
                    C::t('pichome_vapp')->update($this->appid, array('lastid' => $lastid, 'percent' => $percent, 'donum' => $this->donum, 'state' => $state, 'filenum' => $this->filenum));
                }
                $spl_object->next();

            }

        }

        return array('success' => true);

    }
    //生成rid
    public function createRid(){

        //订单年月
        $ridmd = strtoupper(dechex(date('m'))) . date('d');
        //订单时间戳
        $ridms = substr(time(), -5) . substr(microtime(), 2, 5);
        //订单号
        $rid =  md5($ridmd.$ridms. sprintf('%02d', rand(0, 99)).$this->appid);
        return $rid;
    }
    //根据路径创建目录
    public function createfolerbypath($path, $pfid = '')
    {
        if(dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
        if (!$path) {
            return $pfid;
        } else {
            $patharr = explode(BS, $path);

            //生成目录
            foreach ($patharr as $fname) {
                if (!$fname) continue;
                //判断是否含有此目录
                if ($fdata = DB::fetch_first("select fid,level from %t where pfid=%s and appid=%s and fname=%s", array('pichome_folder', $pfid, $this->appid, $fname))) {
                    $pfid = $fdata['fid'];
                    $flevel = $fdata['level'];
                } else {
                    $parentfolder = C::t('pichome_folder')->fetch($pfid);
                    $flevel = isset($parentfolder['level']) ? intval($parentfolder['level']):$this->defaultperm;
                    $fid = random(13) . $this->appid;

                    if (DB::result_first("select fid from %t where fid = %s", array('pichome_folder', $fid))) {
                        $fid = random(13) . $this->appid;
                    }
                    if ($parentfolder) {
                        $pathkey = $parentfolder['pathkey'] . $fid;
                    } else {
                        $pathkey = $fid;
                    }
                    $setarr = [
                        'fid' => $fid,
                        'fname' => $fname,
                        'appid' => $this->appid,
                        'dateline' => TIMESTAMP,
                        'pfid' => $pfid,
                        'pathkey' => $pathkey,
                        'level'=>$flevel
                    ];
                    if (C::t('pichome_folder')->insert($setarr)) $pfid = $fid;
                }
            }
        }
        return ['fid'=>$pfid,'level'=>$flevel];
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
        if(dzz_process::getlocked($this->processname)) exit('vapp isdeleted');
        $delrids = [];
        $data = DB::fetch_all("select rid,name,ext from %t where appid = %s order by lastdate asc limit $limitsql ", array('pichome_resources', $this->appid));

        if (empty($data)) {


            C::t('pichome_vapp')->update($this->appid, array('percent' => 0, 'state' => 4, 'lastid' => 0, 'donum' => 0));
            //处理目录数据
            $folderdata = DB::fetch_all("select fid,pathkey from %t where appid = %s",array('pichome_folder',$this->appid));

            foreach($folderdata as $v){
                $pathkeyarr = explode($this->appid,$v['pathkey']);
                $pathkeyarr = array_filter($pathkeyarr);

                $idelete = false;
                $pfolder =[];
                foreach($pathkeyarr as $fv){
                    $tfid = $fv.$this->appid;
                    if($tfid){
                        if($tfname =  DB::result_first("select fname from %t where fid = %s and appid = %s",array('pichome_folder',$tfid,$this->appid))){
                            $pfolder[] = $tfname;
                        }else{
                            C::t('pichome_folder')->delete($v['fid']);
                            $idelete = true;
                            break;
                        }
                    }
                }
                if($idelete) continue;

                $ppath = implode('/',$pfolder);
                $folderpath  = $this->path.'/'.$ppath;
                if(!IO::checkfileexists($folderpath,1)){
                    C::t('pichome_folder')->delete($v['fid']);
                }
            }
            //校验完成后更新目录文件数
            foreach (DB::fetch_all("select count(rf.id) as num,f.fid  from %t f left join %t rf on rf.fid=f.fid where f.appid = %s group by f.fid", array('pichome_folder', 'pichome_folderresources', $this->appid)) as $v) {
                C::t('pichome_folder')->update($v['fid'], array('filenum' => $v['num']));

            }
            $total = DB::result_first("select count(rid) from %t where appid = %s ", array('pichome_resources', $this->appid));
            $hascatnum = DB::result_first("SELECT count(DISTINCT rid) FROM %t where appid = %s",array('pichome_folderresources',$this->appid));
            $nosubfilenum = $total - $hascatnum;
            /*$getinfonum = DB::result_first("SELECT count(ra.rid) FROM %t ra left join %t fc on ra.rid = fc.rid left join %t  ic on ra.rid= ic.rid
left join %t o on o.rid = ra.rid where ra.appid = %s and ((ra.isget = 0 and ISNULL(fc.rid) and ISNULL(ic.rid) and ISNULL(o.rid)) or (ra.isget=1))",
                array('pichome_resources_attr','pichome_ffmpeg_record','pichome_imagickrecord','pichome_onlyofficethumb',$this->appid));*/
            C::t('pichome_vapp')->update($this->appid,array('filenum'=>$total,'nosubfilenum'=>$nosubfilenum,'getinfonum'=>0,'dateline'=>time()));

            //执行缩略图转换
            dfsockopen(getglobal('localurl') . 'misc.php?mod=getthumb', 0, '', '', false, '',0.01);
            //获取文件信息
            dfsockopen(getglobal('localurl') . 'misc.php?mod=getinfo', 0, '', '', false, '',0.01);
            //执行音视频转换
            dfsockopen(getglobal('localurl') . 'misc.php?mod=convert', 0, '', '', false, '',0.01);
            return true;
        }

        foreach ($data as $v) {
            $rid = $v['rid'];
            $filepath = DB::result_first("select `path` from %t where rid = %s and appid = %s", array('pichome_resources_attr', $rid, $this->appid));
            $filepath = str_replace(array('/', './', '\\'), BS, $this->path . BS . $filepath);
            if (!IO::checkfileexists($filepath) || ($this->allowext && !preg_match('/^(' . $this->allowext . ')$/i', $v['name'])) || ($this->notallowext && preg_match('/^(' . $this->notallowext . ')$/i', $v['name']))) {

                $delrids[] = $rid;
            }



        }
        if (!empty($delrids)) {
            $this->filenum = $this->filenum - count($delrids);
            //如果有需要删除的，删除后，则重新查询上一页数据
            C::t('pichome_resources')->delete_by_rid($delrids);
            if($this->lastid == 1){
                $percent = round(($this->checklimit / $total) * 100);
            }else{
                $percent = round((($this->lastid - 1) * $this->checklimit / $total) * 100);
            }
            $percent = ($percent > 100) ? 100:$percent;
            C::t('pichome_vapp')->update($this->appid, array('lastid' => $this->lastid, 'percent' => $percent, 'state' => 3, 'filenum' => $this->filenum));
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

}