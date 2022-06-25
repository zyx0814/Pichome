<?php

if($_GET['operation']=='progress'){
    $id=intval($_GET['id']);
    $ff=C::t('video_record')->fetch($id);
    if($ff['id']){
        exit(json_encode($ff));
    }else{
        exit(json_encode(array('error'=>true)));
    }

}elseif($_GET['operation']=='retry'){
    $id=intval($_GET['id']);
    $ff=C::t('video_record')->fetch($id);
    C::t('video_record')->update($id,array('status'=>0));
    exit(json_encode($ff));
}elseif($_GET['path']){
    if(!$rid = dzzdecode($_GET['path'],'',0)){
        exit('Access Denied');
    }
    global $_G;
    $rid = dzzdecode($_GET['path'],'',0);
    $resourcesdata = C::t('pichome_resources')->fetch($rid);
    $appdata = C::t('pichome_vapp')->fetch($resourcesdata['appid']);
    if(strpos($appdata['path'],':') === false){
        $bz = 'dzz';
    }else{
        $patharr = explode(':', $appdata['path']);
        $bz = $patharr[0];
        $did = $patharr[1];

    }
    if(!is_numeric($did) || $did < 2){
        $bz = 'dzz';
    }
    if($bz == 'dzz'){
        $videostatus = DB::result_first('select mediastatus from %t where bz = %s',array('connect_storage',$bz));
    }else{
        $videostatus = DB::result_first('select mediastatus from %t where id = %d',array('connect_storage',$did));
    }
    $_GET['ext'] = strtolower($resourcesdata['ext']);
    $pexts=  getglobal('config/pichomeplayermediaext') ? explode(',', getglobal('config/pichomeplayermediaext')):array('mp3','mp4','webm','ogv','ogg','wav','m3u8','hls','mpg','mpeg');
    if(!in_array($resourcesdata['ext'],$pexts)){
        $ff=C::t('video_record')->fetch_by_rid($rid);
        if(!$ff){
            $msg = '该媒体文件不能直接播放，且当前存储位置不支持当前格式文件转码';
            include template('progress');
            exit();
        }
        switch($ff['status']){
            case 2:
                $_GET['ext']=$ff['format'];
                $src=IO::getStream($ff['path']);
                break;
            case 0:
                if($videostatus) {
                    if($ff['ctype'] == 0){
                        dfsockopen($_G['localurl'] . 'index.php?mod=ffmpeg&op=convert&id='.$ff['id'], 0, '', '', false, '', 1);
                    }elseif($ff['ctype'] == 2){
                        dfsockopen($_G['localurl'] . 'index.php?mod=qcos&op=convert&id='.$ff['id'], 0, '', '', false, '', 1);
                    }
                    $ff['status'] = 1;
                }
                else $msg = '该媒体文件不能直接播放，当前存储位置媒体处理未开启，如需播放请联系管理员开启媒体处理';
                include template('progress');
                exit();
                break;
            case 1:
                if($videostatus) {
                    if($ff['ctype'] == 0){
                        dfsockopen($_G['localurl'] . 'index.php?mod=ffmpeg&op=convert&id='.$ff['id'], 0, '', '', false, '', 1);
                    }elseif($ff['ctype'] == 2){
                        dfsockopen($_G['localurl'] . 'index.php?mod=pichome&op=getConvertStatus', 0, '', '', false, '', 1);
                    }
                }
                include template('progress');
                exit();
                break;
            case -1:
                include template('progress');
                exit();
                break;


        }

    }else{
        $src  = getglobal('siteurl') . 'index.php?mod=io&op=getStream&path=' . dzzencode($rid.'_3', '', 14400, 0);
    }


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