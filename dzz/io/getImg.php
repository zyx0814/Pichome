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
$fpath = isset($_GET['fpath']) ? trim($_GET['fpath']):'';
if(!$fpath = dzzdecode(rawurldecode($fpath), '', 0)){
    if (!$path = dzzdecode(rawurldecode($_GET['path']), '', 0)) {
        @header('HTTP/1.1 404 Not Found');
        @header('Status: 404 Not Found');

    }
    $rid = $path;
    $resourcesdata = C::t('pichome_resources')->fetch($rid);
    if(!$resourcesdata){
        exit('file is not exists');
    }
    $resourattrdata = C::t('pichome_resources_attr')->fetch($rid);
    $resourcesdata = array_merge($resourcesdata, $resourattrdata);
    $appdata = C::t('pichome_vapp')->fetch($resourcesdata['appid']);
    if ($resourcesdata['hasthumb']) {
        //如果是本地文件
        if ($appdata['type'] == 1) {
            $filename = 'pichomethumb' . BS . $resourcesdata['appid'] . BS . md5($resourcesdata['path']) . '.jpg';
            $thumbpath = getglobal('setting/attachurl') . $filename;
        } elseif ($resourcesdata['apptype'] == 0) {
            $resourcesdata['path'] = str_replace('\\', '/', $resourcesdata['path']);
            $filepath = dirname($resourcesdata['path']);
            $filename = substr($resourcesdata['path'], strrpos($resourcesdata['path'], '/') + 1);
            $filename = str_replace(strrchr($filename, "."), "", $filename);
            $filepath = str_replace('/', BS, $filepath);
            $tmppath = $appdata['path'];
            $thumbpath = $tmppath . BS . $filepath . BS . $filename . '_thumbnail.png';
        } else {
            $hookdata = ['rid' => $resourcesdata['rid'], 'apppath' => $appdata['path'], 'appid' => $resourcesdata['appid'], 'version' => $appdata['version']];
            $return = Hook::listen('getpichomethumb', $hookdata);
            $thumbpath = str_replace(DZZ_ROOT, '', $return[0]['icon']);
        }

    } else {
        if ($resourcesdata['type'] == 'commonimage') {
            $thumbpath = $appdata['path'] . BS . $resourcesdata['path'];
        } else {
            $thumbpath = geticonfromext($resourcesdata['ext'], $resourcesdata['type']);
        }
    }
}else{
    $thumbpath = $fpath;
}

$url = $thumbpath;
$filename = rtrim($_GET['n'], '.dzz');
$ext = strtolower(substr(strrchr($filename, '.'), 1, 10));
if (!$ext) $ext = strtolower(substr(strrchr(preg_replace("/\.dzz$/i", '', preg_replace("/\?.*/i", '', $url)), '.'), 1, 10));
$mime = dzz_mime::get_type($ext);
if (is_file($url)) {
    $filename = $url;
    $start = 0;
    $total = filesize($filename);
    header("Cache-Control: private, max-age=2592000, pre-check=2592000");
    header("Pragma: private");
    header("Expires: " . date(DATE_RFC822, strtotime(" 30 day")));
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = str_replace('=', '-', $_SERVER['HTTP_RANGE']);
        $range = explode('-', $range);
        if (isset($range[2]) && intval($range[2]) > 0) {
            $end = trim($range[2]);
        } else {
            $end = $total - 1;
        }
        $start = trim($range[1]);
        $size = $end - $start + 1;

        header('HTTP/1.1 206 Partial Content');
        header('Content-Length:' . $size);
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $total);

    } else {
        $size = $end = $total;

        header('HTTP/1.1 200 OK');
        header('Content-Length:' . $size);
        header('Content-Range: bytes 0-' . ($total - 1) . '/' . $total);
    }
    header('Accenpt-Ranges: bytes');
    header('Content-Type:' . $mime);
    $fp = fopen($filename, 'rb');
    fseek($fp, $start, 0);

    $cur = $start;
    @ob_end_clean();
    if (getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    while (!feof($fp) && $cur <= $end && (connection_status() == 0)) {
        print fread($fp, min(1024 * 16, ($end - $cur) + 1));
        $cur += 1024 * 16;
    }

    fclose($fp);
    exit();
} else {
    //$cachefile=$_G['siteurl']['attachdir'].'cache/'.play_cache_md5(file).'.'.$ext;
    //$meta=IO::getMeta($path);
    //$size=$meta['size'];

    header("Cache-Control: private, max-age=2592000, pre-check=2592000");
    header("Pragma: private");
    header("Expires: " . date(DATE_RFC822, strtotime(" 30 day")));
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($url)) . ' GMT');
    header('Content-Type: ' . $mime);
    //header('Content-Length:'.$size);
    //header('Content-Range: bytes 0-'.($size-1).'/'.$size);
    @ob_end_clean();
    if (getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    @readfile($url);
    @flush();
    @ob_flush();
    exit();
}