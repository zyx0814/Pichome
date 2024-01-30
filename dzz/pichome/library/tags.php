<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
$appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
if (!$appid) exit(json_encode(array('error' => true, 'msg' => '缺少必要参数')));
if ($operation == 'deltag') {//删除标签
    $tid = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
    C::t('pichome_vapp_tag')->delete_tag_by_tid_appid($tid,$appid);
    $tagcount = gettagcount($appid);
    exit(json_encode(array('success'=>true,'arr'=>$tagcount)));
} elseif($operation == 'delgroup'){
    $cid = isset($_GET['cid']) ? trim($_GET['cid']):'';
    C::t('pichome_taggroup')->delete_by_cids($cid);
    $tagcount = gettagcount($appid);
    exit(json_encode(array('success'=>true,'arr'=>$tagcount)));
}elseif ($operation == 'addtag') {

} elseif($operation == 'addtagtogroup'){//添加标签到群组
    $tid = isset($_GET['tid']) ? intval($_GET['tid']):'';
    $cid = isset($_GET['cid']) ? trim($_GET['cid']):0;
    $setarr = [
        'tid'=>$tid,
        'appid'=>$appid,
        'cid'=>$cid
    ];
    C::t('pichome_tagrelation')->insert($setarr);
    $tagcount = gettagcount($appid);
    exit(json_encode(array('success'=>true,'arr'=>$tagcount)));

}elseif($operation == 'movetagtogroup'){//移动标签到群组
    $tid = isset($_GET['tid']) ? intval($_GET['tid']):'';
    $cid = isset($_GET['cid']) ? trim($_GET['cid']):0;

    C::t('pichome_tagrelation')->movetag_togroup($tid,$appid,$cid);
    $tagcount = gettagcount($appid);
    exit(json_encode(array('success'=>true,'arr'=>$tagcount)));


}elseif($operation == 'removetagfromgroup'){//从分类中移除标签
    $tid = isset($_GET['tid']) ? intval($_GET['tid']):'';
    $cid = isset($_GET['cid']) ? trim($_GET['cid']):0;
    if($id = DB::fetch_first("select id from %t  where cid = %s and tid = %d and appid = %s",array('pichome_tagrelation',$cid,$tid,$appid))){
        C::t('pichome_tagrelation')->delete($id);
        $tagcount = gettagcount($appid);
        exit(json_encode(array('success'=>true,'arr'=>$tagcount)));
    }

}elseif ($operation == 'addgroup') {//新建分组
    $pcid = isset($_GET['pcid']) ? trim($_GET['pcid']) : '';
    $name = getstr($_GET['name'], 120);
    // if (DB::result_first("select count(cid) from %t where pcid = %s and appid = %s and catname = %s", array('pichome_taggroup', $pcid, $appid, $name))) {
    //     exit(json_encode(array('success' => false, 'msg' => '标签分类名称不能重复')));
    // }
    if (submitcheck('classifysubmit')) {
        $setarr = array(
            'pcid' => $pcid,
            'catname' => $name,
            'appid' => $appid,
            'dateline' => TIMESTAMP
        );
        $cid = C::t('pichome_taggroup')->insert($setarr);
        exit(json_encode(array('success' => true, 'cid' => $cid)));
    }

} elseif ($operation == 'sorttaggroup') {//分类排序
    $cids = isset($_GET['cids']) ? $_GET['cids'] : [];
    foreach ($cids as $k => $v) {
        C::t('pichome_taggroup')->update($v, ['disp' => $k]);
    }
    exit(json_encode(array('success' => true)));
} elseif($operation == 'renametaggroup'){//重命名
    $cid = isset($_GET['cid']) ? trim($_GET['cid']):'';
    $catname = isset($_GET['catname']) ? getstr($_GET['catname']):'';
    C::t('pichome_taggroup')->update($cid, ['catname' => $catname]);
    exit(json_encode(array('success' => true)));
}
elseif ($operation == 'gettaggroup') {

    $cid = isset($_GET['cid']) ? trim($_GET['cid']) : '';
    $nocat = isset($_GET['nocat']) ? intval($_GET['nocat']) : 0;
    $tagname = isset($_GET['tagname']) ? trim($_GET['tagname']) : '';


    if (!$cid) {
        if($nocat){
            $param = array('pichome_vapp_tag', 'pichome_tag', 'pichome_tagrelation', $appid);
            //  $sql="  and t.tid in(%n) ";
            $sql = " and vt.appid = %s ";
            if ($tagname) {
                $param[] = '%' . $tagname . '%';
                $sql .= ' and t.tagname like %s';
            }
            $tagdata = array();
            $letters = array('#', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($letters as $val) {
                $tagdata[$val] = array();
            }

            foreach (DB::fetch_all("select t.tid,t.tagname,t.initial,vt.hots from %t vt left join %t t on vt.tid = t.tid left join %t tg on vt.tid = tg.tid where isnull(tg.tid) $sql  order by t.initial , vt.hots DESC ", $param) as $value) {
                /*if(isset($searchtidnums[$value['tid']])) $value['num'] =$searchtidnums[$value['tid']];
                else $value['num'] = 0;*/
                 $tagdata[$value['initial']][$value['tid']] = $value;
            }
        }else{
            $param = array('pichome_vapp_tag', 'pichome_tag',  $appid);
            //  $sql="  and t.tid in(%n) ";
            $sql = "  vt.appid = %s ";
            if ($tagname) {
                $param[] = '%' . $tagname . '%';
                $sql .= ' and t.tagname like %s';
            }
            $tagdata = array();
            $letters = array('#', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($letters as $val) {
                $tagdata[$val] = array();
            }

            foreach (DB::fetch_all("select t.tid,t.tagname,t.initial,vt.hots from %t vt left join %t t on vt.tid = t.tid  where  $sql  order by t.initial , vt.hots DESC ", $param) as $value) {
                /*if(isset($searchtidnums[$value['tid']])) $value['num'] =$searchtidnums[$value['tid']];
                else $value['num'] = 0;*/
                $tagdata[$value['initial']][$value['tid']] = $value;
            }
        }

    } else {


        $param = array('pichome_vapp_tag', 'pichome_tag', 'pichome_tagrelation', $cid, $appid);

        $sql = ' ';
        if ($tagname) {
            $param[] = '%' . $tagname . '%';
            $sql .= ' and t.tagname like %s';
        }
        foreach (DB::fetch_all("select  t.tid,t.tagname,t.initial,vt.hots from %t vt left join %t t on vt.tid = t.tid left join %t tg on vt.tid = tg.tid 
        where tg.cid = %s and vt.appid = %s $sql  order by t.initial , vt.hots DESC ", $param) as $value) {
            /*if(isset($searchtidnums[$value['tid']])) $value['num'] =$searchtidnums[$value['tid']];
            else $value['num'] = 0;*/
            //$tagdata[$value['initial']][$value['tid']] = $value;
            $tagdata[$value['tid']] = $value;
        }

    }
    exit(json_encode(array('data' => $tagdata)));

}else{
    $arr = gettagcount($appid);
	$arr = json_encode($arr);
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    if($appid){
        $appdata  = C::t('pichome_vapp')->fetch($appid);
    }
    include template('librarylist/pc/page/tag');
    exit();
}

function gettagcount($appid){
    $allnum = DB::result_first("select count(*) from %t where appid = %s", array('pichome_vapp_tag', $appid));
    $nocatnum = DB::result_first("select count(vt.tid) from %t vt left join %t  tg on vt.tid = tg.tid 
	where  isnull(tg.tid) and vt.appid = %s", array('pichome_vapp_tag', 'pichome_tagrelation', $appid));
    $groupdata = C::t('pichome_taggroup')->fetch_tagcatandnum_by_pcid($appid);
    $arr = [];
    foreach ($groupdata as $key => $val) {
        $arr[] = array('cid' => $key, 'text' => $val['tagname'],'num'=>$val['num']);
    }
    return array('all'=>$allnum,'nocat'=>$nocatnum,'data'=>$arr);
}