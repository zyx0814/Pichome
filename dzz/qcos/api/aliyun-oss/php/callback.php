<?php
// 1.获取OSS的签名header和公钥url header
$authorizationBase64 = "";
$pubKeyUrlBase64 = "";
/*
 * 注意：如果要使用HTTP_AUTHORIZATION头，你需要先在apache或者nginx中设置rewrite，以apache为例，修改
 * 配置文件/etc/httpd/conf/httpd.conf(以你的apache安装路径为准)，在DirectoryIndex index.php这行下面增加以下两行
    RewriteEngine On
    RewriteRule .* - [env=HTTP_AUTHORIZATION:%{HTTP:Authorization},last]
 * */
if (isset($_SERVER['HTTP_AUTHORIZATION']))
{
    $authorizationBase64 = $_SERVER['HTTP_AUTHORIZATION'];
}
if (isset($_SERVER['HTTP_X_OSS_PUB_KEY_URL']))
{
    $pubKeyUrlBase64 = $_SERVER['HTTP_X_OSS_PUB_KEY_URL'];
}

if ($authorizationBase64 == '' || $pubKeyUrlBase64 == '')
{
    header("http/1.1 403 Forbidden");
    exit();
}

// 2.获取OSS的签名
$authorization = base64_decode($authorizationBase64);

// 3.获取公钥
$pubKeyUrl = base64_decode($pubKeyUrlBase64);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $pubKeyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
$pubKey = curl_exec($ch);
if ($pubKey == "")
{
    //header("http/1.1 403 Forbidden");
    exit();
}

// 4.获取回调body
$body = file_get_contents('php://input');

// 5.拼接待签名字符串
$authStr = '';
$path = $_SERVER['REQUEST_URI'];
$pos = strpos($path, '?');
if ($pos === false)
{
    $authStr = urldecode($path)."\n".$body;
}
else
{
    $authStr = urldecode(substr($path, 0, $pos)).substr($path, $pos, strlen($path) - $pos)."\n".$body;
}

// 6.验证签名
$ok = openssl_verify($authStr, $authorization, $pubKey, OPENSSL_ALGO_MD5);
if ($ok == 1)
{
    header("Content-Type: application/json");
    $data = array("Status"=>"Ok");
    echo json_encode($data);
}
else
{
    //header("http/1.1 403 Forbidden");
    exit();
}

?>



