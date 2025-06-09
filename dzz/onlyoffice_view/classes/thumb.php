<?php

namespace dzz\onlyoffice_view\classes;

use \core as C;
use \DB as DB;
use \IO as IO;

require_once libfile('class/xml');
require_once(DZZ_ROOT . './dzz/onlyoffice_view/jwt/jwtmanager.php');

class thumb
{
    public $onlyofficethumbext = '';
    public $onlyDocumentUrl = '';
    public $onlyDocumentdocUrl = '';
    public $onlyDocumentdocSecret = '';


    public function __construct()
    {
        $app = C::t('app_market')->fetch_by_identifier('onlyoffice_view', 'dzz');
        $onlyofficedata = unserialize($app['extra']);

        $this->onlyDocumentUrl = $onlyofficedata['DocumentUrl'];
        $this->onlyDocumentdocSecret = $onlyofficedata['secret'];
        $this->onlyDocumentdocUrl = $onlyofficedata['FileUrl'] ? $onlyofficedata['FileUrl'] : getglobal('siteurl');
        $onlyofficethumbext = getglobal('config/onlyofficeviewextlimit');
        $this->onlyofficethumbext = explode(',', $onlyofficedata['exts']);
    }
    public function run($meta)
    {
        global $_G;
        if (strpos($meta['realpath'], ':') === false) {
            $bz = 'dzz';
        } else {
            $patharr = explode(':', $meta['realpath']);
            $bz = $patharr[0];
            $did = $patharr[1];

        }
        if (!is_numeric($did) || $did < 2) {
            $bz = 'dzz';
        }
        $app = C::t('app_market')->fetch_by_identifier('onlyoffice_view', 'dzz');
        $onlyofficedata = unserialize($app['extra']);

        $this->onlyDocumentUrl = $onlyofficedata['DocumentUrl'];
        $this->onlyDocumentdocSecret = $onlyofficedata['secret'];
        $this->onlyDocumentdocUrl = $onlyofficedata['FileUrl'] ? $onlyofficedata['FileUrl'] : getglobal('siteurl');
        $onlyofficethumbext = getglobal('config/onlyofficeviewextlimit');
        $this->onlyofficethumbext = explode(',', $onlyofficedata['exts']);

        if (!in_array($meta['ext'], $this->onlyofficethumbext)) {
            return '';
        } else {

            if($meta['aid']){
                $attach = IO::getMeta('attach::'.$meta['aid']);
            }else{
                $attach = IO::getMeta($meta['rid']);
            }
            $attach['stream'] = IO::getFileUri($attach['path']);

            if($meta['aid']) $attach['stream'] = $this->onlyDocumentdocUrl . 'index.php?mod=io&op=getStream&hash=' . VERHASH . '&path=' . dzzencode('attach::'.$meta['aid']);
            else $attach['stream'] = $this->onlyDocumentdocUrl . 'index.php?mod=io&op=getStream&hash=' . VERHASH . '&path=' . dzzencode($attach['rid'] . '_3', '', 0, 0);

            if ($url = $this->getThumb($attach)) {

                return array($url);
            }

        }
    }

