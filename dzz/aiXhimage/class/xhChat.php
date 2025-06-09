<?php
require_once DZZ_ROOT . 'dzz/aiXhimage/websocket/vendor/autoload.php';
require_once 'Class.StreamHandler.php';

use WebSocket\Client as Client;
use \C as C;

class xhChat
{
    private $appid = '';
    private $ak = '';
    private $sk = '';
    private $idval = '';
    private $idtype = '';
    private $isstream = '';
    private $connectid = 0;
    private $apiurl = '';

    private $processname = '';
    private $processnamequery = '';

    public function pareseMethod($params)
    {
        $methods = [
            'image' => 'xh::chatImage',
            'chat'=>'xh::chat',
        ];
        $path = $methods[$params['type']];
        $this->getApiData($path, $params);
    }
    public function getApiData($path, $params)
    {
        //解析方法
        $this->parseMethod($path);
        //获取api配置项
         $this->getapiConfig();

         $this->processname = $params['processname'];

        //判断是否为多轮对话
        $this->isstream = $params['hasStream'] ?? 0;
        if($this->isstream){
            $this->idval = $params['idval'];
            $this->idtype = $params['idtype'];
        }

        //获取请求体
        $body = $this->getImageBody($params);

        if (method_exists($this, $this->method)) {

            return call_user_func([$this, $this->method], $body);

        }else{
            return ['error_msg'=>'function is not exists'];
        }

    }

    private function getapiConfig()
    {
        $apiSetting = [
            'chatImage'=>'setting_xhImageDataSetting',
            'chat'=>'setting_xhDataSetting'
        ];
        $imageSetting = C::t('setting')->fetch($apiSetting[$this->method], true);
        $this->appid = $imageSetting['appid'];
        $this->ak = $imageSetting['ak'];
        $this->sk = $imageSetting['sk'];
    }

    private function parseMethod($path)
    {
        $arr = explode(':', $path);
        $connectid = $arr[1];
        $method = $arr[2];
        $this->connectid = $connectid;
        $this->method = $method;
        $this->getapiUrl($this->method);
        // return array('id' => $connectid, 'method' => $method);
    }

    private function getapiUrl($method)
    {
        $apiurls = [
            'chatImage' => 'wss://spark-api.cn-huabei-1.xf-yun.com/v2.1/image',
            'chat'=>'wss://spark-api.xf-yun.com/v3.5/chat'
        ];
        $this->apiurl = $apiurls[$method];
    }

    private function chat($body)
    {
        $authUrl = $this->assembleAuthUrl("GET");
        return $this->getQuerstData($authUrl, $body);
    }
    private function chatImage($body)
    {
        $authUrl = $this->assembleAuthUrl("GET");
        return $this->getQuerstData($authUrl, $body);
    }

    private function getImageData($imageUrl)
    {
        $imagedata = file_get_contents($imageUrl);
        return base64_encode($imagedata);
    }

    private function getQuerstData($authUrl, $body)
    {

        //创建ws连接对象
        $client = new Client($authUrl);

        // 连接到 WebSocket 服务器
        if ($client) {
            if ($this->isstream) {
                $streamHandler = new StreamHandler([
                    'qmd5' => md5($body . '' . time())
                ]);
            }
            // 发送数据到 WebSocket 服务器
            $client->send($body);
            // 从 WebSocket 服务器接收数据
            $answer = "";
            while (true) {
                $response = $client->receive();
                if ($this->isstream) $streamHandler->xhcallback($response);
                $resp = json_decode($response, true);
                $code = $resp["header"]["code"];
                if (0 == $code) {
                    $status = $resp["header"]["status"];
                    if ($status != 2) {
                        $content = $resp['payload']['choices']['text'][0]['content'];
                        $answer .= $content;
                    } else {
                        $content = $resp['payload']['choices']['text'][0]['content'];
                         $answer .= $content;
                        $total_tokens = $resp['payload']['usage']['text']['total_tokens'];
                        // print("\n本次消耗token用量：\n");
                        // print($total_tokens);
                        //记录数据表
                        break;
                    }
                } else {
                    $response = json_decode($response, true);
                    $error_message = str_replace("\n\t",'<br>',$response['header']['message']);
                    $ret['error_msg'] = "服务返回报错：" . $error_message;
                    break;
                    // print_r($ret);die;

                }

            }
            if ($this->isstream) {
                $message=[
                    'role'=>'assistant',
                    'content' => $answer,
                    'content_type' => 'text',
                ];
                $anwser =[
                    ['content'=>json_encode($message), 'totaltoken' => $total_tokens]
                ];
                $this->insetMessageData($anwser);
            }
            $ret['result'] = $answer;
            $ret['totaltoken'] = $total_tokens;
           // \dzz_process::unlock($this->processname1);
        } else {
            $ret['error_msg'] = "无法连接到 WebSocket 服务器";
        }
        \dzz_process::unlock($this->processname);
        return $ret;
    }

