<?php

if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}
Hook::listen('adminlogin');
include_once libfile('function/cache');
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if (isset($_G['setting'])) $setting = $_G['setting'];
else  $setting = C::t('setting')->fetch_all();
if ($do == 'addspace') {
    $bz = $_GET['bz'];
    IO::authorize($bz);
    exit();
} elseif ($do == 'getstoragelist') {
    $spacelist = C::t('connect_storage')->fetch_all_space();
    exit(json_encode($spacelist));
} elseif ($do == 'deletespace') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) exit(json_encode(array('success' => false, 'msg' => '参数非法')));
    $connectdata = C::t('connect_storage')->fetch($id);
    $bzpath = $connectdata['bz'] . ':' . $id . ':';
    if (DB::result_first("select count(appid) from %t where `path` like %s and isdelete < 1", array('pichome_vapp', $bzpath . '%'))) {
        exit(json_encode(array('success' => false, 'msg' => '有使用此存储位置的库，请先删除库后再执行此操作')));
    } elseif (DB::result_first("select count(aid) from %t where remote = %d", array('attachment', $id))) {
        exit(json_encode(array('success' => false, 'msg' => '站点有文件在当前存储位置，请删除后再执行此操作')));
    } else {
        C::t('connect_storage')->delete($id);
        exit(json_encode(array('success' => true)));
    }
} elseif ($do == 'setdefault') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) exit(json_encode(array('success' => false)));
    $space = C::t('connect_storage')->fetch($id);
    if ($space['hostname']) $hostdataarr = explode(':', $space['hostname']);
    else $hostdataarr = [];
    $defaultspacesettingdata = [
        'bucket' => $space['bucket'],
        'bz' => $space['bz'],
        'remoteid' => $space['id'],
        'region' => ($space['bz'] == 'ALIOSS') ? $space['hostname'] : ($hostdataarr[1] ? $hostdataarr[1] : ''),
        'did' => $space['id'],
        'host' => $space['host'],
    ];
    if (DB::update('connect_storage', ['isdefault' => 1], 'id =' . $id)) {
        DB::update('connect_storage', ['isdefault' => 0], 'id !=' . $id);
        C::t('setting')->update('defaultspacesetting', $defaultspacesettingdata);
        updatecache('setting');
    }
    exit(json_encode(array('success' => true)));
} elseif ($do == 'getsettingdata') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $connectdata = C::t('connect_storage')->fetch($id);
    if ($connectdata['bz'] == 'dzz') {
        $connectdata['gdstatus'] = extension_loaded('GD') ? 1 : 0;
        $connectdata['imagickstatus'] = extension_loaded('imagick') ? 1 : 0;
        $ffmpegbinaries = (getglobal('config/pichomeffmpegposition')) ? getglobal('config/pichomeffmpegposition') : (strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffmpeg.exe' : '/usr/bin/ffmpeg');
        $ffprobebinaries = (getglobal('config/pichomeffprobeposition')) ? (getglobal('config/pichomeffprobeposition')) : (strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffprobe.exe' : '/usr/bin/ffprobe');
        $connectdata['mediastate'] = 1;
        if (!function_exists('proc_open') || !is_executable($ffmpegbinaries) || !is_executable($ffprobebinaries)) {
            $connectdata['mediastate'] = 0;
        }
        if ($connectdata['docstatus']) {
            $app = C::t('app_market')->fetch_by_identifier('onlyoffice_view', 'dzz');
            $connectdata['officedata'] = unserialize($app['extra']);
        }
        if ($connectdata['imagestatus']) {
            $connectdata['imagelib'] = getglobal('setting/imagelib') ? 'imagick' : 'gd';
        }

    } elseif ($connectdata['bz'] == 'QCOS') {
        $hostarr = explode(':', $connectdata['hostname']);
        $config = [
            'secretId' => trim($connectdata['access_id']),
            'secretKey' => dzzdecode($connectdata['access_key'], 'QCOS'),
            'region' => $hostarr[1],
            'schema' => $hostarr[0],
            'bucket' => trim($connectdata['bucket']),
        ];
        include_once DZZ_ROOT . 'dzz' . BS . 'qcos' . BS . 'class' . BS . 'class_video.php';
        $video = new \video($config);

        $connectdata['mediastate'] = $video->check_videobucket();

        $connectdata['docstate'] = $video->check_docbucket();

        if (!$connectdata['mediastate'] && $connectdata['mediastatus']) C::t('connect_storage')->update($id, array('mediastatus' => 0));
        if (!$connectdata['docstate'] && $connectdata['docstatus']) C::t('connect_storage')->update($id, array('docstatus' => 0));
    }
    exit(json_encode($connectdata));
} elseif ($do == 'videosetting') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $setarr['mediastatus'] = intval($_GET['mediastatus']);
    $setarr['videoquality'] = intval($_GET['videoquality']);
    $connectdata = C::t('connect_storage')->fetch($id);
    if ($connectdata['bz'] == 'dzz') {
        //获取ffmpeg应用信息
        $app = C::t('app_market')->fetch_by_identifier('ffmpeg', 'dzz');
        $appextra = unserialize($app['extra']);
        $appextra['status'] = $setarr['mediastatus'];
        if (C::t("app_market")->update($app['appid'], array("extra" => serialize($appextra)))) {
            C::t('connect_storage')->update($id, $setarr);
            updateMediaStatus('dzz::', $setarr['mediastatus']);
        } else {
            exit(json_encode(array('error' => true, 'msg' => 'ffmpeg开启失败')));
        }
    } elseif ($connectdata['bz'] == 'QCOS') {
        if ($setarr['mediastatus']) {
            $hostarr = explode(':', $connectdata['hostname']);
            $config = [
                'secretId' => trim($connectdata['access_id']),
                'secretKey' => dzzdecode($connectdata['access_key'], 'QCOS'),
                'region' => $hostarr[1],
                'schema' => $hostarr[0],
                'bucket' => trim($connectdata['bucket']),
            ];
            include_once DZZ_ROOT . 'dzz' . BS . 'qcos' . BS . 'class' . BS . 'class_video.php';
            $video = new \video($config);
            if ($video->check_videobucket()) {
                C::t('connect_storage')->update($id, $setarr);
                updateMediaStatus('QCOS:' . $id . ':', $setarr['mediastatus']);
                // dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=convert', 0, '', '', false, '', 1);
            } else {
                exit(json_encode(array('error' => true, 'msg' => '请检查存储桶是否开启媒体处理')));
            }
        } else {
            C::t('connect_storage')->update($id, $setarr);
            updateMediaStatus('QCOS:' . $id . ':', $setarr['mediastatus']);
        }

    }
    exit(json_encode(array('success' => true)));

} elseif ($do == 'docsetting') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $setarr['docstatus'] = intval($_GET['docstatus']);
    $connectdata = C::t('connect_storage')->fetch($id);
    if ($connectdata['bz'] == 'dzz') {
        $app = C::t('app_market')->fetch_by_identifier('onlyoffice_view', 'dzz');
        $appextra = unserialize($app['extra']);
        $extra["DocumentUrl"] = trim($_GET['onlyofficeurl']);
        $extra["FileUrl"] = $_GET['fileurl'] ? trim($_GET['fileurl']) : '';
        $extra["exts"] = $_GET['exts'] ? trim($_GET['exts']) : '';
        $extra["secret"] = $_GET['secret'] ? trim($_GET['secret']) : '';
        C::t("app_market")->update($app['appid'], array("extra" => serialize($extra)));
        if ($setarr['docstatus']) {
            $onlyDocumentUrl = rtrim(str_replace('web-apps/apps/api/documents/api.js', '', $extra["DocumentUrl"]), '/') . '/web-apps/apps/api/documents/api.js';
            C::t('app_market')->update_by_identifier($app['appid'], ['available' => 1]);
        } else {
            C::t('app_market')->update_by_identifier($app['appid'], ['available' => 0]);
        }
        //updatesetting($setting, $settingnew);
        C::t('connect_storage')->update($id, $setarr);
        updateDocStatus('dzz::', $setarr['docstatus']);
    } elseif ($connectdata['bz'] == 'QCOS') {
        if ($setarr['docstatus']) {
            $hostarr = explode(':', $connectdata['hostname']);
            $config = [
                'secretId' => trim($connectdata['access_id']),
                'secretKey' => dzzdecode($connectdata['access_key'], 'QCOS'),
                'region' => $hostarr[1],
                'schema' => $hostarr[0],
                'bucket' => trim($connectdata['bucket']),
            ];
            include_once DZZ_ROOT . 'dzz' . BS . 'qcos' . BS . 'class' . BS . 'class_video.php';
            $video = new \video($config);
            if ($video->check_docbucket()) {
                C::t('connect_storage')->update($id, $setarr);
                updateDocStatus('QCOS:' . $id . ':', $setarr['docstatus']);
            } else {
                exit(json_encode(array('error' => true, 'msg' => '请检查存储桶是否开启文档处理')));
            }
        } else {

            C::t('connect_storage')->update($id, $setarr);
            updateDocStatus('QCOS:' . $id . ':', $setarr['docstatus']);
        }
    }
    exit(json_encode(array('success' => true)));
} elseif ($do == 'imagesetting') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $setarr['imagestatus'] = intval($_GET['imagestatus']);
    $connectdata = C::t('connect_storage')->fetch($id);
    if ($connectdata['bz'] == 'dzz') {
        $settingnew['imagelib'] = (trim($_GET['imagelib']) == 'gd') ? 0 : 1;
        updatesetting($setting, $settingnew);
        updateThumbStatus('dzz::', $setarr['imagestatus']);
    } else {
        updateThumbStatus('QCOS:' . $id . ':', $setarr['imagestatus']);
    }
    C::t('connect_storage')->update($id, $setarr);

    exit(json_encode(array('success' => true)));

} else {

    $storagelist = C::t('connect')->fetch_all_by_available();
}
$themeid = isset($_G['setting']['pichometheme']) ? intval($_G['setting']['pichometheme']) : 1;
$themedata = getthemedata($themeid);
$lefsetdata = $themedata['singlepage'];
function updatesetting($setting, $settingnew)
{
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

function updateMediaStatus($k, $status)
{
    $cachename = 'PICHOMECONVERTSTATUS';
    $convertstatus = C::t('cache')->fetch_cachedata_by_cachename($cachename);
    if (!$convertstatus) {
        $convertstatus = [];
        $app = C::t('app_market')->fetch_by_identifier('ffmpeg', 'dzz');
        $appextra = unserialize($app['extra']);
        $convertstatus['dzz::'] = $appextra['status'];
        foreach (DB::fetch_all("select id,bz,mediastatus from %t where 1", array('connect_storage')) as $v) {
            if ($v['bz'] == 'dzz') continue;
            $key = $v['bz'] . ':' . $v['id'] . ':';
            $convertstatus[$key] = intval($v['mediastatus']);
        }
    }
    $convertstatus[$k] = $status;
    $setarr = ['cachekey' => $cachename, 'cachevalue' => serialize($convertstatus), 'dateline' => time()];
    C::t('cache')->insert_cachedata_by_cachename($setarr);
}

function updateDocStatus($k, $status)
{
    $cachename = 'PICHOMEDOCSTATUS';
    $convertstatus = C::t('cache')->fetch_cachedata_by_cachename($cachename);
    if (!$convertstatus) {
        $convertstatus = [];
        $app = C::t('app_market')->fetch_by_identifier('onlyoffice_view', 'dzz');
        $appextra = unserialize($app['extra']);
        $convertstatus['dzz::'] = $appextra['status'];
        foreach (DB::fetch_all("select id,bz,docstatus from %t where 1", array('connect_storage')) as $v) {
            if ($v['bz'] == 'dzz') continue;
            $key = $v['bz'] . ':' . $v['id'] . ':';
            $convertstatus[$key] = intval($v['docstatus']);
        }
    }
    $convertstatus[$k] = $status;
    $setarr = ['cachekey' => $cachename, 'cachevalue' => serialize($convertstatus), 'dateline' => time()];
    C::t('cache')->insert_cachedata_by_cachename($setarr);
}

function updateThumbStatus($k, $status)
{
    $cachename = 'PICHOMETHUMBSTATUS';
    $convertstatus = C::t('cache')->fetch_cachedata_by_cachename($cachename);
    if (!$convertstatus) {
        $convertstatus = [];
        foreach (DB::fetch_all("select id,bz,imagestatus from %t where 1", array('connect_storage')) as $v) {
            if ($v['bz'] == 'dzz') {
                $key = $v['bz'] . '::';
            } else {
                $key = $v['bz'] . ':' . $v['id'] . ':';
            }
            $convertstatus[$key] = intval($v['imagestatus']);
        }
    }
    $convertstatus[$k] = $status;
    $setarr = ['cachekey' => $cachename, 'cachevalue' => serialize($convertstatus), 'dateline' => time()];
    C::t('cache')->insert_cachedata_by_cachename($setarr);
}

$theme = GetThemeColor();
include template('admin/pc/page/adminstorage');
