<?php

if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

function dshowmessage($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0)
{
    global $_G, $show_message;
    $_G['messageparam'] = func_get_args();

    if ($extraparam['break']) {
        return;
    }
    $_G['inshowmessage'] = true;

    $param = array(
        'header' => false,
        'timeout' => null,
        'refreshtime' => null,
        'alert' => null,
        'login' => false,
        'location'=>false,
        'extrajs' => '',
    );

    $navtitle = lang('board_message');

    if ($custom) {
        $alerttype = 'alert_info';
        $show_message = $message;
        include template('common/showmessage');
        dexit();
    }
    define('CACHE_FORBIDDEN', TRUE);
    if ($url_forward) {
        $param['timeout'] = true;
    }
    foreach ($extraparam as $k => $v) {
        $param[$k] = $v;
    }
    $timedefault = intval($param['refreshtime'] === null ? $_G['setting']['refreshtime'] : $param['refreshtime']);
    if ($param['timeout'] !== null) {
        $refreshsecond = !empty($timedefault) ? $timedefault : 3;
        $refreshtime = $refreshsecond * 1000;
    } else {
        $refreshtime = $refreshsecond = 0;
    }
    if ($param['login'] && $_G['uid'] || $url_forward) {
        $param['login'] = false;
    }
    //参数 header  PHP跳转
    $param['header'] = $url_forward && $param['header'] ? true : false;
    if ($param['header']) {
        header("HTTP/1.1 301 Moved Permanently");
        dheader("location: " . str_replace('&amp;', '&', $url_forward));
        dexit();
    }
    //location js跳转
    $url_forward_js = addslashes(str_replace('\\', '%27', $url_forward));
    if ($param['location'] && !empty($_G['inajax'])) {
        include template('common/header_ajax');
        echo '<script type="text/javascript" reload="1">window.location.href=\'' . $url_forward_js . '\';</script>';
        include template('common/footer_ajax');
        dexit();
    }
    //转登录
    if ($param['login']) {
        dheader('location: user.php?mod=login' . ($url_forward ? '&referer=' . urlencode($url_forward) : ''));
        dexit();
    }

    $vars = explode(':', $message);
    if (count($vars) == 2) {
        $show_message = lang($vars[1], $values, null, $vars[0]);
    } else {
        $show_message = lang($message, $values);
    }

    if ($param['alert'] === null) {
        $alerttype = $url_forward ? (preg_match('/\_(succeed|success|成功)$/', $message) ? 'alert_right' : 'alert_info') : 'alert_info';
    } else {
        $alerttype = 'alert_' . $param['alert'];
    }

    $extra = '';
    if ($param['timeout']) {
        $extra .= 'setTimeout("window.location.href =\'' . $url_forward_js . '\';", ' . $refreshtime . ');';
    }
    $show_message .= $extra ? '<script type="text/javascript" reload="1">' . $extra . '</script>' : '';
    $show_message .= $param['extrajs'] ? $param['extrajs'] : '';
    include template('common/showmessage');
    exit();
}
