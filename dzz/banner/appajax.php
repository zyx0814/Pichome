<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$overt = getglobal('setting/overt');
if(!$overt && !$overt = C::t('setting')->fetch('overt')){
    Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
}

updatesession();
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
global $_G;
$lang = '';
Hook::listen('lang_parse',$lang,['checklang']);
if ($do == 'addsearch') {//增加关键词搜索次数
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $gid = isset($_GET['gid']) ? intval($_GET['gid']) : '';
    // $bid = isset($_GET['bid']) ? intval($_GET['bid']) : '';
    if (!$keyword) exit(json_encode(array('success'=>false)));
    //增加统计关键词次数
    $arr1 = explode('+', $keyword);
    foreach($arr1 as $value1){
        $arr2 = explode(' ', $value1);
        foreach($arr2 as $kval){
            if($appid){
                addVappkeywordStats($kval,$appid);
            }elseif($gid){
                addTabgroupkeywordStats($kval,$gid);
            }else{
                addKeywordStats($kval);
            }
        }
    }

    exit(json_encode(array('success'=>true)));
} elseif ($do == 'getsearchkeyword') {//热搜关键词
    $cachetime = 3600;
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $gid = isset($_GET['gid']) ? intval($_GET['gid']) : 0;
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']):10;
    if($gid){
        $cachename = 'PICHOMESEARCHHOTKEYWORD_TABGROUP'.$gid.'_'.$page;
    } elseif($appid){
        $cachename = 'PICHOMESEARCHHOTKEYWORD_VAPP'.$appid.'_'.$page;
    }else{
        $cachename = 'PICHOMESEARCHHOTKEYWORD_'.$page;
    }
    $hotdatas = false;
    $hotdatas = C::t('cache')->fetch_cachedata_by_cachename($cachename,$cachetime);
    if (!$hotdatas) {
        if($appid)$hotdatas = C::t('keyword_hots')->fetch_by_appid($appid,$page,$perpage);
        elseif($gid)$hotdatas = C::t('keyword_hots')->fetch_by_gid($gid,$page,$perpage);
        else $hotdatas = C::t('keyword_hots')->fetch_file_hots($page,$perpage);
        if($hotdatas){
            $setarr = ['cachekey' => $cachename, 'cachevalue' => serialize($hotdatas), 'dateline' => time()];
            C::t('cache')->insert_cachedata_by_cachename($setarr,$cachetime,1);
        }

    }
    exit(json_encode($hotdatas));
}elseif($do == 'getsearchtag'){//热门标签

} elseif ($do == 'getfolderdata') {
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $pfids = isset($_GET['pfids']) ? trim($_GET['pfids']):'';
    $isall = isset($_GET['isall']) ? intval($_GET['isall']):0;
    $folderdatanum = [];
    if($appid){
        $appdata = DB::fetch_first("select appid,path,view,type from %t where isdelete = 0 and appid =%s", array('pichome_vapp',$appid));
        if(!C::t('pichome_vapp')->getpermbypermdata($appdata['view'],$appdata['appid']) || ($appdata['type'] !=3 && !IO::checkfileexists($appdata['path'],1))){
            exit(json_encode(array( 'folderdatanum' => $folderdatanum)));
        }
    }
    if($pfids)$pfids = explode(',',$pfids);
    else $pfids = [];
    if($isall){
        $folderdatanum = C::t('pichome_folder')->fetch_all_folder_by_appid($appid);
    }else{
        $folderdatanum = C::t('pichome_folder')->fetch_folder_by_appid_pfid($appid,$pfids);
    }
    if(isset($folderdatanum['langkey'])) unset($folderdatanum['langkey']);
    exit(json_encode(array( 'folderdatanum' => $folderdatanum)));
}elseif($do == 'getsearchfoldernum'){//获取左侧目录数字
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $pathkeys = isset($_GET['pathkeys']) ? trim($_GET['pathkeys']):'';
    $pathkeyarr = explode(',',$pathkeys);
    $data = C::t('pichome_folder')->getFolderNumByPathkey($appid,$pathkeyarr);
    exit(json_encode(array( 'data' => $data)));
}elseif($do == 'getleftnum'){//获取左侧文件数
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    $data = ['all'=>0,'nocat'=>0];
    $data['nocat'] = DB::result_first("select count(rid) as num from %t 
        where  appid = %s and isdelete = 0 and (isnull(fids) or fids='')",array('pichome_resources',$appid));

    $data['all'] = DB::result_first("select count(rid) as num  from %t 
        where  appid = %s and isdelete = 0",array('pichome_resources',$appid));

    exit(json_encode(['success'=>true,'data'=>$data]));
}elseif($do == 'searchfolderbyname'){
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $folderdatanum = C::t('pichome_folder')->search_by_fname($keyword,$appid);
    exit(json_encode(array( 'folderdata' => $folderdatanum)));
}
elseif ($do == 'searchmenu_num') {
    $hassub = isset($_GET['hassub']) ? intval($_GET['hassub']) : 0;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    //是否获取标签数量
    $hasnum = isset($_GET['hasnum']) ? intval($_GET['hasnum']):0;
    $prepage = isset($_GET['prepage']) ? intval($_GET['prepage']):15;
    $pagelimit = 'limit '.($page - 1) * $prepage . ',' . $prepage;
    $presql = '';
    $preparams = [];
    $havingsql ='';
    $havingpara = [];
    $cid = isset($_GET['cid']) ? trim($_GET['cid']) : '';
    $tagkeyword = isset($_GET['tagkeyword']) ? htmlspecialchars($_GET['tagkeyword']) : '';
    $skey = isset($_GET['skey']) ? trim($_GET['skey']) : '';

    $para = [];
    if ($skey == 'tag') {
        $sql = "   %t rt   left join %t r on rt.rid=r.rid ";
        $params = [ 'pichome_resourcestag','pichome_resources'];

    }
    else{
        $sql = "   %t r ";
        $params = ['pichome_resources'];
    }
    $isrecycle = isset($_GET['isrecycle']) ? intval($_GET['isrecycle']):0;
    if(!$isrecycle) $wheresql = " r.isdelete = 0 and r.level <= %d ";
    else $wheresql = " r.isdelete =0  and r.level <= %d ";
    $ismusic = isset($_GET['ismusic']) ? intval($_GET['ismusic']) : 0;
    if($ismusic){
        $wheresql .= ' and r.ext in(%n) ';
        $para[] = ['mp3','ogg','wav','wmv','flac','aac','asf','aiff','au','mid','ra','rma'];
    }
    if(isset($_GET['aid'])){
        $aid = intval($_GET['aid']);
        $imageRids = [];
        Hook::listen('search_condition_filter',$imageRids,['aid'=>$aid]);
        if(!empty($imageRids['rids'])){
            $wheresql .= ' and r.rid in(%n)';
            $para[] = $imageRids['rids'];
        }
    }
    //用户权限等级
    $para[]= $_G['pichomelevel'];

    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $gappid = isset($_GET['appid']) ? [trim($_GET['appid'])] : [];

    if(!is_array($appid)) $appid = (array)$appid;
    $fids = isset($_GET['fids']) ? trim($_GET['fids']) : '';



    $appid = isset($_GET['appid']) ? [trim($_GET['appid'])] : [-1];
    //库权限判断部分
    foreach (DB::fetch_all("select appid,path,view,type from %t where isdelete = 0 and appid in(%n)", array('pichome_vapp',$appid)) as $v) {
        if ($v['type'] != 3 && !IO::checkfileexists($v['path'],1)) {
            continue;
        }
        if (C::t('pichome_vapp')->getpermbypermdata($v['view'],$v['appid'])) {
            $vappids[] = $v['appid'];
        }
    }

    $whererangesql = [];
    //库栏目条件
    if ($vappids) {
        $whererangesql[]= '  r.appid in(%n)';
        $para[] = $vappids;
    }else{
        $whererangesql[]= '  0 ';
    }

    if($whererangesql){
        $wheresql .= ' and ('.implode(' OR ',$whererangesql).') ';
    }

    if ($fids) {
        if ($fids == 'not' || $fids == 'notclassify') {
            $sql .= " LEFT JOIN %t fr on fr.rid = r.rid ";
            $params[] = 'pichome_folderresources';
            $wheresql .= ' and ISNULL(fr.fid)';
        } else {

            $sql .= " LEFT JOIN %t fr on fr.rid = r.rid ";
            $params[] = 'pichome_folderresources';
            $fidarr = explode(',', $fids);
            $childsqlarr = [];
            if ($hassub) {
                foreach ($fidarr as $v) {
                    if ($v == 'not' || $v=='notclassify') $childsqlarr[] = " ISNULL(fr.fid) ";
                    else {
                        if (!in_array('pichome_folder', $params)) {
                            $sql .= ' LEFT JOIN %t f1 on f1.fid=fr.fid ';
                            $params[] = 'pichome_folder';
                        }
                        $childsqlarr[] = " f1.pathkey like %s ";
                        $tpathkey = DB::result_first("select pathkey from %t where fid = %s", array('pichome_folder', $v));
                        $para[] = $tpathkey . '%';
                    }

                }
                if (count($childsqlarr) > 1) $wheresql .= ' and (' . implode(' or ', $childsqlarr) . ')';
                else $wheresql .= ' and ' . $childsqlarr[0];
            } else {
                if (in_array('not', $fidarr)) {
                    $nindex = array_search('not', $fidarr);
                    unset($fidarr[$nindex]);
                    $wheresql .= ' and (fr.fid  in(%n) or ISNULL(fr.fid))';
                }elseif(in_array('notclassify', $fidarr)) {
                    $nindex = array_search('notclassify', $fidarr);
                    unset($fidarr[$nindex]);
                    $wheresql .= ' and (fr.fid  in(%n) or ISNULL(fr.fid))';
                } else {
                    $wheresql .= ' and fr.fid  in(%n)';
                }
                $para[] = $fidarr;

            }


        }

    }
    //添加日期
    if (isset($_GET['btime'])) {
        $btime = explode('_', $_GET['btime']);
        $bstart = strtotime($btime[0]);
        $bend = strtotime($btime[1]) + 24 * 60 * 60;
        if ($bstart) {
            $wheresql .= " and r.btime > %d ";
            //将时间补足13位
            $para[] = $bstart * 1000;
        }
        if ($bend) {
            $wheresql .= " and r.btime < %d ";
            //将时间补足13位
            $para[] = $bend * 1000;
        }
    }
    //修改日期
    if (isset($_GET['dateline'])) {
        $dateline = explode('_', $_GET['dateline']);
        $dstart = strtotime($dateline[0]);
        $dend = strtotime($dateline[1]) + 24 * 60 * 60;
        if ($dstart) {
            $wheresql .= " and r.dateline > %d ";
            //将时间补足13位
            $para[] = $dstart * 1000;
        }

        if ($dend) {
            $wheresql .= " and r.dateline < %d ";
            //将时间补足13位
            $para[] = $dend * 1000;
        }
    }
    //创建日期
    if (isset($_GET['mtime'])) {
        $mtime = explode('_', $_GET['mtime']);
        $mstart = strtotime($mtime[0]);
        $mend = strtotime($mtime[1]) + 24 * 60 * 60;
        if ($mstart) {
            $wheresql .= " and r.mtime > %d ";
            //将时间补足13位
            $para[] = $mstart * 1000;
        }

        if ($mend) {
            $wheresql .= " and r.mtime < %d ";
            //将时间补足13位
            $para[] = $mend * 1000;
        }
    }
    //评分条件
    if (isset($_GET['grade'])) {
        $grade = trim($_GET['grade']);
        $grades = explode(',', $grade);
        $wheresql .= " and r.grade in(%n) ";
        $para[] = $grades;
    }
    //类型条件
    if (isset($_GET['ext'])) {
        $ext = trim($_GET['ext']);
        $exts = explode(',', $ext);
        $wheresql .= " and r.ext in(%n) ";
        $para[] = $exts;
    }

    //时长条件
    if (isset($_GET['duration'])) {
        if (!in_array('pichome_resources_attr', $params)) {
            $sql .= " left join %t ra on r.rid = ra.rid";
            $params[] = 'pichome_resources_attr';
        }
        $durationarr = explode('_', $_GET['duration']);
        $dunit = isset($_GET['dunit']) ? trim($_GET['dunit']) : 's';
        if ($durationarr[0]) {
            $wheresql .= " and ra.duration >= %d ";
            $para[] = ($dunit == 'm') ? $durationarr[0] * 60 : $durationarr[0];
        }

        if ($durationarr[1]) {
            $wheresql .= " and ra.duration <= %d ";
            $para[] = ($dunit == 'm') ? $durationarr[1] * 60 : $durationarr[1];
        }
    }
    //标注条件
    if (isset($_GET['comments'])) {
        $sql .= "  left join %t c on r.rid = c.rid";
        $params[] = 'pichome_comments';
        $comments = intval($_GET['comments']);
        $cval = isset($_GET['cval']) ? trim($_GET['cval']) : '';
        if (!$comments) {
            $wheresql .= " and  isnull(c.annotation) ";
        } else {
            if ($cval) {
                $cvalarr = explode(',', $cval);
                $cvalwhere = [];
                foreach ($cvalarr as $cv) {
                    $cvalwhere[] = " c.annotation like %s";
                    $para[] = '%' . $cv . '%';
                }
                $wheresql .= " and (" . implode(" or ", $cvalwhere) . ") ";
            } else {
                $wheresql .= " and  !isnull(c.annotation) ";
            }
        }
    }
    //注释条件
    if (isset($_GET['desc'])) {
        if (!in_array('pichome_resources_attr', $params)) {
            $sql .= " left join %t ra on r.rid = ra.rid";
            $params[] = 'pichome_resources_attr';
        }
        $desc = intval($_GET['desc']);
        $descval = isset($_GET['descval']) ? trim($_GET['descval']) : '';
        if (!$desc) {
            $wheresql .= " and  (isnull(ra.desc) or ra.desc='') ";
        } else {
            if ($descval) {
                $descvalarr = explode(',', $descval);
                $descvalwhere = [];
                foreach ($descvalarr as $dv) {
                    $descvalwhere[] = "  ra.desc  like %s";
                    $para[] = '%' . $dv . '%';
                }
                $wheresql .= " and (" . implode(" or ", $descvalwhere) . ") ";
            } else {
                $wheresql .= " and ra.desc !='' ";
            }
        }
    }
    //链接条件
    if (isset($_GET['link'])) {
        if (!in_array('pichome_resources_attr', $params)) {
            $sql .= " left join %t ra on r.rid = ra.rid";
            $params[] = 'pichome_resources_attr';
        }
        $link = intval($_GET['link']);
        $linkval = isset($_GET['linkval']) ? trim($_GET['linkval']) : '';
        if (!$link) {
            $wheresql .= " and  (isnull(ra.link) or ra.link='') ";
        } else {
            if ($linkval) {
                $linkvalarr = explode(',', $linkval);
                $linkvalwhere = [];
                foreach ($linkvalarr as $lv) {
                    $linkvalwhere[] = "  ra.link  like %s";
                    $para[] = '%' . $lv . '%';
                }
                $wheresql .= " and (" . implode(" or ", $linkvalwhere) . ") ";
            } else {
                $wheresql .= " and  ra.link !='' ";
            }
        }
    }


    //形状条件
    if (isset($_GET['shape'])) {
        $shape = trim($_GET['shape']);
        $shapes = explode(',', $shape);
        $shapewherearr = [];
        foreach ($shapes as $v) {
            switch ($v) {
                case 7://方图
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = 100;
                    break;
                case 8://横图
                    $shapewherearr[] = '  round((r.width / r.height) * 100) > %d and  round((r.width / r.height) * 100) < 250';
                    $para[] = 100;
                    break;
                case 5://细长横图
                    $shapewherearr[] = '  round((r.width / r.height) * 100) >= %d';
                    $para[] = 250;
                    break;
                case 6://细长竖图
                    $shapewherearr[] = '  round((r.width / r.height) * 100) <= %d';
                    $para[] = 40;
                    break;
                case 9://竖图
                    $shapewherearr[] = '  round((r.width / r.height) * 100) < %d and round((r.width / r.height) * 100) > %d';
                    $para[] = 100;
                    $para[] = 40;
                    break;
                case 1://4:3
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = (4 / 3) * 100;
                    break;
                case 2://3:4
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = (3 / 4) * 100;
                    break;
                case 3://16:9
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = (16 / 9) * 100;
                    break;
                case 4://9:16
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = (9 / 16) * 100;
                    break;
                /*case 10:
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = ($swidth / $sheight) * 100;
                    break;*/
            }
        }
        if (isset($_GET['shapesize'])) {
            $shapesize = trim($_GET['shapesize']);
            $shapesizes = explode(':', $shapesize);
            $swidth = intval($shapesizes[0]);
            $sheight = intval($shapesizes[1]);
            $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
            $para[] = ($swidth / $sheight) * 100;
        }
        if ($shapewherearr) {
            $wheresql .= " and (" . implode(" or ", $shapewherearr) . ") ";
        }
    }

    //尺寸条件
    if (isset($_GET['wsize']) || isset($_GET['hsize'])) {
        $wsizearr = explode('_', $_GET['wsize']);
        $hsizearr = explode('_', $_GET['hsize']);
        if ($wsizearr[0]) {
            $wheresql .= " and r.width >= %d ";
            $para[] = intval($wsizearr[0]);
        }
        if ($wsizearr[1]) {
            $wheresql .= " and r.width <= %d ";
            $para[] = intval($wsizearr[1]);
        }
        if ($hsizearr[0]) {
            $wheresql .= " and r.height >= %d ";
            $para[] = intval($hsizearr[0]);
        }
        if ($hsizearr[1]) {
            $wheresql .= " and r.height <= %d ";
            $para[] = intval($hsizearr[1]);
        }
    }

    //大小条件
    if (isset($_GET['size'])) {
        $size = explode('_', $_GET['size']);
        $unit = isset($_GET['unit']) ? intval($_GET['unit']) : 1;
        switch ($unit) {
            case 0://b
                $size[0] = $size[0];
                $size[1] = $size[1];
                break;
            case 1://kb
                $size[0] = $size[0] * 1024;
                $size[1] = $size[1] * 1024;
                break;
            case 2://mb
                $size[0] = $size[0] * 1024 * 1024;
                $size[1] = $size[1] * 1024 * 1024;
                break;
            case 3://gb
                $size[0] = $size[0] * 1024 * 1024 * 1024;
                $size[1] = $size[1] * 1024 * 1024 * 1024;
                break;
        }
        if ($size[0]) {
            $wheresql .= " and r.szie > %d ";
            $para[] = $size[0];
        }
        if ($size[1]) {
            $wheresql .= " and r.size < %d ";
            $para[] = $size[1];
        }
    }

    if($lang) $wheresql .= " and (r.lang = '".$_G['language']."' or r.lang = 'all' ) ";
    //关键词条件
    $keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '';
    if ($keyword) {
        if (!in_array('pichome_resources_attr', $params)) {
            $sql .= " left join %t ra on r.rid = ra.rid";
            $params[] = 'pichome_resources_attr';
        }
        if($lang && !in_array('lang_search',$params)){
            $sql .= " LEFT JOIN %t lang ON lang.idvalue=r.rid and lang.lang = %s ";
            $params[] = 'lang_search';
            $params[] = $lang;
        }
        $keywords = array();
        $arr1 = explode('+', $keyword);
        foreach ($arr1 as $value1) {
            $value1 = trim($value1);
            $arr2 = explode(' ', $value1);
            $arr3 = array();
            foreach ($arr2 as $value2) {

                $arr3[] = "ra.searchval LIKE %s";
                $para[] = '%' . $value2 . '%';
                if($lang){
                    $arr3[] = "lang.svalue LIKE %s";
                    $para[] = '%' . $value2 . '%';
                }
            }
            $keywords[] = "(" . implode(" OR ", $arr3) . ")";
        }
        if ($keywords) {
            $wheresql .= " and (" . implode(" AND ", $keywords) . ") ";
        }
    }
    //颜色条件
    if (isset($_GET['color'])) {
        $persion = isset($_GET['persion']) ? intval($_GET['persion']) : 0;
        $color = trim($_GET['color']);
        $rgbcolor = hex2rgb($color);
        $rgbarr = [$rgbcolor['r'],$rgbcolor['g'],$rgbcolor['b']];
        $c = new Color($rgbarr);
        $color = $c->toInt();
        $p = getPaletteNumber($color);
        $sql .= " left join %t p on r.rid = p.rid ";
        $params[] = 'pichome_palette';
        $wheresql .= ' and (p.p = %d and p.weight >= %d)';
        $para[] = $p;
        $para[] = 30-(30 -  $persion*30/100);
        $orderarr[] = ' p.weight desc';
    }
    //标签条件
    if (isset($_GET['tag'])) {
        $tagwherearr = [];
        $tagrelative = isset($_GET['tagrelative']) ? intval($_GET['tagrelative']) : 0;

        $tagrelative = isset($_GET['tagrelative']) ? intval($_GET['tagrelative']) : 0;
        $tag = trim($_GET['tag']);
        if ($tag == -1) {
            if (!in_array('pichome_resourcestag', $params)) {
                $sql .= "left join %t rt on r.rid = rt.rid ";
                $params[] = 'pichome_resourcestag';
            }
            $wheresql .= " and isnull(rt.tid) ";
        } else {
            if(!$tagrelative){
                $tagval = explode(',', trim($_GET['tag']));
                $tagwheresql = [];
                foreach($tagval as $k=>$v){
                    $sql .= ' left join %t rt'.($k+1).' on rt'.($k+1).'.rid = r.rid  ';
                    $params[] = 'pichome_resourcestag';
                    $tagwheresql[] = '  (rt'.($k+1).'.tid = %d and !isnull(rt'.($k+1).'.tid)) ';
                    $para[] = $v;
                }

                if(count($tagwheresql) > 1) $wheresql .= " and (" .implode(' or ',$tagwheresql).')';
                elseif(count($tagwheresql)) $wheresql .= " and $tagwheresql[0] ";

            } else {
                $tagval = explode(',', trim($_GET['tag']));
                foreach($tagval as $k=>$v){
                    $sql .= ' left join %t rt'.($k+1).' on rt'.($k+1).'.rid = r.rid ';
                    $params[] = 'pichome_resourcestag';
                    $wheresql .= '  and rt'.($k+1).'.tid = %d ';
                    $para[] = $v;
                }

            }
        }


    }
    $tabgroupflags = [];
    foreach($_GET as $k=>$v){
        if(strpos($k,'tabgroup_') === 0){
            $tabgroupflags[$k] = $v;
        }
    }
    if(!empty($tabgroupflags)){
        foreach($tabgroupflags as $k=>$v){
            $tabgroupflag = trim($k);
            $tabgid = intval(str_replace('tabgroup_','',$tabgroupflag));
            $tabgroupid = trim($v);

            $tabrelative = isset($_GET[$k.'_relative']) ? intval($_GET[$k.'_relative']) : 0;
            if($tabgroupid == -1){
                // if (!in_array('pichome_resourcestab', $params)) {
                $sql .= "left join %t rtab on r.rid = rtab.rid ";
                $params[] = 'pichome_resourcestab';
                // }
                $wheresql .= " and (rtab.gid != $tabgid or isnull(rtab.tid))";
            }else{
                $tabgroupids = explode(',',$tabgroupid);
                if(!$tagrelative){
                    $tagval = explode(',', $tabgroupid);
                    $tabwheresql = [];
                    foreach($tagval as $key=>$val){
                        $sql .= ' left join %t rtab'.($key+1).' on rtab'.($key+1).'.rid = r.rid  ';
                        $params[] = 'pichome_resourcestab';
                        $tabwheresql[] = '  (rtab'.($key+1).'.tid = %d and !isnull(rtab'.($key+1).'.tid)) ';
                        $para[] = $val;
                    }

                    if(count($tabwheresql) > 1) $wheresql .= " and (" .implode(' or ',$tabwheresql).')';
                    elseif(count($tabwheresql)) $wheresql .= " and $tabwheresql[0] ";

                } else {
                    $tagval = explode(',', $tabgroupid);
                    foreach($tagval as $key=>$val){
                        $sql .= ' left join %t rtab'.($key+1).' on rtab'.($key+1).'.rid = r.rid ';
                        $params[] = 'pichome_resourcestab';
                        $wheresql .= '  and rtab'.($key+1).'.tid = %d ';
                        $para[] = $val;
                    }

                }
            }
        }


    }
    //摄影师
    if(isset($_GET['sys'])){
        if(!in_array('pichome_sys',$params)){
            $sql .= " left join %t rs on r.rid = rs.rid";
            $params[] = 'pichome_sys';
        }
        $sys = trim($_GET['sys']);
        if ($sys == -1) {
            $wheresql .= " and isnull(rs.rid) ";
        } else {

            $sysarr = explode(',', $sys);
            $syswheresql = [];
            foreach($sysarr as $k=>$v){
                $sql .= ' left join %t rs'.($k+1).' on rs'.($k+1).'.rid = r.rid ';
                $params[] = 'pichome_sys';
                $wheresql .= '  and rs'.($k+1).'.labelname = %s ';
                $para[] = $v;
            }


        }
    }
    $shapedataarr = array(
        1 => array(
            'start' => 'round((4 / 3) * 100)',
            'end' => '',
            'val' => 1,
            'lablename' => '4:3'
        ),
        2 => array(
            'start' => 'round((3 / 4) * 100)',
            'end' => '',
            'val' => 2,
            'lablename' => '3:4'
        ),
        3 => array(
            'start' => 'round((16 / 9) * 100)',
            'end' => '',
            'val' => 3,
            'lablename' => '16:9'
        ),
        4 => array(
            'start' => 'round((9 / 16) * 100)',
            'end' => '',
            'val' => 4,
            'lablename' => '9:16'
        ),
        5 => array(
            'start' => 250,
            'end' => '',
            'val' => 5,
            'lablename' => lang('elongated_horizontal_chart')
        ),
        6 => array(
            'start' => 0,
            'end' => 40,
            'val' => 6,
            'lablename' => lang('elongated_vertical_view')
        ),
        7 => array(
            'start' => 100,
            'end' => '',
            'val' => 7,
            'lablename' => lang('square_diagram')
        ),
        8 => array(
            'start' => 100,
            'end' => 250,
            'val' => 8,
            'lablename' => lang('horizontal_chart')
        ),
        9 => array(
            'start' => 40,
            'end' => 100,
            'val' => 9,
            'lablename' => lang('portrait_view')
        )

    );
    $timedataarr = array(
        1 => array(
            'start' => strtotime(date("Y-m-d", time())) * 1000,
            'end' => (strtotime(date("Y-m-d", time())) + 24 * 60 * 60) * 1000,
            'val' => 1,
            'label' => lang('filter_range_day'),
        ),
        -1 => array(
            'start' => strtotime(date("Y-m-d", strtotime("-1 day"))) * 1000,
            'end' => (strtotime(date("Y-m-d", time())) + 24 * 60 * 60) * 1000,
            'val' => -1,
            'label' => lang('filter_range_yesterday'),
        ),
        -7 => array(
            'start' => strtotime(date("Y-m-d", strtotime("-7 day"))) * 1000,
            'end' => (strtotime(date("Y-m-d", time())) + 24 * 60 * 60) * 1000,
            'val' => -7,
            'label' => lang('filter_range_week')
        ),
        -30 => array(
            'start' => strtotime(date("Y-m-d", strtotime("-30 day"))) * 1000,
            'end' => (strtotime(date("Y-m-d", time())) + 24 * 60 * 60) * 1000,
            'val' => -30,
            'label' =>  lang('filter_range_month')
        ),
        -90 => array(
            'start' => strtotime(date("Y-m-d", strtotime("-90 day"))) * 1000,
            'end' => (strtotime(date("Y-m-d", time())) + 24 * 60 * 60) * 1000,
            'val' => -90,
            'label' =>  lang('filter_range_month3')
        ),
        -365 => array(
            'start' => strtotime(date("Y-m-d", strtotime("-365 day"))) * 1000,
            'end' => (strtotime(date("Y-m-d", time())) + 24 * 60 * 60) * 1000,
            'val' => -365,
            'label' => lang('filter_range_year')
        ),
    );
    $hookdata = ['params'=>$params,'para'=>$para,'wheresql'=>$wheresql,'sql'=>$sql];
    //Hook::listen('fileFilter',$hookdata);
    $params = $hookdata['params'];
    $para = $hookdata['para'];
    $wheresql = $hookdata['wheresql'];
    $sql = $hookdata['sql'];
    //标签统计
    if ($skey == 'tag') {
        $cid = isset($_GET['cid']) ? $_GET['cid']:'';
        if ($cid) {
            if ($cid == -1) {
                $sql .= "  left join %t tr  on rt.tid=tr.tid ";
                $wheresql .= " and isnull(tr.cid) ";
                $params[] = 'pichome_tagrelation';
            } else {
                $sql .= "  left join %t tr on tr.tid = rt.tid ";
                $params[] = 'pichome_tagrelation';
                $wheresql .= ' and tr.cid = %s ';
                $para[] = $cid;
            }

        }
        $tagkeyword = isset($_GET['tagkeyword']) ? trim($_GET['tagkeyword']):'';
        if ($tagkeyword) {
            $sql .= "  left join %t t on t.tid=rt.tid ";
            $params[] = 'pichome_tag';
            $para[] = '%'.$tagkeyword.'%';
            if($lang){
                $wheresql .= "  and ((t.tagname LIKE %s and  ISNULL(langtag_$lang.idvalue)) or langtag_$lang.svalue LIKE %s) ";
                $sql .= " left join %t langtag_$lang on t.tid =  langtag_$lang.idvalue and  langtag_$lang.idtype=8 ";
                $params[] = 'lang_'.$lang;
                $para[] = '%'.$tagkeyword.'%';
            }else{
                $wheresql .= "  and t.tagname LIKE %s ";
            }
        }
        $tagdata = [];
        //每个标签对应文件个数
        $tdata = [];
        //所有符合条件标签id
        $tids= [];

        if(!$hasnum){
            $sql .= ' left join %t t1 on t1.tid = rt.tid ';
            $params[] = 'pichome_tag';
            if(!empty($preparams)) $params = array_merge($preparams,$params);
            if (!empty($para)) $params = array_merge($params, $para);
            if(!empty($havingpara)) $params = array_merge($params,$havingpara);
            if($presql) $presql = "distinct rt.tid,t1.tagname,$presql";
            else $presql = "distinct rt.tid,t1.tagname";
            foreach (DB::fetch_all("select $presql from $sql where $wheresql $havingsql $pagelimit", $params) as $v){
                Hook::listen('lang_parse',$v,['getTagLangData']);
                $tagdata[$v['tid']]['tagname'] = $v['tagname'];
            }
        }else{
            $fparams = $params;
            if(!empty($preparams)) $params = array_merge($preparams,$params);
            if (!empty($para)) $params = array_merge($params, $para);
            if(!empty($havingpara)) $params = array_merge($params,$havingpara);
            if($presql) $presql = "distinct rt.tid,$presql";
            else $presql = 'distinct rt.tid';
            foreach (DB::fetch_all("select $presql from $sql where $wheresql $havingsql $pagelimit", $params) as $v){
                $tids[] = $v['tid'];
            }
            $sql .= ' left join %t t1 on t1.tid = rt.tid ';
            $fparams[] = 'pichome_tag';
            $wheresql .= ' and rt.tid in(%n) ';
            $para[] = $tids;
            if (!empty($para)) $fparams = array_merge($fparams, $para);
            foreach (DB::fetch_all("select rt.tid,t1.tagname from $sql where $wheresql",$fparams) as $v) {
                Hook::listen('lang_parse',$v,['getTagLangData']);
                if (!isset($tagdata[$v['tid']])) {
                    $tagdata[$v['tid']]['tagname'] = $v['tagname'];
                    $tagdata[$v['tid']]['num'] = 1;
                } else {
                    $tagdata[$v['tid']]['num'] += 1;
                }
            }

        }
        $tids = array_keys($tagdata);
        $finish = (count($tids) >= 15) ? false:true;

        //最后返回数组
        $data = [];
        //含分类标签数据数组
        $catdata = [];
        //如果有appid则获取标签分类数据
        if ($appid) {
            $taggroupdata[] = ['cid'=>0,'catname'=>lang('all')];
            //获取标签分类数据
            $taggroupdata = DB::fetch_all("SELECT cid,catname 
            FROM  %t where appid in(%n) group by cid", array( 'pichome_taggroup', $appid));
            Hook::listen('lang_parse',$taggroupdata,['getTaggroupLangData',1]);
            $taggroupdata[] = ['cid'=>-1,'catname' => lang('unclassify')];

        }
        //分类标签数据
        $data['catdata'] = $taggroupdata;
        //标签不含分类数据
        $alltagdata = $tagdata;
        $data['finish'] = $finish;
        $data['alltagdata'] = $alltagdata;
        //$data['tgdata'] = $seltagdata;
    }
    elseif ($skey == 'shape') {
        //if($hasnum){
        //形状统计
        $presql .= ($presql) ? ' ,case ':' case ';

        foreach ($shapedataarr as $sv) {
            if ($sv['start'] && $sv['end'] === '') {
                $presql .= ' when round((r.width/r.height) * 100) = %i  then %d ';
                $preparams[] = $sv['start'];
            } else {
                $presql .= ' when round((r.width/r.height) * 100) > %d ' . (($sv['end']) ? ' and    round((r.width/r.height)*100) <= %d then %d' : ' then %d');
                $preparams[] = $sv['start'];
                if ($sv['end']) $preparams[] = $sv['end'];

            }
            $preparams[] = $sv['val'];

        }
        if ($presql) {
            $presql .= ' end as %s';
            $preparams[] = 'shapedata';
        }

        if (!empty($para)) $params = array_merge($params, $para);
        if (!empty($preparams)) $shapeparams = array_merge($preparams, $params);
        if(!empty($havingsql)) $shapeparams = array_merge($shapeparams,$havingpara);

        foreach (DB::fetch_all("select  $presql FROM $sql where $wheresql $havingsql", $shapeparams) as $value) {
            if (!isset($data[$value['shapedata']]) && $shapedataarr[$value['shapedata']]['val']) {
                $data[$value['shapedata']]['num'] = 1;
                $data[$value['shapedata']]['lablename'] = $shapedataarr[$value['shapedata']]['lablename'];
                $data[$value['shapedata']]['val'] = $shapedataarr[$value['shapedata']]['val'];
            } elseif ($data[$value['shapedata']]['num']) {
                $data[$value['shapedata']]['num']++;
            }
        }
        //将3:4 9:16 细长竖图归类到竖图
        $data[9]['num'] = ($data[9]['num'] ? $data[9]['num'] : 0) + ($data[2]['num'] ? $data[2]['num'] : 0) + ($data[4]['num'] ? $data[4]['num'] : 0) + ($data[6]['num'] ? $data[6]['num'] : 0);

        if ($data[9]['num']) {
            $data[9]['lablename'] = $shapedataarr[9]['lablename'];
            $data[9]['val'] = $shapedataarr[9]['val'];
        } else {
            unset($data[9]);
        }
        //将4:3 16:9 细长横图图归类到横图
        $data[8]['num'] = ($data[8]['num'] ? $data[8]['num'] : 0) + ($data[1]['num'] ? $data[1]['num'] : 0) + ($data[3]['num'] ? $data[3]['num'] : 0) + ($data[5]['num'] ? $data[5]['num'] : 0);
        if ($data[8]['num']) {
            $data[8]['val'] = $shapedataarr[8]['val'];
            $data[8]['lablename'] = $shapedataarr[8]['lablename'];
        } else {
            unset($data[8]);
        }
        //}else{
        $data = $shapedataarr;
        // }

    }
    elseif ($skey == 'grade') {
        //评分统计
        if (!empty($para)) $params = array_merge($params, $para);
        if (!empty($preparams)) $params = array_merge($preparams, $params);
        //if(!empty($havingsql)) $params = array_merge($params,$havingparams);
        $pselsql = ($presql) ? "distinct r.rid,r.grade,$presql":"distinct r.rid,r.grade";
        $datas = DB::fetch_all("select $pselsql  from $sql   where $wheresql  group by r.rid $havingsql", $params);
        for($i = 1;$i <= 5;$i++){
            $data[$i]['num'] = 0;
            $data[$i]['grade'] = $i;
        }
        foreach($datas as $v){
            $data[$v['grade']]['num'] += 1;
            $data[$v['grade']]['grade'] = $v['grade'];
        }
    }
    elseif ($skey == 'level') {
        //评分统计
        if (!empty($para)) $params = array_merge($params, $para);
        if (!empty($preparams)) $params = array_merge($preparams, $params);
        //if(!empty($havingsql)) $params = array_merge($params,$havingparams);
        $pselsql = ($presql) ? "distinct r.rid,r.level,$presql":"distinct r.rid,r.level";
        $datas = DB::fetch_all("select $pselsql  from $sql   where $wheresql  group by r.rid $havingsql", $params);
        for($i = 1;$i <= 5;$i++){
            $data[$i]['num'] = 0;
            $data[$i]['level'] = $i;
        }
        foreach($datas as $v){
            $data[$v['level']]['num'] += 1;
            $data[$v['level']]['level'] = $v['level'];
        }
    }
    elseif ($skey == 'ext') {
        //类型统计
        if (!empty($para)) $params = array_merge($params, $para);
        if (!empty($preparams)) $params = array_merge($preparams, $params);
        if(!empty($havingsql)) $params = array_merge($params,$havingpara);
        $pselsql = ($presql) ? "distinct r.rid,r.ext,$presql":"distinct r.rid,r.ext";

        $datas = DB::fetch_all("select $pselsql from $sql   where $wheresql group by r.rid $havingsql", $params);

        $tmpdata = [];
        foreach($datas as $v){
            $tmpdata[$v['ext']]['num'] += 1;
            $tmpdata[$v['ext']]['ext'] = $v['ext'];
        }
        foreach($tmpdata as $v){
            $data[] = $v;
        }


    }
    elseif ($skey == 'btime') {
        //添加时间
        $presql = ' case ';
        $prepara = [];
        foreach ($timedataarr as $sv) {
            $presql .= ' when r.btime >= %d ' . (($sv['end']) ? ' and  r.btime < %d then %d' : ' then %d');
            $prepara[] = $sv['start'];
            if ($sv['end']) $prepara[] = $sv['end'];
            $prepara[] = $sv['val'];
        }
        if ($presql) {
            $presql .= ' end as %s';
            $prepara[] = 'btimedata';
        }
        if (!empty($para)) $params = array_merge($params, $para);
        if (!empty($prepara)) $params = array_merge($prepara, $params);
        foreach (DB::fetch_all("select  $presql  FROM $sql  where $wheresql", $params) as $value) {
            if (!$value['btimedata']) continue;
            if (!isset($data[$value['btimedata']])) {
                $data[$value['btimedata']]['num'] = 1;
                $data[$value['btimedata']]['val'] = $timedataarr[$value['btimedata']]['val'];
                $data[$value['btimedata']]['label'] = $timedataarr[$value['btimedata']]['label'];
            } else {
                $data[$value['btimedata']]['num']++;
            }
        }
        //将今天昨天归类到最近七天，将最近七天归到最近30天，将近30天归到最近90天，将最近90天归到最近365天
        $data[-7]['num'] = (isset($data[-7]['num']) ? intval($data[-7]['num']) : 0) + (isset($data[1]['num']) ? intval($data[1]['num']) : 0) + (isset($data[-1]['num']) ? intval($data[-1]['num']) : 0);
        if ($data[-7]['num']) $data[-7] = array('num' => $data[-7]['num'], 'val' => $timedataarr[-7]['val'], 'label' => $timedataarr[-7]['label']);
        $data[-30]['num'] = (isset($data[-30]['num']) ? intval($data[-30]['num']) : 0) + (isset($data[-7]['num']) ? intval($data[-7]['num']) : 0);
        if ($data[-30]['num']) $data[-30] = array('num' => $data[-30]['num'], 'val' => $timedataarr[-30]['val'], 'label' => $timedataarr[-30]['label']);
        $data[-90]['num'] = (isset($data[-90]['num']) ? intval($data[-90]['num']) : 0) + (isset($data[-30]['num']) ? intval($data[-30]['num']) : 0);
        if ($data[-90]['num']) $data[-90] = array('num' => $data[-90]['num'], 'val' => $timedataarr[-90]['val'], 'label' => $timedataarr[-90]['label']);
        $data[-365]['num'] = (isset($data[-365]['num']) ? intval($data[-365]['num']) : 0) + (isset($data[-90]['num']) ? intval($data[-90]['num']) : 0);
        if ($data[-365]['num']) $data[-365] = array('num' => $data[-365]['num'], 'val' => $timedataarr[-365]['val'], 'label' => $timedataarr[-365]['label']);
        foreach ($data as $k => $v) {
            if ($v['num'] == 0) unset($data[$k]);
        }
        krsort($data);
    }
    elseif ($skey == 'mtime') {
        //创建时间
        $presql = ' case ';
        $prepara = [];
        foreach ($timedataarr as $sv) {
            $presql .= ' when r.mtime >= %d ' . (($sv['end']) ? ' and  r.mtime < %d then %d' : ' then %d');
            $prepara[] = $sv['start'];
            if ($sv['end']) $prepara[] = $sv['end'];
            $prepara[] = $sv['val'];
        }
        if ($presql) {
            $presql .= ' end as %s';
            $prepara[] = 'mtimedata';
        }
        if (!empty($para)) $params = array_merge($params, $para);
        if (!empty($prepara)) $params = array_merge($prepara, $params);
        foreach (DB::fetch_all("select  $presql FROM $sql  where $wheresql", $params) as $value) {
            if (!$value['mtimedata']) continue;
            if (!isset($data[$value['mtimedata']])) {
                $data[$value['mtimedata']]['num'] = 1;
                $data[$value['mtimedata']]['val'] = $timedataarr[$value['mtimedata']]['val'];
                $data[$value['mtimedata']]['label'] = $timedataarr[$value['mtimedata']]['label'];
            } else {
                $data[$value['mtimedata']]['num']++;
            }
        }
        //将今天昨天归类到最近七天，将最近七天归到最近30天，将近30天归到最近90天，将最近90天归到最近365天
        $data[-7]['num'] = (isset($data[-7]['num']) ? intval($data[-7]['num']) : 0) + (isset($data[1]['num']) ? intval($data[1]['num']) : 0) + (isset($data[-1]['num']) ? intval($data[-1]['num']) : 0);
        if ($data[-7]['num']) $data[-7] = array('num' => $data[-7]['num'], 'val' => $timedataarr[-7]['val'], 'label' => $timedataarr[-7]['label']);
        $data[-30]['num'] = (isset($data[-30]['num']) ? intval($data[-30]['num']) : 0) + (isset($data[-7]['num']) ? intval($data[-7]['num']) : 0);
        if ($data[-30]['num']) $data[-30] = array('num' => $data[-30]['num'], 'val' => $timedataarr[-30]['val'], 'label' => $timedataarr[-30]['label']);
        $data[-90]['num'] = (isset($data[-90]['num']) ? intval($data[-90]['num']) : 0) + (isset($data[-30]['num']) ? intval($data[-30]['num']) : 0);
        if ($data[-90]['num']) $data[-90] = array('num' => $data[-90]['num'], 'val' => $timedataarr[-90]['val'], 'label' => $timedataarr[-90]['label']);
        $data[-365]['num'] = (isset($data[-365]['num']) ? intval($data[-365]['num']) : 0) + (isset($data[-90]['num']) ? intval($data[-90]['num']) : 0);
        if ($data[-365]['num']) $data[-365] = array('num' => $data[-365]['num'], 'val' => $timedataarr[-365]['val'], 'label' => $timedataarr[-365]['label']);
        foreach ($data as $k => $v) {
            if ($v['num'] == 0) unset($data[$k]);
        }
        krsort($data);
    }
    elseif ($skey == 'dateline') {
        //修改时间
        $presql = ' case ';
        $prepara = [];
        foreach ($timedataarr as $sv) {
            $presql .= ' when r.dateline >= %d ' . (($sv['end']) ? ' and  r.dateline < %d then %d' : ' then %d');
            $prepara[] = $sv['start'];
            if ($sv['end']) $prepara[] = $sv['end'];
            $prepara[] = $sv['val'];
        }
        if ($presql) {
            $presql .= ' end as %s';
            $prepara[] = 'datelinedata';
        }
        if (!empty($para)) $params = array_merge($params, $para);
        if (!empty($prepara)) $params = array_merge($prepara, $params);
        foreach (DB::fetch_all("select  $presql FROM $sql  where$wheresql ", $params) as $value) {
            if (!$value['datelinedata']) continue;
            if (!isset($data[$value['datelinedata']])) {
                $data[$value['datelinedata']]['num'] = 1;
                $data[$value['datelinedata']]['val'] = $timedataarr[$value['datelinedata']]['val'];
                $data[$value['datelinedata']]['label'] = $timedataarr[$value['datelinedata']]['label'];
            } else {
                $data[$value['datelinedata']]['num']++;
            }
        }
        //将今天昨天归类到最近七天，将最近七天归到最近30天，将近30天归到最近90天，将最近90天归到最近365天
        $data[-7]['num'] = (isset($data[-7]['num']) ? intval($data[-7]['num']) : 0) + (isset($data[1]['num']) ? intval($data[1]['num']) : 0) + (isset($data[-1]['num']) ? intval($data[-1]['num']) : 0);
        if ($data[-7]['num']) $data[-7] = array('num' => $data[-7]['num'], 'val' => $timedataarr[-7]['val'], 'label' => $timedataarr[-7]['label']);
        $data[-30]['num'] = (isset($data[-30]['num']) ? intval($data[-30]['num']) : 0) + (isset($data[-7]['num']) ? intval($data[-7]['num']) : 0);
        if ($data[-30]['num']) $data[-30] = array('num' => $data[-30]['num'], 'val' => $timedataarr[-30]['val'], 'label' => $timedataarr[-30]['label']);
        $data[-90]['num'] = (isset($data[-90]['num']) ? intval($data[-90]['num']) : 0) + (isset($data[-30]['num']) ? intval($data[-30]['num']) : 0);
        if ($data[-90]['num']) $data[-90] = array('num' => $data[-90]['num'], 'val' => $timedataarr[-90]['val'], 'label' => $timedataarr[-90]['label']);
        $data[-365]['num'] = (isset($data[-365]['num']) ? intval($data[-365]['num']) : 0) + (isset($data[-90]['num']) ? intval($data[-90]['num']) : 0);
        if ($data[-365]['num']) $data[-365] = array('num' => $data[-365]['num'], 'val' => $timedataarr[-365]['val'], 'label' => $timedataarr[-365]['label']);
        foreach ($data as $k => $v) {
            if ($v['num'] == 0) unset($data[$k]);
        }
        krsort($data);
    }
    elseif ($skey == 'grouptag') {
        //标签分类id
        $cid = isset($_GET['cid']) ? trim($_GET['cid']) : '';
        $sql .= '  left join %t rt on rt.rid=r.rid  left join %t tr on tr.tid=rt.tid ';
        $params[] = 'pichome_resourcestag';
        $params[] = 'pichome_tagrelation';
        $wheresql .= ' and tr.cid = %s';
        $para[] = $cid;
        if (!empty($para)) $params = array_merge($params, $para);
        //每个标签对应文件个数
        $tdata = [];
        //所有符合条件标签id
        $tids = [];
        foreach (DB::fetch_all("select rt.tid,r.rid from $sql   where $wheresql", $params) as $v) {
            if (!isset($tdata[$v['tid']])) $tdata[$v['tid']]['num'] = 1;
            else $tdata[$v['tid']]['num'] += 1;
            if ($v['tid']) $tids[] = $v['tid'];
        }
        //统计所有标签，去掉重复标签
        $tids = array_unique($tids);
        //标签id对应标签名称数组
        $tagdata = [];
        foreach (DB::fetch_all("select tagname,tid from %t where tid in(%n)", array('pichome_tag', $tids)) as $v) {
            $tagdata[$v['tid']] = $v['tagname'];
        }
        //最后返回数组
        $data = [];
        foreach ($tdata as $tid => $num) {
            if (isset($tagdata[$tid])) $data[$tid] = array('tid' => intval($tid), 'tagname' => $tagdata[$tid], 'num' => $num['num']);
        }
    }
    elseif(strpos($skey,'tabgroup_') === 0){
        $tabkeyword = isset($_GET['tabkeyword']) ? trim($_GET['tabkeyword']) : '';
        $tabgid = intval(str_replace('tabgroup_','',$skey));
        // if (!in_array('pichome_resourcestab', $params)) {
        $sql .= "left join %t rtab on r.rid = rtab.rid ";
        $params[] = 'pichome_resourcestab';
        // }
        $wheresql .= " and rtab.gid = $tabgid ";
        if($tabkeyword){
            if(!in_array('tab',$params)){
                $sql .= " left join %t tab on tab.tid = rtab.tid ";
                $params[] = 'tab';
            }
            if($lang && !in_array('lang_search',$params)){
                $sql .= " LEFT JOIN %t lang ON lang.idvalue=tab.tid and lang.lang = %s ";
                $params[] = 'lang_search';
                $params[] = $lang;
            }
            $sql .= " LEFT JOIN %t searchattr ON searchattr.tid=tab.tid and searchattr.skey='searchattr'";
            $params[] = 'tab_attr';
            $keywords = array();
            $arr1 = explode('+', $tabkeyword);
            foreach ($arr1 as $value1) {
                $value1 = trim($value1);
                $arr2 = explode(' ', $value1);
                $arr3 = array();
                foreach ($arr2 as $value2) {
                    $arr3[] = "tab.tabname LIKE %s";
                    $para[] = '%' . $value2 . '%';
                    $arr3[] = "searchattr.svalue LIKE %s";
                    $para[] = '%' . $value2 . '%';
                    if($lang){
                        $arr3[] = "lang.svalue LIKE %s";
                        $para[] = '%' . $value2 . '%';
                    }

                }
                $keywords[] = "(" . implode(" OR ", $arr3) . ")";
            }
            if ($keywords) {
                $wheresql .= " and (" . implode(" AND ", $keywords) . ")";
            }

        }
        if (!empty($para)) $params = array_merge($params, $para);
        if (!empty($preparams)) $params = array_merge($preparams, $params);
        $tmpdata = $tids =  [];
        /* echo "select distinct rtab.tid from $sql where $wheresql  $pagelimit";
         print_r($params);
         die;*/
        foreach(DB::fetch_all("select distinct rtab.tid from $sql where $wheresql  $pagelimit", $params) as $v){
            $tids[] =$v['tid'];
        }
        if($tids)$tabdata = C::t('#tab#tab')->fetch_by_tids($tids);
        else $tabdata = [];
        $tabdatas = [];
        foreach($tabdata as $val){
            $tabdatas[$val['tid']] = $val;
        }
        if($tabdatas){
            foreach(DB::fetch_all("select distinct rtab.rid,rtab.tid from %t rtab where rtab.tid in(%n)", ['pichome_resourcestab',$tids]) as $v){
                $tmpdata[$v['tid']]['num'] += 1;
            }
            foreach($tmpdata as $k=>$v){
                if(isset($tabdatas[$k])){
                    $data[$k] = array('tid'=>$k,'tabname'=>$tabdatas[$k]['tabname'],'num'=>$v['num']);
                }
            }
        }

    }
    elseif($skey == 'sys'){
        $syskeyword = isset($_GET['syskeyword']) ? trim($_GET['syskeyword']) : '';
        $sql .= "left join %t ps on r.rid = ps.rid ";
        $params[] = 'pichome_sys';

        $wheresql .= ' and !isnull(ps.rid) ';
        if($syskeyword){
            $wheresql .= " and ps.labelname like %s ";
            $para[] = '%'.$syskeyword.'%';
        }
        //查询当前符合条件的数据分页

        if (!empty($para)) $params = array_merge($params, $para);
        if (!empty($preparams)) $params = array_merge($preparams, $params);
        if(!empty($havingsql)) $params = array_merge($params,$havingparams);
        $pselsql = ($presql) ? "  ps.labelname,count(ps.rid) as num ,$presql":"ps.labelname,count(ps.rid) as num";
        $datas = DB::fetch_all("select $pselsql from $sql   where $wheresql  $havingsql group by labelname $pagelimit", $params);
        $tmpdata = [];
        foreach($datas as $v){
            $tmpdata[$v['labelname']]['num'] = $v['num'];
            $tmpdata[$v['labelname']]['text'] = $v['labelname'];
        }
        foreach($tmpdata as $v){
            $data[] = $v;
        }


    }
    exit(json_encode($data));
}
elseif ($do == 'search_menu') {

    $skey = isset($_GET['skey']) ? trim($_GET['skey']) : '';
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $hassub = isset($_GET['hassub']) ? intval($_GET['hassub']) : 0;
    $para = [];
    if($skey == 'tag'){
        if($appid){
            $sql = "select count(DISTINCT(rt.tid)) as num,g.cid,g.catname  from  %t rt   left join %t r on rt.rid=r.rid ";
        }else{
            $sql = "select count(DISTINCT(rt.tid)) as num  from  %t rt   left join %t r on rt.rid=r.rid ";
        }

        $params = [ 'pichome_resourcestag','pichome_resources'];

    }else{
        exit(json_encode(array()));
    }
    $isrecycle = isset($_GET['isrecycle']) ? intval($_GET['isrecycle']):0;
    if(!$isrecycle) $wheresql = " r.isdelete = 0 and r.level <= %d ";
    else $wheresql = " r.isdelete =0  and r.level <= %d ";
    $ismusic = isset($_GET['ismusic']) ? intval($_GET['ismusic']) : 0;
    if($ismusic){
        $wheresql .= ' and r.ext in(%n) ';
        $para[] = ['mp3','ogg','wav','wmv','flac','aac','asf','aiff','au','mid','ra','rma'];
    }
    //用户权限等级
    $para[] = $_G['pichomelevel'];

    if(!empty($nopermtids)){
        $sql .= "left join %t ra on r.rid = ra.rid";
        $params[] = 'pichome_resources_attr';
        foreach ($nopermtids as $v) {
            $tagwherearr[] = " !find_in_set(%d,ra.tag)";
            $para[] = $v;
        }
        $wheresql .= " and (" . implode(" and ", $tagwherearr) . ")";

    }
    $fids = isset($_GET['fids']) ? trim($_GET['fids']) : '';

    $appid = isset($_GET['appid']) ? [trim($_GET['appid'])] : [];
    //库权限判断部分
    foreach (DB::fetch_all("select appid,path,view,type from %t where isdelete = 0 and appid in(%n)", array('pichome_vapp',$appid)) as $v) {
        if ($v['type'] != 3 && !IO::checkfileexists($v['path'],1)) {
            continue;
        }
        if (C::t('pichome_vapp')->getpermbypermdata($v['view'],$v['appid'])) {
            $vappids[] = $v['appid'];
        }
    }

    $whererangesql = [];
    //库栏目条件
    if ($appid) {
        $whererangesql[]= '  r.appid in(%n)';
        $para[] = $vappids;
    }else{
        $whererangesql[]= '  0 ';
    }
    if($whererangesql){
        $wheresql .= ' and ('.implode(' OR ',$whererangesql).') ';
    }
    if ($fids) {
        if ($fids == 'not' || $fids == 'notclassify') {
            $sql .= " LEFT JOIN %t fr on fr.rid = r.rid ";
            $params[] = 'pichome_folderresources';
            $wheresql .= ' and ISNULL(fr.fid)';
        } else {

            $sql .= " LEFT JOIN %t fr on fr.rid = r.rid ";
            $params[] = 'pichome_folderresources';
            $fidarr = explode(',', $fids);
            $childsqlarr = [];
            if ($hassub) {
                foreach ($fidarr as $v) {
                    if ($v == 'not' || $v=='notclassify') $childsqlarr[] = " ISNULL(fr.fid) ";
                    else {
                        if (!in_array('pichome_folder', $params)) {
                            $sql .= ' LEFT JOIN %t f1 on f1.fid=fr.fid ';
                            $params[] = 'pichome_folder';
                        }
                        $childsqlarr[] = " f1.pathkey like %s ";
                        $tpathkey = DB::result_first("select pathkey from %t where fid = %s", array('pichome_folder', $v));
                        $para[] = $tpathkey . '%';
                    }

                }
                if (count($childsqlarr) > 1) $wheresql .= ' and (' . implode(' or ', $childsqlarr) . ')';
                else $wheresql .= ' and ' . $childsqlarr[0];
            } else {
                if (in_array('not', $fidarr)) {
                    $nindex = array_search('not', $fidarr);
                    unset($fidarr[$nindex]);
                    $wheresql .= ' and (fr.fid  in(%n) or ISNULL(fr.fid))';
                }elseif(in_array('notclassify', $fidarr)) {
                    $nindex = array_search('notclassify', $fidarr);
                    unset($fidarr[$nindex]);
                    $wheresql .= ' and (fr.fid  in(%n) or ISNULL(fr.fid))';
                } else {
                    $wheresql .= ' and fr.fid  in(%n)';
                }
                $para[] = $fidarr;

            }


        }

    }
    //关键词条件
    //关键词条件
    $keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '';
    if ($keyword) {
        if (!in_array('pichome_resources_attr', $params)) {
            $sql .= "  left join %t ra on r.rid = ra.rid";
            $params[] = 'pichome_resources_attr';
        }
        $keywords = array();
        $arr1 = explode('+', $keyword);
        foreach ($arr1 as $value1) {
            $value1 = trim($value1);
            $arr2 = explode(' ', $value1);
            $arr3 = array();
            foreach ($arr2 as $value2) {
                $arr3[] = "r.name LIKE %s";
                $para[] = '%' . $value2 . '%';
                $arr3[] = "ra.link LIKE %s";
                $para[] = '%' . $value2 . '%';
                $arr3[] = "ra.desc LIKE %s";
                $para[] = '%' . $value2 . '%';
                $arr3[] = "ra.searchval LIKE %s";
                $para[] = '%' . $value2 . '%';
            }
            $keywords[] = "(" . implode(" OR ", $arr3) . ")";
        }
        if ($keywords) {
            $wheresql .= " and (" . implode(" AND ", $keywords) . ")";
        }
    }
    //颜色条件
    //颜色条件
    if (isset($_GET['color'])) {
        $persion = isset($_GET['persion']) ? intval($_GET['persion']) : 0;
        $color = trim($_GET['color']);
        $rgbcolor = hex2rgb($color);
        $rgbarr = [$rgbcolor['r'],$rgbcolor['g'],$rgbcolor['b']];
        $c = new Color($rgbarr);
        $color = $c->toInt();
        $p = getPaletteNumber($color);
        $sql .= " left join %t p on r.rid = p.rid ";
        $params[] = 'pichome_palette';
        $wheresql .= ' and (p.p = %d and p.weight >= %d)';
        $para[] = $p;
        $para[] = 30-(30 -  $persion*30/100);
        $orderarr[] = ' p.weight desc';
    }
    //标签条件
    if (isset($_GET['tag'])) {
        $tagwherearr = [];
        $tagrelative = isset($_GET['tagrelative']) ? intval($_GET['tagrelative']) : 0;

        $tagrelative = isset($_GET['tagrelative']) ? intval($_GET['tagrelative']) : 0;
        $tag = trim($_GET['tag']);
        if ($tag == -1) {
            if (!in_array('pichome_resourcestag', $params)) {
                $sql .= "left join %t rt on r.rid = rt.rid ";
                $params[] = 'pichome_resourcestag';
            }
            $wheresql .= " and isnull(rt.tid) ";
        } else {
            if(!$tagrelative){
                $tagval = explode(',', trim($_GET['tag']));
                $tagwheresql = [];
                foreach($tagval as $k=>$v){
                    $sql .= ' left join %t rt'.($k+1).' on rt'.($k+1).'.rid = r.rid  ';
                    $params[] = 'pichome_resourcestag';
                    $tagwheresql[] = '  (rt'.($k+1).'.tid = %d and !isnull(rt'.($k+1).'.tid)) ';
                    $para[] = $v;
                }

                if(count($tagwheresql) > 1) $wheresql .= " and (" .implode(' or ',$tagwheresql).')';
                elseif(count($tagwheresql)) $wheresql .= " and $tagwheresql[0] ";

            } else {
                $tagval = explode(',', trim($_GET['tag']));
                foreach($tagval as $k=>$v){
                    $sql .= ' left join %t rt'.($k+1).' on rt'.($k+1).'.rid = r.rid ';
                    $params[] = 'pichome_resourcestag';
                    $wheresql .= '  and rt'.($k+1).'.tid = %d ';
                    $para[] = $v;
                }

            }
        }


    }

    //时长条件
    if (isset($_GET['duration'])) {
        if (!in_array('pichome_resources_attr', $params)) {
            $sql .= "  left join %t ra on r.rid = ra.rid";
            $params[] = 'pichome_resources_attr';
        }
        $durationarr = explode('_', $_GET['duration']);
        $dunit = isset($_GET['dunit']) ? trim($_GET['dunit']) : 's';
        if ($durationarr[0]) {
            $wheresql .= " and ra.duration >= %d";
            $para[] = ($dunit == 'm') ? $durationarr[0] * 60 : $durationarr[0];
        }

        if ($durationarr[1]) {
            $wheresql .= " and ra.duration <= %d";
            $para[] = ($dunit == 'm') ? $durationarr[1] * 60 : $durationarr[1];
        }
    }
    //标注条件
    if (isset($_GET['comments'])) {
        $sql .= "  left join %t c on r.rid = c.rid";
        $params[] = 'pichome_comments';
        $comments = intval($_GET['comments']);
        $cval = isset($_GET['cval']) ? trim($_GET['cval']) : '';
        if (!$comments) {
            $wheresql .= " and  isnull(c.annotation) ";
        } else {
            if ($cval) {
                $cvalarr = explode(',', $cval);
                $cvalwhere = [];
                foreach ($cvalarr as $cv) {
                    $cvalwhere[] = " c.annotation like %s";
                    $para[] = '%' . $cv . '%';
                }
                $wheresql .= " and (" . implode(" or ", $cvalwhere) . ")";
            } else {
                $wheresql .= " and  !isnull(c.annotation)";
            }
        }
    }
    //注释条件
    if (isset($_GET['desc'])) {
        if (!in_array('pichome_resources_attr', $params)) {
            $sql .= " left join %t ra on r.rid = ra.rid";
            $params[] = 'pichome_resources_attr';
        }
        $desc = intval($_GET['desc']);
        $descval = isset($_GET['descval']) ? trim($_GET['descval']) : '';
        if (!$desc) {
            $wheresql .= " and  (isnull(ra.desc) or ra.desc='') ";
        } else {
            if ($descval) {
                $descvalarr = explode(',', $descval);
                $descvalwhere = [];
                foreach ($descvalarr as $dv) {
                    $descvalwhere[] = "  ra.desc  like %s";
                    $para[] = '%' . $dv . '%';
                }
                $wheresql .= " and (" . implode(" or ", $descvalwhere) . ")";
            } else {
                $wheresql .= " and ra.desc !=''";
            }
        }
    }
    //链接条件
    if (isset($_GET['link'])) {
        if (!in_array('pichome_resources_attr', $params)) {
            $sql .= " left join %t ra on r.rid = ra.rid";
            $params[] = 'pichome_resources_attr';
        }
        $link = intval($_GET['link']);
        $linkval = isset($_GET['linkval']) ? trim($_GET['linkval']) : '';
        if (!$link) {
            $wheresql .= " and  (isnull(ra.link) or ra.link='') ";
        } else {
            if ($linkval) {
                $linkvalarr = explode(',', $linkval);
                $linkvalwhere = [];
                foreach ($linkvalarr as $lv) {
                    $linkvalwhere[] = "  ra.link  like %s";
                    $para[] = '%' . $lv . '%';
                }
                $wheresql .= " and (" . implode(" or ", $linkvalwhere) . ")";
            } else {
                $wheresql .= " and  ra.link !='' ";
            }
        }
    }


    //形状条件
    if (isset($_GET['shape'])) {
        $shape = trim($_GET['shape']);
        $shapes = explode(',', $shape);

        $shapewherearr = [];
        foreach ($shapes as $v) {
            switch ($v) {
                case 7://方图
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = 100;
                    break;
                case 8://横图
                    $shapewherearr[] = '  round((r.width / r.height) * 100) > %d and  round((r.width / r.height) * 100) < 250';
                    $para[] = 100;
                    break;
                case 5://细长横图
                    $shapewherearr[] = '  round((r.width / r.height) * 100) >= %d';
                    $para[] = 250;
                    break;
                case 6://细长竖图
                    $shapewherearr[] = '  round((r.width / r.height) * 100) <= %d';
                    $para[] = 40;
                    break;
                case 9://竖图
                    $shapewherearr[] = '  round((r.width / r.height) * 100) < %d and round((r.width / r.height) * 100) > %d';
                    $para[] = 100;
                    $para[] = 40;
                    break;
                case 1://4:3
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = (4 / 3) * 100;
                    break;
                case 2://3:4
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = (3 / 4) * 100;
                    break;
                case 3://16:9
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = (16 / 9) * 100;
                    break;
                case 4://9:16
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = (9 / 16) * 100;
                    break;
                /*case 10:
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = ($swidth / $sheight) * 100;
                    break;*/
            }
        }
        if (isset($_GET['shapesize'])) {
            $shapesize = trim($_GET['shapesize']);
            $shapesizes = explode(':', $shapesize);
            $swidth = intval($shapesizes[0]);
            $sheight = intval($shapesizes[1]);
            $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
            $para[] = ($swidth / $sheight) * 100;
        }
        if ($shapewherearr) {
            $wheresql .= " and (" . implode(" or ", $shapewherearr) . ")";
        }
    }
    //评分条件
    if (isset($_GET['grade'])) {
        $grade = trim($_GET['grade']);
        $grades = explode(',', $grade);
        $wheresql .= " and r.grade in(%n)";
        $para[] = $grades;
    }
    //类型条件
    if (isset($_GET['ext'])) {
        $ext = trim($_GET['ext']);
        $exts = explode(',', $ext);
        $wheresql .= " and r.ext in(%n)";
        $para[] = $exts;
    }
    //添加日期
    if (isset($_GET['btime'])) {
        $btime = explode('_', $_GET['btime']);
        $bstart = strtotime($btime[0]);
        $bend = strtotime($btime[1]) + 24 * 60 * 60;
        if ($bstart) {
            $wheresql .= " and r.btime > %d";
            //将时间补足13位
            $para[] = $bstart * 1000;
        }
        if ($bend) {
            $wheresql .= " and r.btime < %d";
            //将时间补足13位
            $para[] = $bend * 1000;
        }
    }
    //修改日期
    if (isset($_GET['dateline'])) {
        $dateline = explode('_', $_GET['dateline']);
        $dstart = strtotime($dateline[0]);
        $dend = strtotime($dateline[1]) + 24 * 60 * 60;
        if ($dstart) {
            $wheresql .= " and r.dateline > %d";
            //将时间补足13位
            $para[] = $dstart * 1000;
        }

        if ($dend) {
            $wheresql .= " and r.dateline < %d";
            //将时间补足13位
            $para[] = $dend * 1000;
        }
    }
    //创建日期
    if (isset($_GET['mtime'])) {
        $mtime = explode('_', $_GET['mtime']);
        $mstart = strtotime($mtime[0]);
        $mend = strtotime($mtime[1]) + 24 * 60 * 60;
        if ($mstart) {
            $wheresql .= " and r.mtime > %d";
            //将时间补足13位
            $para[] = $mstart * 1000;
        }

        if ($mend) {
            $wheresql .= " and r.mtime < %d";
            //将时间补足13位
            $para[] = $mend * 1000;
        }
    }
    //尺寸条件
    if (isset($_GET['wsize']) || isset($_GET['hsize'])) {
        $wsizearr = explode('_', $_GET['wsize']);
        $hsizearr = explode('_', $_GET['hsize']);
        if ($wsizearr[0]) {
            $wheresql .= " and r.width >= %d";
            $para[] = intval($wsizearr[0]);
        }
        if ($wsizearr[1]) {
            $wheresql .= " and r.width <= %d";
            $para[] = intval($wsizearr[1]);
        }
        if ($hsizearr[0]) {
            $wheresql .= " and r.height >= %d";
            $para[] = intval($hsizearr[0]);
        }
        if ($hsizearr[1]) {
            $wheresql .= " and r.height <= %d";
            $para[] = intval($hsizearr[1]);
        }
    }

    //大小条件
    if (isset($_GET['size'])) {
        $size = explode('_', $_GET['size']);
        $unit = isset($_GET['unit']) ? intval($_GET['unit']) : 1;
        switch ($unit) {
            case 0://b
                $size[0] = $size[0];
                $size[1] = $size[1];
                break;
            case 1://kb
                $size[0] = $size[0] * 1024;
                $size[1] = $size[1] * 1024;
                break;
            case 2://mb
                $size[0] = $size[0] * 1024 * 1024;
                $size[1] = $size[1] * 1024 * 1024;
                break;
            case 3://gb
                $size[0] = $size[0] * 1024 * 1024 * 1024;
                $size[1] = $size[1] * 1024 * 1024 * 1024;
                break;
        }
        if ($size[0]) {
            $wheresql .= " and r.szie > %d";
            $para[] = $size[0];
        }
        if ($size[1]) {
            $wheresql .= " and r.size < %d";
            $para[] = $size[1];
        }
    }
    $isrecycle = isset($_GET['isrecycle']) ? intval($_GET['isrecycle']):0;
    if(!$isrecycle) $wheresql .= " and r.isdelete = 0 ";
    else $wheresql .= " and r.isdelete =0 ";
    $data = array();
    if ($skey == 'tag') {

        $tagkeyword = isset($_GET['tagkeyword']) ? trim($_GET['tagkeyword']):'';
        if ($tagkeyword) {
            $sql .= "  left join %t t on t.tid=rt.tid ";
            $params[] = 'pichome_tag';
            $para[] = '%'.$tagkeyword.'%';
            if($lang){
                $wheresql .= "  and ((t.tagname LIKE %s and  ISNULL(langtag_$lang.idvalue)) or langtag_$lang.svalue LIKE %s) ";
                $sql .= " left join %t langtag_$lang on t.tid =  langtag_$lang.idvalue and  langtag_$lang.idtype=8 ";
                $params[] = 'lang_'.$lang;
                $para[] = '%'.$tagkeyword.'%';
            }else{
                $wheresql .= "  and t.tagname LIKE %s ";
            }
        }

        $catdata = [];
        if($appid){
            $sql .= "  left join %t t1 on rt.tid = t1.tid ";
            $params[] = 'pichome_tag';
            if(!in_array('pichome_tagrelation',$params)){
                $sql .= "  left join %t tr on tr.tid=t1.tid ";
                $params[] = 'pichome_tagrelation';
            }
            $sql .= "  left join %t g  on g.cid = tr.cid ";
            $params[] = 'pichome_taggroup';
            if (!empty($para)) $params = array_merge($params, $para);
            $sum = 0;
            foreach (DB::fetch_all("$sql where $wheresql group by g.cid",$params) as $v) {
                Hook::listen('lang_parse',$v,['getTaggroupLangData']);
                if($v['cid']){
                    $catdata[]=['cid'=>$v['cid'],'catname'=>$v['catname'],'num'=>$v['num']];
                }else{
                    $catdata[]=['cid'=>-1,'catname'=>lang('unclassify'),'num'=>$v['num']];
                }
                $sum += $v['num'];

            }
            $catdata[]=['cid'=>0,'catname'=>lang('all'),'num'=>$sum];

        }else{
            //if (!empty($para)) $params = array_merge($params, $para);
            //echo $sql;die;
            //$numdata =  DB::result_first("$sql where $wheresql ",$params);
            //print_r($numdata);die;
            //$catdata[]=['cid'=>0,'catname'=>'全部','num'=>$numdata['num']];
        }


        //最后返回数组
        $data = [];

        $data['catdata'] = $catdata;
    }
    exit(json_encode($data));
}
elseif ($do == 'likewords') {//联想词
    $keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '';
    //取出name,desc,tagname,comments和最近搜索中包含关键词的内容各10条
    $resourceslikeword = C::t('pichome_resources')->fetch_like_words($keyword);
    $resourcesattrlikeword = C::t('pichome_resources_attr')->fetch_like_words($keyword);
    $taglikeword = C::t('pichome_tag')->fetch_like_words($keyword);
    $commentlikeword = C::t('pichome_comments')->fetch_like_words($keyword);
    $recentlikekeyword = C::t('pichome_searchrecent')->fetch_like_words($keyword);
    //获得所有包含关键词的数组
    $likekeywords = array_merge($resourceslikeword, $taglikeword, $commentlikeword, $resourcesattrlikeword, $recentlikekeyword);
    $likekeyworddata = [];
    //按关键词出现的位置得到二维数组
    foreach ($likekeywords as $v) {
        $index = strpos($keyword, $v);
        $likekeyworddata[$index][] = $v;
    }
    //以出现的位置排序
    ksort($likekeyworddata);
    $returnlikekeywords = [];
    //得到关键词排序后的数组
    foreach ($likekeyworddata as $v) {
        $returnlikekeywords = array_merge($returnlikekeywords, $v);
    }
    //去除重复的关键词
    $returnlikekeywords = array_unique($returnlikekeywords);
    //取出前10个关键词
    $returnlikekeywords = array_slice($returnlikekeywords, 0, 10);

    exit(json_encode(array('likewords' => $returnlikekeywords)));
}
elseif($do == 'getpagesetting'){
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $tagval = $_GET['tag'] ? explode(',',trim($_GET['tag'])):[];
    $shape = $_GET['shape'] ? trim($_GET['shape']):'';
    $shapes = explode(',', $shape);
    $shapedataarr = array(
        1 => array(
            'start' => 'round((4 / 3) * 100)',
            'end' => '',
            'val' => 1,
            'lablename' => '4:3'
        ),
        2 => array(
            'start' => 'round((3 / 4) * 100)',
            'end' => '',
            'val' => 2,
            'lablename' => '3:4'
        ),
        3 => array(
            'start' => 'round((16 / 9) * 100)',
            'end' => '',
            'val' => 3,
            'lablename' => '16:9'
        ),
        4 => array(
            'start' => 'round((9 / 16) * 100)',
            'end' => '',
            'val' => 4,
            'lablename' => '9:16'
        ),
        5 => array(
            'start' => 250,
            'end' => '',
            'val' => 5,
            'lablename' => lang('elongated_horizontal_chart')
        ),
        6 => array(
            'start' => 0,
            'end' => 40,
            'val' => 6,
            'lablename' => lang('elongated_vertical_view')
        ),
        7 => array(
            'start' => 100,
            'end' => '',
            'val' => 7,
            'lablename' => lang('square_diagram')
        ),
        8 => array(
            'start' => 100,
            'end' => 250,
            'val' => 8,
            'lablename' => lang('horizontal_chart')
        ),
        9 => array(
            'start' => 40,
            'end' => 100,
            'val' => 9,
            'lablename' => lang('portrait_view')
        )

    );
    $shapelable = [];
    if(!empty($shapes)){
        $lableshape = [];
        foreach($shapedataarr as $v){
            $lableshape[$v['lablename']] = $v;
        }
        foreach($shapes as $s){
            $shapelable[] = $lableshape[$s]['val'];
        }
    }
    $tagdata=[];
    if(!empty($tagval)){
        foreach(DB::fetch_all("select t.tagname,t.tid,tr.cid from %t  t  left  join %t tr on t.tid = tr.tid and tr.appid=%s where t.tid in(%n) ",array('pichome_tag','pichome_tagrelation',$appid,$tagval)) as $tv){
            $tagdata[] = array('tagname'=>$tv['tagname'],'tid'=>intval($tv['tid']),'cid'=>$tv['cid']);
        }


    }
    $fids = trim($_GET['fids']);
    $fidarr = explode(',',$fids);
    $folderdata = [];
    foreach(DB::fetch_all("select fname,fid,pathkey,appid from %t where fid in(%n)",array('pichome_folder',$fidarr)) as $v){
        $folderdata[$v['fid']] = ['fname'=>$v['fname'],'pathkey'=>$v['pathkey'],'appid'=>$v['appid']];
        $folderdata[$v['fid']]['leaf'] = DB::result_first("select count(fid) from %t where pfid = %s",array('pichome_folder',$v['fid'])) ? false:true;
    }
    $vappdata = C::t('pichome_vapp')->fetch($appid);
    $pagesetting = unserialize($vappdata['pagesetting']);

    $vapp = [
        'appname'=>$vappdata['appname'],
        'type'=>$vappdata['type'],
    ];

    exit(json_encode(array('success' => true, 'vappdata'=>$vapp, 'data' => $pagesetting,'folderdata'=>$folderdata,'tagdata'=>$tagdata,'shape'=>$shapelable)));

}elseif ($do == 'getscreen') {//获取筛选项
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $bid = isset($_GET['bid']) ? intval($_GET['bid']):0;
    $tagval = $_GET['tag'] ? explode(',',trim($_GET['tag'])):[];
    $shape = $_GET['shape'] ? trim($_GET['shape']):'';
    $shapes = explode(',', $shape);
    $shapedataarr = array(
        1 => array(
            'start' => 'round((4 / 3) * 100)',
            'end' => '',
            'val' => 1,
            'lablename' => '4:3'
        ),
        2 => array(
            'start' => 'round((3 / 4) * 100)',
            'end' => '',
            'val' => 2,
            'lablename' => '3:4'
        ),
        3 => array(
            'start' => 'round((16 / 9) * 100)',
            'end' => '',
            'val' => 3,
            'lablename' => '16:9'
        ),
        4 => array(
            'start' => 'round((9 / 16) * 100)',
            'end' => '',
            'val' => 4,
            'lablename' => '9:16'
        ),
        5 => array(
            'start' => 250,
            'end' => '',
            'val' => 5,
            'lablename' => lang('elongated_horizontal_chart')
        ),
        6 => array(
            'start' => 0,
            'end' => 40,
            'val' => 6,
            'lablename' => lang('elongated_vertical_view')
        ),
        7 => array(
            'start' => 100,
            'end' => '',
            'val' => 7,
            'lablename' => lang('square_diagram')
        ),
        8 => array(
            'start' => 100,
            'end' => 250,
            'val' => 8,
            'lablename' => lang('horizontal_chart')
        ),
        9 => array(
            'start' => 40,
            'end' => 100,
            'val' => 9,
            'lablename' => lang('portrait_view')
        )

    );
    $shapelable = [];
    if($shape && !empty($shapes)){
        $shapes = explode(',', $shape);
        foreach($shapes as $s){
            $shapelable[] = $shapedataarr[$s];
        }
    }
    $tagdata=[];
    if(!empty($tagval)){
        if($appid){

            foreach(DB::fetch_all("select t.tagname,t.tid,tr.cid from %t  t  left  join %t tr on t.tid = tr.tid and tr.appid=%s where t.tid in(%n) ",array('pichome_tag','pichome_tagrelation',$appid,$tagval)) as $tv){
                $tagdata[] = array('tagname'=>$tv['tagname'],'tid'=>intval($tv['tid']),'cid'=>$tv['cid']);
            }
        }else{
            foreach(DB::fetch_all("select tagname,tid from %t where tid in(%n) ",array('pichome_tag',$tagval)) as $tv){
                $tagdata[] = array('tagname'=>$tv['tagname'],'tid'=>intval($tv['tid']));
            }
        }

    }
    $fids = trim($_GET['fids']);
    $fidarr = explode(',',$fids);
    $folderdata = [];
    foreach(DB::fetch_all("select fname,fid,pathkey,appid from %t where fid in(%n)",array('pichome_folder',$fidarr)) as $v){
        $folderdata[$v['fid']] = ['fname'=>$v['fname'],'pathkey'=>$v['pathkey'],'appid'=>$v['appid']];
        $folderdata[$v['fid']]['leaf'] = DB::result_first("select count(fid) from %t where pfid = %s",array('pichome_folder',$v['fid'])) ? false:true;
    }

    if ($appid) {
        $data = DB::fetch_first("select * from %t where appid=%s ", array('pichome_vapp', $appid));
        //如果没有设置库筛选项，使用系统默认筛选项作为库筛选项
        $data['filter'] = [
            [
                'key' => 'classify',
                'label' => lang('classify'),
                'checked' => 1,
            ],
            [
                'key' => 'tag',
                'label' => lang('label'),
                'checked' => 1,
            ],
            [
                'key' => 'color',
                'label' => lang('fs_color'),
                'checked' => 1
            ],
            [
                'key' => 'link',
                'label' => lang('fs_link'),
                'checked' => 1
            ],
            [
                'key' => 'desc',
                'label' => lang('note'),
                'checked' => 1
            ],
            [
                'key' => 'duration',
                'label' => lang('duration'),
                'checked' => 1
            ],
            [
                'key' => 'size',
                'label' => lang('size'),
                'checked' => 1
            ],
            [
                'key' => 'ext',
                'label' => lang('type'),
                'checked' => 1
            ],
            [
                'key' => 'shape',
                'label' => lang('shape'),
                'checked' => 1
            ],
            [
                'key' => 'grade',
                'label' => lang('grade'),
                'checked' => 1
            ],
            [
                'key' => 'btime',
                'label' => lang('add_time'),
                'checked' => 1
            ],
            [
                'key' => 'dateline',
                'label' => lang('modify_time'),
                'checked' => 1
            ],
            [
                'key' => 'mtime',
                'label' => lang('creation_time'),
                'checked' => 1
            ]

        ];
        $data['screen'] = unserialize($data['screen']);
        //获取tab部分以处理默认筛选和标注字段数据
        $tabstatus = 0;
        Hook::listen('checktab', $tabstatus);
        if ($tabstatus) {//获取有tab数据
            $tabgroupdata = [];
            Hook::listen('gettabgroupdata', $tabgroupdata);
            foreach ($tabgroupdata as $v) {
                if($v['available']){
                    $defaultfileds[] = ['flag' => 'tabgroup_' . $v['gid'], 'type' => 'tabgroup', 'name' => $v['name'], 'checked' => 0];
                    $data['filter'][] = ['key' => 'tabgroup_' . $v['gid'], 'type' => 'tabgroup', 'label' => $v['name'], 'checked' => 0];
                }
            }
        }
        //筛选器设置
        if ($data['screen']) {
            //去除重复的筛选项值
            $temp = [];
            foreach($data['screen'] as $k=>$v){
                if(!in_array($v['key'],$temp)) $temp[] = $v['key'];
                else unset($data['screen'][$k]);
            }
            $defaultfilterkeys = array_column($data['filter'], 'key');
            $screenfilterkeys = array_column($data['screen'], 'key');
            //获取当前库的所有标签分类
            $taggroupcid = C::t('pichome_taggroup')->fetch_cid_by_appid($appid);
            foreach ($data['screen'] as $k => $v) {
                if(isset($v['group']) && $v['group']){

                    if(!in_array($v['key'],$taggroupcid)){
                        unset($data['screen'][$k]);
                    }else{
                        $cgroupdata = ['cid'=>$v['key'],'catname'=>$v['label']];
                        Hook::listen('lang_parse',$cgroupdata,['getTaggroupLangData']);
                        $data['screen'][$k]['label'] = $cgroupdata['catname'];
                    }

                }
                if($v['type'] == 'tabgroup'){
                    if( !in_array($v['key'],$defaultfilterkeys)){
                        unset($data['screen'][$k]);
                    }else{
                        $index = array_search($v['key'], $defaultfilterkeys);
                        $data['screen'][$k]['label'] = $data['filter'][$index]['label'];
                    }
                }
            }
            /* foreach($data['filter'] as $k=>$v){
                 if($v['type'] == 'tabgroup' && !in_array($v['key'],$screenfilterkeys)){
                     $data['screen'][] = $v;
                 }
             }*/
        }
        $pichomefilterfileds = $data['screen'];

    }else{
        $pichomefilterfileds = [
            ['key'=>'tag','text'=>'标签','checked'=>1],
            ['key'=>'grade','text'=>'评分','checked'=>1],
            ['key'=>'ext','text'=>'类型','checked'=>1],
        ];
    }
    $tabgroupflags = [];
    foreach($_GET as $k=>$v){
        if(strpos($k,'tabgroup_') === 0){
            $tabgroupflags[$k] = $v;
        }
    }
    $tabdata = [];
    foreach($tabgroupflags as $k=>$v){
        $tids = explode(',',$v);
        Hook::listen('gettab',$tids);
        $tmptab = [];
        foreach($tids as $tab){
            $tmptab[] = ['tid'=>$tab['tid'],'tabname'=>$tab['tabname']];
        }
        $tabdata[$k] = $tmptab;
    }
    exit(json_encode(array('success' => true, 'data' => $pichomefilterfileds,'folderdata'=>$folderdata,'tagdata'=>$tagdata,'shape'=>$shapelable,'tabdata'=>$tabdata)));

} elseif ($do == 'getapptagcat') {//获取标签分类
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $catdata = C::t('pichome_taggroup')->fetch_by_appid($appid);
    exit(json_encode($catdata));


} elseif ($do == 'createshare') {//分享
    $path = $_GET['path'] ? dzzdecode($_GET['path'],'',0):'';
    if(!$path){
        exit(json_encode(array('error' => true)));
    }else{
        $patharr = explode('_',$path);
        $rid = $patharr[0];
    }
    if ($shareurl = C::t('pichome_share')->add_share($rid)) {
        exit(json_encode(array('success' => $shareurl)));
    } else {
        exit(json_encode(array('error' => true)));
    }

}  elseif($do == 'getsearchmenu_data'){//获取筛选项数据

    $gappid = isset($_GET['appid']) ? [trim($_GET['appid'])] : [];
    $skey = isset($_GET['skey']) ? trim($_GET['skey']):'';
    $cid = isset($_GET['cid']) ? trim($_GET['cid']):'';
    $cids = ($cid) ? explode(',',$cid):[];
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']):50;
    $limitsql = "limit 0," . $perpage;
    $skearr = explode(',',$skey);
    $datas = [];
    $vappids = [];
    //如果是搜索所有栏目
    if(isset($_GET['all']) && $_GET['all']){
        foreach(DB::fetch_all("select bdata from %t where btype = 0 and isshow = 1",array('pichome_banner')) as $v){
            $gappid[] = $v['bdata'];
        }
    }
    foreach (DB::fetch_all("select appid,path,view,type from %t where isdelete = 0", array('pichome_vapp')) as $v) {
        if ($v['type'] != 3 && !IO::checkfileexists($v['path'],1)) {
            continue;
        }
        if (C::t('pichome_vapp')->getpermbypermdata($v['view'],$v['appid'])) {
            $vappids[] = $v['appid'];
        }

    }
    $appid = [];

    if($gappid){
        $appid = array_intersect($vappids,$gappid);
    }
    foreach($skearr as $v){
        if($v == 'tag'){
            $datas['tag'] = [];
            if($appid){
                if(count($cids)){
                    if(in_array(-1,$cids)){
                        $nocatindex = array_search(-1,$cids);
                        unset($cids[$nocatindex]);
                        foreach(DB::fetch_all("select distinct t.tid,t.tagname,vt.hots from %t vt 
                            left join  %t tr on tr.tid=vt.tid 
                            left join %t t on t.tid=vt.tid where isnull(tr.cid) and vt.appid = %s ORDER BY vt.hots DESC $limitsql ",
                            array('pichome_vapp_tag','pichome_tagrelation','pichome_tag',$appid)) as $tdata){
                            $datas['tag'][-1]['tags'][] = ['tid'=>$tdata['tid'],'tagname'=>$tdata['tagname']];
                            $tmpnum += 1;
                        }
                        if($tmpnum >= $perpage)  $datas['tag'][-1]['next'] = true;
                        else $datas['tag'][-1]['next'] = false;
                    }
                    if(count($cids)){
                        $taggroupdata = DB::fetch_all("select * from %t where cid in(%n) and appid in(%n)",array('pichome_taggroup',$cids,$appid));
                        foreach($taggroupdata as $tg){
                            $datas['tag'][$tg['cid']] = ['catname'=>$tg['catname'],'cid'=>$tg['cid']];
                            $datas['tag'][$tg['cid']]['tags'] = [];
                            $tmpnum = 0;
                            foreach(DB::fetch_all("select distinct t.tid,t.tagname,vt.hots from %t vt 
                            left join  %t tr on tr.tid=vt.tid 
                            left join %t t on t.tid=vt.tid where tr.cid=%s and vt.appid in(%n) ORDER BY vt.hots DESC $limitsql ",
                                array('pichome_vapp_tag','pichome_tagrelation','pichome_tag',$tg['cid'],$appid)) as $tdata){
                                $datas['tag'][$tg['cid']]['tags'][] = ['tid'=>$tdata['tid'],'tagname'=>$tdata['tagname']];
                                $tmpnum += 1;
                            }
                            if($tmpnum >= $perpage)  $datas['tag'][$tg['cid']]['next'] = true;
                            else $datas['tag'][$tg['cid']]['next'] = false;
                        }
                    }

                }
                else{
                    $params = [ 'pichome_vapp_tag','pichome_tag',$appid];
                    $tmpnum = 0;
                    foreach(DB::fetch_all("select distinct t.tid,t.tagname from %t vt left join %t 
                        t on vt.tid=t.tid where vt.appid in(%n) order by vt.hots desc $limitsql",$params) as $tv){
                        $datas['tag']['data'][] = ['tid'=>$tv['tid'],'tagname'=>$tv['tagname']];
                        $tmpnum += 1;
                    }
                    if($tmpnum >= $perpage) $datas['tag']['next'] = true;
                    else $datas['tag']['next'] = false;
                    // print_r($datas);die;
                }


            }else{
                $params = [ 'pichome_tag'];
                $tmpnum = 0;
                foreach(DB::fetch_all("select * from %t where 1 order by hots desc $limitsql",$params) as $tv){
                    $datas['tag']['data'][] = ['tid'=>$tv['tid'],'tagname'=>$tv['tagname']];
                    $tmpnum += 1;
                }
                if($tmpnum >= $perpage) $datas['tag']['next'] = true;
                else $datas['tag']['next'] = false;
            }

        }elseif($v == 'ext'){
            $params = ['pichome_resources'];
            $wheresql = ' isdelete < 1 ';
            if($appid){
                $params[] = $appid;
                $wheresql .= " and appid in(%n) ";
            }
            $datas['ext'] =[];
            foreach(DB::fetch_all("select ext from %t  where $wheresql group by ext", $params) as $ev){
                $datas['ext'][] = $ev['ext'];
            }
        }
    }
    exit(json_encode($datas));

}
elseif($do == 'getMoretag'){//获取更多标签

    $gappid = [];
    $cid = isset($_GET['cid']) ? trim($_GET['cid']):'';
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']):50;
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $start = ($page - 1) * $perpage;
    $limitsql = "limit $start," . $perpage;
    $datas = [];
    $vappids = [];
    //如果是搜索所有栏目
    if(isset($_GET['all']) && $_GET['all']){
        foreach(DB::fetch_all("select bdata from %t where btype = 0 and isshow = 1",array('pichome_banner')) as $v){
            $gappid[] = $v['bdata'];
        }
    }
    foreach (DB::fetch_all("select appid,path,view,type from %t where isdelete = 0", array('pichome_vapp')) as $v) {
        if ($v['type'] != 3 && !IO::checkfileexists($v['path'],1)) {
            continue;
        }
        if (C::t('pichome_vapp')->getpermbypermdata($v['view'],$v['appid'])) {
            $vappids[] = $v['appid'];
        }

    }
    $appid = [];

    if($gappid){
        $appid = array_intersect($vappids,$gappid);
    }
    if(!empty($appid)){
        if($cid){
            if($cid == -1){
                $tmpnum = 0;
                foreach(DB::fetch_all("select distinct t.tid,t.tagname,vt.hots from %t vt 
                        left join  %t tr on tr.tid=vt.tid 
                        left join %t t on t.tid=vt.tid where isnull(tr.cid) and  vt.appid in(%n) ORDER BY vt.hots DESC $limitsql ",
                    array('pichome_vapp_tag','pichome_tagrelation','pichome_tag',$appid)) as $tdata){
                    $datas['tag'][] = ['tid'=>$tdata['tid'],'tagname'=>$tdata['tagname']];
                    $tmpnum += 1;
                }
                if($tmpnum >= $perpage)  $datas['next'] = true;
                else $datas['next'] = false;
            }else{
                $tmpnum = 0;
                foreach(DB::fetch_all("select distinct t.tid,t.tagname,vt.hots from %t vt 
                        left join  %t tr on tr.tid=vt.tid 
                        left join %t t on t.tid=vt.tid where tr.cid=%s and vt.appid in(%n) ORDER BY vt.hots DESC $limitsql ",
                    array('pichome_vapp_tag','pichome_tagrelation','pichome_tag',$cid,$appid)) as $tdata){
                    $datas['tag'][] = ['tid'=>$tdata['tid'],'tagname'=>$tdata['tagname']];
                    $tmpnum += 1;
                }
                if($tmpnum >= $perpage)  $datas['next'] = true;
                else $datas['next'] = false;
            }

        }

        else{
            $params = [ 'pichome_vapp_tag','pichome_tag',$appid];
            $tmpnum = 0;
            foreach(DB::fetch_all("select distinct t.tid,t.tagname from %t vt left join %t 
                        t on vt.tid=t.tid where vt.appid in(%n) order by vt.hots desc $limitsql",$params) as $tv){
                $datas['tag'][] = ['tid'=>$tv['tid'],'tagname'=>$tv['tagname']];
                $tmpnum += 1;
            }

            if($tmpnum >= $perpage) $datas['next'] = true;
            else $datas['next'] = false;
        }
    }else{
        $params = [ 'pichome_vapp_tag','pichome_tag'];
        $tmpnum = 0;
        foreach(DB::fetch_all("select distinct t.tid,t.tagname from %t vt left join %t 
                        t on vt.tid=t.tid where 1  order by vt.hots desc $limitsql",$params) as $tv){
            $datas['tag'][] = ['tid'=>$tv['tid'],'tagname'=>$tv['tagname']];
            $tmpnum += 1;
        }
        if($tmpnum >= $perpage) $datas['next'] = true;
        else $datas['next'] = false;
    }
    exit(json_encode($datas));
}

elseif($do == 'view'){//增加热度
    $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
    $fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
    $uid = getglobal('uid');
    if($fid){
        $resourcesdata = C::t('pichome_folder')->fetch($rid);
    }else{
        $resourcesdata = C::t('pichome_resources')->fetch($rid);
    }

    $viewsdata = [
        'rid'=>$rid,
        'filename'=>$resourcesdata['name'] ? $resourcesdata['name']:$resourcesdata['fname'],
        'appid'=>$resourcesdata['appid'],
        'uid'=>$uid ? $uid:0,
        'dateline'=>TIMESTAMP
    ];
    C::t('pichome_views')->insert($viewsdata);
    exit(json_encode(array('success'=>true)));
}