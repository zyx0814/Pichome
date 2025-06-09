<?php

$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'getHistory') {//获取历史对话
    $idval = $_GET['rid'] ?? '';
    $type = $_GET['type'] ?? 'image';
    if ($type == 'image') {
        $metadata = IO::getMeta($idval);
        if (!$metadata) json_decode(['success' => false, 'msg' => 'file is not exists']);
        if ($metadata['aid']) {
            $idval = $metadata['aid'];
            $idtype = 0;
        } else {
            $idval = $idval;
            $idtype = 1;
        }
        $historydaya = C::t('#aiXhimage#ai_xhchat')->fetchContentByIdvalue($idval, $idtype);
        $returndata = [];
        foreach ($historydaya as $k => $v) {
            if ($k == 0) continue;
            $tmmpcontent = json_decode($v['content'], true);
            $returndata[$v['id']] = [
                'role' => $v['role'],
                'content' => $tmmpcontent['content'],
                'dateline' => dgmdate($v['dateline'],'Y-m-d H:i:s' )
            ];
        }
    } elseif ($type == 'chat') {
        $idval = $_GET['id'] ?? '';
        $idtype= 2;
        $historydaya = C::t('#aiXhimage#ai_xhchat')->fetchContentByIdvalue($idval, $idtype);
        $returndata = [];
        foreach ($historydaya as $k => $v) {
            $tmmpcontent = json_decode($v['content'], true);
            $returndata[$v['id']] = [
                'role' => $v['role'],
                'content' => $tmmpcontent['content'],
                'dateline' => dgmdate($v['dateline'],'Y-m-d H:i:s' )
            ];
        }
    }

    exit(json_encode(['success' => true, 'data' => $returndata]));

} elseif($do == 'clearchat'){
    $idval = $_GET['rid'] ?? '';
    $type = $_GET['type'] ?? 'image';
    if ($type == 'image') {
        $metadata = IO::getMeta($idval);
        if (!$metadata) json_decode(['success' => false, 'msg' => 'file is not exists']);
        if ($metadata['aid']) {
            $idval = $metadata['aid'];
            $idtype = 0;
        } else {
            $idval = $idval;
            $idtype = 1;
        }
        C::t('#aiXhimage#ai_xhchat')->deleteContentByIdvalue($idval, $idtype);


    } elseif ($type == 'chat') {
        $idval = $_GET['id'] ?? '';
        $idtype= 2;
        C::t('#aiXhimage#ai_xhchat')->deleteContentByIdvalue($idval, $idtype);
    }

    exit(json_encode(['success' => true]));
}elseif ($do == 'createChat') {//创建会话

} else {
    // 设置时区为东八区
    date_default_timezone_set('PRC');

// 这行代码用于关闭输出缓冲。关闭后，脚本的输出将立即发送到浏览器，而不是等待缓冲区填满或脚本执行完毕。
    ini_set('output_buffering', 'off');

// 这行代码禁用了 zlib 压缩。通常情况下，启用 zlib 压缩可以减小发送到浏览器的数据量，但对于服务器发送事件来说，实时性更重要，因此需要禁用压缩。
    ini_set('zlib.output_compression', false);

// 这行代码使用循环来清空所有当前激活的输出缓冲区。ob_end_flush() 函数会刷新并关闭最内层的输出缓冲区，@ 符号用于抑制可能出现的错误或警告。
    while (@ob_end_flush()) {
    }

// 这行代码设置 HTTP 响应的 Content-Type 为 text/event-stream，这是服务器发送事件（SSE）的 MIME 类型。
    header('Content-Type: text/event-stream');

// 这行代码设置 HTTP 响应的 Cache-Control 为 no-cache，告诉浏览器不要缓存此响应。
    header('Cache-Control: no-cache');

// 这行代码设置 HTTP 响应的 Connection 为 keep-alive，保持长连接，以便服务器可以持续发送事件到客户端。
    header('Connection: keep-alive');

// 这行代码设置 HTTP 响应的自定义头部 X-Accel-Buffering 为 no，用于禁用某些代理或 Web 服务器（如 Nginx）的缓冲。
// 这有助于确保服务器发送事件在传输过程中不会受到缓冲影响。
    header('X-Accel-Buffering: no');

// 引入调用 OpenAI 接口类，该类由 GPT4 生成大部分代码
    require DZZ_ROOT . './dzz/aiXhimage/class/xhChat.php';

    echo 'data: ' . json_encode(['time' => date('Y-m-d H:i:s'), 'content' => '']) . PHP_EOL . PHP_EOL;
    flush();
// 从 get 中获取提问
    $question = urldecode($_GET['q'] ?? '');
    if (empty($question)) {
        stopMessage('Missing question');
    }

    $question = str_ireplace('{[$add$]}', '+', $question);
    $type = $_GET['type'] ?? 'image';
    $params = [
        'question' => $question,
        'type' => $type,
        'hasStream' => 1
    ];

    if ($type == 'image') {
        $idval = $_GET['rid'] ?? '';
        //缺少图片id参数
        if (!$idval) {
            stopMessage('Missing image id');
        } else {
            //获取对话id
            $metadata = IO::getMeta($idval);
            if (!$metadata) stopMessage('file is not exists');
            else {
                $rid = $idval;
                if ($metadata['aid']) {
                    $idval = $metadata['aid'];
                    $idtype = 0;
                } else {
                    $idtype = 1;
                }
                $allowExts = ['jpg', 'jpeg', 'png', 'webp'];
                $imgurl = '';
                $imgurl = C::t('pichome_resources')->geticondata_by_rid($rid,1,0);
                if(!$imgurl) $imgurl = IO::getThumb($rid,'small',0,1, 1,1);
                if(!$imgurl){
                    if (in_array($metadata['ext'], $allowExts) && $metadata['filesize'] <= 10 * 1024 * 1024) {
                        $imgurl = IO::getStream('attach::' . $metadata['aid']);
                        $params['idtype'] = $idtype;
                        $params['idval'] = $idval;
                        $params['imageurl'] = $imgurl;
                        $params['processname'] = waitLock('DZZ_LOCK_XHIAMGEPARSE');
                        $xhchatclient = new xhChat();
                        $xhchatclient->pareseMethod($params);
                    } else {
                        stopMessage('file is not allowed');
                    }
                }else{
                    $params['idtype'] = $idtype;
                    $params['idval'] = $idval;
                    $params['imageurl'] = $imgurl;
                    $params['processname'] = waitLock('DZZ_LOCK_XHIAMGEPARSE');
                    $xhchatclient = new xhChat();
                    $xhchatclient->pareseMethod($params);
                }

            }

        }
    }
    elseif($type == 'chat'){
        $idval = $_GET['id'] ?? '';
        $idtype = 2;
        $params['idtype'] = $idtype;
        $params['idval'] = $idval;
        $params['processname'] = waitLock('DZZ_LOCK_XHAICHAT');
        $xhchatclient = new xhChat();
        $xhchatclient->pareseMethod($params);
    }
}

function waitLock($processnameprefix){
    $locked = true;
    for($i=0;$i<2;$i++){
        $processname =$processnameprefix.$i;
        if (!dzz_process::islocked($processname, 60)) {
            $locked=false;
            break;
        }
    }
    if($locked){
        sleep(3);
        for($i=0;$i<2;$i++){
            $processname = $processnameprefix.$i;
            if (!dzz_process::islocked($processname, 60)) {
                $locked=false;
                break;
            }
        }
        if($locked){
            stopMessage(lang('system_busy'));;
        }
    }
    return $processname;
}
function stopMessage($messgae)
{
    echo "message: close" . PHP_EOL;
    echo "data: " . json_encode(['time' => date('Y-m-d H:i:s'), 'content' => $messgae]) . PHP_EOL . PHP_EOL;
    echo 'retry: 86400000' . PHP_EOL;
    echo "event: close" . PHP_EOL;
    echo "data: Connection closed" . PHP_EOL . PHP_EOL;
    flush();
    exit();
}