    function textEncode($data)
    {
        $mime = \dzz_mime::get_type($data['ext']);
        list($pre) = explode('/', $mime);

        if ($pre != 'text') return $data['stream'];
        $str = file_get_contents($data['stream']);
        require_once DZZ_ROOT . './dzz/class/class_encode.php';
        $p = new \Encode_Core();
        $code = $p->get_encoding($str);
        if ($code != CHARSET) $str = diconv($str, $code, CHARSET);
        if($data['aid'])$cachekey = 'cache/' .md5($data['aid']) . '.' . $data['ext'];
        else $cachekey = 'cache/' .  $data['rid'] . '.' . $data['ext'];
        $file = getglobal('setting/attachdir') . $cachekey;
        if (file_put_contents($file, $str)) {
            $data['stream'] = getglobal('localurl') . getglobal('setting/attachurl') . $cachekey;
        }
        return $data['stream'];

    }
    function convertHtmlToword($data,$ext='docx'){
        global $_G;
        $stream = $this->textEncode($data);
        $post_data = array(
            'async' => false,
            'filetype' => 'html',
            'key' => ($data['aid']) ? md5($data['aid']):$data['rid'],
            'outputtype' => $ext,
            'title' => $data['name'],
            'url' => $stream
        );
        if ($this->onlyDocumentdocSecret) {
            $post_data['token'] = jwtEncode($post_data, $this->onlyDocumentdocSecret);
        }
        $conversion_url = $this->getCUrl();
        $ret = ($this->getConvertUrl($conversion_url, json_encode($post_data)));
        if ($url = $ret['FileUrl']) {
            //echo $url;
            $target = 'pichomethumb/' . date('Ym') . '/' . date('d') .'/'.($data['aid'] ? md5($data['aid']):md5($data['rid'])) .'.'.$ext;
            $png = $_G['setting']['attachdir'] .'/' .$target;
            $dir = dirname($png);
            dmkdir($dir, 0777, false);
            //echo $png;
            //var_dump(file_put_contents($png, curl_file_get_contents($url)) != false);			die;
            if (file_put_contents($png, curl_file_get_contents($url)) != false) {
                $defaultspace = $_G['setting']['defaultspacesetting'];
                //如果原文件位置不在本地，则将转换完成文件迁移到对应位置
                if ($defaultspace['bz'] != 'dzz') {
                    $cloudpath = $defaultspace['bz'].':'.$defaultspace['did'] . ':/' .$target;
                    //组合云端保存位置
                    $filepath = \IO::moveThumbFile($cloudpath, 'dzz::'.$target);
                    if (!isset($filepath['error'])) {
                        @unlink($png);
                        return $target;
                    }
                } else {
                    //echo $target;die;
                    return $target;
                }

            }

        } else {
            runlog('onlyoffice', $conversion_url . '===' . print_r($post_data,true) . '===' . print_r($ret, true));
        }
        return false;
    }
    function getThumb($data, $width = 993, $height = 1043)
    {

        global $_G;
        $stream=$this->textEncode($data);
        //print_r($stream);die;
        $post_data = array(
            'async' => false,
            'filetype' => $data['ext'],
            'key' => ($data['aid']) ? md5($data['aid']):$data['rid'],
            'outputtype' => 'png',
            'thumbnail' => array(
                'aspect' => true,
                'first' => 1,
                'height' => $height,
                'width' => $width
            ),
            'title' => $data['name'],
            'url' => $stream
        );
        if ($this->onlyDocumentdocSecret) {
            $post_data['token'] = jwtEncode($post_data, $this->onlyDocumentdocSecret);
        }
        $conversion_url = $this->getCUrl();
        $ret = ($this->getConvertUrl($conversion_url, json_encode($post_data)));
        if ($url = $ret['FileUrl']) {
            $target = 'pichomethumb/' . date('Ym') . '/' . date('d') .'/'.($data['aid'] ? md5($data['aid']):md5($data['rid'])) . '_original.png';
            $png = getglobal('setting/attachdir') .$target;
            $dir = dirname($png);
            dmkdir($dir, 0777, false);

            if (file_put_contents($png, curl_file_get_contents($url)) != false) {
                $defaultspace = $_G['setting']['defaultspacesetting'];
                //如果原文件位置不在本地，则将转换完成文件迁移到对应位置
                if ($defaultspace['bz'] != 'dzz') {
                    $cloudpath = $defaultspace['bz'].':'.$defaultspace['did'] . ':/' .$target;
                    //组合云端保存位置
                    $filepath = \IO::moveThumbFile($cloudpath, 'dzz::'.$target);
                    if (!isset($filepath['error'])) {
                        @unlink($png);
                        return $target;
                    }
                } else {
                    return $target;
                }

            }

        } else {
            runlog('onlyoffice', $conversion_url . '===' . print_r($post_data,true) . '===' . print_r($ret, true));
        }
        return false;
    }

    private function curl_get($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    private function getCUrl()
    {
        global $_SERVER;
        $onlyDocumentUrl = rtrim(str_replace('web-apps/apps/api/documents/api.js', '', $this->onlyDocumentUrl), '/') . '/ConvertService.ashx';
        return $onlyDocumentUrl;

    }

    public function getConvertUrl($posturl, $post_data)
    {
        //CURLOPT_URL 是指提交到哪里？相当于表单里的“action”指定的路径
        //$url = "http://local.jumei.com/DemoIndex/curl_pos/";
        //$posturl.='?'.http_build_query($post_data);
        $ch = curl_init();
        //    设置变量
        curl_setopt($ch, CURLOPT_URL, $posturl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //执行结果是否被返回，0是返回，1是不返回
        //curl_setopt($ch, CURLOPT_HEADER, 0);//参数设置，是否显示头部信息，1为显示，0为不显示

        //伪造网页来源地址,伪造来自百度的表单提交
        //curl_setopt($ch, CURLOPT_REFERER, '');

        //表单数据，是正规的表单设置值为非0
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        //设置curl执行超时时间最大是多少

        //使用数组提供post数据时，CURL组件大概是为了兼容@filename这种上传文件的写法，
        //默认把content_type设为了multipart/form-data。虽然对于大多数web服务器并
        //没有影响，但是还是有少部分服务器不兼容。本文得出的结论是，在没有需要上传文件的
        //情况下，尽量对post提交的数据进行http_build_query，然后发送出去，能实现更好的兼容性，更小的请求数据包。
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($post_data));

        //执行并获取结果
        if (!$r = curl_exec($ch)) {
            return (array('error' => curl_error($ch)));
        }
        curl_close($ch);
        return $ret = xml2array($r, true, 'utf-8');
    }
}