<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
Hook::listen('adminlogin');//检查是否登录，未登录跳转到登录界面
global $_G;
$dzzroot = str_replace(BS, '/', DZZ_ROOT);
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
if ($operation == 'fetch') {
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    if (submitcheck('settingsubmit')) {
        if (!$appid) exit(json_encode(array('error' => true)));
        $setarr = [
            'appname' => isset($_GET['appname']) ? trim($_GET['appname']) : '',
            //'filter' => isset($_GET['filter']) ? serialize($_GET['filter']) : '',
            'share' => isset($_GET['share']) ? intval($_GET['share']) : 0,
            'download' => isset($_GET['download']) ? intval($_GET['download']) : 0,
			'getinfo' => isset($_GET['getinfo']) ? intval($_GET['getinfo']) : 0,
            'allowext' => isset($_GET['allowext']) ? trim($_GET['allowext']) : '',
            'notallowext' => isset($_GET['notallowext']) ? trim($_GET['notallowext']) : '',
        ];
        C::t('pichome_vapp')->update($appid, $setarr);
       /* if($setarr['getinfo']){
            //开启器获取信息后执行获取文件信息
            dfsockopen(getglobal('localurl') . 'index.php?mod=imageColor&op=index', 0, '', '', false, '', 1);
            dfsockopen(getglobal('localurl') . 'index.php?mod=ffmpeg&op=getinfo', 0, '', '', false, '', 1);
            dfsockopen(getglobal('localurl') . 'index.php?mod=ffmpeg&op=thumb', 0, '', '', false, '', 1);
        }*/
        exit(json_encode(array('success' => true)));
    } else {
        require_once(DZZ_ROOT . './dzz/class/class_encode.php');
        if ($data = DB::fetch_first("select * from %t where appid=%s and isdelete = 0 ", array('pichome_vapp', $appid))) {
            $arr = explode(':', $data['path']);
            if($arr[1] && is_numeric($arr[1])){
                $pathpre =  DB::result_first("select cloudname from %t where id = %d",array('connect_storage',$arr[1]));
                $arr1 = explode('/', $arr[2]);
                unset($arr1[0]);
                $object = implode('/', $arr1);
                $data['convertpath'] = $pathpre.'/'.$object;
            }else{
                $p = new Encode_Core();
                $charset = $p->get_encoding($data['path']);
                $data['convertpath'] = ($data['charset'] != CHARSET) ? diconv($data['path'], $charset, CHARSET):$data['path'];
            }
            $data['path'] = urlencode($data['path']);
            $data['filter'] = unserialize($data['filter']);
            $data['convertpath'] = str_replace('dzz::','',$data['convertpath']);
            $catdata = C::t('pichome_taggroup')->fetch_by_appid($appid);
            if (($data['state'] == 2)) {
                $processname = 'DZZ_PAGEEXPORTFILE_LOCK_' . $appid;
                $locked = true;
                if (!dzz_process::islocked($processname, 60 * 5)) {
                    $locked = false;
                }
                if (!$locked) {
                    dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=exportfile&appid=' . $appid, 0, '', '', false, '', 1);
                }

            } elseif ($data['state'] == 3) {
                $processname = 'DZZ_PAGEEXPORTCHECKFILE_LOCK_' . $appid;
                $locked = true;
                if (!dzz_process::islocked($processname, 60 * 5)) {
                    $locked = false;
                }
                if (!$locked) {
                    dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=exportfilecheck&appid=' . $appid, 0, '', '', false, '', 1);
                }
            }
            exit(json_encode(array('success' => true, 'data' => $data, 'catdata' => $catdata)));
        } else {
            exit(json_encode(array('error' => true)));
        }
    }
}
elseif ($operation == 'getdata') {
    $data = array();
    require_once(DZZ_ROOT . './dzz/class/class_encode.php');
    foreach (DB::fetch_all("select * from %t where isdelete = 0 order by disp", array('pichome_vapp')) as $val) {
        $val['connect'] = IO::checkfileexists($val['path'],1) ? 1:0;
        $arr = explode(':', $val['path']);
        if($arr[1] && is_numeric($arr[1])){
            $pathpre =  DB::result_first("select cloudname from %t where id = %d",array('connect_storage',$arr[1]));
            $arr1 = explode('/', $arr[2]);
            unset($arr1[0]);
            $object = implode('/', $arr1);
            $val['path'] = $pathpre.'/'.$object;
        }else{
            $p = new Encode_Core();
            $charset = $p->get_encoding($val['path']);
            if($val['charset'] != CHARSET){
                $val['path'] = diconv($val['path'], $charset, CHARSET);
            }
        }
        $val['path'] = str_replace('dzz::','',$val['path']);

        $data[] = $val;
    }
    exit(json_encode(array('data' => $data)));

}elseif($operation == 'getinfonum'){//已获取文件信息个数
    $returndata = [];
    foreach(DB::fetch_all("select count(r.rid) as thumbnum,v.appid from %t r left join %t v on r.appid = v.appid where v.isdelete = 0  and v.`type` = 1 and r.hasthumb = 1 group by v.appid", array('pichome_resources','pichome_vapp')) as $v){
        $returndata[$v['appid']] = $v['thumbnum'];
    }
    exit(json_encode(array('data' => $returndata)));
} elseif($operation == 'getexportstatus'){
    $appids = isset($_GET['appids']) ? trim($_GET['appids']):'';
    $appidarr = ($appids) ? explode(',',$appids):[''];
    $returndata = [];
    foreach(DB::fetch_all("select appid,percent,state,filenum from %t where isdelete = 0 and appid in(%n) ", array('pichome_vapp',$appidarr)) as $v){
        $returndata[$v['appid']] = $v;
    }
    exit(json_encode(array('data' => $returndata)));
}elseif ($operation == 'addlibrary') {
    //接收路径
    $path = isset($_GET['path']) ? trim($_GET['path']) : '';
    //接收编码
    $charset = isset($_GET['charset']) ? trim($_GET['charset']) : 'utf8';
    //转换路径
    $path = str_replace('/', BS, $path);

    //获取库名称
    $appname = getbasename($path);

    //存在相同路径的不允许重复添加
    if (DB::result_first("select appid from %t where path = %s and isdelete = 0", array('pichome_vapp', $path))) {
        exit(json_encode(array('error' => '库已存在，不允许重复添加')));
    }
    $type = isset($_GET['type']) ? intval($_GET['type']) : 0;
    $gettype = isset($_GET['gettype']) ? trim($_GET['gettype']) : '';
    $iscloud = false;
    if($gettype && $gettype !==1 && $gettype !== '1'){
        $cloudid = str_replace('cloud:','',$gettype);
        if($cloudid < 2){
            if (CHARSET != $charset) $path = diconv($path, CHARSET, $charset);
            $path = 'dzz::'.$path;
        }else{
            $connectdata = DB::fetch_first("select cloudname,id,bz,bucket from %t where id = %d",array('connect_storage',$cloudid));
            //去掉路径中的存储名称部分
            $path = str_replace(array($connectdata['cloudname'].'/',$connectdata['cloudname'].BS),'/',$path);
            //更换路径中的分割符为/
            $rpath = str_replace(BS,'/',$path);
            //得到请求路径
            $path = $connectdata['bz'].':'.$cloudid.':'.$rpath;
            $iscloud = true;
        }


    }else{
        //转换编码，防止路径找不到（linux下中文乱码，前端展示为正常编码，依据前端传递编码转换出原路径存储）
        if (CHARSET != $charset) $path = diconv($path, CHARSET, $charset);
        $path = 'dzz::'.$path;
    }
    $force = isset($_GET['force']) ? intval($_GET['force']) : 0;
    if ($type == 0) {
        $iseagel = false;
        $metajsonfile = ($iscloud) ?  $path.'/metadata.json':$path . BS . 'metadata.json';
        $iseagel = IO::checkfileexists($metajsonfile);
        $iseagel ? '':exit(json_encode(array('error' => '系统检测该库不符合eagle库标准，不能作为eagle库添加')));
        $appname = str_replace('.library', '', $appname);
    }
    if ($type == 2) {
        $isbillfish = false;
        $dbfile = ($iscloud) ? $path.'/.bf/billfish.db':$path . BS . '.bf'.BS.'billfish.db';
        $isbillfish = IO::checkfileexists($dbfile);
        $isbillfish ? '':exit(json_encode(array('tips' => '系统检测该库不符合billfish库标准，不能作为billfish库添加')));

    }

    if ($type == 1 && !$force) {
        $iseagel = false;
        $metajsonfile = ($iscloud) ?  $path.'/metadata.json':$path . BS . 'metadata.json';
        $iseagel = IO::checkfileexists($metajsonfile);
        $iseagel ? exit(json_encode(array('tips' => '系统检测该目录可能为eagle库，不能作为普通目录导入'))):'';
    }

    if ($type == 1 && !$force) {
        $dbfile = ($iscloud) ? $path.'/.bf/billfish.db':$path . BS . '.bf'.BS.'billfish.db';
        $isbillfish = false;
        $isbillfish = IO::checkfileexists($dbfile);
        $isbillfish  ?  exit(json_encode(array('tips' => '系统检测该目录可能为billfish库，不能作为普通目录导入'))):'';
    }
    $appattr = [
        'appname' => $appname,
        'uid' => $_G['uid'],
        'username' => $_G['username'],
        'personal' => 1,
        'dateline' => TIMESTAMP,
        'type' => $type,
        'path' => $path,
        'charset' => $charset,
        'notallowext'=>getglobal('setting/pichomeimportnotallowext'),
        'allowext'=>getglobal('setting/pichomeimportallowext'),
        'filter' =>''
    ];
    //if ($type == 1) $appattr['allowext'] = $Defaultallowext;
    if(!$iscloud) $path = str_replace(array('/', './', '\\'), BS, $path);
    if (strpos($path, DZZ_ROOT) !== 0) $appattr['iswebsitefile'] = 0;
    $appid = C::t('pichome_vapp')->insert($appattr);
    if ($appid) {
        $appattr['appid'] = $appid;
        $appattr['path'] = $_GET['path'];
        exit(json_encode(array('data' => $appattr)));
    } else {
        exit(json_encode(array('error' => 'create failer')));
    }


}
elseif($operation == 'changePath'){
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $path = isset($_GET['path']) ? trim($_GET['path']):'';
    //接收编码
    $charset = isset($_GET['charset']) ? trim($_GET['charset']) : 'utf8';
    //转换路径
    $path = str_replace('/', BS, $path);
    //转换编码，防止路径找不到（linux下中文乱码，前端展示为正常编码，依据前端传递编码转换出原路径存储）
    if (CHARSET != $charset) $path = diconv($path, CHARSET, $charset);
    //存在相同路径的不允许重复添加
    if (DB::result_first("select appid from %t where path = %s and isdelete = 0", array('pichome_vapp', $path))) {
        exit(json_encode(array('tips' => '路径对应库已存在，不允许修改')));
    }else{
        $appdata = C::t('pichome_vapp')->fetch($appid);
    }
    if(!$appdata) exit(json_encode(array('tips' => '库不存在或已被删除，不允许修改')));
    $type = $appdata['type'];
    if ($type == 0) {
        $metajsonfile = $path . BS . 'metadata.json';
        if (!is_file($metajsonfile)) {
            exit(json_encode(array('error' => '系统检测该库不已符合eagle库标准，修改失败')));
        }
    }
    if ($type == 2) {
        $dbfile = $path . BS . '.bf'.BS.'billfish.db';
        if (!is_file($dbfile)) {
            exit(json_encode(array('tips' => '系统检测该库已不符合billfish库标准，修改失败')));
        }
    }
    //if(!is_dir($path))  exit(json_encode(array('tips' => '系统检测该库准，修改失败')));
    if (strpos($path, DZZ_ROOT) !== 0) $iswebsitefile = 0;
    else $iswebsitefile = 1;
    if (C::t('pichome_vapp')->update($appid, array('path' => $path,'iswebsitefile'=>$iswebsitefile))) {
        //dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=initexport&appid='.$appid, 0, '', '', false, '', 0.1);
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => true)));
    }
}
elseif ($operation == 'dellibrary') {
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $appdata = C::t('pichome_vapp')->fetch($appid);
    //if (C::t('pichome_vapp')->update($appid, array('isdelete' => 1,'deluid'=>getglobal('uid'),'delusername'=>getglobal('username')))) {
    if (C::t('pichome_vapp')->update($appid, array('isdelete' => 1))) {
        if($appdata['type'] == 1){
            $readtxt = DZZ_ROOT . './data/attachment/cache/' . 'loaclexport' . md5($appdata['path']) . '.txt';
        }elseif($appdata['type'] == 0){
            $readtxt = DZZ_ROOT . './data/attachment/cache/' . 'eagleexport' . md5($appdata['path']) . '.txt';
        }
        if(is_file($readtxt)){
            @unlink($readtxt);
        }
        dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=delete', 0, '', '', false, '', 0.1);
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => true)));
    }
}
elseif ($operation == 'getpath') {
    require_once(DZZ_ROOT . './dzz/class/class_encode.php');
    $path = isset($_GET['path']) ? trim($_GET['path']) : '';
    $gettype = isset($_GET['gettype']) ? trim($_GET['gettype']) : 0;
    $charset = isset($_GET['charset']) ? trim($_GET['charset']) : CHARSET;
    $path = str_replace('/', BS, $path);
    if (CHARSET != $charset) $path = diconv($path, CHARSET, $charset);
   if (!$gettype && !$path) {
        $path = DZZ_ROOT . 'library';
    }
    if (!empty($Defaultnotallowdir)) {
        $notallowdir = getglobal('setting/pichomeimportnotdir') ? getglobal('setting/pichomeimportnotdir'):implode(',',$Defaultallowext);
        $notallowdir = str_replace(array('.', ',','+','$',"'",'^','(',')','[',']','{','}'), array('\.', '|','\+','\$',"'",'\^','\(',')','\[','\]','\{','\}'), $notallowdir);
        $notallowdir = str_replace('*', '.*', $notallowdir);
    }

    $datas = [];
    if (!$path && $gettype) {
        $path = (PHP_OS == 'Linux') ? '/' : '';
        if($path == ''){
            $diskarr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            foreach ($diskarr as $v) {
                if (is_dir($v . ':')) {
                    $datas[] = ['path' => $v . ':', 'charset' => CHARSET];
                }
            }
        }else{
            if (is_dir($path)) {
                if ($dh = @opendir($path)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file != '.' && $file != '..' && !preg_match('/^(' . $notallowdir . ')$/i', $file) && (strpos($file, '.') !== 0) && is_dir($path . BS . $file)) {
                            $returnfile = $path . $file;
                            $p = new Encode_Core();
                            $charset = $p->get_encoding($file);
                            $returnfile = diconv($returnfile, $charset, CHARSET);
                            $datas[] = ['path' => $returnfile, 'charset' => $charset];
                        }
                    }
                    //关闭
                    closedir($dh);
                }
            }
        }
        //云存储位置
        foreach(DB::fetch_all("select id,cloudname from %t where bz != %s",array('connect_storage','dzz')) as $v){
            $datas[] = ['path'=>$v['cloudname'], 'charset' => CHARSET,'type'=>'cloud:'.$v['id']];
        }
        $datas[] = ['path' => DZZ_ROOT . 'library', 'charset' => CHARSET,'type'=>1];

    } else {
        if (is_dir($path)) {
            if ($dh = @opendir($path)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && is_dir($path . BS . $file) && !preg_match('/^(' . $notallowdir . ')$/i', $file)) {

                        $returnfile = trim($file);
                        $p = new Encode_Core();
                        $charset = $p->get_encoding($file);
                        $returnfile = diconv($returnfile, $charset, CHARSET);
                        $datas[] = ['path' => $returnfile, 'charset' => $charset];
                    }
                }
                //关闭
                closedir($dh);
            }
        }elseif($gettype && strpos($gettype,'cloud') !== false){

            // $datas[] = ['path'=>'aaaa','type'=>$gettype];
            $cloudid = str_replace('cloud:','',$gettype);
            $connectdata = DB::fetch_first("select cloudname,id,bz,bucket from %t where id = %d  ",array('connect_storage',$cloudid));
            if($connectdata['cloudname'] == $path){
                $path = '/';
            }else{
                //去掉路径中的存储名称部分
                $path = str_replace(array($connectdata['cloudname'].'/',$connectdata['cloudname'].BS),'/',$path);
            }
            //更换路径中的分割符为/
            $rpath = str_replace(BS,'/',$path);
            //得到请求路径
            $path = $connectdata['bz'].':'.$cloudid.':'.$rpath;
            //准备替换结果中的路径前半部分
            $replacepath = substr($rpath,1);
            $returndata = IO::getFolderlist($path);
            foreach($returndata['folder'] as $v){
                //去掉路径中的前半部分及斜杠用以显示
                $v = str_replace($replacepath,'',$v);
                $v = trim($v,'/');
                $datas[] = ['path'=>$v,'charset'=>CHARSET,'type'=>$gettype];
            }

        }

    }
    exit(json_encode(array('data' => $datas)));
}
elseif ($operation == 'sort') {
	$appids = isset($_GET['appids']) ? trim($_GET['appids']) : '';
	if (submitcheck('settingsubmit')) {
	    if (!$appids) exit(json_encode(array('error' => true)));
		$appidarr = explode(',', $appids);
		$setarr = [];
		foreach($appidarr as $k=>$v){
           $setarr['disp'] = $k;
           C::t('pichome_vapp')->update($v,$setarr);
        }
	    exit(json_encode(array('success' => true)));
	}
}else {
    $theme = GetThemeColor();
    include template('pc/page/library');
}
function getbasename($filename)
{
    return preg_replace('/^.+[\\\\\\/]/', '', $filename);
}
	