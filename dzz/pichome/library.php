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
            'filter' => isset($_GET['filter']) ? serialize($_GET['filter']) : '',
            'share' => isset($_GET['share']) ? intval($_GET['share']) : 0,
            'download' => isset($_GET['download']) ? intval($_GET['download']) : 0,
			'getinfo' => isset($_GET['getinfo']) ? intval($_GET['getinfo']) : 0,
            'allowext' => isset($_GET['allowext']) ? trim($_GET['allowext']) : '',
            'notallowext' => isset($_GET['notallowext']) ? trim($_GET['notallowext']) : '',
        ];
        C::t('pichome_vapp')->update($appid, $setarr);
        if($setarr['getinfo']){
            //开启器获取信息后执行获取文件信息
            dfsockopen(getglobal('localurl') . 'index.php?mod=imageColor&op=index', 0, '', '', false, '', 1);
            dfsockopen(getglobal('localurl') . 'index.php?mod=ffmpeg&op=getinfo', 0, '', '', false, '', 1);
            dfsockopen(getglobal('localurl') . 'index.php?mod=ffmpeg&op=thumb', 0, '', '', false, '', 1);
        }
        exit(json_encode(array('success' => true)));
    } else {
        if ($data = DB::fetch_first("select * from %t where appid=%s and isdelete = 0 ", array('pichome_vapp', $appid))) {
            if($data['charset'] != CHARSET)$data['convertpath'] = diconv($data['path'],$data['charset'],CHARSET);
            else $data['convertpath'] = $data['path'];
            $data['path'] = urlencode($data['path']);
            $data['filter'] = unserialize($data['filter']);
            $getinfonum = 0;
            $data['getinfonum'] = DB::result_first("SELECT count(ra.rid) FROM %t ra left join %t fc on ra.rid = fc.rid left join %t  ic on ra.rid= ic.rid 
where ra.appid = %s and ((ra.isget = 0 and ISNULL(fc.rid) and ISNULL(ic.rid)) or (ra.isget=1))",
                array('pichome_resources_attr','pichome_ffmpeg_record','pichome_imagickrecord',$appid));
            $catdata = C::t('pichome_taggroup')->fetch_by_appid($appid);
            if (($data['state'] == 2)) {
                $processname = 'DZZ_PAGEEXPORTFILE_LOCK_' . $appid;
                $locked = true;
                if (!dzz_process::islocked($processname, 60 * 5)) {
                    $locked = false;
                }
                if ($locked) {
                    dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=exportfile&appid=' . $appid, 0, '', '', false, '', 1);
                }

            } elseif ($data['state'] == 3) {
                $processname = 'DZZ_PAGEEXPORTCHECKFILE_LOCK_' . $appid;
                $locked = true;
                if (!dzz_process::islocked($processname, 60 * 5)) {
                    $locked = false;
                }
                if ($locked) {
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
    foreach (DB::fetch_all("select * from %t where isdelete = 0 order by disp", array('pichome_vapp')) as $val) {
        $val['connect'] = (is_dir($val['path'])) ? 1:0;
        if($val['charset'] != CHARSET){
            //echo $val['path'];die;
            $val['path'] = diconv($val['path'], $val['charset'], CHARSET);
        }
        $data[] = $val;
    }
    exit(json_encode(array('data' => $data)));

} elseif($operation == 'getinfonum'){//已获取文件信息个数
    $returndata = [];
    foreach(DB::fetch_all("select appid from %t where isdelete = 0 and getinfo = 1 and `type` = 1 and getinfonum < filenum", array('pichome_vapp')) as $v){
         $getinfonum= DB::result_first("SELECT count(ra.rid) FROM %t ra left join %t fc on ra.rid = fc.rid left join %t  ic on ra.rid= ic.rid 
where ra.appid = %s and ((ra.isget = 0 and ISNULL(fc.rid) and ISNULL(ic.rid)) or (ra.isget=1))",
            array('pichome_resources_attr','pichome_ffmpeg_record','pichome_imagickrecord',$v['appid']));
        C::t('pichome_vapp')->update($v['appid'],array('getinfonum'=>$getinfonum));
            $returndata['appid'] = $getinfonum;
    }
    exit(json_encode(array('data' => $returndata)));
}
elseif ($operation == 'addlibrary') {
    //接收路径
    $path = isset($_GET['path']) ? trim($_GET['path']) : '';
    //接收编码
    $charset = isset($_GET['charset']) ? trim($_GET['charset']) : 'utf8';
    //转换路径
    $path = str_replace('/', BS, $path);

    //获取库名称
    $appname = getbasename($path);

    //转换编码，防止路径找不到（linux下中文乱码，前端展示为正常编码，依据前端传递编码转换出原路径存储）
    if (CHARSET != $charset) $path = diconv($path, CHARSET, $charset);
    //存在相同路径的不允许重复添加
    if (DB::result_first("select appid from %t where path = %s and isdelete = 0", array('pichome_vapp', $path))) {
        exit(json_encode(array('error' => '库已存在，不允许重复添加')));
    }
    $type = isset($_GET['type']) ? intval($_GET['type']) : 0;
    $force = isset($_GET['force']) ? intval($_GET['force']) : 0;
    if ($type == 0) {
        $metajsonfile = $path . BS . 'metadata.json';
        if (!is_file($metajsonfile)) {
            exit(json_encode(array('error' => '系统检测该库不符合eagle库标准，不能作为eagle库添加')));
        }
        $appname = str_replace('.library', '', $appname);
    }
    if ($type == 2) {
        $dbfile = $path . BS . '.bf'.BS.'billfish.db';
        if (!is_file($dbfile)) {
            exit(json_encode(array('tips' => '系统检测该库不符合billfish库标准，不能作为billfish库添加')));
        }
    }
    if ($type == 1 && !$force) {
        $metajsonfile = $path . BS . 'metadata.json';
        if (is_file($metajsonfile) && is_dir($path . BS . 'images')) {
            exit(json_encode(array('tips' => '系统检测该目录可能为eagle库，您确认要作为普通目录导入吗')));
        }
    }
    if ($type == 1 && !$force) {
        $dbfile = $path . BS . '.bf'.BS.'billfish.db';
        if (is_file($dbfile)) {
            exit(json_encode(array('tips' => '系统检测该目录可能为billfish库，您确认要作为普通目录导入吗')));
        }
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
        'filter' => 'a:13:{i:0;a:3:{s:3:"key";s:8:"classify";s:4:"text";s:6:"分类";s:7:"checked";s:1:"1";}i:1;a:4:{s:3:"key";s:3:"tag";s:4:"text";s:6:"标签";s:7:"checked";s:1:"1";s:8:"showtype";s:1:"0";}i:2;a:3:{s:3:"key";s:5:"color";s:4:"text";s:6:"颜色";s:7:"checked";s:1:"1";}i:3;a:3:{s:3:"key";s:4:"link";s:4:"text";s:6:"链接";s:7:"checked";s:1:"1";}i:4;a:3:{s:3:"key";s:4:"desc";s:4:"text";s:6:"注释";s:7:"checked";s:1:"1";}i:5;a:3:{s:3:"key";s:8:"duration";s:4:"text";s:6:"时长";s:7:"checked";s:1:"1";}i:6;a:3:{s:3:"key";s:4:"size";s:4:"text";s:6:"尺寸";s:7:"checked";s:1:"1";}i:7;a:3:{s:3:"key";s:3:"ext";s:4:"text";s:6:"类型";s:7:"checked";s:1:"1";}i:8;a:3:{s:3:"key";s:5:"shape";s:4:"text";s:6:"形状";s:7:"checked";s:1:"1";}i:9;a:3:{s:3:"key";s:5:"grade";s:4:"text";s:6:"评分";s:7:"checked";s:1:"1";}i:10;a:3:{s:3:"key";s:5:"btime";s:4:"text";s:12:"添加时间";s:7:"checked";s:1:"1";}i:11;a:3:{s:3:"key";s:8:"dateline";s:4:"text";s:12:"修改日期";s:7:"checked";s:1:"1";}i:12;a:3:{s:3:"key";s:5:"mtime";s:4:"text";s:12:"创建日期";s:7:"checked";s:1:"1";}}'
    ];
    if ($type == 1) $appattr['allowext'] = $Defaultallowext;
    $path = str_replace(array('/', './', '\\'), BS, $path);
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
    //if (C::t('pichome_vapp')->update($appid, array('isdelete' => 1,'deluid'=>getglobal('uid'),'delusername'=>getglobal('username')))) {
    if (C::t('pichome_vapp')->update($appid, array('isdelete' => 1))) {
        dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=delete', 0, '', '', false, '', 0.1);
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => true)));
    }
}
elseif ($operation == 'getpath') {
    require_once(DZZ_ROOT . './dzz/class/class_encode.php');
    $path = isset($_GET['path']) ? trim($_GET['path']) : '';
    $gettype = isset($_GET['gettype']) ? intval($_GET['gettype']) : 0;
    $charset = isset($_GET['charset']) ? trim($_GET['charset']) : CHARSET;
    $path = str_replace('/', BS, $path);
    if (CHARSET != $charset) $path = diconv($path, CHARSET, $charset);
    if ($gettype && !$path) {
        $path = (PHP_OS == 'Linux') ? '/' : '';
    } elseif (!$path) {
        $path = DZZ_ROOT . 'library';
    }
    if (!empty($Defaultnotallowdir)) {
        $notallowdir = getglobal('setting/pichomeimportnotdir') ? getglobal('setting/pichomeimportnotdir'):implode(',',$Defaultallowext);
        $notallowdir = str_replace(array('.', ',','+','$',"'",'^','(',')','[',']','{','}'), array('\.', '|','\+','\$',"'",'\^','\(',')','\[','\]','\{','\}'), $notallowdir);
        $notallowdir = str_replace('*', '.*', $notallowdir);
    }
    $datas = [];
    if ($path == '') {
        $diskarr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        foreach ($diskarr as $v) {
            if (is_dir($v . ':')) {
                $datas[] = ['path' => $v . ':', 'charset' => CHARSET];
            }
        }
        $datas[] = ['path' => DZZ_ROOT . 'library', 'charset' => CHARSET, 'type' => 1];
    } else {
        if (is_dir($path) && !$gettype) {
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
        } elseif ($gettype) {
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
            $datas[] = ['path' => DZZ_ROOT . 'library', 'charset' => CHARSET, 'type' => 1];


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
	