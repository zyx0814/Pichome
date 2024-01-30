<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$overt = getglobal('setting/overt');
if(!$overt && !$overt = C::t('setting')->fetch('overt')){
    Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
}
global $_G;
$do = isset($_GET['do']) ? trim($_GET['do']):'';
$tid = isset($_GET['tid']) ? intval($_GET['tid']):0;
$gid = isset($_GET['gid']) ? intval($_GET['gid']):0;
//获取标签组数据
$gdata = C::t('#tab#tab_group')->fetch_by_gid($gid);
//记录哪个专辑过来得
$kid = isset($_GET['kid']) ? trim($_GET['kid']):0;
if($do == 'tabdetail'){//卡片详情页
    //获取tab数据
    $tabdata = [];
    //$tabstatus = 0;
        //获取字段数据
        $formlist=unserialize($gdata['formfiled']);
        $forms = array();
        $temp = array();
        foreach ( $formlist as $key => $val ) {
            if(!$val['status']) continue;
            foreach ( C::t( 'form_setting' )->fetch_all_conditions( $key ) as $value ) {
                $value['labelname']=lang($value['labelname']);
                $value[ 'checked' ] = $val;
                $value[ 'style' ] = $val['style'];
                $value[ 'tofolder' ] = $val['tofolder'];
                $value[ 'writable' ] = intval($val['writable']);
                $value[ 'status' ] = $val['status'];
                $temp[ $value[ 'flag' ] ] = $value;

            }
        }
        $sorts = array( 'name' ); //允许排序的字段
        //排序
        foreach ( $temp as $k => $v ) {
            if ( $k != 'name' && $temp[ $k ][ 'checked' ][ 'sort' ] > 0 )$sorts[] = $k;
            $forms[ $k ] = $temp[ $k ];
        }
        //获取tab信息及其属性值
        $tabdata = C::t('#tab#tab')->fetch($tid);
        $tabattr = C::t('#tab#tab_attr')->fetch_by_tid($tid);
        if($tabattr)$tabdata = array_merge($tabdata,$tabattr);
        $tabdata['catdata'] = C::t('#tab#tab_cat_relation')->fetch_catname_by_tid($tid);
        if($tabdata['icon']){
            $tabdata['icon'] = $_G['setting']['attachurl'].$tabdata['icon'];
        }else{
            $tabdata['icon'] = $gdata['defaultico'];
        }
        $tabdata['icotype'] = $gdata['icotype'];
        //将tab属性值对应字段信息
        foreach($forms as $key=>$val){
            if(isset($tabdata[$key])){
                $forms[$key]['values'] = $tabdata[$key];
            }
        }
        //获取字段分类信息再组织字段数据
        $filedcatdata = C::t('form_setting_filedcat')->fetch_all_by_tabgroupid($gid);
        $formlists = [];
        foreach($filedcatdata as $k=>$v){
            foreach($forms as $val){
                if($val['filedcat'] == $k){
                    $v['fileds'][] = $val;
                }
            }
            $formlists[] = $v;
        }
        $tabdata['forms'] = $formlists;
    //增加浏览次数
    if($tid){
        addTabviewStats($tid);
    }
    //获取各种类型文件数量
    $numdatas = ['image'=>0,'video'=>0,'audio'=>0,'document'=>0,'other'=>0];
    //获取图片类型文件数
    $numdatas['image'] = DB::result_first("select count(DISTINCT r.rid) from %t r 
left join %t rt on rt.rid = r.rid 
 where r.isdelete = 0 and rt.tid = %d and r.ext in(%n) ",array('pichome_resources','pichome_resourcestab',$tid,array_merge($Types['commonimage'],$Types['image'])));
    $numdatas['video'] = DB::result_first("select count(DISTINCT r.rid) from %t r 
left join %t rt on rt.rid = r.rid 
 where r.isdelete = 0 and rt.tid = %d and r.ext in(%n) ",array('pichome_resources','pichome_resourcestab',$tid,$Types['video']));
    $numdatas['audio'] = DB::result_first("select count(DISTINCT r.rid) from %t r 
left join %t rt on rt.rid = r.rid 
 where r.isdelete = 0 and rt.tid = %d and r.ext in(%n) ",array('pichome_resources','pichome_resourcestab',$tid,$Types['audio']));
    $numdatas['document'] = DB::result_first("select count(DISTINCT r.rid) from %t r 
left join %t rt on rt.rid = r.rid 
 where r.isdelete = 0 and rt.tid = %d and r.ext in(%n) ",array('pichome_resources','pichome_resourcestab',$tid,$Types['document']));
    $numdatas['other'] = DB::result_first("select count(DISTINCT r.rid) from %t r 
left join %t rt on rt.rid = r.rid 
 where r.isdelete = 0 and rt.tid = %d and r.ext not in(%n) ",array('pichome_resources','pichome_resourcestab',$tid,
        array_merge($Types['document'],$Types['commonimage'],$Types['image'],$Types['video'],$Types['document'],$Types['audio'])));
    exit(json_encode(array('tabdata'=>$tabdata,'numdata'=>$numdatas)));
}elseif($do == 'gettag'){//获取标签数据
    $tid = isset($_GET['tid']) ? intval($_GET['tid']):0;
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 10;//每页数量
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;//页码数
    $start = ($page - 1) * $perpage; //开始条数
    $limitsql = "limit $start,$perpage";
    $params= ['tab_tagnum','pichome_tag',$tid];
    $tagdata = [];
    foreach(DB::fetch_all("select t.tid,t.tagname,tn.nums from %t tn left join %t t on t.tid = tn.tid where tn.tabid = %d order by tn.nums desc $limitsql",$params) as $v){
        $tagdata[$v['tid']]['tagname'] = $v['tagname'];
        $tagdata[$v['tid']]['num'] = $v['nums'];
        $tagdata[$v['tid']]['tid'] = $v['tid'];
    }
    //按照关联文件数进行排序
    usort($tagdata, function($a, $b) {
        return $b['nums'] - $b['nums'];
    });
    $next = false;
    if(count($tagdata) >= $perpage){
        $next = true;
    }
    exit(json_encode(array('tagdata'=>$tagdata,'next'=>$next)));
}elseif($do == 'getoverviewfile'){//获取概览文件
    $tid = isset($_GET['tid']) ? intval($_GET['tid']):0;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']):6;
    $limitsql = "limit 0,$limit";
    //获取各种类型文件数量
    $datas = ['image'=>[],'video'=>[],'audio'=>[],'document'=>[]];
    //获取图片文件
    $imagerids = [];
    foreach(DB::fetch_all("select DISTINCT r.rid from %t r 
left join %t rt on rt.rid = r.rid where r.isdelete = 0 and rt.tid = %d and r.ext in(%n) $limitsql",
        array('pichome_resources','pichome_resourcestab',$tid,array_merge($Types['commonimage'],$Types['image']))) as $v){
        $imagerids[] = $v['rid'];
    }

    if (!empty($imagerids)) $datas['image'] = C::t('pichome_resources')->getdatasbyrids($imagerids);
    //获取文档文件
    $videorids = [];
    foreach(DB::fetch_all("select DISTINCT r.rid from %t r 
left join %t rt on rt.rid = r.rid where r.isdelete = 0 and rt.tid = %d and r.ext in(%n) $limitsql",
        array('pichome_resources','pichome_resourcestab',$tid,$Types['video'])) as $v){
        $videorids[] = $v['rid'];
    }
    if (!empty($videorids)) $datas['video'] = C::t('pichome_resources')->getdatasbyrids($videorids);
    //获取音频文件
    //获取文档文件
    $audiorids = [];
    foreach(DB::fetch_all("select DISTINCT r.rid from %t r 
left join %t rt on rt.rid = r.rid where r.isdelete = 0 and rt.tid = %d and r.ext in(%n) $limitsql",
        array('pichome_resources','pichome_resourcestab',$tid,$Types['audio'])) as $v){
        $audiorids[] = $v['rid'];
    }
    if (!empty($audiorids)) $datas['audio'] = C::t('pichome_resources')->getdatasbyrids($audiorids);
   //获取文档文件
    $documentrids = [];
    foreach(DB::fetch_all("select DISTINCT r.rid from %t r 
left join %t rt on rt.rid = r.rid where r.isdelete = 0 and rt.tid = %d and r.ext in(%n) $limitsql",
        array('pichome_resources','pichome_resourcestab',$tid,$Types['document'])) as $v){
        $documentrids[] = $v['rid'];
    }
    if (!empty($documentrids)) $datas['document'] = C::t('pichome_resources')->getdatasbyrids($documentrids);
    exit(json_encode(array('data'=>$datas)));
}elseif($do == 'getfilelist'){
    $tid = isset($_GET['tid']) ? intval($_GET['tid']):0;
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 10;//每页数量
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;//页码数
    $start = ($page - 1) * $perpage; //开始条数
    $limitsql = "limit $start,$perpage";
    $selectsql = " DISTINCT r.rid ";
    $sql = " from %t r 
left join %t rb on rb.rid = r.rid ";
    $params = ['pichome_resources','pichome_resourcestab'];
    $wheresql = 'where r.isdelete = 0 and rb.tid = %d ';
    $para[] = $tid;
    //标签条件
    if(isset($_GET['tagid'])){
        $tagid  = explode(',',$_GET['tagid']);
        $sql .= ' left join %t rt on r.rid = rt.rid ';
        $params[] = 'pichome_resourcestag';
       $wheresql .= ' and rt.tid in(%n) ';
       $para[] = $tagid;
    }
    //后缀条件
    if(isset($_GET['type'])){
        $filetype = trim($_GET['type']);
        $numdatas = ['image'=>0,'video'=>0,'audio'=>0,'document'=>0,'other'=>0];
        switch ($filetype){
            case 'image':
                $wheresql .= ' and r.ext in(%n) ';
                $para[] = array_merge($Types['commonimage'],$Types['image']);
                break;
            case 'video':
                $wheresql .= ' and r.ext in(%n) ';
                $para[] = $Types['video'];
                break;
            case 'audio':
                $wheresql .= ' and r.ext in(%n) ';
                $para[] = $Types['audio'];
                break;
            case 'document':
                $wheresql .= ' and r.ext in(%n) ';
                $para[] = $Types['document'];
                break;
            case 'other':
                $wheresql .= ' and r.ext not in(%n) ';
                $para[] = array_merge($Types['document'],$Types['commonimage'],$Types['image'],$Types['video'],$Types['document'],$Types['audio']);
                break;

        }
    }
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
    $order = isset($_GET['order']) ? intval($_GET['order']):0;
    if(!$order){//默认最新
       $ordersql =  ' r.btime desc,r.rid asc' ;
    }else{//最热文件
        $sql .= ' left join %t v on r.rid=v.idval ';
        $selectsql .= " ,v.nums ";
        $params[] = 'views';
        $ordersql = '  v.nums desc ,r.rid asc ';

    }
    if (!empty($para)) $params = array_merge($params, $para);
    foreach (DB::fetch_all("select $selectsql  $sql   $wheresql  order by $ordersql $limitsql", $params) as $value) {
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
    //增加统计关键词次数
    if($rids && $keyword){
        $statskeywords = array();
        $arr1 = explode('+', $keyword);
        foreach($arr1 as $v){
            $arr2 = explode(' ', $value1);
            foreach($arr2 as $kval){
                addKeywordStats($kval);

            }
        }

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
            'keyword' => $keyword
        )
    );
    updatesession();
    exit(json_encode(array('data' => $return)));
}elseif($do == 'addviews'){//增加浏览数
 $tid = isset($_GET['tid']) ? intval($_GET['tid']):0;
 if($tid) C::t('#tab#tab')->addviews_by_tid($tid);
 exit(json_encode(array('success'=>true)));
}else{
    $bannerdata = C::t('pichome_banner')->getbannerlist(0,1);
    $bannerdata = json_encode($bannerdata);

    $collectlis = Hook::listen('collectlist');
    $collectdata = [];
    if(isset($collectlis[0])){
		if(isset($collectlis[0]['x']) && $collectlis[0]['x']){
			foreach($collectlis[0]['x'] as $value){
				$collectdata[] =['name'=>$value['title'],'url'=>'index.php?mod=fileCollect&op=upload&cid='.$value['cid']];
			}
			// $collectdata[] = ['name'=>'我提交的','url'=>'index.php?mod=fileCollect&type=1'];
		}
		// if(isset($collectlis[0]['m']) && $collectlis[0]['m']){
		// 	$collectdata[] = ['name'=>'我审核的','url'=>'index.php?mod=fileCollect&type=2'];
		// }
    }
    include template('page/information');
    exit();
}

