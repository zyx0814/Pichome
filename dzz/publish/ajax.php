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

$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
if(!$pid){
    exit('参数错误');
}
$pdata = C::t('publish_list')->fetch($pid);
$pdata['perm']= C::t('publish_list')->getpermById($pid);

if(!perm::check('read2', $pdata['perm'])){
    exit(json_encode(array('success'=>false,'msg'=>lang('no_perm'))));
}



if($do == 'getfilename'){
    $rids = isset($_GET['rids']) ? trim($_GET['rids']) : '';
    $rids = explode(',', $rids);
    //获取发布标题
    $resources = C::t('pichome_resource')->fetch($rids[0]);
    $title = $resources['name'];
    if(count($rids) > 1){
        $title .= '等';
    }
    exit(json_encode(array('title'=>$title)));
}
elseif($do == 'getvappdata'){//获取库基本数据
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $data = C::t('pichome_vapp')->fetchByAppid($appid);
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
    if (defined('PICHOME_LIENCE')) {
        $data['filter'][] = [
            'key' => 'level',
            'label' => lang('level'),
            'checked' => 1,
            'type'=>'grade'
        ];
    }
    //默认标注设置
    $defaultfileds = [
        [
            'flag' => 'tag',
            'type' => 'multiselect',
            'name' => lang('tag'),
            'enable' => 1,
            'checked' => 1,
            'system'=>1
        ],
        [
            'flag' => 'desc',
            'type' => 'input',
            'name' => lang('describe'),
            'enable' => 1,
            'checked' => 1,
            'system'=>1
        ],
        [
            'flag' => 'link',
            'type' => 'input',
            'name' => lang('link'),
            'enable' => 1,
            'checked' => 1
        ],

        [
            'flag' => 'grade',
            'type' => 'grade',
            'name' => lang('grade'),
            'enable' => 1,
            'checked' => 1,
            'system'=>1
        ],
        [
            'flag' => 'fid',
            'type' => 'multiselect',
            'name' => lang('classify'),
            'enable' => 1,
            'checked' => 1,
            'system'=>1
        ]
    ];


    if (defined('PICHOME_LIENCE')) {
        $defaultfileds[] = [
            'flag' => 'level',
            'type' => 'grade',
            'name' => lang('level'),
            'enable' => 0,
            'checked' => 1
        ];
    }
    if ($data['type'] == 1 || $data['type'] == 3) {
        $defaultfileds[] = [
            'flag' => 'preview',
            'type' => 'multiupload',
            'name' => lang('more_picture_preview'),
            'checked' => 0,
            'enable' => 1
        ];
    }
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
    if ($data['type'] == 1 || $data['type'] == 3) {
        $defaultfileds[] = [
            'flag' => 'preview',
            'type' => 'multiupload',
            'name' => lang('more_picture_preview'),
            'checked' => 0,
            'enable' => 1,
            'system'=>1
        ];

        $filedType = [
            'input'=>lang('input'),
            'time'=>lang('timefiled'),
            'fulltext'=>lang('fulltext'),
            'textarea'=>lang('textarea'),
            'timerange'=>lang('timerange'),
            'select'=>lang('select'),
            'multiselect'=>lang('multiselect'),
            'link'=>lang('link'),
            'bool'=>lang('bool'),
            'tabgroup'=>lang('album'),
            'inputselect'=>lang('inputselect'),
            'inputmultiselect'=>lang('inputmultiselect'),
        ];

    }else{
        $filedType = [];
    }
    //获取默认字段数据的flag
    $dfkeys =array_unique(array_column($defaultfileds, 'flag'));
    //获取默认筛选字段flag
    $defaultfilterkeys = array_column($data['filter'], 'key');
    //获取自定义字段
    $cusfileds = C::t('form_setting')->fetch_flags_by_appid($appid);
    foreach($cusfileds as $filed){
        if(!in_array($filed['flag'],$dfkeys) && !in_array($filed['type'],['fulltext','text'])){
            $defaultfileds[] = [
                'flag' => $filed['flag'],
                'type' => $filed['type'],
                'name' => $filed['labelname'],
                'enable' => 1,
                'checked' => 0,
                'system'=>0
            ];
        }
        if(!in_array($filed['flag'],$defaultfilterkeys)) {
            $data['filter'][] = ['key' => $filed['flag'], 'type' => $filed['type'], 'label' => $filed['labelname'], 'checked' => 0];
        }

    }
    //获取默认字段数据的flag
    $dfkeys =array_unique(array_column($defaultfileds, 'flag'));
    //获取默认筛选字段flag
    $defaultfilterkeys = array_column($data['filter'], 'key');

    //标注设置数据，如果没有设置值使用默认值
    $data['fileds'] = $data['fileds'] ? unserialize($data['fileds']) : $defaultfileds;
    //筛选数据，如果没有设置值使用默认值
    $data['screen'] = $data['screen'] ? unserialize($data['screen']) : $data['filter'];


    //print_r($data['fileds']);
    if ($data['fileds']) {
        //去除重复的字段值
        $temp = [];
        foreach($data['fileds'] as $k=>$v){
            if(!in_array($v['flag'],$temp)) $temp[] = $v['flag'];
            else unset($data['fileds'][$k]);

        }
        //获取当前设置标注字段的flag
        $ffkeys = array_unique(array_column($data['fileds'], 'flag'));

        //处理默认设置数据
        foreach ($ffkeys as $k => $v) {
            if(!in_array($v, $dfkeys)){
                unset($data['fileds'][$k]);
            }else{
                $index = array_search($v, $dfkeys);
                $data['fileds'][$k] = [
                    'flag' => $v,
                    'type' => isset($defaultfileds[$index]['type']) ? $defaultfileds[$index]['type'] : '',
                    'name' =>  $defaultfileds[$index]['name'],
                    'checked' => $data['fileds'][$k]['checked'],
                    'enable'=>$data['fileds'][$k]['enable'],
                    'options'=>$data['fileds'][$k]['options'],
                    'system'=>$defaultfileds[$index]['system'] ? 1 : 0
                ];
            }
        }
        foreach ($dfkeys as $k => $v) {
            if (!in_array($v, $ffkeys)) {
                $data['fileds'][] = $defaultfileds[$k];
            }
        }
    }
    $data['fileds'] = array_values($data['fileds']);
    //标注设置结束
    //筛选器设置
    if ($data['screen']) {
        //去除重复的筛选项值
        $temp = [];
        foreach($data['screen'] as $k=>$v){
            if(!in_array($v['key'],$temp)) $temp[] = $v['key'];
            else unset($data['screen'][$k]);
        }

        $screenfilterkeys = array_column($data['screen'], 'key');
        //获取当前库的所有标签分类
        $taggroupcid = C::t('pichome_taggroup')->fetch_cid_by_appid($appid);
        foreach ($data['screen'] as $k => $v) {
            if(isset($v['group']) && $v['group']){
                if(!in_array($v['key'],$taggroupcid)){
                    unset($data['screen'][$k]);
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
    }
   exit(json_encode(['data'=>$data]));

}

elseif($do == 'getsearchfoldernum'){//获取左侧目录数字
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $pathkeys = isset($_GET['pathkeys']) ? trim($_GET['pathkeys']):'';
    $pathkeyarr = explode(',',$pathkeys);
    $data = C::t('pichome_folder')->getFolderNumByPathkey($appid,$pathkeyarr);
    exit(json_encode(array( 'data' => $data)));
}
elseif ($do == 'getalonepagetagdata') {//获取标签位文件列表数据
    $tdid = isset($_GET['tdid']) ? intval($_GET['tdid']) : 0;
    $tagdata = C::t('pichome_templatetagdata')->fetch($tdid);

    $reurn = [];
    if ($tagdata) {
        //获取类型
        $tag = C::t('pichome_templatetag')->fetch($tagdata['tid']);
        $tagtype = $tag['tagtype'];

        if ($tagtype == 'file_rec' || $tagtype == 'db_ids') {//如果是文件推荐
            $tagval = unserialize($tagdata['tdata']);
            $tagval = $tagval[0];
            $limitnum = $tagval['number'];
            if(!$_G['config']['filterFileByTabPerm']){
                $cachename = 'templatetagdata_'.$tdid;
            }else{
                $uid = $_G['uid'] ? $_G['uid'] : 'guest';
                $cachename = 'templatetagdata_'.$tdid.'_'.$uid;
            }
            $processname = 'templatetagdatalock_'.$tdid;
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 200;
            if($tagtype == 'db_ids' && $page == 1 && $limitnum && $perpage > $limitnum) $perpage = $limitnum;
            if($tagtype == 'db_ids' && $page > 1){
                $count =($page - 1) * $perpage;
                if($limitnum && $count > $limitnum) $perpage = 0;
                elseif( $limitnum && (($count+$perpage) > $limitnum)){
                    $perpage = (($limitnum - $count) < 0) ? 0:intval($limitnum - $count);
                }
            }

            $start = ($page - 1) * $perpage;
            $limitsql = "limit $start," . $perpage;

            if($tagtype == 'db_ids' && $page == 1 && $tagdata['cachetime'] &&  $cachedata = C::t('cache')->fetch_cachedata_by_cachename($cachename,$tagdata['cachetime'])){
                $rids = $cachedata;
            }
            elseif($tagtype != 'db_ids' && $tagdata['cachetime'] &&  $cachedata = C::t('cache')->fetch_cachedata_by_cachename($cachename,$tagdata['cachetime'])){
                $rids = $cachedata;
            }
            else{

                $sql = " from %t r  ";
                //$selectsql = "  distinct r.rid,r.name ";
                $selectsql = "   r.rid,r.name ";
                $wheresql = " r.appid = %s and r.isdelete = 0 ";
                $params = ['pichome_resources'];
                $para[] = trim($tagval['id']);
                //}
                $countsql = " count(distinct(r.rid))";

                if($tagval['type'] == 2){//标签
                    $tagarr = explode(',',$tagval['value']);
                    $tids = [];
                    foreach(DB::fetch_all("select tid from %t where tagname in(%n)",array('pichome_tag',$tagarr)) as $tid){
                        $tids[] = $tid['tid'];
                    }
                    $sql .= "left join %t rt on rt.rid=r.rid ";
                    $params[] = 'pichome_resourcestag';
                    $wheresql .= ' and rt.tid in(%n) ';
                    $para[] = $tids;
                } elseif($tagval['type'] == 3){//评分
                    switch ($tagval['gradetype']) {
                        case 0:
                            $wheresql .= ' and r.grade = %d ';
                            $para[] = intval($tagval['value']);
                            break;
                        case 1:
                            $wheresql .= ' and r.grade != %d ';
                            $para[] = intval($tagval['value']);
                            break;
                        case 2:
                            $wheresql .= ' and r.grade <= %d ';
                            $para[] = intval($tagval['value']);
                            break;
                        case 3:
                            $wheresql .= ' and r.grade >= %d ';
                            $para[] = intval($tagval['value']);
                            break;
                    }
                }
                elseif($tagval['type'] == 4){//分类
                    $fidarr = $tagval['classify']['checked'];
                    /*$wheresql .= ' and r.fids in(%n) ';
                    $para[] = $fidarr;*/
                    $sql .= "left join %t fr on fr.rid=r.rid ";
                    $params[] = 'pichome_folderresources';
                    $wheresql .= ' and fr.fid in(%n) ';
                    $para[] = $fidarr;
                }
                $clang = '';
                Hook::listen('lang_parse',$clang,['checklang']);
                if($clang) $wheresql .= " and (r.lang = '".$_G['language']."' or r.lang = 'all' ) ";
                if ($tagval['sort'] == 1) {//最新推荐
                    $ordersql = '  r.dateline desc ';
                }
                elseif ($tagval['sort'] == 2) {//热门排序
                    $sql .= ' left join %t v on r.rid=v.idval and v.idtype = 0 ';
                    $selectsql .= " ,v.nums as num  ";
                    $params[] = 'views';
                    $ordersql = '  num desc ,r.dateline desc ';
                }
                elseif ($tagval['sort'] == 3) {//名字排序
                    //$ordersql = ' r.dateline desc ';
                    $ordersql = '   cast((r.name) as unsigned) asc, CONVERT((r.name) USING gbk) asc';

                }
                elseif ($tagval['type'] == 4) {//最新排序

                    $ordersql = ' r.dateline desc ';
                }else{
                    $ordersql = ' r.dateline desc ';
                }
                $hookdata = ['params'=>$params,'para'=>$para,'wheresql'=>$wheresql,'sql'=>$sql];
                Hook::listen('fileFilter',$hookdata);
                $params = $hookdata['params'];
                $para = $hookdata['para'];
                $wheresql = $hookdata['wheresql'];
                $sql = $hookdata['sql'];
                if ($para) $params = array_merge($params, $para);
                $count = DB::result_first("select $countsql $sql where  $wheresql  ", $params);
                $rids = [];

                foreach (DB::fetch_all(" select  $selectsql $sql where  $wheresql  group by r.rid  order by $ordersql  $limitsql", $params) as $value) {
                    $rids[] = $value['rid'];
                }
                if ((($tagtype == 'db_ids' && $page == 1) || $tagtype == 'file_rec') && $tagdata['cachetime'] && !empty($rids)){
                    $cachearr = [
                        'cachekey'=>$cachename,
                        'cachevalue'=>serialize($rids),
                        'dateline'=>TIMESTAMP
                    ];
                    C::t('cache')->insert_cachedata_by_cachename($cachearr,$tagdata['cachetime'],1);
                }
            }
            if (!empty($rids)) {
                $data = [];
                $rdata = C::t('pichome_resources')->getdatasbyrids($rids,1,$pdata['perm']);
                foreach($rdata as $key=>$value){
                    $value['dpath'] = Pencode(array('path'=>$value['rid'],'perm'=>$pdata['perm'],'ishare'=>0,'isadmin'=>0),3600);
                    $data[$key] = $value;
                }
            }

            $next = true;
            //获取已查询总数
            if (count($rids) >= $perpage) {
                $total = $start + $perpage * 2 - 1;
                if (!$limitnum || $total >= $limitnum) {
                    $next = true;
                }else{
                    $next = false;
                }
            } else {
                $total = $start + count($rids);
                $next = false;
            }

            $return = array(
                'tdid' => $tdid,
                'next' => $next,
                'count' => $count,
                'data' => $data ? $data : array(),
                'param' => array(
                    'page' => $page,
                    'perpage' => $perpage,
                )
            );

        }
        elseif($tagtype == 'tab_rec'){//如果是专辑推荐
            $tagval = unserialize($tagdata['tdata']);
            $limitnum = $tagval['number'];
            $cachename = 'templatetagdata_'.$tdid;
            $processname = 'templatetagdatalock_'.$tdid;
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 100;
            if($limitnum && $perpage > $limitnum) $perpage = $limitnum;
            $start = ($page - 1) * $perpage;
            $limitsql = "limit $start," . $perpage;
            $tagval = $tagval[0];
            if($tagdata['cachetime'] &&  $cachedata = C::t('cache')->fetch_cachedata_by_cachename($cachename,$tagdata['cachetime'])){
                $tids =$cachedata;

            }
            else{

                // print_r($tagval);die;
                $sql = " from %t t  ";
                $selectsql = "   t.tid ";
                $wheresql = " t.gid = %d and t.isdelete < 1 ";
                $params = ['tab'];
                $para[] = intval($tagval['id']);
                //}
                $countsql = " count(distinct(t.tid))";
                if(isset($tagval['classify']['checked'])){//如果分类有值
                    $cidarr = $tagval['classify']['checked'];
                    $sql .= ' LEFT JOIN %t tabcatrelation ON tabcatrelation.tid = t.tid ';
                    $params[] = 'tab_cat_relation';
                    $wheresql .= ' and tabcatrelation.cid in(%n) ';
                    $para[]= $cidarr;
                }

                if ($tagval['sort'] == 1) {//最新推荐
                    $ordersql = '  t.dateline desc ';
                }
                elseif ($tagval['sort'] == 2) {//热门排序
                    $sql .= ' left join %t v on t.tid=v.idval and v.idtype = 2 ';
                    $selectsql .= " ,v.nums as num  ";
                    $params[] = 'views';
                    $ordersql = '  num desc ,t.dateline desc ';
                }


                if ($para) $params = array_merge($params, $para);
                $count = DB::result_first("select $countsql $sql where  $wheresql  ", $params);
                $tiddata = [];
                /* echo " select  $selectsql $sql where  $wheresql  group by t.tid order by $ordersql  $limitsql";
                 print_r($params);die;*/
                foreach (DB::fetch_all(" select  $selectsql $sql where  $wheresql  group by t.tid order by $ordersql  $limitsql", $params) as $value) {
                    $tids[] = $value['tid'];
                }

                if ($tids && $tagdata['cachetime'] && !empty($tids)){
                    $cachearr = [
                        'cachekey'=>$cachename,
                        'cachevalue'=>serialize($tids),
                        'dateline'=>TIMESTAMP
                    ];
                    C::t('cache')->insert_cachedata_by_cachename($cachearr,$tagdata['cachetime'],1);
                }
            }
            if (!empty($tids)) {
                $data = C::t('#tab#tab')->fetch_by_tids($tids,1);
            }
            $next = true;
            //获取已查询总数
            if (count($tids) >= $perpage) {
                $total = $start + $perpage * 2 - 1;
                if (!$limitnum || $total <= $limitnum) {
                    $next = true;
                }else{
                    $next = false;
                }
            } else {
                $total = $start + count($tids);
                $next = false;
            }
            $gid = intval($tagval['id']);
            $gdata =C::t('#tab#tab_group')->fetch_by_gid($gid);
            $return = array(
                'tdid' => $tdid,
                'next' => $next,
                'count' => $count,
                'data' => $data ? $data : array(),
                'gdata'=>$gdata,
                'param' => array(
                    'page' => $page,
                    'perpage' => $perpage,
                )
            );
        }

    }
    exit(json_encode(['success' => true, 'data' => $return]));
}
elseif ($do == 'getalonpagedata') {//获取单页数据
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $pagedata = C::t('pichome_templatepage')->fetch_pagedata_by_id($id);
    exit(json_encode(['success' => true, 'data' => $pagedata]));
}
elseif($do == 'getappdatalist'){//获取库文件列表
    $ulevel = $_G['pichomelevel'] ? $_G['pichomelevel']:0;

    $sql = " from %t  r ";
    $selectsql = " r.rid ";
    $preparams = [];
    $para = [];
    $params = ['pichome_resources'];
    $havingsql = '';
    $havingparams = [];
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 30;
    $start = ($page - 1) * $perpage;
    $limitsql = "limit $start," . $perpage;
    $isrecycle = isset($_GET['isrecycle']) ? intval($_GET['isrecycle']):0;
    $wheresql = ' 1 ';


    $appid = isset($_GET['appid']) ? [trim($_GET['appid'])] : [-1];
 //处理智能数据相关的条件
    //范围条件
    $vappids=array();

    if($pdata['ptype']==5) {
        $stdata = C::t('#intelligent#intelligent')->fetch_by_tid($pdata['pval']);

        if ($stdata['searchRange']) {
            $appids = array_keys($stdata['searchRange']['appids']);
            foreach ($stdata['searchRange']['folders'] as $pathkey => $v) {
                $appids[] = $v['appid'];
            }
            $vappids = $appids;
        }else{
            foreach (DB::fetch_all("select appid,path,view,type from %t where isdelete = 0", array('pichome_vapp')) as $v) {
                if ($v['type'] != 3 && !IO::checkfileexists($v['path'],1)) {
                    continue;
                }
                if (C::t('pichome_vapp')->getpermbypermdata($v['view'],$v['appid'])) {
                    $vappids[] = $v['appid'];
                }
            }
        }

        if ($vappids) {
            $wheresql .= ' and r.appid in(%n)';
            $para[] = $vappids;

            //处理范围分类

            if (!empty($stdata['searchRange']['folders'])) {
                $pathkeys = array_keys($stdata['searchRange']['folders']);
            } else {
                $pathkeys = array();
            }
            if ($pathkeys) {
                $sql .= " LEFT JOIN %t frx on frx.rid = r.rid ";
                $params[] = 'pichome_folderresources';
                $warr = array();
                foreach ($pathkeys as $pathkey) {
                    $warr[] = " frx.pathkey like %s ";
                    $para[] = str_replace('_', '\_', $pathkey) . '%';
                }
                if ($warr) {
                    $wheresql .= ' and (' . implode(' or ', $warr) . ')';
                }
            }

            //处理名称包含
            if ($stdata['extra'] && $stdata['extra']['searchName']) {
                $arr = explode(' ', $stdata['extra']['searchName']);
                $osqlarr = array();

                foreach ($arr as $v) {
                    $nsqlarr = array();
                    if (strpos($v, '+') !== false) {
                        $andarr = explode('+', $v);
                        foreach ($andarr as $v1) {
                            if (empty($v1)) continue;
                            $nsqlarr[] = " r.name like %s ";
                            $para[] = '%' . str_replace('_', '\_', $v1) . '%';
                        }
                        if ($andarr) {
                            $osqlarr[] = '(' . implode(' and ', $nsqlarr) . ')';
                        }
                    } else {
                        $osqlarr[] = " r.name like %s ";
                        $para[] = '%' . str_replace('_', '\_', $v) . '%';
                    }
                }
                if ($osqlarr) {
                    $wheresql .= ' and (' . implode(' or ', $osqlarr) . ')';
                }
            }
            //处理评分
            if ($stdata['extra'] && $stdata['extra']['grade'] && !in_array('0', $stdata['extra']['grade'])) {
                $wheresql .= ' and r.grade in(%n) ';
                $para[] = $stdata['extra']['grade'];
            }

            //处理标签范围

            if ($stdata['tags']) {
                $tagtids = array();
                $tagnames = explode(',', $stdata['tags']);

                foreach (DB::fetch_all("select tid from %t where tagname IN(%n)", array('pichome_tag', $tagnames)) as $v2) {
                    $tagtids[$v2['tid']] = $v2['tid'];
                }
                foreach (DB::fetch_all("select langflag from %t where state>0", array('language')) as $value) {
                    $table = 'lang_' . strtolower(str_replace('-', '_', $value['langflag']));
                    foreach (DB::fetch_all("select * from %t where idtype='8' and filed='tagname' and svalue IN(%n)", array($table, $tagnames)) as $v2) {
                        $tagtids[$v2['idvalue']] = $v2['idvalue'];
                    }
                }
                if ($tagtids) {
                    $sql .= "left join %t rts on r.rid = rts.rid ";
                    $params[] = 'pichome_resourcestag';
                    $wheresql .= ' and rts.tid in(%n) ';
                    $para[] = $tagtids;
                }
            }
        }else{
            $wheresql .= ' and 0 ';
        }

    }else{
        $vappids=$appid;
        if($vappids){
            $wheresql .= ' and r.appid in(%n)';
            $para[] = $appid;
        }else{
            $wheresql .= ' and 0 ';
        }
    }
    //处理模板后缀限制
    $templateData=C::t('publish_template')->fetch($pdata['tid']);
    if($templateData['exts']){
        $exts = explode(',',$templateData['exts']);
        if($exts){
            $wheresql .= ' and r.ext in(%n)';
            $para[] = $exts;
        }
    }

    //以图搜图相关
    $imageRids = [];
    if(isset($_GET['aid'])){
        $aid = intval($_GET['aid']);
        $searchparams= array(
            'appids'=>$vappids,
            'aid'=>$aid,
            'keyword'=>$_GET['keyword'],
            'limit'=>$perpage,
            'offset'=>$start
        );

        Hook::listen('search_condition_filter',$imageRids,$searchparams);
        if(!empty($imageRids['rids'])){
            $wheresql .= ' and r.rid in(%n)';
            $para[] = $imageRids['rids'];

            $limitsql='';
        }

    }

    if(!$isrecycle) $wheresql .= " and r.isdelete = 0 and   r.level <= $ulevel ";
    else $wheresql .= " and r.isdelete = 1 and level <= $ulevel ";
    $sortfilearr = ['btime' => 1, 'mtime' => 2, 'dateline' => 3, 'name' => 4, 'size' => 5,'filesize' => 5, 'grade' => 6, 'duration' => 7, 'whsize' => 8];
    if (!isset($_GET['order'])) {
        //获取用户默认排序方式
        $sortdata = C::t('user_setting')->fetch_by_skey('pichomesortfileds');

        if ($sortdata) {
            $sortdatarr = unserialize($sortdata);
            $order = $sortdatarr['filed'] ? $sortfilearr[$sortdatarr['filed']] : 1;
            $asc = ($sortdatarr['scolorort']) ? $sortdatarr['sort'] : 'desc';
        } else {
            $order = 1;
            $asc = 'desc';
        }
    } else {
        if(isset($sortfilearr[$_GET['order']])){
            $order=$sortfilearr[$_GET['order']];
        }else{
            $order = isset($_GET['order']) ? intval($_GET['order']) : 1;
        }
        $asc = (isset($_GET['asc']) && trim($_GET['asc'])) ? trim($_GET['asc']) : 'desc';
    }

    $orderarr = [];
    $orderparams = [];


    $fids = isset($_GET['fids']) ? trim($_GET['fids']) : '';
    $hassub = isset($_GET['hassub']) ? intval($_GET['hassub']) : 0;


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
    //关键词条件
    $keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '';
    $clang = '';
    Hook::listen('lang_parse',$clang,['checklang']);
    if($clang) $wheresql .= " and (r.lang = '".$_G['language']."' or r.lang = 'all' ) ";

    if ($keyword) {
        if (!in_array('pichome_resources_attr', $params)) {
            $sql .= "left join %t ra on r.rid = ra.rid";
            $params[] = 'pichome_resources_attr';
        }
        if($clang && !in_array('lang_search',$params)){
            $sql .= " LEFT JOIN %t lang ON lang.idvalue=r.rid and lang.lang = %s ";
            $params[] = 'lang_search';
            $params[] = $clang;
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
                if($clang){
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
                if (!in_array('pichome_resourcestab', $params)) {
                    $sql .= "left join %t rtab on r.rid = rtab.rid ";
                    $params[] = 'pichome_resourcestab';
                }
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
        case 3://修改日期
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
        case 9://热度排序
            $sql .= ' left join %t v on r.rid=v.idval and v.idtype = 0 ';
            $selectsql .= " ,v.nums as num  ";
            $params[] = 'views';
            $orderarr[] = '  num desc  ';
        default:
            $orderarr[] = ' r.dateline ' . $asc;
    }
    $orderarr[] = " r.rid " . $asc;
    $ordersql = implode(',', $orderarr);

    $hookdata = ['params'=>$params,'para'=>$para,'wheresql'=>$wheresql,'sql'=>$sql];
    //Hook::listen('fileFilter',$hookdata);
    $params = $hookdata['params'];
    $para = $hookdata['para'];
    $wheresql = $hookdata['wheresql'];
    $sql = $hookdata['sql'];
    if (!empty($para)) $params = array_merge($params, $para);

    $counttotal = DB::result_first(" select  count(distinct r.rid) as filenum $sql  where $wheresql ", $params);
    if($fids || isset($_GET['color'])  || $order = 9){
        $groupby = ' group by r.rid';
    }else{
        $groupby='';
    }
    if(!empty($preparams)) $params = array_merge($preparams,$params);

    if(!empty($havingparams)) $params = array_merge($params,$havingparams);
    if (!empty($orderparams)) $params = array_merge($params, $orderparams);
    foreach (DB::fetch_all(" select  $selectsql $sql where  $wheresql $groupby $havingsql order by $ordersql $limitsql", $params) as $value) {
        $rids[] = $value['rid'];
    }


    $data = array();
    if (!empty($rids)) {
        $rdata = C::t('pichome_resources')->getdatasbyrids($rids,1,$pdata['perm']);
        if(!empty($imageRids)){
            $rdatas = [];
            foreach($rdata as $key=>$value){
                if(isset($imageRids['distances'][$value['rid']]) && is_array($imageRids['distances'][$value['rid']])) {
                    $value['distances']=$imageRids['distances'][$value['rid']];
                }
                $rdatas[$value['rid']] = $value;
            }

            foreach($imageRids['rids'] as $value){
                if(isset($rdatas[$value])){
                  //  $rdatas[$value]['dpath'] = Pencode(array('path'=>$value,'perm'=>$pdata['perm'],'ishare'=>0,'isadmin'=>0),3600);
                    $data[] = $rdatas[$value];
                }
            }
            //分页
            if(count($data)>$start * ($page - 1)) {
                $data = array_slice($data, $start * ($page - 1), $perpage);
            }

        }else{
            foreach($rdata as $key=>$value){
               // $value['dpath'] = Pencode(array('path'=>$value['rid'],'perm'=>$pdata['perm'],'ishare'=>0,'isadmin'=>0),3600);
                $data[] = $value;
            }
        }
    }

    if (count($rids) >= $perpage) {
        $next = true;
    } else {
        $next = false;
    }
    $return = array(
        'appid' => $appid,
        'next' => $next,
        'data' => $data ? $data : array(),
        'param' => array(
            'order' => $order,
            'page' => $page,
            'perpage' => $perpage,
            'total' => $counttotal,
            'asc' => $asc,
            'keyword' => $keyword
        )
    );
    updatesession();
    if($_GET['keyword']){//增加搜索热度
        $insertdata = [
            'idtype'=>$pdata['ptype']==5?4:0,
            'idval'=>$pdata['val'],
            'keyword'=>trim($_GET['keyword']),
        ];
        C::t('keyword_hots')->insert_data($insertdata);
    }
    exit(json_encode(array('data' => $return)));

}
elseif ($do == 'getscreen') {//获取筛选项
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
    if($pdata['ptype']==5) { //智能数据时
        $data['screen']=unserialize($pdata['filter']);
        $pichomefilterfileds = $data['screen'];
    }elseif ($pdata['ptype']==3) {
        $appid=$pdata['pval'];
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

        $data['screen']=unserialize($pdata['filter']);
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

}
elseif($do == 'gettagcat'){//获取标签分类
    $appid = isset($_GET['appid']) ?  getstr($_GET['appid']):'';
    //获取标签分类
    $tagcat = C::t('pichome_taggroup')->fetch_all_by_appid_pcid($appid);
    exit(json_encode(['success'=>true,'data'=>$tagcat]));
}
elseif($do == 'getfiles'){//获取多文件信息
    $rids = isset($_GET['rids']) ?explode(',',$_GET['rids']) : [];

    $wheresql="1";
    $params=array('pichome_resources');
    //处理模板后缀限制
    $templateData=C::t('publish_template')->fetch($pdata['tid']);
    if($templateData['exts']) {
        $exts = explode(',', $templateData['exts']);
        if ($exts) {
            $wheresql .= ' and ext IN(%n)';
            $params[] = $exts;
        }
        $rrids = array();
        foreach (DB::fetch_all(" select  rid from %t where  $wheresql ", $params) as $value) {
            $rrids[$value['rid']] = $value['rid'];
        }
        //按原先顺序返回
        $nrids=[];
        foreach($rids as $rid){
            if(isset($rrids[$rid])){
                $nrids[]=$rrids[$rid];
            }
        }
        if($nrids) {
            $data = C::t('pichome_resources')->getdatasbyrids($nrids, 1, $pdata['perm']);
        }else{
            $data=[];
        }
    }else{
        $data = C::t('pichome_resources')->getdatasbyrids($rids,1,$pdata['perm']);
    }
    exit(json_encode(['success' => true, 'data' => $data]));
}
elseif($do == 'getDefaultFiles'){//获取多文件信息
    $rids = isset($_GET['rids']) ? $_GET['rids'] : '';
    if(!$rids){
        $rids=$pdata['pval'];
    }
    $rids = explode(',',$rids);
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 30;
    $start = ($page - 1) * $perpage;
    $limitsql = "limit $start," . $perpage;
    $sortfilearr = ['btime' => 1, 'mtime' => 2, 'dateline' => 3, 'name' => 4, 'size' => 5,'filesize'=>5, 'grade' => 6, 'duration' => 7, 'whsize' => 8];
    if (!isset($_GET['order'])) {
        //获取用户默认排序方式
        $sortdata = C::t('user_setting')->fetch_by_skey('pichomesortfileds');
        if ($sortdata) {
            $sortdatarr = unserialize($sortdata);
            $order = $sortdatarr['filed'] ? $sortfilearr[$sortdatarr['filed']] : 1;
            $asc = ($sortdatarr['scolorort']) ? $sortdatarr['sort'] : 'desc';
        } else {
            $order = 1;
            $asc = 'desc';
        }
    } else {
        if(isset($sortfilearr[$_GET['order']])){
            $order=$sortfilearr[$_GET['order']];
        }else{
            $order = isset($_GET['order']) ? intval($_GET['order']) : 1;
        }
        $asc = (isset($_GET['asc']) && trim($_GET['asc'])) ? trim($_GET['asc']) : 'desc';
    }
    $sql = " from %t  r ";
    $selectsql = " r.rid ";
    $params=array('pichome_resources');
    //过滤后缀
    $orderarr = [];
    switch ($order) {
        case 1://添加日期
            $orderarr[] = ' r.btime ' . $asc;
            break;
        case 2://创建日期
            $orderarr[] = ' r.mtime ' . $asc;
            break;
        case 3://修改日期
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
        case 9://热度排序
            $sql .= ' left join %t v on r.rid=v.idval and v.idtype = 0 ';
            $selectsql .= " ,v.nums as num  ";
            $params[] = 'views';
            $orderarr[] = '  num desc  ';
        default:
            $orderarr[] = ' r.dateline ' . $asc;
    }
    $orderarr[] = " r.rid " . $asc;
    $ordersql = implode(',', $orderarr);
    $wheresql=" r.rid in(%n)";
    $params[]=$rids;
    $keyword='';
    if($_GET['keyword']){
        $keyword=getstr($_GET['keyword']);
        $wheresql.=" and r.name like %s";
        $params[]='%'.$keyword.'%';
    }
    //处理模板后缀限制
    $templateData=C::t('publish_template')->fetch($pdata['tid']);
    if($templateData['exts']){
        $exts = explode(',',$templateData['exts']);
        if($exts){
            $wheresql .= ' and r.ext in(%n)';
            $params[] = $exts;
        }
    }
    $limitsql = " limit $start,$perpage";
    $rids=array();
    foreach (DB::fetch_all(" select  $selectsql $sql where  $wheresql  order by $ordersql $limitsql", $params) as $value) {
        $rids[] = $value['rid'];
    }
    $data = C::t('pichome_resources')->getdatasbyrids($rids,1,$pdata['perm']);

    $counttotal=count($rids);
    if (count($rids) >= $perpage) {
        $next = true;
    } else {
        $next = false;
    }
    $return = array(
        'appid' => '',
        'next' => $next,
        'data' => $data ? $data : array(),
        'param' => array(
            'order' => $order,
            'page' => $page,
            'perpage' => $perpage,
            'total' => $counttotal,
            'asc' => $asc,
            'keyword' => $keyword
        )
    );
    exit(json_encode(array('success' => true, 'data' => $return)));
}
elseif($do == 'getTempate'){//获取发布模版数据
    $ptype = isset($_GET['ptype']) ? intval($_GET['ptype']) : 0;
    $tdata = C::t('pichome_publish_template')->fetch_by_ttype($ptype);
    exit(json_encode(['success' => true, 'data' => $tdata]));
}
elseif($do == 'geturlqrcode'){//获取二维码
    $pid=intval($_GET['pid']);
    $url=MOD_URL.'&id='.$pid;
    $qrcodeurl=C::t('pichome_route')->getQRcodeByUrl($url);
    exit(json_encode(['success' => true, 'qrcode' => $qrcodeurl]));
}
elseif($do == 'getfiledetails'){//文件详情页
    if(!$patharr=Pdecode($_GET['path'])){
        exit(json_encode(array('status'=>2,'error'=>lang('no_perm'))));
    }
    $rid = $patharr['path'];
    $isshare = $patharr['isshare'];
    $perm = $patharr['perm'];
    $isadmin = $patharr['isadmin'];
    if (!$rid) {
        exit(json_encode(array('status'=>2,'error'=>lang('no_perm'))));
    }
    $resourcesdata = C::t('pichome_resources')->fetch_by_rid($rid,$isshare,1,$perm);

    if($perm){
        $resourcesdata['download'] =perm::check('download2',$perm)?1:0;
        $resourcesdata['share'] =perm::check('share',$perm)?1:0;
        $resourcesdata['view'] =perm::check('read2',$perm)?1:0;
        $resourcesdata['dpath']=Pencode(array('path' => $resourcesdata['rid'], 'perm' => $perm, 'ishare' => $isshare, 'isadmin' => $isadmin), 7200);
        if($resourcesdata['realfianllypath']){
            $resourcesdata['realfianllypath']=$_G['siteurl'].'index.php?mod=io&op=getStream&path='.$resourcesdata['dpath'];
        }
    }

    $resourcesdata['preview']=array();
    if(!$resourcesdata['iniframe']){
        $resourcesdata['preview'] = C::t('thumb_preview')->fetchPreviewByRid($rid,false,$perm);
        $resourcesCover = ['spath'=>$resourcesdata['icondata'],'lpath'=>$resourcesdata['originalimg'],'name'=>$resourcesdata['name'],'ext'=>$resourcesdata['ext'],'realfianllypath'=>IO::getFileUri($resourcesdata['rid'])];
        if($resourcesdata['preview']) array_unshift($resourcesdata['preview'],$resourcesCover);
    }


    //增加浏览次数
    if($resourcesdata){
        addFileviewStats($rid,$isadmin);
    }
    Hook::listen('getDetailRighturl',$resourcesdata);
   exit(json_encode(array('status'=>1,'resourcesdata' => $resourcesdata,'sitename'=>$_G['setting']['sitename'])));


}
elseif($do=='getFileContent'){

    if(!$patharr=Pdecode($_GET['path'])){
        exit(json_encode(array('error'=>'File not found')));
    }

    $rid = $patharr['path'];
    $resourcesdata=C::t('pichome_resources')->fetch($rid);
    $str = IO::getFileContent($rid);
    if($_GET['type']=='text') {
        require_once DZZ_ROOT . './dzz/class/class_encode.php';
        $p = new Encode_Core();
        $code = $p->get_encoding($str);
        if ($code) $str = diconv($str, $code, CHARSET);
        $str = htmlspecialchars($str);
        $str = nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $str));
    }
    exit(json_encode(array('success'=>true,'data'=>$str)));
}
elseif($do == 'getColletLists'){//获取合集列表
    $page=$_GET['page']?intval($_GET['page']):1;
    $perpage=$_GET['perpage']?intval($_GET['perpage']):20;
    $start=($page-1)*$perpage;
    $orderby=$_GET['orderby']?$_GET['orderby']:'dateline';
    $order=$_GET['order']?$_GET['order']:'DESC';
    $allowed_orderby = ['dateline', 'pname', 'cviews', 'cdownload'];
    $allowed_order   = ['ASC', 'DESC'];
    $orderby = (is_string($_GET['orderby'] ?? null)) ? $_GET['orderby'] : 'dateline';
    $order   = strtoupper((is_string($_GET['order'] ?? null)) ? $_GET['order'] : 'DESC');
    if (!in_array($orderby, $allowed_orderby, true)) {
        $orderby = 'dateline';
    }
    if (!in_array($order, $allowed_order, true)) {
        $order = 'DESC';
    }
    if($pdata['ptype']=='6' and $pdata['flag']>0) {
       $sql=" from %t ";
        $countsql=" COUNT(*)  from %t ";
        $sql=" id from %t ";
        $wheresql = "pstatus='1' and ptype!='6' ";
        $param = array('publish_list');
       $pageset=unserialize($pdata['pageset']);
       if(!empty($pageset['condition']['keyword'])){
           $arr=explode(' ',$pageset['condition']['keyword']);
           $osqlarr=array();
           foreach($arr as $v){
               $nsqlarr=array();
               if(strpos($v,'+')!==false){
                   $andarr=explode('+',$v);
                   foreach($andarr as $v1){
                       if(empty($v1)) continue;
                       $nsqlarr[]= " pname like %s ";
                       $param[] = '%'.str_replace('_','\_',$v1).'%';
                   }
                   if($andarr){
                       $osqlarr[]= '('.implode(' and ',$nsqlarr).')';
                   }
               }else{
                   $osqlarr[]= " pname like %s ";
                   $param[] = '%'.str_replace('_','\_',$v).'%';
               }
           }
           if($osqlarr){
               $wheresql .= ' and ('.implode(' or ',$osqlarr).')';
           }
       }
       if(!empty($pageset['condition']['ptype'])){
           $wheresql.=" and ptype in(%n)";
           $param[]=$pageset['condition']['ptype'];
       }
        if(!empty($pageset['condition']['starttime'])){
            $wheresql.=" and dateline>%d";
            $param[]=strtotime($pageset['condition']['starttime']);
        }
        if(!empty($pageset['condition']['endtime'])){
            $wheresql.=" and dateline<%d";
            $param[]=strtotime($pageset['condition']['endtime'])+24*60*60;
        }
        if ($_GET['keyword']) {
            $wheresql .= " and pname like %s";
            $param[] = '%' . getstr($_GET['keyword']) . '%';
        }
    }else{
        $countsql=" COUNT(*)  from %t r LEFT JOIN %t p on r.pid=p.id ";
        $sql=" p.id from %t r LEFT JOIN %t p on r.pid=p.id ";
        $wheresql = "p.pstatus='1' and p.ptype!='6'  and r.rpid=%d";
        $param = array('publish_relation', 'publish_list', $pid);
        if ($_GET['keyword']) {
            $wheresql .= " and p.pname like %s";
            $param[] = '%' . getstr($_GET['keyword']) . '%';
        }

    }
    $ordersql = "order by p.$orderby $order";

    $data=array();
    if($count=DB::result_first("select $countsql where $wheresql",$param)) {
        foreach (DB::fetch_all("select  $sql where $wheresql $ordersql limit $start,$perpage", $param) as $value) {

            $value=C::t('publish_list')->fetch_by_id($value['id']);
            $value['dateline']=dgmdate($value['dateline'],'Y-m-d H:i:s');
            if($value['pageset']['_file_cover'][0]['src']){
                $value['img']=$value['pageset']['_file_cover'][0]['src'];
            }

            //处理地址
            $url='index.php?mod=publish&id=' . $value['id'];
            $value['address']=C::t('pichome_route')->update_path_by_url($url,$value['address']);
            if(strpos($value['url'],'http') === false){
                $value['url'] = $_G['siteurl'] . $value['address'];
            }else{
                $value['url'] =  $_G['siteurl'] .$url;
            }
            $data[]=$value;

        }
    }
    if($_GET['keyword']){//增加搜索热度
        $insertdata = [
            'idtype'=>5,
            'idval'=>$pid,
            'keyword'=>trim($_GET['keyword']),
        ];
        C::t('keyword_hots')->insert_data($insertdata);
    }
    exit(json_encode(array('success'=>true,'data'=>$data,'count'=>$count,'page'=>$page,'perpage'=>$perpage)));
}
elseif($do == 'getLatestByPid'){//获取24小时最新合集
    $limit=$_GET['limit']?intval($_GET['limit']):5;
    $h=isset($_GET['h'])?$_GET['h']:24;
    $time=TIMESTAMP-$h*60*60;

    $data=array();
    $sql=" p.pstatus='1' and v.idtype='6'  and v.dateline>%d";
    $param=array('stats_view','publish_list',$time);
    if($pdata['flag']>0) {
        $pageset=unserialize($pdata['pageset']);
        if(!empty($pageset['condition']['keyword'])){
            $arr=explode(' ',$pageset['condition']['keyword']);
            $osqlarr=array();
            foreach($arr as $v){
                $nsqlarr=array();
                if(strpos($v,'+')!==false){
                    $andarr=explode('+',$v);
                    foreach($andarr as $v1){
                        if(empty($v1)) continue;
                        $nsqlarr[]= " p.pname like %s ";
                        $param[] = '%'.str_replace('_','\_',$v1).'%';
                    }
                    if($andarr){
                        $osqlarr[]= '('.implode(' and ',$nsqlarr).')';
                    }
                }else{
                    $osqlarr[]= " p.pname like %s ";
                    $param[] = '%'.str_replace('_','\_',$v).'%';
                }
            }
            if($osqlarr){
                $sql .= ' and ('.implode(' or ',$osqlarr).')';
            }
        }
        if(!empty($pageset['condition']['ptype'])){
            $sql.=" and p.ptype in(%n)";
            $param[]=$pageset['condition']['ptype'];
        }
        if(!empty($pageset['condition']['starttime'])){
            $sql.=" and p.dateline>%d";
            $param[]=strtotime($pageset['condition']['starttime']);
        }
        if(!empty($pageset['condition']['endtime'])){
            $sql.=" and p.dateline<%d";
            $param[]=strtotime($pageset['condition']['endtime'])+24*60*60;
        }
    }else{
        $rpids=C::t('publish_relation')->fetch_pid_by_rpid($pid);
        if(empty($rpids)){
            exit(json_encode(['success' => true, 'data' => []]));
        }
        $sql.=" and v.idval in(%n)";
        $param[]=$rpids;

    }
    foreach (DB::fetch_all("select p.id,count(*) as sum from %t v  LEFT JOIN %t p  on p.id=v.idval  where $sql group by v.idval  order by sum DESC  limit $limit", $param) as $value) {

        $value=C::t('publish_list')->fetch_by_id($value['id']);

        if(!empty($value['pageset']['_file_cover'][0]['src'])){
            $value['img']=$value['pageset']['_file_cover'][0]['src'];
        }

        //处理地址
        $url='index.php?mod=publish&id=' . $value['id'];
        $value['address']=C::t('pichome_route')->update_path_by_url($url,$value['address']);
        if(strpos($value['url'],'http') === false){
            $value['url'] = $_G['siteurl'] . $value['address'];
        }else{
            $value['url'] =  $_G['siteurl'] .$url;
        }

        $data[]=$value;
    }
    exit(json_encode(['success' => true, 'data' => $data]));

}
elseif ($do == 'searchmenu_num') {
    $clang = '';
    Hook::listen('lang_parse',$clang,['checklang']);
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

    //用户权限等级
    $para[]= $_G['pichomelevel'];

    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $gappid = isset($_GET['appid']) ? [trim($_GET['appid'])] : [];

    if(!is_array($appid)) $appid = (array)$appid;
    $fids = isset($_GET['fids']) ? trim($_GET['fids']) : '';


    $appid = isset($_GET['appid']) ? [trim($_GET['appid'])] : [-1];

    //处理智能数据相关的条件
    //范围条件
    $vappids=array();

    if($pdata['ptype']==5) {
        $stdata = C::t('#intelligent#intelligent')->fetch_by_tid($pdata['pval']);
        if ($stdata['searchRange']) {
            $appids = array_keys($stdata['searchRange']['appids']);
            foreach ($stdata['searchRange']['folders'] as $pathkey => $v) {
                $appids[] = $v['appid'];
            }
            $vappids = $appids;
        }

        if ($vappids) {
            $wheresql .= ' and r.appid in(%n)';
            $para[] = $vappids;

            //处理范围分类

            if ($stdata['searchRange']['folders']) {
                $pathkeys = array_keys($stdata['searchRange']['folders']);
            } else {
                $pathkeys = array();
            }
            if ($pathkeys) {
                $sql .= " LEFT JOIN %t frx on frx.rid = r.rid ";
                $params[] = 'pichome_folderresources';
                $warr = array();
                foreach ($pathkeys as $pathkey) {
                    $warr[] = " frx.pathkey like %s ";
                    $para[] = str_replace('_', '\_', $pathkey) . '%';
                }
                if ($warr) {
                    $wheresql .= ' and (' . implode(' or ', $warr) . ')';
                }
            }

            //处理名称包含
            if ($stdata['extra'] && $stdata['extra']['searchName']) {
                $arr = explode(' ', $stdata['extra']['searchName']);
                $osqlarr = array();

                foreach ($arr as $v) {
                    $nsqlarr = array();
                    if (strpos($v, '+') !== false) {
                        $andarr = explode('+', $v);
                        foreach ($andarr as $v1) {
                            if (empty($v1)) continue;
                            $nsqlarr[] = " r.name like %s ";
                            $para[] = '%' . str_replace('_', '\_', $v1) . '%';
                        }
                        if ($andarr) {
                            $osqlarr[] = '(' . implode(' and ', $nsqlarr) . ')';
                        }
                    } else {
                        $osqlarr[] = " r.name like %s ";
                        $para[] = '%' . str_replace('_', '\_', $v) . '%';
                    }
                }
                if ($osqlarr) {
                    $wheresql .= ' and (' . implode(' or ', $osqlarr) . ')';
                }
            }
            //处理评分
            if ($stdata['extra'] && $stdata['extra']['grade'] && !in_array('0', $stdata['extra']['grade'])) {
                $wheresql .= ' and r.grade in(%s) ';
                $para[] = $stdata['extra']['grade'];
            }

            //处理标签范围

            if ($stdata['tags']) {
                $tagtids = array();
                $tagnames = explode(',', $stdata['tags']);

                foreach (DB::fetch_all("select tid from %t where tagname IN(%n)", array('pichome_tag', $tagnames)) as $v2) {
                    $tagtids[$v2['tid']] = $v2['tid'];
                }
                foreach (DB::fetch_all("select langflag from %t where state>0", array('language')) as $value) {
                    $table = 'lang_' . strtolower(str_replace('-', '_', $value['langflag']));
                    foreach (DB::fetch_all("select * from %t where idtype='8' and filed='tagname' and svalue IN(%n)", array($table, $tagnames)) as $v2) {
                        $tagtids[$v2['idvalue']] = $v2['idvalue'];
                    }
                }
                if ($tagtids) {
                    $sql .= "left join %t rts on r.rid = rts.rid ";
                    $params[] = 'pichome_resourcestag';
                    $wheresql .= ' and rts.tid in(%n) ';
                    $para[] = $tagtids;
                }
            }
        }else{
            $wheresql .= ' and 0 ';
        }
    }else{
        //库权限判断部分
        foreach (DB::fetch_all("select appid,path,view,type from %t where isdelete = 0 and appid in(%n)", array('pichome_vapp',$appid)) as $v) {
            $vappids[] = $v['appid'];
        }
        if($vappids){
            $wheresql .= ' and r.appid in(%n)';
            $para[] = $appid;
        }else{
            $wheresql .= ' and 0 ';
        }
    }
    //处理模板后缀限制
    $templateData=C::t('publish_template')->fetch($pdata['tid']);
    if($templateData['exts']){
        $exts = explode(',',$templateData['exts']);
        if($exts){
            $wheresql .= ' and r.ext in(%n)';
            $para[] = $exts;
        }
    }
    $imageRids = [];
    if(isset($_GET['aid'])){
        $aid = intval($_GET['aid']);
        $searchparams= array(
            'appids'=>$vappids,
            'aid'=>$aid,
        );
        Hook::listen('search_condition_filter',$imageRids,$searchparams);
        if(!empty($imageRids['rids'])){
            $wheresql .= ' and r.rid in(%n)';
            $para[] = $imageRids['rids'];
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

    if($clang) $wheresql .= " and (r.lang = '".$_G['language']."' or r.lang = 'all' ) ";
    //关键词条件
    $keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '';
    if ($keyword) {
        if (!in_array('pichome_resources_attr', $params)) {
            $sql .= " left join %t ra on r.rid = ra.rid";
            $params[] = 'pichome_resources_attr';
        }
        if($clang && !in_array('lang_search',$params)){
            $sql .= " LEFT JOIN %t lang ON lang.idvalue=r.rid and lang.lang = %s ";
            $params[] = 'lang_search';
            $params[] = $clang;
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
                if($clang){
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
            if($clang){
                $wheresql .= "  and ((t.tagname LIKE %s and  ISNULL(langtag_$clang.idvalue)) or langtag_$clang.svalue LIKE %s) ";
                $sql .= " left join %t langtag_$clang on t.tid =  langtag_$clang.idvalue and  langtag_$clang.idtype=8 ";
                $params[] = 'lang_'.$clang;
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
        $data[-7] = array('num' => $data[-7]['num'], 'val' => $timedataarr[-7]['val'], 'label' => $timedataarr[-7]['label']);
        $data[-30]['num'] = (isset($data[-30]['num']) ? intval($data[-30]['num']) : 0) + (isset($data[-7]['num']) ? intval($data[-7]['num']) : 0);
        $data[-30] = array('num' => $data[-30]['num'], 'val' => $timedataarr[-30]['val'], 'label' => $timedataarr[-30]['label']);
        $data[-90]['num'] = (isset($data[-90]['num']) ? intval($data[-90]['num']) : 0) + (isset($data[-30]['num']) ? intval($data[-30]['num']) : 0);
        $data[-90] = array('num' => $data[-90]['num'], 'val' => $timedataarr[-90]['val'], 'label' => $timedataarr[-90]['label']);
        $data[-365]['num'] = (isset($data[-365]['num']) ? intval($data[-365]['num']) : 0) + (isset($data[-90]['num']) ? intval($data[-90]['num']) : 0);
        $data[-365] = array('num' => $data[-365]['num'], 'val' => $timedataarr[-365]['val'], 'label' => $timedataarr[-365]['label']);
        foreach ($data as $k => $v) {
           // if ($v['num'] == 0) unset($data[$k]);
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
       $data[-7] = array('num' => $data[-7]['num'], 'val' => $timedataarr[-7]['val'], 'label' => $timedataarr[-7]['label']);
        $data[-30]['num'] = (isset($data[-30]['num']) ? intval($data[-30]['num']) : 0) + (isset($data[-7]['num']) ? intval($data[-7]['num']) : 0);
        $data[-30] = array('num' => $data[-30]['num'], 'val' => $timedataarr[-30]['val'], 'label' => $timedataarr[-30]['label']);
        $data[-90]['num'] = (isset($data[-90]['num']) ? intval($data[-90]['num']) : 0) + (isset($data[-30]['num']) ? intval($data[-30]['num']) : 0);
       $data[-90] = array('num' => $data[-90]['num'], 'val' => $timedataarr[-90]['val'], 'label' => $timedataarr[-90]['label']);
        $data[-365]['num'] = (isset($data[-365]['num']) ? intval($data[-365]['num']) : 0) + (isset($data[-90]['num']) ? intval($data[-90]['num']) : 0);
        $data[-365] = array('num' => $data[-365]['num'], 'val' => $timedataarr[-365]['val'], 'label' => $timedataarr[-365]['label']);
        foreach ($data as $k => $v) {
           // if ($v['num'] == 0) unset($data[$k]);
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
          $data[-7] = array('num' => $data[-7]['num'], 'val' => $timedataarr[-7]['val'], 'label' => $timedataarr[-7]['label']);
        $data[-30]['num'] = (isset($data[-30]['num']) ? intval($data[-30]['num']) : 0) + (isset($data[-7]['num']) ? intval($data[-7]['num']) : 0);
         $data[-30] = array('num' => $data[-30]['num'], 'val' => $timedataarr[-30]['val'], 'label' => $timedataarr[-30]['label']);
        $data[-90]['num'] = (isset($data[-90]['num']) ? intval($data[-90]['num']) : 0) + (isset($data[-30]['num']) ? intval($data[-30]['num']) : 0);
        $data[-90] = array('num' => $data[-90]['num'], 'val' => $timedataarr[-90]['val'], 'label' => $timedataarr[-90]['label']);
        $data[-365]['num'] = (isset($data[-365]['num']) ? intval($data[-365]['num']) : 0) + (isset($data[-90]['num']) ? intval($data[-90]['num']) : 0);
        $data[-365] = array('num' => $data[-365]['num'], 'val' => $timedataarr[-365]['val'], 'label' => $timedataarr[-365]['label']);
        foreach ($data as $k => $v) {
            //if ($v['num'] == 0) unset($data[$k]);
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
            if($clang && !in_array('lang_search',$params)){
                $sql .= " LEFT JOIN %t lang ON lang.idvalue=tab.tid and lang.lang = %s ";
                $params[] = 'lang_search';
                $params[] = $clang;
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
                    if($clang){
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
        $sql = "select count(DISTINCT(rt.tid)) as num  from  %t rt   left join %t r on rt.rid=r.rid ";

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
    //处理模板后缀限制
    $templateData=C::t('publish_template')->fetch($pdata['tid']);
    if($templateData['exts']){
        $exts = explode(',',$templateData['exts']);
        if($exts){
            $wheresql .= ' and r.ext in(%n)';
            $para[] = $exts;
        }
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
    //处理智能数据相关的条件
    //范围条件
    $vappids=array();

    if($pdata['ptype']==5) {
        $stdata = C::t('#intelligent#intelligent')->fetch_by_tid($pdata['pval']);
        if ($stdata['searchRange']) {
            $appids = array_keys($stdata['searchRange']['appids']);
            foreach ($stdata['searchRange']['folders'] as $pathkey => $v) {
                $appids[] = $v['appid'];
            }
            $vappids = $appids;
        }else{
            foreach (DB::fetch_all("select appid,path,view,type from %t where isdelete = 0", array('pichome_vapp')) as $v) {
                if ($v['type'] != 3 && !IO::checkfileexists($v['path'], 1)) {
                    continue;
                }
                if (C::t('pichome_vapp')->getpermbypermdata($v['view'], $v['appid'])) {
                    $vappids[] = $v['appid'];
                }
            }
        }

        if ($vappids) {
            $wheresql .= ' and r.appid in(%n)';
            $para[] = $vappids;

            //处理范围分类

            if ($stdata['searchRange']['folders']) {
                $pathkeys = array_keys($stdata['searchRange']['folders']);
            } else {
                $pathkeys = array();
            }
            if ($pathkeys) {
                $sql .= " LEFT JOIN %t frx on frx.rid = r.rid ";
                $params[] = 'pichome_folderresources';
                $warr = array();
                foreach ($pathkeys as $pathkey) {
                    $warr[] = " frx.pathkey like %s ";
                    $para[] = str_replace('_', '\_', $pathkey) . '%';
                }
                if ($warr) {
                    $wheresql .= ' and (' . implode(' or ', $warr) . ')';
                }
            }

            //处理名称包含
            if ($stdata['extra'] && $stdata['extra']['searchName']) {
                $arr = explode(' ', $stdata['extra']['searchName']);
                $osqlarr = array();

                foreach ($arr as $v) {
                    $nsqlarr = array();
                    if (strpos($v, '+') !== false) {
                        $andarr = explode('+', $v);
                        foreach ($andarr as $v1) {
                            if (empty($v1)) continue;
                            $nsqlarr[] = " r.name like %s ";
                            $para[] = '%' . str_replace('_', '\_', $v1) . '%';
                        }
                        if ($andarr) {
                            $osqlarr[] = '(' . implode(' and ', $nsqlarr) . ')';
                        }
                    } else {
                        $osqlarr[] = " r.name like %s ";
                        $para[] = '%' . str_replace('_', '\_', $v) . '%';
                    }
                }
                if ($osqlarr) {
                    $wheresql .= ' and (' . implode(' or ', $osqlarr) . ')';
                }
            }
            //处理评分
            if ($stdata['extra'] && $stdata['extra']['grade'] && !in_array('0', $stdata['extra']['grade'])) {
                $wheresql .= ' and r.grade in(%s) ';
                $para[] = $stdata['extra']['grade'];
            }

            //处理标签范围

            if ($stdata['tags']) {
                $tagtids = array();
                $tagnames = explode(',', $stdata['tags']);

                foreach (DB::fetch_all("select tid from %t where tagname IN(%n)", array('pichome_tag', $tagnames)) as $v2) {
                    $tagtids[$v2['tid']] = $v2['tid'];
                }
                foreach (DB::fetch_all("select langflag from %t where state>0", array('language')) as $value) {
                    $table = 'lang_' . strtolower(str_replace('-', '_', $value['langflag']));
                    foreach (DB::fetch_all("select * from %t where idtype='8' and filed='tagname' and svalue IN(%n)", array($table, $tagnames)) as $v2) {
                        $tagtids[$v2['idvalue']] = $v2['idvalue'];
                    }
                }
                if ($tagtids) {
                    $sql .= "left join %t rts on r.rid = rts.rid ";
                    $params[] = 'pichome_resourcestag';
                    $wheresql .= ' and rts.tid in(%n) ';
                    $para[] = $tagtids;
                }
            }
        }else{
            $wheresql .= ' and 0 ';
        }
    }else{
        //库权限判断部分
        foreach (DB::fetch_all("select appid,path,view,type from %t where isdelete = 0 and appid in(%n)", array('pichome_vapp',$appid)) as $v) {
            if ($v['type'] != 3 && !IO::checkfileexists($v['path'],1)) {
                continue;
            }
            $vappids[] = $v['appid'];
        }
        if($vappids){
            $wheresql .= ' and r.appid in(%n)';
            $para[] = $appid;
        }else{
            $wheresql .= ' and 0 ';
        }
    }

    $imageRids = [];
    if(isset($_GET['aid'])){
        $aid = intval($_GET['aid']);
        $searchparams= array(
            'appids'=>$vappids,
            'aid'=>$aid,
        );
        Hook::listen('search_condition_filter',$imageRids,$searchparams);
        if(!empty($imageRids['rids'])){
            $wheresql .= ' and r.rid in(%n)';
            $para[] = $imageRids['rids'];
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
            $clang = '';
            Hook::listen('lang_parse',$clang,['checklang']);
            if($clang){
                $wheresql .= "  and ((t.tagname LIKE %s and  ISNULL(langtag_$clang.idvalue)) or langtag_$clang.svalue LIKE %s) ";
                $sql .= " left join %t langtag_$clang on t.tid =  langtag_$clang.idvalue and  langtag_$clang.idtype=8 ";
                $params[] = 'lang_'.$clang;
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
elseif($do == 'bannerlist'){ //获取栏目列表
    $id = isset($_GET['id']) ? trim($_GET['id']) : '';
    $bannerdata = C::t('pichome_banner')->getBannerTree($id);
    $bannerlist=$bannerdata['bannerlist'];
    if($active=getActiveByBannerList($bannerlist['top'],$pid)){
        $bannerlist['active']=$active;
    }elseif($active=getActiveByBannerList($bannerlist['bottom'],$pid)){
        $bannerlist['active']=$active;
    }else{
        $bannerlist['active']=[];
    }
    $bannerdata['bannerlist']=$bannerlist;
    exit(json_encode(['success' => true, 'data' => $bannerdata]));
}
elseif ($do == 'getfolderdata') {//获取目录数据
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $pfids = isset($_GET['pfids']) ? trim($_GET['pfids']):'';
    $isall = isset($_GET['isall']) ? intval($_GET['isall']):0;
    $includefiles=isset($_GET['includefiles']) ? intval($_GET['includefiles']):1;//是否获取文件
    $folderdatanum = [];
    if($appid){
//        $appdata = DB::fetch_first("select appid,path,view,type from %t where isdelete = 0 and appid =%s", array('pichome_vapp',$appid));
//        if(!C::t('pichome_vapp')->getpermbypermdata($appdata['view'],$appdata['appid']) || ($appdata['type'] !=3 && !IO::checkfileexists($appdata['path'],1))){
//            exit(json_encode(array( 'folderdatanum' => $folderdatanum)));
//        }
    }
    if($pfids=='no_cat'){
        $pfids = [];
    }elseif($pfids){
        $pfids = explode(',',$pfids);
    }else{
        $pfids = [];
    }
    if($isall){
        $folderdatanum = C::t('pichome_folder')->fetch_all_folder_by_appid($appid);
    }else{
        $folderdatanum = C::t('pichome_folder')->fetch_folder_by_appid_pfid($appid,$pfids,1);
    }
    if(isset($folderdatanum['langkey'])) unset($folderdatanum['langkey']);
    if(empty($includefiles)){
        exit(json_encode(array( 'folderdatanum' => $folderdatanum,'resourcesdata'=>[])));
    }
    $maxLimit=100;
    //获取文件当前目录下的文件
    $rids=array();
    $sql=" r.appid = %s and r.isdelete = 0 ";
    $para=array('pichome_resources','pichome_folderresources',$appid);
    //处理模板后缀限制
    $templateData=C::t('publish_template')->fetch($pdata['tid']);
    if($templateData['exts']){
        $exts = explode(',',$templateData['exts']);
        if($exts){
            $sql .= ' and r.ext in(%n)';
            $para[] = $exts;
        }
    }
    if($_GET['pfids']=='no_cat'){
        $sql.=" and (isnull(fids) or fids='')";
        foreach(DB::fetch_all("select r.rid from %t r LEFT JOIN %t f on r.rid = f.rid  where  $sql  limit $maxLimit",$para) as $v) {
            $rids[] = $v['rid'];
        }
    }elseif($pfids){
        $sql.=" and (f.fid IN(%n))";
        $para[] = $pfids;
        foreach(DB::fetch_all("select r.rid from %t r LEFT JOIN %t f on r.rid = f.rid 
        where  $sql limit $maxLimit",array('pichome_resources','pichome_folderresources',$appid,$pfids,$maxLimit)) as $v) {
            $rids[] = $v['rid'];
        }
    }else{
        $sql.=" and (isnull(fids) or fids='')";
        foreach(DB::fetch_all("select r.rid from %t r LEFT JOIN %t f on r.rid = f.rid 
        where  $sql limit $maxLimit",$para) as $v) {
            $rids[] = $v['rid'];
        }
    }
    $datas=[];
    if($rids){
        foreach(C::t('pichome_resources')->getdatasbyrids($rids,1,$pdata['perm']) as $v){
            $datas[$v['rid']] = $v;
        }
    }
    $pageset=unserialize($pdata['pageset']);
    $order=intval($pageset['order']);
    $sort=intval($pageset['sort']);
    function sortChineseByGBK($array) {
        $convertedArray = [];
        // 转码为GBK
        foreach ($array as $key => $value) {
            // 忽略无法转换的字符（如特殊符号）
            $convertedArray[$value['rid']] = iconv('UTF-8', 'GBK//IGNORE', $value['name']);
        }
        // 按GBK编码排序
        asort($convertedArray,SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
        // 还原为UTF-8并重建索引
        $result = [];
        foreach ($convertedArray as $rid => $value) {
            $result[$rid] = $array[$rid];
        }
        return $result;
    }
    switch($order) {
        case 0: //按添加时间
            $parr=array_column($datas,'dateline');
            array_multisort($parr, $sort>0?SORT_ASC:SORT_DESC, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL,$datas);
            break;
        case 1://按文件名称
            $datas = sortChineseByGBK($datas);
            if(!$sort){
               $datas=array_reverse($datas);
            }
            break;
        case 2:
            $parr=array_column($datas,'grade');
            array_multisort($parr, $sort>0?SORT_ASC:SORT_DESC,SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL, $datas);
            break;
    }

    exit(json_encode(array( 'folderdatanum' => $folderdatanum,'resourcesdata'=>array_values($datas))));
}
elseif($do == 'getsearchfoldernum'){//获取左侧目录数字
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $pathkeys = isset($_GET['pathkeys']) ? trim($_GET['pathkeys']):'';
    $pathkeyarr = explode(',',$pathkeys);
    $data = C::t('pichome_folder')->getFolderNumByPathkey($appid,$pathkeyarr);
    exit(json_encode(array( 'data' => $data)));
}
elseif($do == 'getleftnum'){//获取左侧文件数
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    $data = ['all'=>0,'nocat'=>0];
    $data['nocat'] = DB::result_first("select count(rid) as num from %t 
        where  appid = %s and isdelete = 0 and (isnull(fids) or fids='')",array('pichome_resources',$appid));

    $data['all'] = DB::result_first("select count(rid) as num  from %t 
        where  appid = %s and isdelete = 0",array('pichome_resources',$appid));

    exit(json_encode(['success'=>true,'data'=>$data]));
}
elseif($do == 'searchfolderbyname'){
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $folderdatanum = C::t('pichome_folder')->search_by_fname($keyword,$appid);
    $ret=array();
    foreach($folderdatanum as $v){
        if($v['pathkey']) {
            $fidarr = str_split($v['pathkey'], 19);
            $ret=array_merge($ret,$fidarr);
        }

    }
    //获取文件
    $params = array('pichome_resources');
    $wheresql='1';
    $leftsql = 'LEFT JOIN %t fr on r.rid = fr.rid ';
    $params[]='pichome_folderresources';
    if($keyword) {
        $clang = '';
        Hook::listen('lang_parse',$clang,['checklang']);
        if ($clang) {
            $leftsql .= 'left join %t lang on lang.idvalue = r.rid and lang.filed= %s';
            $params[] = 'lang_' . $clang;
            $params[] = 'name';
            $wheresql .= ' and (r.name like %s  or lang.svalue like %s)';
            $params[] = '%' . $keyword . '%';
            $params[] = '%' . $keyword . '%';

        } else {
            $wheresql .= ' and r.name like %s  ';
            $params[] = '%' . $keyword . '%';
        }
          //搜索热度统计
            $insertdata = [
                'idtype'=>0,
                'idval'=>!empty($appid)?$appid:$pdata['val'],
                'keyword'=>trim($_GET['keyword']),
            ];
            C::t('keyword_hots')->insert_data($insertdata);

    }
    if($appid){
        $wheresql .= ' and r.appid = %s ';
        $params[] = $appid;
    }
    $resourcesdata=array();
    $maxLimit=1000;
    $ordersql=" order by r.name DESC";
    foreach(DB::fetch_all("select r.rid,r.name,fr.fid,fr.appid,fr.pathkey from %t r $leftsql where $wheresql $ordersql limit $maxLimit",$params) as $value) {
        if ($value['pathkey']) {
            $fidarr = str_split($value['pathkey'], 19);
            $ret = array_merge($ret, $fidarr);
        }
        $resourcesdata[]=$value;
    }
    $ret=array_values(array_unique($ret));

    exit(json_encode(array('data'=>$ret, 'folderdata' => $folderdatanum,'resourcesdata'=>$resourcesdata)));
}
elseif($do == 'getParentsByFid'){
    $fid=trim($_GET['fid']);
    $folderdata = [];
    if($pathkey = DB::result_first("select pathkey from %t where fid = %s",array('pichome_folder',$fid))) {
        $fidarr = str_split($pathkey, 19);
        foreach (DB::fetch_all("select fid from %t where fid in(%n) order by pathkey asc", array('pichome_folder', $fidarr)) as $v) {
            $folderdata[] = $v['fid'];
        }
    }
    exit(json_encode(array( 'success'=>true,'data'=>array_values($folderdata))));
}
function getActiveByBannerList($list,$pid){

    foreach($list as $k=>$v){

        if($v['btype']=='6' && $v['bdata']==$pid){

           $arr=explode('-',str_replace('_','',$v['pathkey']));
           $arr[]=$v['id'];

           return $arr;
        }
        if($v['children']){
          if($ret  = getActiveByBannerList($v['children'],$pid)){
              return $ret;
          }
        }
    }
    return [];
}