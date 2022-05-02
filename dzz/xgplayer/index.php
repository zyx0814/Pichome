<?php 

if($_GET['operation']=='progress'){
	$id=intval($_GET['id']);
	$ff=C::t('video_record')->fetch($id);
	exit(json_encode($ff));
}elseif($_GET['operation']=='retry'){
	$id=intval($_GET['id']);
	$ff=C::t('video_record')->fetch($id);
	C::t('video_record')->update($id,array('status'=>0));
	exit(json_encode($ff));
}elseif($_GET['path']){
    if(!$rid = dzzdecode($_GET['path'],'',0)){
        exit('Access Denied');
    }
	/*$data=IO::getMeta($path);
	
	$pexts=array('mp3','mp4','webm','ogv','ogg','wav','m3u8','hls','mpg','mpeg');
	$zexts=array('m4v','m4a','avi','rm','rmvb','mkv','wmv','asf','f4v','vob','mts','m2ts','mpe','3gp','midi','wma','vqf','ra','aac','flac','ape','amr','aiff','au','flv');
	if(in_array($data['ext'],$zexts)){
		$ff=C::t('video_record')->fetch_by_aid($data['aid']);
		switch($ff['status']){
			case 2:
				$_GET['ext']=$ff['format'];
				$src=IO::getFileUri($ff['path']);
				break;
			case 0:case 1:
				dfsockopen($_G['localurl'] . 'misc.php?mod=createConvert&aid='.$ff['aid'], 0, '', '', false, '', 1);
				include template('progress');
				exit();
				break;
			case -1:
				include template('progress');
				exit();
				break;
		}
		
	}else{*/
    $rid = dzzdecode($_GET['path'],'',0);
    $src  = getglobal('siteurl') . 'index.php?mod=io&op=getImg&path=' . dzzencode($rid.'_3', '', 14400, 0);
    //$src=IO::getFileUri($fileurl);
	//}
}elseif($_GET['src']){
	$ismobile = helper_browser::ismobile();
	$src = urldecode($_GET['src']);
    //$src = str_replace('+',' ',urlencode($src));
    if(!isset($_GET['ext'])){
        $filedirpathinfo = pathinfo($src);
        $filedirextensionarr = explode('?', $filedirpathinfo['extension']);
        $_GET['ext'] = strtolower($filedirextensionarr[0]);
    }
}

include template('main');