<?php

if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
$tdid = isset($_GET['tdid']) ? intval($_GET['tdid']) : 0;
$locked = true;
$processname = 'DZZ_LOCK_PICHOMEUPADAEPAGEDATA' . $tdid;
dzz_process::unlock($processname);
if (!dzz_process::islocked($processname, 60 * 15)) {
    $locked = false;
}
if ($locked) {
    exit(json_encode(array('error' => '进程已被锁定请稍后再试')));
}

$tagdata = C::t('pichome_templatetagdata')->fetch($tdid);

if ($tagdata) {
    //获取类型
    $tag = C::t('pichome_templatetag')->fetch($tagdata['tid']);
    $tagtype = $tag['tagtype'];
    if ($tagtype == 'file_rec' || $tagtype == 'db_ids') {//如果是文件推荐

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 200;
        $tagval = unserialize($tagdata['tdata']);
        $cachename = 'templatetagdata_' . $tdid;
        $processname = 'templatetagdatalock_' . $tdid;
        $tagval = $tagval[0];

        $limitnum = $tagval['number'];

        if($tagtype == 'db_ids' && $limitnum && $perpage > $limitnum) $perpage = $limitnum;
        $start = ($page - 1) * $perpage;
        $limitsql = "limit $start," . $perpage;
        $sql = " from %t r  ";
        $selectsql = " r.rid,r.name ";
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
        }elseif($tagval['type'] == 3){//评分
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
        }elseif($tagval['type'] == 4){//分类
            $fidarr = $tagval['classify']['checked'];
            /*$wheresql .= ' and r.fids in(%n) ';
            $para[] = $fidarr;*/
            $sql .= "left join %t fr on fr.rid=r.rid ";
            $params[] = 'pichome_folderresources';
            $wheresql .= ' and fr.fid in(%n) ';
            $para[] = $fidarr;
        }
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

        if ($para) $params = array_merge($params, $para);
        $count = DB::result_first("select $countsql $sql where  $wheresql  ", $params);
        $rids = [];

        foreach (DB::fetch_all(" select  $selectsql $sql where  $wheresql  group by r.rid  order by $ordersql  $limitsql", $params) as $value) {
            $rids[] = $value['rid'];
        }
        if ($tagdata['cachetime'] && !empty($rids)) {
            $cachearr = [
                'cachekey' => $cachename,
                'cachevalue' => serialize($rids),
                'dateline' => TIMESTAMP
            ];
            C::t('cache')->insert_cachedata_by_cachename($cachearr, $tagdata['cachetime'], 1);
        }
    }
    elseif($tagtype == 'tab_rec'){//如果是专辑推荐
        $tagval = unserialize($tagdata['tdata']);
        $tagval = $tagval[0];
        $limitnum = $tagval['number'];
        $cachename = 'templatetagdata_'.$tdid;
        $processname = 'templatetagdatalock_'.$tdid;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 100;
        if($limitnum && $perpage > $limitnum) $perpage = $limitnum;
        $start = ($page - 1) * $perpage;
        $limitsql = "limit $start," . $perpage;


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
}
dzz_process::unlock($processname);