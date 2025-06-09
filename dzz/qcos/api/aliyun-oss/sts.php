<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/10
 * Time: 21:09
 */
if (is_file( '../../../.././core/api/aliyun/vendor/autoload.php')) {
    require_once   '../../../.././core/api/aliyun/vendor/autoload.php';
}
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
require_once('../../../.././core/coreBase.php');
$dzz = C::app();

Hook::listen('dzz_initbefore');//初始化前钩子

$dzz->init();
$setting = C::t('setting')->fetch_all('defaultspacesetting',true);
$connectdata = C::t('connect_storage')->fetch($setting['defaultspacesetting']['did']);
$region = str_replace(array('https://oss-','http://oss-','-internal','.aliyuncs.com'),'',$connectdata['hostname']);
$access_id = dzzdecode($connectdata['access_id'], $connectdata['bz']);
if (empty($access_id)) $access_id = $connectdata['access_id'];
$access_key = dzzdecode($connectdata['access_key'], $connectdata['bz']);
$RoleArn=$connectdata['extra'];
//构建一个阿里云客户端，用于发起请求。

AlibabaCloud::accessKeyClient($access_id, $access_key)
    ->regionId($region)
    ->asDefaultClient();
//设置参数，发起请求。
try {
    $result = AlibabaCloud::rpc()
        ->product('Sts')
        ->scheme('https') // https | http
        ->version('2015-04-01')
        ->action('AssumeRole')
        ->method('POST')
        ->host('sts.aliyuncs.com')
        ->options([
            'query' => [
                'RegionId' => $region,
                'RoleArn' => $RoleArn,//"acs:ram::1235666883402774:role/ossmanager",
                'RoleSessionName' => "upload",
            ],
        ])
        ->request();
    $data = $result->toArray();
} catch (ClientException $e) {
    $data =  array('error'=>$e->getErrorMessage());
} catch (ServerException $e) {
    $data =  array('error'=>$e->getErrorMessage());
}
exit(json_encode($data));


