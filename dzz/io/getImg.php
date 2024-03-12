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

$path = isset($_GET['path']) ? trim($_GET['path']) : '';

if (!$patharr = Pdecode($path,  '')) {
    @header('HTTP/1.1 404 Not Found');
    @header('Status: 404 Not Found');
}

$appid = $patharr['appid'];
$appdata = C::t('pichome_vapp')->fetch($appid);
$iscloud = false;

$arr = explode(':', $appdata['apppath']);
if ($arr[1] && is_numeric($arr[1])) {
    $iscloud = true;
} else {
    $appdata['path'] = str_replace('/', BS, $appdata['path']);
    $appdata['path'] = str_replace('dzz::', '', $appdata['path']);
}
$ext =$patharr['ext'];

if ($appdata['type'] == 0) {//eagle缩略图
   // $patharr['path'] = DB::result_first("select path from %t where rid = %s",array('pichome_resources_attr',$patharr['rid']));
    $thumbdir = $appdata['path'];

    //当地址为大图时
    if($patharr['thumbsign']){
        //如果有下载权限，并且当前图片支持预览返回原图地址
        if(in_array($ext, explode(',', getglobal('config/pichomecommimageext'))))$thumbpath =($iscloud) ? IO::getFileUri($thumbdir . BS . $patharr['path']):$thumbdir . BS . $patharr['path'];
        else{

            //否则尝试使用小图作为大图地址展示
            $tmppath = preg_replace('/\.'.$ext.'$/','',$patharr['path']);
            $thumbpath = $thumbdir . BS . $tmppath . '_thumbnail.png';
            //如果小图也不存在,使用图标代替大图
            if (!$patharr['hasthumb'])$thumbpath = 'static/dzzthumb/preview/b.gif';
        }
    }else{//小图时
        $tmppath = preg_replace('/\.'.$ext.'$/','',$patharr['path']);
        $thumbpath = ($iscloud) ? IO::getFileUri($thumbdir . BS . $tmppath . '_thumbnail.png'):$thumbdir . BS . $tmppath . '_thumbnail.png';
        //如果小图不存在
        if (!$patharr['hasthumb']){
            //如果有下载权限，并且当前图片支持预览返回原图地址，否则使用图标替代
            if( in_array($ext, explode(',', getglobal('config/pichomecommimageext'))))$thumbpath =($iscloud) ? IO::getFileUri($thumbdir . BS . $patharr['path']):$thumbdir . BS . $patharr['path'];
            else $thumbpath =  'static/dzzthumb/preview/b.gif';
        }
    }


}
elseif($appdata['type'] == 2) {//billfish缩略图
    //获取记录表缩略图对应信息
    $thumbdata = DB::fetch_first("select thumb,bid from %t where appid = %s and rid = %s", array('billfish_record', $patharr['appid'], $patharr['rid']));

    //判断billfish版本
    if (isset($appdata['version']) && $appdata['version'] >= 30) {
        $bid = $thumbdata['bid'];
        $thumbdir = dechex($bid);
        $thumbdir = (string)$thumbdir;
        if (strlen($thumbdir) < 2) {
            $thumbdir = str_pad($thumbdir, 2, 0, STR_PAD_LEFT);
        } elseif (strlen($thumbdir) > 2) {
            $thumbdir = substr($thumbdir, -2);
        }

        //如果大图时
        if($patharr['thumbsign']){
            $thumbpath = ($iscloud) ? \IO::getFileuri($appdata['path'] . '/.bf/.preview/' . $thumbdir . '/' . $bid . '.hd.webp') : $appdata['path'] . BS . '.bf' . BS . '.preview' . BS . $thumbdir . BS . $bid . '.hd.webp';
            if (!IO::checkfileexists($thumbpath)){
                $patharr['path'] = DB::result_first("select path from %t where rid = %s",array('pichome_resources_attr',$patharr['rid']));
                //如果有下载权限，并且当前图片支持预览返回原图地址，否则使用小图
                if(in_array($ext, explode(',', getglobal('config/pichomecommimageext'))))$thumbpath = $appdata['path'] . BS . $patharr['path'];
                else $patharr['thumbsign'] = 0;

            }
        }
        if(!$patharr['thumbsign']){
            $patharr['path'] = DB::result_first("select path from %t where rid = %s",array('pichome_resources_attr',$patharr['rid']));
            $thumbpath= ($iscloud) ? \IO::getFileuri($appdata['path'] . '/.bf/.preview/' . $thumbdir . '/' . $bid . '.small.webp') : $appdata['path'] . BS . '.bf' . BS . '.preview' . BS . $thumbdir . BS . $bid . '.small.webp';
            //不存在小图使用图标
            if(!$patharr['hasthumb']){
                if(in_array($ext, explode(',', getglobal('config/pichomecommimageext'))))$thumbpath = $appdata['path'] . BS . $patharr['path'];
                else $thumbpath = 'static/dzzthumb/preview/b.gif';
            }
        }
    } else {
        //如果是大图
        if ($patharr['thumbsign']) {
            $patharr['path'] = DB::result_first("select path from %t where rid = %s",array('pichome_resources_attr',$patharr['rid']));
            if (in_array($ext, explode(',', getglobal('config/pichomecommimageext')))) $thumbpath = $appdata['path'] . BS . $patharr['path'];
            else $patharr['thumbsign'] = 0;
        }
        if(!$patharr['thumbsign']){//如果是小图
            $thumbid = $thumbdata['thumb'];
            if (strlen($thumbid) < 9) {
                $thumbid = str_pad($thumbid, 9, 0, STR_PAD_LEFT);
            }
            $pathdir = $appdata['path'] . BS . '.bf' . BS . '.preview';
            $thumbpatharr = mbStrSplit($thumbid, 3);
            array_pop($thumbpatharr);
            $thumbpathdir = implode(BS, $thumbpatharr);
            $thumbpath = $iscloud ? \IO::getFileuri($pathdir . '/' . $thumbpathdir . '/' . $thumbid . '.webp') : $pathdir . BS . $thumbpathdir . BS . $thumbid . '.webp';
            if(!$patharr['hasthumb']){
                $patharr['path'] = DB::result_first("select path from %t where rid = %s",array('pichome_resources_attr',$patharr['rid']));
                if (in_array($ext, explode(',', getglobal('config/pichomecommimageext')))) $thumbpath = $appdata['path'] . BS . $patharr['path'];
                else $thumbpath =  'static/dzzthumb/preview/b.gif';
            }
        }
    }

}else{//普通目录缩略图
    if($patharr['thumbsign']){
        $thumbpath = DB::result_first("select lpath from %t where rid = %s",array('thumb_record',$patharr['rid']));
        $patharr['path'] = DB::result_first("select path from %t where rid = %s",array('pichome_resources_attr',$patharr['rid']));
        //如果大图不存在
        if(!$thumbpath || !IO::checkfileexists($thumbpath)){
            //如果图片可直接预览，并有下载权限时返回原图
            if(in_array($ext, explode(',', getglobal('config/pichomecommimageext'))))$thumbpath = ($iscloud) ? IO::getFileUri($appdata['path'] . BS . $patharr['path']):$appdata['path'] . BS . $patharr['path'];
            else $patharr['thumbsign'] = 0;
        }
    }
    if(!$patharr['thumbsign']){
        //尝试找到小图
        $thumbpath = DB::result_first("select spath from %t where rid = %s",array('thumb_record',$patharr['rid']));
        //如果小图不存在
        if(!$thumbpath || !IO::checkfileexists($thumbpath)){
            $patharr['path'] = DB::result_first("select path from %t where rid = %s",array('pichome_resources_attr',$patharr['rid']));
            //如果图片可直接预览，并有下载权限时返回原图
            if(in_array($ext, explode(',', getglobal('config/pichomecommimageext'))))$thumbpath = ($iscloud) ? IO::getFileUri($appdata['path'] . BS . $patharr['path']):$appdata['path'] . BS . $patharr['path'];
            else $thumbpath =  'static/dzzthumb/preview/b.gif';
        }
    }

}

function mbStrSplit($string, $len = 1)
{
    $start = 0;
    $strlen = mb_strlen($string);
    while ($strlen) {
        $array[] = mb_substr($string, $start, $len, "utf8");
        $string = mb_substr($string, $len, $strlen, "utf8");
        $strlen = mb_strlen($string);
    }
    return $array;
}

$filename = $thumbpath;

$ext = strtolower(substr(strrchr($filename, '.'), 1, 10));
if (!$ext) $ext = strtolower(substr(strrchr(preg_replace("/\.dzz$/i", '', preg_replace("/\?.*/i", '', $url)), '.'), 1, 10));
$mime = dzz_mime::get_type($ext);
$url = IO::getStream($thumbpath);


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


