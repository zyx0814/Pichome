<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 */
if(!defined('IN_OAOOA') ) {
    exit('Access Denied');
}
if(!$patharr=Pdecode($_GET['path'])){
    exit('Access Denied');
}
$rid = $patharr['path'];
$isshare = $patharr['isshare'];
$perm = $patharr['perm'];
$isadmin = $patharr['isadmin'];
//require_once(MOD_PATH . './jwt/jwtmanager.php' );
require_once(DZZ_ROOT.MOD_PATH . '/jwt/jwtmanager.php' );
$app=C::t('app_market')->fetch_by_identifier('onlyoffice_view','dzz');

$onlyofficesetting =unserialize($app['extra']);
$onlyDocumentUrl=$onlyofficesetting['DocumentUrl'];
//$onlyDocumentUrl='http://192.168.124.11:90/';
$onlyofficesetting['secret']=$onlyofficesetting['secret'];
$onlyofficedocurl = $onlyofficesetting['FileUrl'] ? $onlyofficesetting['FileUrl']:getglobal('siteurl');
$onlyDocumentUrl=rtrim(str_replace('web-apps/apps/api/documents/api.js','',$onlyDocumentUrl),'/').'/web-apps/apps/api/documents/api.js';
$host=explode(':',$_SERVER['HTTP_HOST']);
$onlyDocumentUrl=str_replace(array('localhost','127.0.0.1'),$host[0],$onlyDocumentUrl);

if(empty($onlyDocumentUrl)) showmessage(lang('app_url_is_empty'));
/*$pathdata = DB::fetch_first("select v.path,ra.path as fpath   from %t r  left join %t ra on ra.rid = r.rid left join %t v on r.appid=v.appid where r.rid = %s", array('pichome_resources','pichome_resources_attr', 'pichome_vapp', $rid));
$patharr = explode(':',$pathdata['path']);
$did = is_numeric($patharr[1]) ? $patharr[1]:1;
$connectdata =  C::t('connect_storage')->fetch($did);*/
//if(!$connectdata['docstatus']){
//  showmessage('该文件预览需文档处理支持，当前存储位置未开启文档处理，如需预览请联系管理员开启文档处理');
//  exit();
//}

$docexts=array('doc', 'docm', 'docx', 'docxf', 'dot', 'dotm', 'dotx', 'epub', 'fodt', 'fb2', 'htm', 'html','mht', 'odt', 'oform', 'ott', 'oxps', 'pdf', 'rtf', 'txt', 'djvu', 'xml', 'xps','wps');
$sheetexts=array('xls', 'xlsx', 'ods',  'csv','fods','ots','xlsm','xlt','xltm','xltx');
$showexts=array('ppt', 'pptx', 'pps', 'ppsx', 'odp','fodp','otp','pot','potm','potx','ppsm','pptm');
$_G['DOC_SERV_FILLFORMS'] = array(".oform", ".docx");
$_G['DOC_SERV_VIEWD'] = array(".pdf", ".djvu", ".xps", ".oxps");
$_G['DOC_SERV_EDITED'] = array(".docx", ".xlsx", ".csv", ".pptx", ".txt", ".docxf");
$_G['DOC_SERV_CONVERT'] = array(".docm", ".doc", ".dotx", ".dotm", ".dot", ".odt", ".fodt", ".ott", ".xlsm", ".xlsb", ".xls", ".xltx", ".xltm", ".xlt", ".ods", ".fods", ".ots", ".pptm", ".ppt", ".ppsx", ".ppsm", ".pps", ".potx", ".potm", ".pot", ".odp", ".fodp", ".otp", ".rtf", ".mht", ".html", ".htm", ".xml", ".epub", ".fb2");

$meta=IO::getMeta($rid);
$meta['title'] = $meta['name'];
if($meta['ext']=='wps'){
    $meta['ext']='doc';
}
if(in_array($meta['ext'],$docexts)){
    $documentType='word';
}elseif(in_array($meta['ext'],$sheetexts)){
    $documentType='cell';
}elseif(in_array($meta['ext'],$showexts)){
    $documentType='slide';
}
$mode='view';
$perm_edit=false;
$perm_download=perm::check('download2',$perm)?1:0;
$perm_print=false;
$fullscreenstream = getglobal('siteurl').'index.php?mod=onlyoffice_view&path='.$_GET['path'];

$key=$rid;
$stream= $onlyofficedocurl . 'index.php?mod=io&op=getStream&hash='.VERHASH.'&path=' . dzzencode($rid.'_3', '', 0, 0);

$saveurl='';
$config = [
    "type" => helper_browser::ismobile()?"mobile":"desktop",
    "documentType" => $documentType,
    "document" => [
        "title" => $meta['title'],
        "url" => $stream,
        "fileType" => $meta['ext'],
        "key" => $key,
        "info" => [
            "owner" => $meta['username'],
            "uploaded" => dgmdate($meta['dateline'],'Y-m-d'),
            // "folder"=>$meta['relpath'],
        ],
        "permissions" => [  // the permission for the document to be edited and downloaded or not
            "comment" => true,
            "copy" => $perm_download,
            "download" => $perm_download,
            "edit" => $perm_edit,
            "print" => $perm_print,
            "fillForms" => false,//$editorsMode != "view" && $editorsMode != "comment" && $editorsMode != "embedded" && $editorsMode != "blockcontent",
            "modifyFilter" =>false, //$editorsMode != "filter",
            "modifyContentControl" =>false, //$editorsMode != "blockcontent",
            "review" =>false, //$canEdit && ($editorsMode == "edit" || $editorsMode == "review"),
            "rename"=>false,
            "changeHistory"=>false
        ]
    ],
    "editorConfig" => [
        "actionLink" =>null, //empty($_GET["actionLink"]) ? null : json_decode($_GET["actionLink"]),
        "mode" => $mode,
        "lang" => "zh",
        "location" => "cn",
        "callbackUrl" => $saveurl,  // absolute URL to the document storage service
        "createUrl" =>  null,
        "user" => [  // the user currently viewing or editing the document
            "id" => $_G['uid'],
            "name" => $_G['username'],
            "group" => $_G['group']['grouptitle']
        ],

        "customization" => [  // the parameters for the editor interface
            "about" => false,  // the About section display
            "comments" => true,
            "feedback" => false,  // the Feedback & Support menu button display
            "forcesave" => false,  // adds the request for the forced file saving to the callback handler when saving the document
            "goback" => false,
            "plugins" => false,
            "autosave" => false,
            "compactToolbar" => true,
            "leftMenu" =>  false,
            "rightMenu" => false,
            "toolbar" =>  false,
            "header" =>  false,
			"uiTheme"=> empty($_GET['theme'])?"theme-light":'theme-'.$_GET['theme'],
            "features"=> [
                "spellcheck"=>[
                    "mode" => false,
                ]
            ]

        ]
    ]
];
if($onlyofficesetting['secret']){
    $config["token"] = jwtEncode($config,$onlyofficesetting['secret']);
}
include template('main');