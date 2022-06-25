<?php
    
if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}
Hook::listen('adminlogin');
include_once libfile( 'function/cache' );
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if (isset($_G['setting'])) $setting = $_G['setting'];
else  $setting = C::t('setting')->fetch_all();
if($do == 'addspace'){
    $bz=$_GET['bz'];
    IO::authorize($bz);
   exit();
}elseif($do == 'getstoragelist'){
    $spacelist = C::t('connect_storage')->fetch_all_space();
	exit(json_encode($spacelist));
}elseif($do == 'deletespace'){
    $id = isset($_GET['id']) ? intval($_GET['id']):0;
    if(!$id) exit(json_encode(array('error'=>true,'msg'=>'参数非法')));
    $connectdata = C::t('connect_storage')->fetch($id);
    $bzpath = $connectdata['bz'].':'.$id.':';
    if(DB::result_first("select count(appid) from %t where `path` like %s and isdelete < 1",array('pichome_vapp',$bzpath.'%'))){
        exit(json_encode(array('error'=>true,'msg'=>'有使用此存储位置的库，请先删除库后再执行此操作')));
    }else{
        C::t('connect_storage')->delete($id);
        exit(json_encode(array('success'=>true)));
    }
}elseif($do == 'getsettingdata'){
    $id = isset($_GET['id']) ? intval($_GET['id']):0;
    $connectdata = C::t('connect_storage')->fetch($id);
    if($connectdata['bz'] == 'dzz'){
        $connectdata['gdstatus'] = extension_loaded('GD') ? 1:0;
        $connectdata['imagickstatus'] = extension_loaded('imagick') ? 1:0;
        $ffmpegbinaries =(getglobal('config/pichomeffmpegposition')) ? getglobal('config/pichomeffmpegposition'):(strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffmpeg.exe' : '/usr/bin/ffmpeg');
        $ffprobebinaries = (getglobal('config/pichomeffprobeposition')) ? (getglobal('config/pichomeffprobeposition')):(strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffprobe.exe' : '/usr/bin/ffprobe');
        $connectdata['mediastate'] = 1;
        if(!function_exists('proc_open') || !is_file($ffmpegbinaries) || !is_file($ffprobebinaries)){
            $connectdata['mediastate'] = 0;
        }
    }elseif($connectdata['bz'] == 'QCOS'){
        $hostarr = explode(':',$connectdata['hostname']);
        $config = [
            'secretId' => trim($connectdata['access_id']),
            'secretKey' => dzzdecode($connectdata['access_key'], 'QCOS'),
            'region' => $hostarr[1],
            'schema' => $hostarr[0],
            'bucket'=>trim($connectdata['bucket']),
        ];
        include_once DZZ_ROOT.'dzz'.BS.'qcos'.BS.'class'.BS.'class_video.php';
        $video = new \video($config);

        $connectdata['mediastate'] = $video->check_videobucket();

        $connectdata['docstate'] = $video->check_docbucket();

        if(!$connectdata['mediastate'] && $connectdata['mediastatus']) C::t('connect_storage')->update($id,array('mediastatus'=>0));
        if(!$connectdata['docstate'] && $connectdata['docstatus']) C::t('connect_storage')->update($id,array('docstatus'=>0));
    }
    exit(json_encode($connectdata));
}elseif($do == 'videosetting'){
    $id = isset($_GET['id']) ? intval($_GET['id']):0;
    $setarr['mediastatus'] = intval($_GET['mediastatus']);
    $setarr['videoquality'] = intval($_GET['videoquality']);
    $connectdata = C::t('connect_storage')->fetch($id);
    if($connectdata['bz'] == 'dzz') {
        C::t('connect_storage')->update($id,$setarr);
    }elseif($connectdata['bz'] == 'QCOS'){
        if($setarr['mediastatus']){
            $hostarr = explode(':',$connectdata['hostname']);
            $config = [
                'secretId' => trim($connectdata['access_id']),
                'secretKey' => dzzdecode($connectdata['access_key'], 'QCOS'),
                'region' => $hostarr[1],
                'schema' => $hostarr[0],
                'bucket'=>trim($connectdata['bucket']),
            ];
            include_once DZZ_ROOT.'dzz'.BS.'qcos'.BS.'class'.BS.'class_video.php';
            $video = new \video($config);
            if($video->check_videobucket()) {
                C::t('connect_storage')->update($id,$setarr);
               // dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=convert', 0, '', '', false, '', 1);
            }else {
                exit(json_encode(array('error'=>true,'msg'=>'请检查存储桶是否开启媒体处理')));
            }
        }else{
            C::t('connect_storage')->update($id,$setarr);
        }
       
    }
	exit(json_encode(array('success'=>true)));
	
}elseif($do == 'docsetting'){
    $id = isset($_GET['id']) ? intval($_GET['id']):0;
    $setarr['docstatus'] = intval($_GET['docstatus']);
    $connectdata = C::t('connect_storage')->fetch($id);
    if($connectdata['bz'] == 'dzz') {
        $settingnew['onlyofficesetting']['onlyofficeurl'] = trim($_GET['onlyofficeurl']);
        $settingnew['onlyofficesetting']['onlyofficedocurl'] = trim($_GET['onlyofficedocurl']);
        if($setarr['docstatus']){
            $onlyDocumentUrl=rtrim(str_replace('web-apps/apps/api/documents/api.js','',$settingnew['onlyofficesetting']['onlyofficeurl']),'/').'/web-apps/apps/api/documents/api.js';

            if(curl_file_get_contents($onlyDocumentUrl) === false){
                exit(json_encode(array('error'=>true,'msg'=>'请检查地址是否可用')));
            }else{
                fclose($handle);
            }
        }
        updatesetting($setting, $settingnew);
        C::t('connect_storage')->update($id,$setarr);
    } elseif($connectdata['bz'] == 'QCOS'){
        if($setarr['docstatus']){
            $hostarr = explode(':',$connectdata['hostname']);
            $config = [
                'secretId' => trim($connectdata['access_id']),
                'secretKey' => dzzdecode($connectdata['access_key'], 'QCOS'),
                'region' => $hostarr[1],
                'schema' => $hostarr[0],
                'bucket'=>trim($connectdata['bucket']),
            ];
            include_once DZZ_ROOT.'dzz'.BS.'qcos'.BS.'class'.BS.'class_video.php';
            $video = new \video($config);
            if($video->check_docbucket()) {
                C::t('connect_storage')->update($id,$setarr);
            }else {
                exit(json_encode(array('error'=>true,'msg'=>'请检查存储桶是否开启文档处理')));
            }
        }else{
            C::t('connect_storage')->update($id,$setarr);
        }
    }
	exit(json_encode(array('success'=>true)));
}elseif($do == 'imagesetting'){
    $id = isset($_GET['id']) ? intval($_GET['id']):0;
    $setarr['imagestatus'] = intval($_GET['imagestatus']);
    $connectdata = C::t('connect_storage')->fetch($id);
    if($connectdata['bz'] == 'dzz'){
        $settingnew['imagelib'] = trim($_GET['imagelib']);
        updatesetting($setting, $settingnew);
    }
    C::t('connect_storage')->update($id,$setarr);

    exit(json_encode(array('success'=>true)));

}else{
	
	$storagelist = C::t('connect')->fetch_all_by_available();
}
function updatesetting($setting, $settingnew){
    $updatecache = false;
    $settings = array();
    $updatethumb = false;
    foreach ($settingnew as $key => $val) {
        if ($setting[$key] != $val) {
            $updatecache = TRUE;
            if (in_array($key, array('timeoffset', 'regstatus', 'oltimespan', 'seccodestatus'))) {
                $val = (float)$val;
            }
            $settings[$key] = $val;
        }
    }
    if ($settings) {
        C::t('setting')->update_batch($settings);
    }
    if ($updatecache) {
        updatecache('setting');
    }
    return true;
}
$theme = GetThemeColor();
include template('pc/page/adminstorage');
