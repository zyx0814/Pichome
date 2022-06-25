<?php
namespace dzz\onlyoffice_view\classes;

use \core as C;
use \DB as DB;
use \IO as IO;
require_once libfile('class/xml');
class thumb{
    public $onlyofficethumbext = '';
    public $onlyDocumentUrl = '';
    public $onlyDocumentdocUrl = '';

    public function run($meta){
        if(strpos($meta['realpath'],':') === false){
            $bz = 'dzz';
        }else{
            $patharr = explode(':', $meta['realpath']);
            $bz = $patharr[0];
            $did = $patharr[1];

        }
        if(!is_numeric($did) || $did < 2){
            $bz = 'dzz';
        }

        if($bz != 'dzz') return '';
        $onlyofficedata = C::t('setting')->fetch('onlyofficesetting',true);

        $this->onlyDocumentUrl=$onlyofficedata['onlyofficeurl'];
        $this->onlyDocumentdocUrl = $onlyofficedata['onlyofficedocurl'] ? $onlyofficedata['onlyofficedocurl']:getglobal('siteurl');
        $onlyofficethumbext = getglobal('config/onlyofficeviewextlimit');
        $this->onlyofficethumbext=explode(',',$onlyofficethumbext);

        if(!in_array($meta['ext'],$this->onlyofficethumbext)){
            return '';
        }else{
            $meta['stream']=$this->onlyDocumentdocUrl . 'index.php?mod=io&op=getStream&hash='.VERHASH.'&path=' . dzzencode($meta['rid'].'_3', '', 0, 0);
            if($url=$this->getThumb($meta,getglobal('config/pichomethumsmallwidth'),getglobal('config/pichomethumsmallheight'))){
                if(is_file($url) && $info=getimagesize($url)){
                    $attr=array('width'=>$info[0],'height'=>$info[1],'hasthumb'=>1);
                    if($meta['rid']) C::t('pichome_resources')->update($meta['rid'],$attr);
                    return array($url);
                }
            }
        }

    }
    function textEncode($data){
        $mime=\dzz_mime::get_type($data['ext']);
        list($pre)=explode('/',$mime);
        if($pre!='text') return $data['stream'];

        $str = file_get_contents($data['stream']);
        require_once DZZ_ROOT . './dzz/class/class_encode.php';
        $p = new \Encode_Core();
        $code = $p -> get_encoding($str);
        if ($code!=CHARSET) $str = diconv($str, $code, CHARSET);
        $cachekey='cache/'.$data['appid'].$data['rid'].'.'.$data['ext'];
        $file=getglobal('setting/attachdir').$cachekey;
        if(file_put_contents($file,$data['stream'])){
            $data['stream']=getglobal('localurl').getglobal('setting/attachurl').$cachekey;
        }
        return $data['stream'];

    }
    function getThumb($data,$width=993,$height=1043){
        //$stream=$this->textEncode($data);
        $post_data = '{	"async":false,
						"filetype": "'.$data['ext'].'",
						"key": "'.md5($data['rid']).'",
						"outputtype": "jpg",
						"thumbnail": {
							"aspect": 1,
							"first": true,
							"height": '.$width.',
							"width": '.$height.'
						},
						"title": "'.$data['name'].'",
						"url": "'.$data['stream'].'"
					}';
        $conversion_url=$this->getCUrl();
        $ret = ($this->getConvertUrl($conversion_url, $post_data));
        if($url=$ret['FileUrl']){
            $target = md5($data['realpath'].$data['thumbsign']) . '.jpg';
            $png = getglobal('setting/attachdir') . './pichomethumb/' . $data['appid'] . '/' . $target;
            if (!is_dir(getglobal('setting/attachdir') . './' . 'pichomethumb/' . $data['appid'])) {
                dmkdir(getglobal('setting/attachdir') . './' . 'pichomethumb/' . $data['appid'], 0777, false);
            }

            if(file_put_contents($png,curl_file_get_contents($url)) != false){

                return $png;
            }

        }
        return false;
    }

    private  function curl_get($url)
    {
        $ch=curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $content=curl_exec($ch);
        curl_close($ch);
        return $content;
    }
    private function getCUrl(){
        global $_SERVER;
        $onlyDocumentUrl=rtrim(str_replace('web-apps/apps/api/documents/api.js','',$this->onlyDocumentUrl),'/').'/ConvertService.ashx';
        return $onlyDocumentUrl;

    }
    public function getConvertUrl($posturl, $post_data) {
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
            return ( array('error' => curl_error($ch)));
        }
        curl_close($ch);
        return $ret=xml2array($r,true,'utf-8');
    }
}