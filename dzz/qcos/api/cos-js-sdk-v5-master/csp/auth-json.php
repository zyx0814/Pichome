<?php

/**
 * php 签名样例
 */

function isActionAllow($method, $pathname, $query, $headers)
{

    $allow = true;

    // // TODO 这里判断自己网站的登录态
    // if ($!logined) {
    //     $allow = false;
    //     return $allow;
    // }

    // 请求可能带有点所有 action
    // acl,cors,policy,location,tagging,lifecycle,versioning,replication,versions,delete,restore,uploads

    // 请求跟路径，只允许获取 UploadId
    if ($pathname === '/' && !($method === 'get' && isset($query['uploads']))) {
        $allow = false;
    }

    // 不允许前端获取和修改文件权限
    if ($pathname !== '/' && isset($query['acl'])) {
        $allow = false;
    }

    // 这里应该根据需要，限制当前站点的用户只允许操作什么样的路径
    if ($method === 'delete' && $pathname !== '/') { // 这里控制是否允许删除文件
        // TODO 这里控制是否允许删除文件
    }
    if ($method === 'put' && $pathname !== '/') { // 这里控制是否允许上传和修改文件
        // TODO 这里控制是否允许上传和修改文件
    }
    if ($method === 'get' && $pathname !== '/') { // 这里控制是否获取文件和文件相关信息
        // TODO 这里控制是否允许获取文件和文件相关信息
    }

    return $allow;

}

/*
 * 获取签名
 * @param string $method 请求类型 method
 * @param string $pathname 文件名称
 * @param array $query query参数
 * @param array $headers headers
 * @return string 签名字符串
 */
function getAuthorization($method, $pathname, $query, $headers)
{

    // 获取个人 API 密钥 https://console.qcloud.com/capi
    $SecretId = 'AKIDxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
    $SecretKey = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

    // 整理参数
    !$query && ($query = array());
    !$headers && ($headers = array());
    $method = strtolower($method ? $method : 'get');
    $pathname = $pathname ? $pathname : '/';
    substr($pathname, 0, 1) != '/' && ($pathname = '/' . $pathname);

    // 注意这里要过滤好允许什么样的操作
    if (!isActionAllow($method, $pathname, $query, $headers)) {
        return 'action deny';
    }

    // 工具方法
    function getObjectKeys($obj)
    {
        $list = array_keys($obj);
        sort($list);
        return $list;
    }

    function obj2str($obj)
    {
        $list = array();
        $keyList = getObjectKeys($obj);
        $len = count($keyList);
        for ($i = 0; $i < $len; $i++) {
            $key = $keyList[$i];
            $val = isset($obj[$key]) ? $obj[$key] : '';
            $key = strtolower($key);
            $list[] = rawurlencode($key) . '=' . rawurlencode($val);
        }
        return implode('&', $list);
    }

    // 签名有效起止时间
    $now = time() - 1;
    $expired = $now + 600; // 签名过期时刻，600 秒后

    // 要用到的 Authorization 参数列表
    $qSignAlgorithm = 'sha1';
    $qAk = $SecretId;
    $qSignTime = $now . ';' . $expired;
    $qKeyTime = $now . ';' . $expired;
    $qHeaderList = strtolower(implode(';', getObjectKeys($headers)));
    $qUrlParamList = strtolower(implode(';', getObjectKeys($query)));

    // 签名算法说明文档：https://www.qcloud.com/document/product/436/7778
    // 步骤一：计算 SignKey
    $signKey = hash_hmac("sha1", $qKeyTime, $SecretKey);

    // 步骤二：构成 FormatString
    $formatString = implode("\n", array(strtolower($method), $pathname, obj2str($query), obj2str($headers), ''));

    // 步骤三：计算 StringToSign
    $stringToSign = implode("\n", array('sha1', $qSignTime, sha1($formatString), ''));

    // 步骤四：计算 Signature
    $qSignature = hash_hmac('sha1', $stringToSign, $signKey);

    // 步骤五：构造 Authorization
    $authorization = implode('&', array(
        'q-sign-algorithm=' . $qSignAlgorithm,
        'q-ak=' . $qAk,
        'q-sign-time=' . $qSignTime,
        'q-key-time=' . $qKeyTime,
        'q-header-list=' . $qHeaderList,
        'q-url-param-list=' . $qUrlParamList,
        'q-signature=' . $qSignature
    ));

    return $authorization;
}


// 获取前端过来的参数
$inputBody = file_get_contents("php://input");
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $inputBody){
    $params = json_decode($inputBody, 1);
    $pathname = isset($params['pathname']) ? $params['pathname'] : '/';
    $method = isset($params['method']) ? $params['method'] : 'get';
    $query = isset($params['query']) ? $params['query'] : array();
    $headers = isset($params['headers']) ? $params['headers'] : array();
} else {
    $pathname = isset($_GET['pathname']) ? $_GET['pathname'] : '/';
    $method = isset($_GET['method']) ? $_GET['method'] : 'get';
    $query = isset($_GET['query']) && $_GET['query'] ? json_decode($_GET['query'], 1) : array();
    $headers = isset($_GET['headers']) && $_GET['headers'] ? json_decode($_GET['headers'], 1) : array();
}

// 返回数据给前端
header('Content-Type: text/plain');
header('Allow-Control-Allow-Origin: http://127.0.0.1'); // 这里修改允许跨域访问的网站
header('Allow-Control-Allow-Headers: origin,accept,content-type');
$sign = getAuthorization($method, $pathname, $query, $headers);

echo '{"sign":"' . $sign .'"}';
