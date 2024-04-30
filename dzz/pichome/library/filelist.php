<?php

if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
global $_G;
if ($operation == 'filelist') {
    $sql = " from %t  r ";
    $selectsql = " DISTINCT r.rid ";
    $preparams = [];
    $isrecycle = isset($_GET['isrecycle']) ? intval($_GET['isrecycle']):0;
    $params = ['pichome_resources'];
    $preparams = [];
    $isrecycle = isset($_GET['isrecycle']) ? intval($_GET['isrecycle']):0;
    $wheresql = ' 1 ';
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
    //库条件
    if ($vappids) {
        $whererangesql[]= '  r.appid in(%n)';
        $para[] = $vappids;
    }else{
        $whererangesql[]= '  0 ';
    }

    if($whererangesql){
        $wheresql .= ' and ('.implode(' OR ',$whererangesql).') ';
    }
    if(!$isrecycle)$wheresql .= " and r.isdelete = 0 ";
    else $wheresql .= " and r.isdelete = 1 ";
    $havingsql = '';
    $havingparams = [];
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 30;

    $start = ($page - 1) * $perpage;
    $limitsql = "limit $start," . $perpage;

    if (!isset($_GET['order'])) {
        //获取用户默认排序方式
        $sortdata = C::t('user_setting')->fetch_by_skey('pichomesortfileds');
        $sortfilearr = ['btime' => 1, 'mtime' => 2, 'dateline' => 3, 'name' => 4, 'size' => 5, 'grade' => 6, 'duration' => 7, 'whsize' => 8];
        if ($sortdata) {
            $sortdatarr = unserialize($sortdata);
            $order = $sortdatarr['filed'] ? $sortfilearr[$sortdatarr['filed']] : 1;
            $asc = ($sortdatarr['scolorort']) ? $sortdatarr['sort'] : 'desc';
        } else {
            $order = 1;
            $asc = 'desc';
        }
    } else {
        $order = isset($_GET['order']) ? intval($_GET['order']) : 1;
        $asc = (isset($_GET['asc']) && trim($_GET['asc'])) ? trim($_GET['asc']) : 'desc';
    }

    $orderarr = [];

    $fids = isset($_GET['fids']) ? trim($_GET['fids']):'';
    $hassub = isset($_GET['hassub']) ? intval($_GET['hassub']):0;
    if ($fids) {
        if ($fids == 'not' || $fids == 'notclassify') {
            // $sql .= " LEFT JOIN %t fr on fr.rid = r.rid ";
            //$params[] = 'pichome_folderresources';
            $wheresql .= " and (ISNULL(r.fids) or r.fids = '') ";
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
    //密级条件
    if (isset($_GET['level'])) {
        $level = trim($_GET['level']);
        $levels = explode(',', $level);
        $wheresql .= " and r.level in(%n) ";
        $para[] = $levels;
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
            $sql .= "left join %t ra on r.rid = ra.rid";
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
        $sql .= " left join %t c on r.rid = c.rid";
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
                    $cvalwhere[] = " c.annotation like %s ";
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
            $sql .= "left join %t ra on r.rid = ra.rid";
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
                    $descvalwhere[] = "  ra.desc  like %s ";
                    $para[] = '%' . $dv . '%';
                }
                $wheresql .= " and (" . implode(" or ", $descvalwhere) . ") ";
            } else {
                $wheresql .= " and   ra.desc !='' ";
            }
        }
    }
    //链接条件
    if (isset($_GET['link'])) {
        if (!in_array('pichome_resources_attr', $params)) {
            $sql .= "left join %t ra on r.rid = ra.rid";
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
                    $para[] = round((4 / 3) * 100);
                    break;
                case 2://3:4
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = (3 / 4) * 100;
                    break;
                case 3://16:9
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = round((16 / 9) * 100);
                    break;
                case 4://9:16
                    $shapewherearr[] = '  round((r.width / r.height) * 100) = %d';
                    $para[] = round((9 / 16) * 100);
                    break;
                /*   case 10:
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
            $wheresql .= " and r.szie < %d ";
            $para[] = $size[1];
        }
    }
    //关键词条件
    $keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '';

    if ($keyword) {
        if (!in_array('pichome_resources_attr', $params)) {
            $sql .= "left join %t ra on r.rid = ra.rid";
            $params[] = 'pichome_resources_attr';
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
            }
            $keywords[] = "(" . implode(" OR ", $arr3) . ")";
        }
        if ($keywords) {
            $wheresql .= " and (" . implode(" AND ", $keywords) . ") ";
        }
    }
    //标签条件
    if (isset($_GET['tag'])) {
        $tagwherearr = [];
        $tagrelative = isset($_GET['tagrelative']) ? intval($_GET['tagrelative']) : 0;

        $tagrelative = isset($_GET['tagrelative']) ? intval($_GET['tagrelative']) : 0;
        $tag = trim($_GET['tag']);
        if ($tag == -1 || $tag=='nottag') {
            $wheresql .= " AND NOT EXISTS (SELECT 1 FROM %t wrt  WHERE wrt.rid = r.rid) ";
            $para[] = 'pichome_resourcestag';
        } else{
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


    $data = [];

    $rids = [];
    switch ($order) {
        case 1://添加日期
            $orderarr[] = ' r.btime ' . $asc;
            break;
        case 2://创建日期
            $orderarr[] = ' r.mtime ' . $asc;
            break;
        case 3://修改日志
            $orderarr[] = ' r.dateline ' . $asc;
            break;
        case 4://标题
            $orderarr[] = ' cast((r.name)  as unsigned) '.$asc.', CONVERT((r.name) USING gbk) ' . $asc;
            break;
        case 5://大小
            $orderarr[] = ' r.size ' . $asc;
            break;
        case 6://评分
            $orderarr[] = ' r.grade ' . $asc;
            break;
        case 7://时长
            if (!in_array('pichome_resources_attr', $params)) {
                $sql .= "left join %t ra on r.rid = ra.rid";
                $params[] = 'pichome_resources_attr';
            }
            $orderarr[] = ' ra.duration ' . $asc;
            break;
        case 8://尺寸
            $orderarr[] = ' r.width*r.height ' . $asc;
            break;
        default:
            $orderarr[] = ' r.dateline ' . $asc;
    }
    $orderarr[] = " r.rid " . $asc;
    $ordersql = implode(',', $orderarr);
    if (!empty($para)) $params = array_merge($params, $para);
    $time = microtime(true);
    //$counttotal = DB::result_first(" select  count(distinct r.rid) as num $sql  where $wheresql ", $params);

    if($fids || isset($_GET['color'])){
        $groupby = ' group by r.rid';
    }else{
        $groupby='';
    }
    if(!empty($preparams)) $params = array_merge($preparams,$params);

    if(!empty($havingparams)) $params = array_merge($params,$havingparams);
    if (!empty($orderparams)) $params = array_merge($params, $orderparams);
   /* echo " select  $selectsql $sql where $wheresql $groupby $havingsql order by $ordersql $limitsql";
     print_r($params);
     die;*/

    foreach (DB::fetch_all(" select  $selectsql $sql where  $wheresql $groupby $havingsql order by $ordersql $limitsql", $params) as $value) {
        $rids[] = $value['rid'];
    }



    $data = array();
    if (!empty($rids)) $data = C::t('pichome_resources')->getdatasbyrids($rids);

    $next = false;
    if (count($rids) >= $perpage) {
        $total = $start + $perpage * 2 - 1;
        $next = true;
    } else {
        $total = $start + count($rids);
    }
    $return = array(
        'appid' => $appid,
        'total' => $total,
        'next'=>$next,
        'data' => $data ? $data : array(),
        'param' => array(
            'order' => $order,
            'page' => $page,
            'perpage' => $perpage,
            'total' => $total,
            'asc' => $asc,
            'keyword' => $keyword
        )
    );
    updatesession();
    exit(json_encode(array('data' => $return)));
}
elseif($operation == 'folderlist'){
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 30;
    $hassub = isset($_GET['hassub']) ? intval($_GET['hassub']):'';
    $fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
    $start = ($page - 1) * $perpage;
    $limitsql = "limit $start," . $perpage;
    $params = ['pichome_folder',$appid];
    $wheresql = ' appid = %s ';
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    if($keyword){
        $wheresql .= " and fname like %s ";
        $params[] = '%'.$keyword.'%';
    }
    if($fid){
        if($hassub){
            $pathkey = DB::result_first("select pathkey from %t where appid = %s and fid = %s",array('pichome_folder',$appid,$fid));
            $wheresql .= " and pathkey like %s and fid !=  %s";
            $params[] = $pathkey.'%';
            $params[] = $fid;
        }else{
            $wheresql .= " and pfid = %s ";
            $params[] = $fid;
        }
    }else{
        if(!$hassub){
            $wheresql .= " and pfid = %s ";
            $params[] = $fid;
        }
    }
    $folderdata = [];
    $total = DB::result_first("select count(fid) from %t where $wheresql",$params);
    foreach(DB::fetch_all("select * from %t where $wheresql order by length(pathkey) asc,dateline asc $limitsql",$params) as $v){
        //$filernum = DB::result_first("select count(rid) from %t where fid = %s",array('pichome_folderresources',$v['fid']));
        $foldernum = DB::result_first("select count(fid) from %t where pfid = %s",array('pichome_folder',$v['fid']));
        if(!$v['cover']){
            $v['cover'] = DB::result_first("select r.rid from %t fr left join %t r on fr.rid=r.rid where fr.fid = %s order by r.dateline desc ",
                array('pichome_folderresources','pichome_resources',$v['fid']));
        }

        $v['icon'] = C::t('pichome_resources')->geticondata_by_rid($v['cover'],1);
        $v['foldernum'] = $foldernum ? $foldernum:0;
		$v['dateline'] = dgmdate(round($v['dateline'] / 1000), 'Y/m/d H:i');
        $folderdata[] = $v;
    }
    Hook::listen('folderdataFilter',$folderdata,1);
    $return = array(
        'appid' => $appid,
        'total' => $total,
        'data' => $folderdata ? $folderdata : array(),
        'param' => array(
            'page' => $page,
            'perpage' => $perpage,
            'total' => $total,
            'keyword' => $keyword
        )
    );
    exit(json_encode($return));
}
else{
    /*$ismobile = helper_browser::ismobile();
    $themeid = isset($_G['setting']['pichometheme']) ? intval($_G['setting']['pichometheme']):1;
    $themedata = $_G['setting']['pichomethemedata'][$themeid];
    */
    $maxChunkSize = intval($_G['setting']['maxChunkSize']);
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    if($appid){
        $appdata  = C::t('pichome_vapp')->fetch($appid);
    }
    
    // if ($ismobile) {
        // include template($themedata['themefolder'].'/mobile/page/filelist');
    // } else {
        include template('librarylist/pc/page/index');
    // }
}