    //构造参数体
    private function getImageBody($params)
    {

        $paramters = [
            'chatImage' => ['domain' => 'image', 'temperature' => 0.5, 'top_k' => 4, 'max_tokens' => 8192, 'auditing' => 'default'],
            'chat' => ['domain' => 'generalv3.5', 'temperature' => 0.5, 'top_k' => 4, 'max_tokens' => 8192, 'auditing' => 'default']
        ];

        $body['header'] = [
            "app_id" => $this->appid,
            'uid'=>"'".getglobal('uid')."'"
        ];
        $body['parameter'] = [
            "chat" => [
                "domain" => $paramters[$this->method]['domain'],
                "temperature" => $paramters[$this->method]['temperature'],
                "top_k" => $paramters[$this->method]['top_k'],
                "max_tokens" => $paramters[$this->method]['max_tokens'],
                "auditing" => $paramters[$this->method]['auditing']
            ]
        ];
        if($this->method == 'chatImage'){
            if ($this->isstream) {
                $messagedatas = $this->getMessageData();
                if ($messagedatas) {
                    foreach ($messagedatas as $v) {
                        $messagedata = json_decode($v, true);
                        $body['payload']['message']['text'][] = $messagedata;
                    }
                    $newtext = [
                        "role" => "user",
                        "content" => $params['question'],
                        "content_type" => "text"
                    ];
                    $body['payload']['message']['text'][] = $newtext;
                    $questions = [
                       ['content'=> json_encode($newtext)]
                    ];
                    $this->insetMessageData($questions);
                } else {
                    $imagedata = $params['isurl'] ? $params['imageurl'] : $this->getImageData($params['imageurl']);
                    $body['payload'] = [
                        "message" => [
                            "text" => [
                                [
                                    "role" => "user",
                                    "content" => $imagedata,
                                    "content_type" => "image"
                                ], [

                                    "role" => "user",
                                    "content" => $params['question'],
                                    "content_type" => "text"
                                ]
                            ]

                        ]];
                    $questions = [
                        ['content'=>json_encode($body['payload']['message']['text'][0])],
                        ['content'=>json_encode($body['payload']['message']['text'][1])]
                    ];
                    $this->insetMessageData($questions);
                }
            }
            else {
                $body['payload'] = [
                    "message" => [
                        "text" => [
                            [
                                "role" => "user",
                                "content" => $params['isurl'] ? $params['imageurl'] : $this->getImageData($params['imageurl']),
                                "content_type" => "image"
                            ], [

                                "role" => "user",
                                "content" => $params['question'],
                                "content_type" => "text"
                            ]
                        ]

                    ]];
            }
        }
        elseif($this->method == 'chat'){
            if ($this->isstream) {
                $messagedatas = $this->getMessageData();
                if ($messagedatas) {
                    foreach ($messagedatas as $v) {
                        $messagedata = json_decode($v, true);
                        $body['payload']['message']['text'][] = $messagedata;
                    }
                    $newtext = [
                        "role" => "user",
                        "content" => $params['question'],
                        "content_type" => "text"
                    ];
                    $body['payload']['message']['text'][] = $newtext;
                    $questions = [
                       ['content'=>json_encode($newtext)]
                    ];
                    $this->insetMessageData($questions);
                } else {
                    $newtext =  [
                        "role" => "user",
                        "content" => $params['question'],
                        "content_type" => "text"
                    ];
                    $body['payload'] = [
                        "message" => [
                            "text" => [
                                $newtext
                            ]

                        ]];
                    $questions = [
                       ['content'=>json_encode($newtext)],
                    ];
                    $this->insetMessageData($questions);
                }
            }
            else {
                $body['payload'] = [
                    "message" => [
                        "text" => [
                            [
                                "role" => "user",
                                "content" => $params['question'],
                                "content_type" => "text"
                            ]
                        ]

                    ]];
            }
        }
        $json_string = json_encode($body);
        return $json_string;

    }

    private function getMessageData()
    {   $messagedatas = [];
        foreach(C::t('#aiXhimage#ai_xhchat')->fetchContentByIdvalue($this->idval,$this->idtype) as $v){
            $messagedatas[] = $v['content'];
        }
        return $messagedatas;
    }

    private function insetMessageData($messagedatas){

        foreach($messagedatas as $v){
            $cdata = json_decode($v['content'],true);
            if(!$cdata['role']) continue;
            $setarr = [
                'idval'=>$this->idval,
                'idtype'=>$this->idtype,
                'role'=>$cdata['role'],
                'content'=>$v['content'],
                'totaltoken'=>isset($v['totaltoken']) ? intval($v['totaltoken']):0
            ];
            C::t('#aiXhimage#ai_xhchat')->insertData($setarr);
        }

    }


    //鉴权方法
    private function assembleAuthUrl($method)
    {
        if ($this->ak == "" && $this->sk == "") { // 不鉴权
            return $this->apiurl;
        }
        $ul = parse_url($this->apiurl); // 解析地址
        if ($ul === false) { // 地址不对，也不鉴权
            return $this->apiurl;
        }

        // // $date = date(DATE_RFC1123); // 获取当前时间并格式化为RFC1123格式的字符串
        $timestamp = time();
        $rfc1123_format = gmdate("D, d M Y H:i:s \G\M\T", $timestamp);
        // $rfc1123_format = "Mon, 31 Jul 2023 08:24:03 GMT";


        // 参与签名的字段 host, date, request-line
        $signString = array("host: " . $ul["host"], "date: " . $rfc1123_format, $method . " " . $ul["path"] . " HTTP/1.1");

        // 对签名字符串进行排序，确保顺序一致
        // ksort($signString);
        //print_r($signString);die;
        // 将签名字符串拼接成一个字符串
        $sgin = implode("\n", $signString);
        // print( $sgin);

        // 对签名字符串进行HMAC-SHA256加密，得到签名结果
        $sha = hash_hmac('sha256', $sgin, $this->sk, true);
        //print("signature_sha:\n");
        // print($sha);
        $signature_sha_base64 = base64_encode($sha);

        // 将API密钥、算法、头部信息和签名结果拼接成一个授权URL
        $authUrl = "api_key=\"$this->ak\", algorithm=\"hmac-sha256\", headers=\"host date request-line\", signature=\"$signature_sha_base64\"";

        // 对授权URL进行Base64编码，并添加到原始地址后面作为查询参数
        $authAddr = $this->apiurl . '?' . http_build_query(array(
                'host' => $ul['host'],
                'date' => $rfc1123_format,
                'authorization' => base64_encode($authUrl),
            ));

        return $authAddr;
    }



}

?>