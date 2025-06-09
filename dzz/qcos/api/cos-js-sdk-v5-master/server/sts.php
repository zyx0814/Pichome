<?php
// 临时密钥计算样例
require_once('../../../../.././core/coreBase.php');
$dzz = C::app();

Hook::listen('dzz_initbefore');//初始化前钩子

$dzz->init();
//$setting = C::t('setting')->fetch_all('defaultspacesetting',true);
//$_G['setting']['defaultspacesetting']['did'];
$connectdata = C::t('connect_storage')->fetch($_G['setting']['defaultspacesetting']['did']);
$hostnamearr = explode(':', $connectdata['hostname']);
$access_id = dzzdecode($connectdata['access_id'], $connectdata['bz']);
if (empty($access_id)) $access_id = $connectdata['access_id'];
$access_key = dzzdecode($connectdata['access_key'], $connectdata['bz']);
include './qcloud-sts-sdk.php'; // 这里获取 sts.php https://github.com/tencentyun/qcloud-cos-sts-sdk/blob/master/php/sts/sts.php
$sts = new STS();
// 配置参数
$config = array(
    'url' => 'https://sts.tencentcloudapi.com/',
    'domain' => 'sts.tencentcloudapi.com',
    'proxy' => '',
    'secretId' =>$access_id, // 固定密钥
    'secretKey' => $access_key, // 固定密钥
    'bucket' =>$connectdata['bucket'], // 换成你的 bucket
    'region' => $hostnamearr[1], // 换成 bucket 所在园区
    'durationSeconds' => 1800, // 密钥有效期
    // 允许操作（上传）的对象前缀，可以根据自己网站的用户登录态判断允许上传的目录，例子： user1/* 或者 * 或者a.jpg
    // 请注意当使用 * 时，可能存在安全风险，详情请参阅：https://cloud.tencent.com/document/product/436/40265
    'allowPrefix' => 'tmpupload/*',
    // 密钥的权限列表。简单上传和分片需要以下的权限，其他权限列表请看 https://cloud.tencent.com/document/product/436/31923
    'allowActions' => array (
        // 所有 action 请看文档 https://cloud.tencent.com/document/product/436/31923
        // 简单上传
        'name/cos:PutObject',
        'name/cos:PostObject',
        // 分片上传
        'name/cos:InitiateMultipartUpload',
        'name/cos:ListMultipartUploads',
        'name/cos:ListParts',
        'name/cos:UploadPart',
        'name/cos:CompleteMultipartUpload'
    )
);
// 获取临时密钥，计算签名
$tempKeys = $sts->getTempKeys($config);

// 返回数据给前端
header('Content-Type: application/json');
header('Access-Control-Allow-Origin:'.getglobal('siteurl')); // 这里修改允许跨域访问的网站
header('Access-Control-Allow-Headers: origin,accept,content-type');
echo json_encode($tempKeys);
