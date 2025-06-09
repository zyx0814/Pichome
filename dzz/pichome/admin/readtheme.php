<?php

if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}
Hook::listen('adminlogin');

$templatedir = DZZ_ROOT.'dzz'.BS.'pichome'.BS.'template';
$defaultdirname = ['admin','default'];
require_once libfile('class/xml');

if (is_dir($templatedir)) {
    if ($dh = @opendir($templatedir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..'  && !in_array($file,$defaultdirname)) {
                if(is_file($templatedir.BS.$file.BS.'template.xml')){
                    $xmlpath = $templatedir.BS.$file.BS.'template.xml';
                    //$filemtime = filemtime($xmlpath);
                    $themedata = readthemedata($xmlpath);
                    $themedata['themefolder'] = $file;
                    $themedata['dateline'] = TIMESTAMP;
                    C::t('pichome_theme')->insert_data($themedata);
                }
            }
        }
        //关闭
        closedir($dh);
    }
}
function readthemedata($xmlpath){
    $importtxt = @implode('',file($xmlpath));
    $data = xml2array($importtxt,FALSE,'UTF-8');
    $_attributes=xmlattribute($importtxt,'UTF-8'); //item 属性获取
    $themearr = [
        'themename'=>$data['themename'],
        'themetag'=>isset($data['templatetag']) ? serialize($data['templatetag']):''
    ];
    if(isset($data['singlepage'])){
        $singlepagedata = [];
        foreach($data['singlepage'] as $k=>$v){
            if(isset($_attributes['singlepage'][$k]['_attributes']['priority'])){
                $singlepagedata[$k]['disp'] = $_attributes['singlepage'][$k]['_attributes']['priority'];
            }else{
                $singlepagedata[$k]['disp'] = 0;
            }
            $singlepagedata[$k]['name'] = $v;
        }
        $themearr['templates'] = serialize($singlepagedata);
    }
    if(isset($data['colors'])){
        $themearr['colors'] = $data['colors']['colorlist'];
        $themearr['selcolor'] = $data['colors']['defaultcolor'];
    }
   return $themearr;
}
exit('success');

