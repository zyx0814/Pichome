<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perpage = 1000;
$start = ($page-1)*$perpage;
$i=0;
foreach (DB::fetch_all("select r.rid,rt.tag,r.name,rt.desc,rt.link from %t r 
 left join %t rt on r.rid = rt.rid
 where 1 limit $start,$perpage",
        array('pichome_resources', 'pichome_resources_attr')) as $v) {
        //标签
        $searchval = $v['name'];
        $tids = explode(',',$v['tag']);
        foreach(DB::fetch_all("select tagname from %t where tid in(%n)",array('pichome_tag',$tids)) as $tag){
            $searchval .= $tag['tagname'];
        }
        foreach(DB::fetch_all("select annotation from %t where rid = %s",array('pichome_comments',$v['rid'])) as $comments){
            $searchval .= $comments['annotation'];
        }
		$searchval .=$v['desc'].$v['link'];
		$setarr['searchval'] = $searchval;
        C::t('pichome_resources_attr')->update($v['rid'], $setarr);
	$i++;
 }
 
if ($i) {
	$page++;
    $href = getglobal('localurl') . 'index.php?mod=pichome&op=updatesearchvaldata&page='.$page;
   include template('header_reload');
	echo "<script>";
	echo "location.href='$href';";
	echo "</script>";
    include template('footer_reload');
	exit();
} else {
    exit('success');
}


