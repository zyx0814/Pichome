<?php
if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}

global $_G;
$themeid = isset($_G['setting']['pichometheme']) ? intval($_G['setting']['pichometheme']):1;
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
$pagename = isset($_GET['pagename']) ? trim($_GET['pagename']):'';

if($operation == 'tagset'){
    $tagdatas = gettpltagdata($themeid,$pagename);
    if(submitcheck('submit')){
        $flag = isset($_GET['flag']) ? trim($_GET['flag']):'';
        $pagename = isset($_GET['pagename']) ? trim($_GET['pagename']):'';
        $tagtype = isset($_GET['type']) ? trim($_GET['type']):'';
        $id = DB::result_first("select id from %t where themeid = %d and tagflag = %s and pagename = %s",array('pichome_templatetag',$themeid,$flag,$pagename));

            $data = $_GET['data'];
            $conditiondata = $_GET['data'];
            if($tagtype == 'recommend')$data = getrecommenddata($conditiondata,$themeid);
            elseif($tagtype == 'introduce'){
                $odata = '';
                if($id){
                    $olddata = DB::result_first("select tagval from %t where id = %d",'pichome_templatetag',$id);
                    $odata = unserialize($olddata);
                }
                $data = getcontentdata($conditiondata,$odata);
            }
            else $data = $conditiondata;


            $setarr = [
                'themeid'=>$themeid,
                'tagflag'=>$flag,
                'pagename'=>$pagename,
                'tagtype'=>$tagtype,
                'tagval'=>serialize($data),
                'dateline'=>TIMESTAMP
            ];
            if($id){
                C::t('pichome_templatetag')->update($id,$setarr);
            }else{
                C::t('pichome_templatetag')->insert($setarr);
            }

        exit(json_encode(array('success'=>true)));
    }
}elseif($operation=='upload'){//上传图片
    include libfile( 'class/uploadhandler' );

    $options = array( 'accept_file_types' => '/\.(gif|jpe?g|png|svg)$/i',

        'upload_dir' => $_G[ 'setting' ][ 'attachdir' ] . 'cache/',

        'upload_url' => $_G[ 'setting' ][ 'attachurl' ] . 'cache/',

        'thumbnail' => array( 'max-width' => 40, 'max-height' => 40 ) );

    $upload_handler = new uploadhandler( $options );
    updatesession();
    exit();
}else{
    $tagdatas = [];
    $tagdata = gettpltagdata($themeid,$pagename);
	$themedata = getthemedata($themeid);
    $tagdataval = C::t('pichome_templatetag')->fetch_tag_bypagename($themeid,$pagename);
    foreach($tagdata as $k=>$v){
        if(isset($tagdataval[$k])){
            $v['val'] = $tagdataval[$k];
        }
        $tagdatas[$k] = $v;
    }
    foreach($themedata['singlepage'] as $v){
        if($v['flag'] == $pagename){
            $navtitle = $v['name'];
        }
    }
    //print_r($themedata);die;
	$tagdatas = json_encode($tagdatas);
	$lefsetdata = $themedata['singlepage'];
	include template('admin/pc/page/site/tag');
}
function gettpltagdata($themeid,$pagename){
    global $_G;
    $setdata =[];
    $themedata = $_G['setting']['pichomethemedata'][$themeid];
    $singletpltagdata = unserialize($themedata['themetag']);
    $setdata =  $singletpltagdata[$pagename];
    return $setdata;
}

function getcontentdata($data,$odata){
    global $naids;
    $naids= [];
   $data= preg_replace_callback('/path=(\w+)/',function($matchs){
       global $naids;
       if($aidstr = dzzdecode($matchs[1])){
           $aid = str_replace('attach::','',$aidstr);
           $naids[] = $aid;
           return 'path='.$matchs[1].'&amp;aflag='.$aidstr;
       }

    },$data);
   //旧数据copy数减一
   if($odata){
       preg_match_all('/attach::\d/',$data,$match);
       foreach($match[0] as $v){
           $oaid = str_replace('attach::','',$v);
           C::t('attachment')->delete_by_aid($oaid);
       }
   }
   C::t('attachment')->addcopy_by_aid($naids);
   return $data;

}

