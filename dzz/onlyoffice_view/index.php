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
if(!$rid = dzzdecode($_GET['path'],'',0)){
    exit('Access Denied');
}

$onlyofficesetting = C::t('setting')->fetch('onlyofficesetting',true);
$onlyDocumentUrl=$onlyofficesetting['onlyofficeurl'];
$onlyofficedocurl = $onlyofficesetting['onlyofficedocurl'] ? $onlyofficesetting['onlyofficedocurl']:getglobal('siteurl');
$onlyDocumentUrl=rtrim(str_replace('web-apps/apps/api/documents/api.js','',$onlyDocumentUrl),'/').'/web-apps/apps/api/documents/api.js';
$host=explode(':',$_SERVER['HTTP_HOST']);
$onlyDocumentUrl=str_replace(array('localhost','127.0.0.1'),$host[0],$onlyDocumentUrl);

if(empty($onlyDocumentUrl)) showmessage('应用文档服务器为空，不能调用，请卸载重新安装');
$pathdata = DB::fetch_first("select v.path,ra.path as fpath   from %t r  left join %t ra on ra.rid = r.rid left join %t v on r.appid=v.appid where r.rid = %s", array('pichome_resources','pichome_resources_attr', 'pichome_vapp', $rid));
$patharr = explode(':',$pathdata['path']);
$did = is_numeric($patharr[1]) ? $patharr[1]:1;
$connectdata =  C::t('connect_storage')->fetch($did);
if(!$connectdata['docstatus']){
    showmessage('该文件预览需文档处理支持，当前存储位置未开启文档处理，如需预览请联系管理员开启文档处理');
    exit();
}
//$path=dzzdecode(rawurldecode($_GET['path']));
$docexts=array('doc', 'docx', 'rtf', 'odt', 'htm', 'html', 'mht', 'txt');
$sheetexts=array('xls', 'xlsx', 'ods',  'csv');
$showexts=array('ppt', 'pptx', 'pps', 'ppsx', 'odp');
$meta=C::t('pichome_resources')->fetch($rid);
$meta['title'] = $meta['name'];
if($meta['ext']=='wps'){
    $meta['ext']='doc';
}
if(in_array($meta['ext'],$docexts)){
    $documentType='text';
}elseif(in_array($meta['ext'],$sheetexts)){
    $documentType='spreadsheet';
}elseif(in_array($meta['ext'],$showexts)){
    $documentType='presentation';
}
$mode='view';
$perm_edit=0;
$perm_download=0;
$perm_print=0;
$fullscreenstream = getglobal('siteurl').'index.php?mod=onlyoffice_view&path='.$_GET['path'];
$key=$rid;
$stream= $onlyofficedocurl . 'index.php?mod=io&op=getStream&hash='.VERHASH.'&path=' . dzzencode($rid.'_3', '', 0, 0);

$saveurl='';
include template('main');