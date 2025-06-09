<?php
require_once('../../../../.././core/coreBase.php');
$dzz = C::app();

Hook::listen('dzz_initbefore');//初始化前钩子

$dzz->init();
$setting = C::t('setting')->fetch_all('defaultspacesetting', true);
$connectdata = C::t('connect_storage')->fetch($setting['defaultspacesetting']['did']);
$hostnamearr = explode(':', $connectdata['hostname']);
$id = dzzdecode($connectdata['access_id'], $connectdata['bz']);
if (empty($id)) $id = $connectdata['access_id'];
$key = dzzdecode($connectdata['access_key'], $connectdata['bz']);
function gmt_iso8601($time)
{
    $dtStr = date("c", $time);
    $mydatetime = new DateTime($dtStr);
    $expiration = $mydatetime->format(DateTime::ISO8601);
    $pos = strpos($expiration, '+');
    $expiration = substr($expiration, 0, $pos);
    return $expiration . "Z";
}

//$id= 'LTAI4GAsnkSpigkRt1E8wyWN';          // 请填写您的AccessKeyId。
// $key= 'cwSd2FkaiqPrnuZW9ueVIFKifTSPOT';     // 请填写您的AccessKeySecret。
// $host的格式为 bucketname.endpoint，请替换为您的真实信息。
$host = $connectdata['host'];
// $callbackUrl为上传回调服务器的URL，请将下面的IP和Port配置为您自己的真实URL信息。
$callbackUrl = 'http://88.88.88.88:8888/aliyun-oss-appserver-php/php/callback.php';
$dir = '';          // 用户上传文件时指定的前缀。

$callback_param = array('callbackUrl' => $callbackUrl,
    'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
    'callbackBodyType' => "application/x-www-form-urlencoded");
$callback_string = json_encode($callback_param);

$base64_callback_body = base64_encode($callback_string);
$now = time();
$expire = 7200;  //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问。
$end = $now + $expire;
$expiration = gmt_iso8601($end);


//最大文件大小.用户可以自己设置
$condition = array(0 => 'content-length-range', 1 => 0, 2 => 1048576000);
$conditions[] = $condition;

// 表示用户上传的数据，必须是以$dir开始，不然上传会失败，这一步不是必须项，只是为了安全起见，防止用户通过policy上传到别人的目录。
$start = array(0 => 'starts-with', 1 => '', 2 => $dir);
$conditions[] = $start;


$arr = array('expiration' => $expiration, 'conditions' => $conditions);
$policy = json_encode($arr);
$base64_policy = base64_encode($policy);
$string_to_sign = $base64_policy;
$signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

$response = array();
$response['accessid'] = $id;
$response['host'] = $host;
$response['policy'] = $base64_policy;
$response['signature'] = $signature;
$response['expire'] = $end;
$response['callback'] = $base64_callback_body;
$response['dir'] = $dir;  // 这个参数是设置用户上传文件时指定的前缀。
echo json_encode($response);
?>
