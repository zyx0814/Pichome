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
    //$rid = dzzdecode($_GET['path'],'',0);
    if(strpos($rid, 'attach::') === 0){
        $resourcesdata = C::t('attachment')->fetch(intval(str_replace('attach::', '', $path)));
        $resourcesdata['iswebsitefile'] = 1;
    }else{
        $resourcesdata = C::t('pichome_resources')->fetch_data_by_rid($rid);
    }


    $_GET['ext'] = strtolower($resourcesdata['ext']);
    $videosatus = 0;
    $cloudvideostatus = 0;
    //获取音视频转换开启状态
    if($resourcesdata['remoteid'] && $resourcesdata['remoteid']!=1){
        $clouddata = DB::fetch_first("select * from %t where id = %d",array('connect_storage',$resourcesdata['remoteid']));
        $videostatus = $cloudvideostatus = $clouddata['mediastatus'];
    }
    if(!$videostatus){
        $app = C::t('app_market')->fetch_by_identifier('ffmpeg', 'dzz');
        $appextra = unserialize($app['extra']);
        $videostatus = $appextra['status'];
    }
    $pexts=  getglobal('config/pichomeplayermediaext') ? explode(',', getglobal('config/pichomeplayermediaext')):array('mov','mp3','mp4','webm','ogv','ogg','wav','m3u8','hls','mpg','mpeg');
    if(!$videostatus && !in_array($resourcesdata['ext'],$pexts)){
        $msg = '该媒体文件不能直接播放，且当前未安装支持转码应用或未开启转码支持';
        include template('progress');
        exit();
    }
    if(!in_array($resourcesdata['ext'],$pexts)){
        if($resourcesdata['rid']){
            $ff=C::t('video_record')->fetch_by_rid($resourcesdata['rid']);
        }else{
            $ff=C::t('video_record')->fetch_by_aid($resourcesdata['aid']);
        }
        //如果没有转码记录生成记录
        if(!$ff){
            if ('audio' == getTypeByExt($resourcesdata['ext'])) {
                $ext = 'mp3';
            } else {
                $ext = 'mp4';
            }
            $setarr = ['rid' => $resourcesdata['rid'], 'dateline' => TIMESTAMP,'format'=>$ext,'videoquality'=>1];
            $setarr['aid']= $resourcesdata['aid'] ? $resourcesdata['aid']:0;
            //如果是云存储状态，当前默认腾讯云
            if($cloudvideostatus){
                $setarr['ctype'] = 2;
                $ff = C::t('video_record')->insert_data($setarr);
                if($ff['status'] == 0){
                    dfsockopen($_G['localurl'] . 'index.php?mod=qcos&op=convert&id='.$ff['id'], 0, '', '', false, '', 1);
                }

            }else{
                $setarr['ctype'] = 1;
                $ff = C::t('video_record')->insert_data($setarr);
                if($ff['status'] == 0){
                    dfsockopen($_G['localurl'] . 'index.php?mod=ffmpeg&op=convert&id='.$ff['id'], 0, '', '', false, '', 1);
                }
            }
        }
        switch($ff['status']){
            case 2:
                $_GET['ext']=$ff['format'];
                $bz = io_remote::getBzByRemoteid($ff['remoteid']);
                $src=IO::getFileuri($bz.$ff['path']);
                break;
            case 0:
                if($videostatus) {
                    if($ff['ctype'] == 1){
                        dfsockopen($_G['localurl'] . 'index.php?mod=ffmpeg&op=convert&id='.$ff['id'], 0, '', '', false, '', 1);
                    }elseif($ff['ctype'] == 2){
                        dfsockopen($_G['localurl'] . 'index.php?mod=qcos&op=convert&id='.$ff['id'], 0, '', '', false, '', 1);
                    }else{
                        dfsockopen($_G['localurl'] . 'misc.php?mod=convert&id='.$ff['id'], 0, '', '', false, '', 1);
                    }
                    $ff['status'] = 1;
                }
                else $msg = '该媒体文件不能直接播放，且当前未安装支持转码应用或未开启转码支持，如需播放请联系管理员安装或开启对应应用';
                include template('progress');
                exit();
                break;
            case 1:
                if($videostatus) {
                    if($ff['ctype'] == 1){
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
        if(!$resourcesdata['iswebsitefile'] && $resourcesdata['bz'] == 'dzz::'){
            $src  = getglobal('siteurl') . 'index.php?mod=io&op=getStream&path=' . dzzencode($rid.'_3', '', 14400, 0);
        }else{
            $src=IO::getFileuri($resourcesdata['path']);
        }

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