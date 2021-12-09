<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    require_once(DZZ_ROOT . './dzz/class/class_encode.php');
    require_once libfile('function/user', '', 'user');
    $member = getuserbyuid(1, 1);
    setloginstatus($member,30*60*60);
    //检测管理员登陆
    // Hook::listen('adminlogin');
    global $_G;
    $eagledir =DZZ_ROOT.'library';
    $force = isset($_GET['force']) ? intval($_GET['force']):0;
    //待同步目录
    $syspaths = [];
    if ($dh = opendir($eagledir)) {
        while (($file = readdir($dh)) != false) {
            if ($file != '.' && $file != '..') {
                $filePath = $eagledir . BS . $file;
                if (is_dir($filePath)) $syspaths[] = $filePath;
            }
        }
        closedir($dh);
    }
   
    //待导入库目录
    $apppaths = [];
    $lastfoldername = '.library';
    foreach ($syspaths as $v) {
        if(substr_compare($v, $lastfoldername, -strlen($lastfoldername)) === 0){
            $apppaths[] = $v;
        }else{
            if ($dh = opendir($v)) {
                while (($file = readdir($dh)) != false) {
                    if ($file != '.' && $file != '..') {
                        $filePath = $v . BS . $file;
                        if (is_dir($filePath) && substr_compare($filePath, $lastfoldername, -strlen($lastfoldername)) === 0) $apppaths[] = $filePath;
                    }
                }
                closedir($dh);
            }
        }
      
    }

    foreach(DB::fetch_all("select * from %t where 1",array('pichome_vapp')) as $v){
        if(!in_array($eagledir.BS.$v['path'],$apppaths)){
            C::t('pichome_vapp')->delete_vapp_by_appid($v['appid']);
        }
    }

    //创建库
    foreach ($apppaths as $val) {
        $jsonfile = $val . BS . 'metadata.json';
        $mtime = filemtime($jsonfile);
        $path = str_replace($eagledir.BS, '', $val);
        //如果已有vapp则不再创建
        if ($rdata = C::t('pichome_vapp')->fetch_by_path($path)) {
            if (!file_exists($jsonfile)) {
                continue;
            } else {
                //如果更新时间小于文件时间，如果目录有改变则更新目录信息和标签分类信息
                if ($force || $rdata['dateline'] < $mtime) {
                    $appid = $rdata['appid'];
                    $appdatas = file_get_contents($jsonfile);
                    //解析出json数据
                    $appdatas = json_decode($appdatas, true);
                    
                    //目录数据
                    $folderdata = $appdatas['folders'];
                    
                    C::t('pichome_folder')->insert_folderdata_by_appid($appid,$folderdata);
                    //对比目录数据
                    $folderarr = getpathdata($folderdata,$appid);
                    $folderfids = array_keys($folderarr);
                    $delfids = [];
                    foreach(DB::fetch_all("select fid from %t where fid not in(%n) and appid = %s",array('pichome_folder',$folderfids,$appid)) as $v){
                        $delfids[] = $v['fid'];
                    }
                    C::t('pichome_folder')->delete($delfids);
                    //标签数据
                    $tagdata = $appdatas['tagsGroups'];
                    $currentcids = [];
                    $tids = [];
                    foreach($tagdata as $v){
                        $taggroupdata = [
                            'cid'=>$v['id'].$appid,
                            'catname'=>$v['name'],
                            'appid'=>$appid,
                            'dateline'=>TIMESTAMP
                        ];
                        //插入或更新标签分类数据
                        $cid = C::t('pichome_taggroup')->insert($taggroupdata);
                        $currentcids[] = $cid;
                        
                        foreach($v['tags'] as $val){
                            $tid = C::t('pichome_tag')->insert($val,1);
                            $tids[] = $tid;
                            if($cid){
                                $relasetarr = ['cid'=>$cid,'tid'=>$tid,'appid'=>$appid];
                                C::t('pichome_tagrelation')->insert($relasetarr);
                            }
                        }
                    }
                    if($tids){
                        //查询关系表中包含的不存在的标签关系
                        $drids = [];
                        foreach(DB::fetch_all("select id from %t where tid  not in(%n)  and appid = %s",array('pichome_tagrelation',$tids,$appid)) as $rv){
                            $drids[] = $rv['id'];
                        }
                        //删除不存在的标签关系数据
                        C::t('pichome_tagrelation')->delete($drids);
                    }
                    $ocids = C::t('pichome_taggroup')->fetch_cid_by_appid($appid);
                    $delcids = array_diff($ocids,$currentcids);
                    C::t('pichome_taggroup')->delete_by_cids($delcids);
                    C::t('pichome_vapp')->update($appid,array('dateline'=>$mtime));
                }
            }
            //continue;
        }
        else {
            //如果配置文件不存在不创建库
            if (!file_exists($jsonfile)){
                $p = new Encode_Core();
                if (!$charset = $p->get_encoding($val)) $charset = getglobal('config/system_charset');
                if ($charset != CHARSET) $val = diconv($val, $charset, CHARSET);

                //获取库名称
                $appname = get_basename($val);
                $appname = substr($appname, 0, strlen($appname) - 8);
                $appname = htmlspecialchars($appname);

                //创建虚拟应用
                $appattr = [
                    'appname' => $appname,
                    'uid' => $_G['uid'],
                    'username' => $_G['username'],
                    'personal' => 1,
                    'dateline' => $mtime,
                    'path' => $path,
                    'type'=>1,
                    'charset'=>$charset,
                    'filter'=>'a:13:{i:0;a:3:{s:3:"key";s:8:"classify";s:4:"text";s:6:"分类";s:7:"checked";s:1:"1";}i:1;a:4:{s:3:"key";s:3:"tag";s:4:"text";s:6:"标签";s:7:"checked";s:1:"1";s:8:"showtype";s:1:"0";}i:2;a:3:{s:3:"key";s:5:"color";s:4:"text";s:6:"颜色";s:7:"checked";s:1:"1";}i:3;a:3:{s:3:"key";s:4:"link";s:4:"text";s:6:"链接";s:7:"checked";s:1:"1";}i:4;a:3:{s:3:"key";s:4:"desc";s:4:"text";s:6:"注释";s:7:"checked";s:1:"1";}i:5;a:3:{s:3:"key";s:8:"duration";s:4:"text";s:6:"时长";s:7:"checked";s:1:"1";}i:6;a:3:{s:3:"key";s:4:"size";s:4:"text";s:6:"尺寸";s:7:"checked";s:1:"1";}i:7;a:3:{s:3:"key";s:3:"ext";s:4:"text";s:6:"类型";s:7:"checked";s:1:"1";}i:8;a:3:{s:3:"key";s:5:"shape";s:4:"text";s:6:"形状";s:7:"checked";s:1:"1";}i:9;a:3:{s:3:"key";s:5:"grade";s:4:"text";s:6:"评分";s:7:"checked";s:1:"1";}i:10;a:3:{s:3:"key";s:5:"btime";s:4:"text";s:12:"添加时间";s:7:"checked";s:1:"1";}i:11;a:3:{s:3:"key";s:8:"dateline";s:4:"text";s:12:"修改日期";s:7:"checked";s:1:"1";}i:12;a:3:{s:3:"key";s:5:"mtime";s:4:"text";s:12:"创建日期";s:7:"checked";s:1:"1";}}'
                ];
                $appid =  C::t('pichome_vapp')->insert($appattr);
            }else{
                $p = new Encode_Core();
                if (!$charset = $p->get_encoding($val)) $charset = getglobal('config/system_charset');
                if ($charset != CHARSET) $val = diconv($val, $charset, CHARSET);

                //获取库名称
                $appname = get_basename($val);
                $appname = substr($appname, 0, strlen($appname) - 8);
                $appname = htmlspecialchars($appname);
                //创建虚拟应用
                $appattr = [
                    'appname' => $appname,
                    'uid' => $_G['uid'],
                    'username' => $_G['username'],
                    'personal' => 1,
                    'dateline' => $mtime,
                    'path' => $path,
                    'filter'=>'a:13:{i:0;a:3:{s:3:"key";s:8:"classify";s:4:"text";s:6:"分类";s:7:"checked";s:1:"1";}i:1;a:4:{s:3:"key";s:3:"tag";s:4:"text";s:6:"标签";s:7:"checked";s:1:"1";s:8:"showtype";s:1:"0";}i:2;a:3:{s:3:"key";s:5:"color";s:4:"text";s:6:"颜色";s:7:"checked";s:1:"1";}i:3;a:3:{s:3:"key";s:4:"link";s:4:"text";s:6:"链接";s:7:"checked";s:1:"1";}i:4;a:3:{s:3:"key";s:4:"desc";s:4:"text";s:6:"注释";s:7:"checked";s:1:"1";}i:5;a:3:{s:3:"key";s:8:"duration";s:4:"text";s:6:"时长";s:7:"checked";s:1:"1";}i:6;a:3:{s:3:"key";s:4:"size";s:4:"text";s:6:"尺寸";s:7:"checked";s:1:"1";}i:7;a:3:{s:3:"key";s:3:"ext";s:4:"text";s:6:"类型";s:7:"checked";s:1:"1";}i:8;a:3:{s:3:"key";s:5:"shape";s:4:"text";s:6:"形状";s:7:"checked";s:1:"1";}i:9;a:3:{s:3:"key";s:5:"grade";s:4:"text";s:6:"评分";s:7:"checked";s:1:"1";}i:10;a:3:{s:3:"key";s:5:"btime";s:4:"text";s:12:"添加时间";s:7:"checked";s:1:"1";}i:11;a:3:{s:3:"key";s:8:"dateline";s:4:"text";s:12:"修改日期";s:7:"checked";s:1:"1";}i:12;a:3:{s:3:"key";s:5:"mtime";s:4:"text";s:12:"创建日期";s:7:"checked";s:1:"1";}}'
                ];

                if($appid =  C::t('pichome_vapp')->insert($appattr)){
                    //获取配置信息，插入目录和标签数据
                    $appdatas = file_get_contents($jsonfile);
                    //解析出json数据
                    $appdatas = json_decode($appdatas, true);

                    //目录数据
                    $folderdata = $appdatas['folders'];
                    //新建目录和更新目录
                    C::t('pichome_folder')->insert_folderdata_by_appid($appid,$folderdata);
                    //标签数据
                    $tagdata = $appdatas['tagsGroups'];
                    foreach($tagdata as $v){
                        $taggroupdata = [
                            'cid'=>$v['id'].$appid,
                            'catname'=>$v['name'],
                            'appid'=>$appid,
                            'dateline'=>TIMESTAMP
                        ];
                        $cid = C::t('pichome_taggroup')->insert($taggroupdata);
                        foreach($v['tags'] as $val){
                            $tid = C::t('pichome_tag')->insert($val);
                            $relasetarr = ['cid'=>$cid,'tid'=>$tid,'appid'=>$appid];
                            C::t('pichome_tagrelation')->insert($relasetarr,1);
                        }
                    }
                }
            }

            
        }
    }
    function getpathdata($folderdata,$appid, $pathdata = array())
    {
        foreach ($folderdata as $v) {
            $pathdata[$v['id'].$appid] =  $v['name'];
            if ($v['children']) {
                $tmpchild = $v['children'];
                $pathdata = getpathdata($tmpchild, $appid,$pathdata);
                
            }
        }
        
        return $pathdata;
    }

//兼容linux下获取文件名称
    function get_basename($filename)
    {
        if ($filename) {
            return preg_replace('/^.+[\\\\\\/]/', '', $filename);
        }
        return '';
        
    }