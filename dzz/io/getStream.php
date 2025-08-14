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
global $_G;
$path = isset($_GET['path']) ? trim($_GET['path']):'';
if(!$path = Decode($path,'read')){
    @header( 'HTTP/1.1 403 Not Found' );
    @header( 'Status: 403 Not Found' );
    exit('File not found');
}


$rid = $path;

//是否忽略密级权限
$ulevel = intval(getglobal('pichomelevel') );
//是否获取真实文件地址


if(strpos($path, 'attach::') === 0){
    $thumbpath = $path;
    $resourcesdata = IO::getMeta($path);
}else {
    $resourcesdata = C::t('pichome_resources')->fetch($rid);
}

if(!$resourcesdata){
    exit('file is not exists');
}

$resourcesdata['name'] = preg_replace('/\.'.$resourcesdata['ext'] . '/', '', $resourcesdata['name']);
$resourcesdata['name'] .='.'. $resourcesdata['ext'];

if($resourcesdata['level']  &&  $ulevel < $resourcesdata['level']){
    @header('HTTP/1.1 403 No Perm');
    @header('Status: 404 No Perm');
    exit('No Level Permission');
}

$url = IO::getStream($path);

$filename = $_GET['filename'] ? getstr($_GET['filename']) : $resourcesdata['name'];

// 定义要移除的后缀
$suffix = '\.dzz$';

// 使用正则表达式替换后缀
$filename = preg_replace('/' . $suffix . '/', '', $filename);

$ext = strtolower(substr(strrchr($filename, '.'), 1, 10));
if (!$ext) $ext = strtolower(substr(strrchr(preg_replace("/\.dzz$/i", '', preg_replace("/\?.*/i", '', $url)), '.'), 1, 10));

$mime = dzz_mime::get_type($ext);

if (is_file($url)) {
    $name = $filename;
    $filename = $url;
    $start = 0;
    $total = filesize($filename);
    header("Cache-Control: private, max-age=2592000, pre-check=2592000");
    header("Pragma: private");
    header("Expires: " . date(DATE_RFC822, strtotime(" 30 day")));
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
    if (preg_match("/Firefox/", $_SERVER["HTTP_USER_AGENT"])) {
        $attachment = 'attachment; filename*='.CHARSET.'\'\'' . $name;
    } elseif (!preg_match("/Chrome/", $_SERVER["HTTP_USER_AGENT"]) && preg_match("/Safari/", $_SERVER["HTTP_USER_AGENT"])) {
        $name = trim($name,'"');
        $filename = rawurlencode($name); // 注意：rawurlencode与urlencode的区别
        $attachment = 'attachment; filename*='.CHARSET.'\'\'' . $filename;
    } else{
        $attachment = 'attachment; filename='.$name;
    }

    // header('content-disposition:'.$attachment);
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