function getrecommenddata($conditiondata,$themeid){
    global $_G;
    $datas = [];
    foreach ($conditiondata as $k=>$v){
        //如果未指定栏目，视为所有栏目数据
        if($v['bannermultiple'] == 'false'){
            $settype = $_G['setting']['pichomethemedata'][$themeid]['themebanner'];
            //查询所有内容栏目
            $bannerdata =  C::t('pichome_banner')->fetch_contentbanner_by_themeid($themeid,$settype);
        }else{
            $bids = $v['banner'];
            $bannerdata= DB::fetch_all("select * from %t where id in (%n)",array('pichome_banner',$bids));
        }
        $sql = " select rid from %t  r ";
        $wheresql = " r.isdelete < 1";
        $params = ['pichome_resources'];
        //查询所有库id
        $vappids = [];
        foreach (DB::fetch_all("select appid,path,view from %t where isdelete = 0", array('pichome_vapp')) as $appval) {
            if (!IO::checkfileexists($appval['path'],1)) {
                continue;
            }
            $vappids[] = $appval['appid'];
        }

        $orsql = [];
        $para = [];
        //print_r($bannerdata);
        //查询栏目条件组合查询语句
        foreach($bannerdata as $val){
            $andsql = [];
            if($val['appids'] != '1'){
                $appid = explode(',',$val['appids']);
            }else{
                $appid = $vappids;
            }
            $andsql[]  = ' r.appid in(%n)';
            $para[] = $appid;

            if($val['ctype']){
                $bexts = explode(',', $val['typefilter']);
                $andsql[] =  '  r.ext in(%n)';
                $para[] = $bexts;
            }
            $orsql[] = '('.implode(' AND ',$andsql).')';
        }
        $orsqlstr = implode(' OR ',$orsql);
        $wheresql .= ' and '.$orsqlstr;
        $limitsql = "limit 0," . $v['num'];
        $ordersql = ' r.dateline desc ';
        switch ($v['type']){
            case 'hot':

                $hsql = "select SUBSTRING_INDEX(GROUP_CONCAT(v.rid),',',1) as rid,count(v.id) as num from %t v left join %t r on r.rid=v.rid ";

                   $hparams = ['pichome_views','pichome_resources'];
                   $hwheresql = $wheresql .' and (v.dateline < %d and v.dateline > %d)';
                if($v['condition'] == 'week'){
                    $hpara[] = strtotime('+1 days');
                    $hpara[] = strtotime ('-7 days');
                }else{
                    $hpara[] = strtotime('+1 days');
                    $hpara[] = strtotime ('-30 days');
                }
                   if(!empty($para)) $hpara = array_merge($para,$hpara);
                   $hparams = array_merge($hparams,$hpara);
                    foreach (DB::fetch_all(" $hsql where  $hwheresql   group by v.rid order by num desc $limitsql", $hparams) as $value) {
                        $rids[] = $value['rid'];
                    }
                    $hcount = count($rids);
                    if($hcount < $v['num']){
                        $limitsql = "limit 0," . intval($v['num'] - $hcount);
                        if (!empty($para)) $params = array_merge($params, $para);
                        foreach (DB::fetch_all("    $sql where  $wheresql   order by $ordersql $limitsql", $params) as $value) {
                            $rids[] = $value['rid'];
                        }
                    }
                    if (!empty($rids)) $data = C::t('pichome_resources')->getdatasbyrids($rids);
                    else $data = [];
                    $v['dataval'] = $data;
                break;
            case 'tag':

                    $tags = explode(',',$v['condition']);
                    $tids = [];
                    foreach(DB::fetch_all('select tid from %t where tagname in(%n)',array('pichome_tag',$tags)) as $tv){
                        $tids[] = $tv['tid'];
                    }
                    if($tids){
                        $tagval = explode(',', trim($_GET['tag']));
                        $tagwheresql = [];
                        foreach($tagval as $tagk=>$tagv){
                            $sql .= ' left join %t rt'.($tagk+1).' on rt'.($k+1).'.rid = r.rid and rt'.($tagk+1).'.tid = %d';
                            $params[] = 'pichome_resourcestag';
                            $tagwheresql[] = '  !isnull(rt'.($tagk+1).'.tid) ';
                            $paras[] = $tagv;
                        }

                        if(count($tagwheresql) > 1) $wheresql .= " and (" .implode(' or ',$tagwheresql).')';
                        elseif(count($tagwheresql)) $wheresql .= " and $tagwheresql[0] ";
                        if (!empty($para)) $params = array_merge($params, $para);
                        foreach (DB::fetch_all("    $sql where  $wheresql   order by $ordersql $limitsql", $params) as $value) {
                            $rids[] = $value['rid'];
                        }
                        if (!empty($rids)) $data = C::t('pichome_resources')->getdatasbyrids($rids);
                        else $data = [];
                        $v['dataval'] = $data;
                    }else{
                        $data = [];
                        $v['dataval'] = $data;
                    }
                break;
            case 'grade':
                $wheresql .= " and r.grade > %d ";
                $para[] = intval($v['condition']);
                if (!empty($para)) $params = array_merge($params, $para);
                foreach (DB::fetch_all("    $sql where  $wheresql   order by $ordersql $limitsql", $params) as $value) {
                    $rids[] = $value['rid'];
                }

                if (!empty($rids)) $data = C::t('pichome_resources')->getdatasbyrids($rids);
                else $data = [];
                $v['dataval'] = $data;
                break;
            default:
                if (!empty($para)) $params = array_merge($params, $para);
                foreach (DB::fetch_all("    $sql where  $wheresql   order by $ordersql $limitsql", $params) as $value) {
                    $rids[] = $value['rid'];
                }
                if (!empty($rids)) $data = C::t('pichome_resources')->getdatasbyrids($rids);
                else $data = [];
                $v['dataval'] = $data;
                break;
        }
        $datas[] = $v;
    }
    return $datas;
}