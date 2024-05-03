<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

define('CURSCRIPT', 'misc');
require __DIR__ . '/../core/coreBase.php';
require_once __DIR__ . '/../core/class/class_Color.php';

@set_time_limit(0);
error_reporting(0);
$cachelist = array();
$dzz = dzz_app::instance();
global $_G;
$dzz->cachelist = $cachelist;
$dzz->init_cron = false;
$dzz->init_setting = true;
$dzz->init_user = false;
$dzz->init_session = false;
$dzz->init_misc = false;
$dzz->init();



$config = array(
    'dbcharset' => $_G['config']['db']['1']['dbcharset'],
    'charset' => $_G['config']['output']['charset'],
    'tablepre' => $_G['config']['db']['1']['tablepre']
);



$theurl = 'update.php';

$_G['siteurl'] = preg_replace('/\/install\/$/i', '/', $_G['siteurl']);


if ($_GET['from']) {
    if (md5($_GET['from'] . $_G['config']['security']['authkey']) != $_GET['frommd5']) {
        $refererarr = parse_url(dreferer());
        list($dbreturnurl, $dbreturnurlmd5) = explode("\t", authcode($_GET['from']));
        if (md5($dbreturnurl) == $dbreturnurlmd5) {
            $dbreturnurlarr = parse_url($dbreturnurl);

        } else {
            $dbreturnurlarr = parse_url($_GET['from']);
        }
        parse_str($dbreturnurlarr['query'], $dbreturnurlparamarr);
        $operation = $dbreturnurlparamarr['operation'];
        $version = $dbreturnurlparamarr['version'];
        $release = $dbreturnurlparamarr['release'];
        if (!$operation || !$version || !$release) {
            show_msg('请求的参数不正确');
        }
        $time = $_G['timestamp'];
        dheader('Location: ' . $_G['siteurl'] . basename($refererarr['path']) . '?action=upgrade&operation=' . $operation . '&version=' . $version . '&release=' . $release . '&ungetfrom=' . $time . '&ungetfrommd5=' . md5($time . $_G['config']['security']['authkey']));
    }
}
$lockfile = DZZ_ROOT . './data/update.lock';
if (file_exists($lockfile) && !$_GET['from']) {
    show_msg('请您先登录服务器ftp，手工删除 ./data/update.lock 文件，再次运行本文件进行升级。');
}

$sqlfile = DZZ_ROOT . './install/data/install.sql';

if (!file_exists($sqlfile)) {
    show_msg('SQL文件 ' . $sqlfile . ' 不存在');
}

if ($_POST['delsubmit']) {
    if (!empty($_POST['deltables'])) {
        foreach ($_POST['deltables'] as $tname => $value) {
            DB::query("DROP TABLE `" . DB::table($tname) . "`");
        }
    }
    if (!empty($_POST['delcols'])) {
        foreach ($_POST['delcols'] as $tname => $cols) {
            foreach ($cols as $col => $indexs) {
                if ($col == 'PRIMARY') {
                    DB::query("ALTER TABLE " . DB::table($tname) . " DROP PRIMARY KEY", 'SILENT');
                } elseif ($col == 'KEY' || $col == 'UNIQUE') {
                    foreach ($indexs as $index => $value) {
                        DB::query("ALTER TABLE " . DB::table($tname) . " DROP INDEX `$index`", 'SILENT');
                    }
                } else {
                    DB::query("ALTER TABLE " . DB::table($tname) . " DROP `$col`");
                }
            }
        }
    }
    show_msg('删除表和字段操作完成了', $theurl . '?step=cache');
}

function waitingdb($curstep, $sqlarray)
{
    global $theurl;
    foreach ($sqlarray as $key => $sql) {
        $sqlurl .= '&sql[]=' . md5($sql);
        $sendsql .= '<img width="1" height="1" src="' . $theurl . '?step=' . $curstep . '&waitingdb=1&sqlid=' . $key . '">';
    }
    show_msg("优化数据表", $theurl . '?step=waitingdb&nextstep=' . $curstep . $sqlurl . '&sendsql=' . base64_encode($sendsql), 5000, 1);
}

function q_runquery($sql)
{
    global $_G;
    $tablepre = $_G['config']['db'][1]['tablepre'];
    $dbcharset = $_G['config']['db'][1]['dbcharset'];

    $sql = str_replace(array(' dzz_', ' `dzz_', ' cdb_', ' `cdb_'), array(' {tablepre}', ' `{tablepre}', ' {tablepre}', ' `{tablepre}'), $sql);

    $sql = str_replace("\r", "\n", str_replace(array(' {tablepre}', ' `{tablepre}'), array(' ' . $tablepre, ' `' . $tablepre), $sql));

    $ret = array();
    $num = 0;
    foreach (explode(";\n", trim($sql)) as $query) {
        $queries = explode("\n", trim($query));
        foreach ($queries as $query) {
            $ret[$num] .= $query[0] == '#' || $query[0] . $query[1] == '--' ? '' : $query;
        }
        $num++;
    }
    unset($sql);
    foreach ($ret as $query) {
        $query = trim($query);
        if ($query) {

            if (substr($query, 0, 12) == 'CREATE TABLE') {
                $name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
                DB::query(q_createtable($query, $dbcharset));

            } else {
                DB::query($query);
            }

        }
    }
}

function q_createtable($sql, $dbcharset)
{
    $type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
    $type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
    return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql) .
        (" ENGINE=$type DEFAULT CHARSET=$dbcharset");
}

if (empty($_GET['step'])) $_GET['step'] = 'start';

if ($_GET['step'] == 'start') {
    if (!C::t('setting')->fetch('bbclosed')) {
        C::t('setting')->update('bbclosed', 1);
        require_once libfile('function/cache');
        updatecache('setting');
        show_msg('您的站点未关闭，正在关闭，请稍后...', $theurl . '?step=start', 5000);
    }
    show_msg('说明：<br>本升级程序会参照最新的SQL文件，对数据库进行同步升级。<br>
			请确保当前目录下 ./data/install.sql 文件为最新版本。<br><br>
			<a href="' . $theurl . '?step=prepare' . ($_GET['from'] ? '&from=' . rawurlencode($_GET['from']) . '&frommd5=' . rawurlencode($_GET['frommd5']) : '') . '">准备完毕，升级开始</a>');

} elseif ($_GET['step'] == 'waitingdb') {
    $query = DB::fetch_all("SHOW FULL PROCESSLIST");
    foreach ($query as $row) {
        if (in_array(md5($row['Info']), $_GET['sql'])) {
            $list .= '[时长]:' . $row['Time'] . '秒 [状态]:<b>' . $row['State'] . '</b>[信息]:' . $row['Info'] . '<br><br>';
        }
    }
    if (empty($list) && empty($_GET['sendsql'])) {
        $msg = '准备进入下一步操作，请稍后...';
        $notice = '';
        $url = "?step=$_GET[nextstep]";
        $time = 5;
    } else {
        $msg = '正在升级数据，请稍后...';
        $notice = '<br><br><b>以下是正在执行的数据库升级语句:</b><br>' . $list . base64_decode($_GET['sendsql']);
        $sqlurl = implode('&sql[]=', $_GET['sql']);
        $url = "?step=waitingdb&nextstep=$_GET[nextstep]&sql[]=" . $sqlurl;
        $time = 20;
    }
    show_msg($msg, $theurl . $url, $time * 1000, 0, $notice);
} elseif ($_GET['step'] == 'prepare') {
    $repeat = array();
    unset($_G['config']['security']['querysafe']['daction']['4']);
    save_config_file(DZZ_ROOT. './config/config.php',$_G['config'],[],[]);
    /*//检查数据库表 app_market 中有无appurl重复的情况；
    foreach(DB::fetch_all("select appid,appurl from ".DB::table('app_market')." where 1") as $value){
        if(in_array($value['appurl'],$repeat)){
            C::t('app_market')->update($value['appid'],array('appurl'=>$value['appurl'].'&appid='.$value['appid']));
        }
        $repeat[]=$value['appurl'];
    }*/

    show_msg('准备完毕，进入下一步数据库结构升级', $theurl . '?step=sql');
} elseif ($_GET['step'] == 'sql') {
    $sql = implode('', file($sqlfile));
    preg_match_all("/CREATE\s+TABLE.+?(dzz|oaooa)\_(.+?)\s*\((.+?)\)\s*(ENGINE|TYPE)\s*=\s*(\w+)/is", $sql, $matches);
    $newtables = empty($matches[2]) ? array() : str_replace('`', '', $matches[2]);

    $newsqls = empty($matches[0]) ? array() : $matches[0];
    if (empty($newtables) || empty($newsqls)) {
        show_msg('SQL文件内容为空，请确认');
    }

    $i = empty($_GET['i']) ? 0 : intval($_GET['i']);
    $count_i = count($newtables);
    if ($i >= $count_i) {
        show_msg('数据库结构升级完毕，进入下一步数据升级操作', $theurl . '?step=data');
    }
    $newtable = $newtables[$i];

    $specid = intval($_GET['specid']);


    $newcols = getcolumn($newsqls[$i]);

    if (!$query = DB::query("SHOW CREATE TABLE " . DB::table($newtable), 'SILENT')) {
        preg_match("/(CREATE TABLE .+?)\s*(ENGINE|TYPE)\s*=\s*(\w+)/s", $newsqls[$i], $maths);
        $maths[3] = strtoupper($maths[3]);
        if ($maths[3] == 'MEMORY' || $maths[3] == 'HEAP') {
            $type = " ENGINE=MEMORY" . (empty($config['dbcharset']) ? '' : " DEFAULT CHARSET=$config[dbcharset]");
        } else {
            $type = " ENGINE=MYISAM" . (empty($config['dbcharset']) ? '' : " DEFAULT CHARSET=$config[dbcharset]");
        }
        $usql = $maths[1] . $type;
        $usql = str_replace("CREATE TABLE IF NOT EXISTS dzz_", 'CREATE TABLE IF NOT EXISTS ' . $config['tablepre'], $usql);
        $usql = str_replace("CREATE TABLE IF NOT EXISTS `dzz_", 'CREATE TABLE IF NOT EXISTS `' . $config['tablepre'], $usql);
        $usql = str_replace("CREATE TABLE dzz_", 'CREATE TABLE ' . $config['tablepre'], $usql);
        $usql = str_replace("CREATE TABLE `dzz_", 'CREATE TABLE `' . $config['tablepre'], $usql);
        if (!DB::query($usql, 'SILENT')) {
            show_msg('添加表 ' . DB::table($newtable) . ' 出错,请手工执行以下SQL语句后,再重新运行本升级程序:<br><br>' . dhtmlspecialchars($usql));
        } else {
            $msg = '添加表 ' . DB::table($newtable) . ' 完成';
        }
    } else {
        $value = DB::fetch($query);
        $oldcols = getcolumn($value['Create Table']);
        $tablepre = $_G['config']['db'][1]['tablepre'];
        $tablist = fetchtablelist($tablepre);
        $updates = array();
        $allfileds = array_keys($newcols);
        foreach ($newcols as $key => $value) {
            if ($key == 'PRIMARY') {
                if ($value != $oldcols[$key]) {
                    if (!empty($oldcols[$key])) {
                        $baktab = DB::table($newtable . '_bak');
                        if (!in_array($baktab, $tablist)) {
                            $usql = "RENAME TABLE " . DB::table($newtable) . " TO " . DB::table($newtable . '_bak');
                            if (!DB::query($usql, 'SILENT')) {
                                show_msg('升级表 ' . DB::table($newtable) . ' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">' . dhtmlspecialchars($usql) . "</div><br><b>Error</b>: " . DB::error() . "<br><b>Errno.</b>: " . DB::errno());
                            } else {
                                $msg = '表改名 ' . DB::table($newtable) . ' 完成！';
                                show_msg($msg, $theurl . '?step=sql&i=' . $_GET['i']);
                            }
                            $updates[] = "ADD PRIMARY KEY $value";
                        }

                    }

                }
            } elseif ($key == 'KEY') {
                foreach ($value as $subkey => $subvalue) {
                    if (!empty($oldcols['KEY'][$subkey])) {
                        if ($subvalue != $oldcols['KEY'][$subkey]) {
                            $updates[] = "DROP INDEX `$subkey`";
                            $updates[] = "ADD INDEX `$subkey` $subvalue";
                        }
                    } else {
                        $updates[] = "ADD INDEX `$subkey` $subvalue";
                    }
                }
            } elseif ($key == 'UNIQUE') {
                foreach ($value as $subkey => $subvalue) {
                    if (!empty($oldcols['UNIQUE'][$subkey])) {
                        if ($subvalue != $oldcols['UNIQUE'][$subkey]) {
                            $updates[] = "DROP INDEX `$subkey`";
                            $updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
                        }
                    } else {
                        $usql = "ALTER TABLE  " . DB::table($newtable) . " DROP INDEX `$subkey`";
                        DB::query($usql, 'SILENT');
                        $updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
                    }
                }
            } else {
                if (!empty($oldcols[$key])) {
                    if (strtolower($value) != strtolower($oldcols[$key])) {
                        $updates[] = "CHANGE `$key` `$key` $value";
                    }
                } else {
                    $i = array_search($key, $allfileds);
                    $fieldposition = $i > 0 ? 'AFTER `' . $allfileds[$i - 1] . '`' : 'FIRST';
                    $updates[] = "ADD `$key` $value $fieldposition";
                }
            }
        }

        if (!empty($updates)) {
            $usql = "ALTER TABLE " . DB::table($newtable) . " " . implode(', ', $updates);
            if (!DB::query($usql, 'SILENT')) {
                show_msg('升级表 ' . DB::table($newtable) . ' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">' . dhtmlspecialchars($usql) . "</div><br><b>Error</b>: " . DB::error() . "<br><b>Errno.</b>: " . DB::errno());
            } else {
                $msg = '升级表 ' . DB::table($newtable) . ' 完成！';
            }
        } else {
            $msg = '检查表 ' . DB::table($newtable) . ' 完成，不需升级，跳过';
        }
    }

    if ($specid) {
        $newtable = $spectable;
    }

    if (get_special_table_by_num($newtable, $specid + 1)) {
        $next = $theurl . '?step=sql&i=' . ($_GET['i']) . '&specid=' . ($specid + 1);
    } else {
        $next = $theurl . '?step=sql&i=' . ($_GET['i'] + 1);
    }
    show_msg("[ $i / $count_i ] " . $msg, $next);

} elseif ($_GET['step'] == 'data') {
    //如果没有识别码，增加识别码
    if (!$_GET['dp']) {
        //appmarket表增加数据
        if (!DB::result_first("select appid from %t where identifier = %s", array('app_market', 'aiXhimage'))) {
            DB::insert('app_market', array(
                'mid' => 0,
                'appname' => '星火图片理解',
                'appico' => 'appico/202404/29/095905euqa4ujagqqttlu1.png',
                'appdesc' => '',
                'appurl' => '{dzzscript}?mod=aiXhimage',
                'appadminurl' => '{dzzscript}?mod=aiXhimage&op=setting',
                'noticeurl' => '',
                'dateline' => 0,
                'disp' => 0,
                'vendor' => '',
                'haveflash' => 0,
                'isshow' => 1,
                'havetask' => 0,
                'hideInMarket' => 1,
                'feature' => '',
                'fileext' => '',
                'group' => 3,
                'orgid' => 0,
                'position' => 0,
                'system' => 0,
                'identifier' => 'aiXhimage', 'app_path' => 'dzz', 'available' => 1, 'version' => '1.0',
                'upgrade_version' => '', 'check_upgrade_time' => 0, 'extra' => 'a:1:{s:11:\"installfile\";s:11:\"install.php\";}', 'uids' => '', 'showadmin' => 1));
        }
        //更新计划任务
        $cornarr = [
            [
                'available' => 1,
                'type' => 'system',
                'name' => '定时执行ai批量处理到任务表',
                'filename' => 'cron_ai_checkaicrontotask.php',
                'lastrun' => 1714468447,
                'nextrun' => 1714468500,
                'weekday' => -1,
                'day' => -1,
                'hour' => -1,
                'minute' => '0	5	10	15	20	25	30	35	40	45	50	55',
            ],
            [
                'available' => 1,
                'type' => 'system',
                'name' => '定时执行ai批量处理',
                'filename' => 'cron_ai_doaitask.php',
                'lastrun' => 1714469057,
                'nextrun' => 1714469100,
                'weekday' => -1,
                'day' => -1,
                'hour' => -1,
                'minute' => '0	5	10	15	20	25	30	35	40	45	50	55',
            ],
            [
                'available' => 1,
                'type' => 'system',
                'name' => '定时获取预览图缩略图任务',
                'filename' => 'cron_pichome_getpreviewthumb.php',
                'lastrun' => 1714469179,
                'nextrun' => 1714469400,
                'weekday' => -1,
                'day' => -1,
                'hour' => -1,
                'minute' => '0	5	10	15	20	25	30	35	40	45	50	55',
            ],
            [
                'available' => 1,
                'type' => 'system',
                'name' => '定时检查预览图缩略图任务',
                'filename' => 'cron_thumbcheckpreviewchange.php',
                'lastrun' => 1714469240,
                'nextrun' => 171446940,
                'weekday' => -1,
                'day' => -1,
                'hour' => -1,
                'minute' => '0	5	10	15	20	25	30	35	40	45	50	55',
            ],
            [
                'available' => 1,
                'type' => 'system',
                'name' => '定时改变预览图缩略图任务',
                'filename' => 'cron_thumbdopreviewchange.php',
                'lastrun' => 1714469301,
                'nextrun' => 1714469400,
                'weekday' => -1,
                'day' => -1,
                'hour' => -1,
                'minute' => '0	5	10	15	20	25	30	35	40	45	50	55',
            ]

        ];
        foreach ($cornarr as $v) {
            if (!DB::result_first("select cronid from %t where filename = %s", array('cron', $v['filename']))) {
                DB::insert('cron', $v);
            }
        }
        //更新onlyoffice设置位置
        $docstatus = DB::result_first("select docstatus from %t where bz = %s", array('connect_storage', 'dzz'));
        //管理界面不显示onlyoffice_view
        $app = C::t('app_market')->fetch_by_identifier('onlyoffice_view', 'dzz');
        if($app['showadmin']) C::t('app_market')->update($app['appid'], ['showadmin' => 0]);
        if ($docstatus == 1) {
            if (!$app['available']) C::t('app_market')->update($app['appid'], ['available' => 1]);
        } else {
            if ($app['available']) C::t('app_market')->update($app['appid'], ['available' => 1]);
        }
        $app = C::t('app_market')->fetch_by_identifier('ffmpeg');
        if($app['showadmin']) C::t('app_market')->update($app['appid'], ['showadmin' => 0]);
        //增加星火模型图片理解默认模板
        $xhimageparsetpl = [
            [
                'id' => 1,
                'name' => '通用标签',
                'prompt' => '请根据图片内容给出5到20个关键词作为图片标签，每个标签词的长度不能超过5个字，且标签词之间不能重复。标签尽量简洁，能用一个字用一个字，能用2个字的用2个字。',
                'cate' => 1,
                'isdefault' => 0,
                'status' => 1,
                'disp' => 1,
                'dateline' => 0
            ],
            [
                'id' => 2,
                'name' => '通用描述',
                'prompt' => '请你根据所给出的图片，详细描述其内容，并给出一个不超过120个字符的简短介绍。请确保你的描述中包含主要元素、色彩和可能的主题或情感表达，同时注意保持描述的连贯性和准确性。',
                'cate' => 2,
                'isdefault' => 0,
                'status' => 1,
                'disp' => 1,
                'dateline' => 0
            ],
            [
                'id' => 3,
                'name' => '通用改文件名',
                'prompt' => '根据图片内容给出图片文件名，不超过30个字符',
                'cate' => 0,
                'isdefault' => 0,
                'status'=>1,
                'disp' => 1,
                'dateline' => 0
                ]
        ];
        foreach($xhimageparsetpl as $value){
            if(!DB::result_first("select id from %t where id = %d",array('ai_xhimageprompt',$value['id']))){
                DB::insert('ai_xhimageprompt',$value);
            }
        }

        //去掉个人版的收藏夹挂载点
        if ($delhooksid = DB::result_first("select id from %t where addons = %s", array('hooks', 'dzz\collection\classes\deleteafter'))) {
            DB::delete('hooks', ['id' => $delhooksid]);
        }
        //获取aiKey挂载点
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\aiXhimage\classes\ImageAIkey'))) {
            DB::insert('hooks', array(
                'app_market_id' => 12,
                'name' => 'editfilefilter',
                'description' => 'ImageAIkey',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\aiXhimage\classes\ImageAIkey',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        //获取描述名词标签挂载点
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\aiXhimage\classes\ImagetagAnddesc'))) {
            DB::insert('hooks', array(
                'app_market_id' => 12,
                'name' => 'imageAiData',
                'description' => 'ImagetagAnddesc',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\aiXhimage\classes\ImagetagAnddesc',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        //彻底删除附件处理挂载点
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\aiXhimage\classes\attachmentDelAfter'))) {
            DB::insert('hooks', array(
                'app_market_id' => 12,
                'name' => 'finalydelete',
                'description' => 'finalydelete',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\aiXhimage\classes\attachmentDelAfter',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        //彻底删除文件处理挂载点
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\aiXhimage\classes\pichomedatadeleteafter'))) {
            DB::insert('hooks', array(
                'app_market_id' => 12,
                'name' => 'pichomedatadeleteafter',
                'description' => 'pichomedatadeleteafter',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\aiXhimage\classes\pichomedatadeleteafter',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        //统计token使用数
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\stats\classes\addTokenuse'))) {
            DB::insert('hooks', array(
                'app_market_id' => 12,
                'name' => 'statsTokenuse',
                'description' => 'statsTokenuse',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\stats\classes\addTokenuse',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        show_msg("基础数据升级完成", "$theurl?step=data&dp=1");

    } elseif ($_GET['dp'] == 1) {//pathkey数据升级
        $i = isset($_GET['i']) ? intval($_GET['i']) : 1;
        //获取普通目录库id

            if (!isset($_GET['count'])) {
                $count = DB::result_first("select count(DISTINCT fid) from %t where 1  ", ['pichome_folderresources']);
                if(!$count) show_msg("数据升级结束", "$theurl?step=delete");
            } else {
                $count = intval($_GET['count']);
            }
            $i = isset($_GET['i']) ? intval($_GET['i']) : 1;
            $perpage = 1000;
            $start = ($i - 1) * $perpage;
            $j = 0;
            foreach (DB::fetch_all("select DISTINCT fid from %t where 1  limit $start,$perpage", ['pichome_folderresources']) as $value) {
                $pathkey = DB::result_first("select pathkey from %t where fid = %s", ['pichome_folder', $value['fid']]);
                if ($pathkey) {
                    DB::update('pichome_folderresources', ['pathkey' => $pathkey], ['fid' => $value['fid']]);
                    $j++;
                }
            }
            if ($j >= $perpage) {
                $complatei = ($i - 1) * $perpage + $j;
                $i++;
                $msg = '目录关系数据升级完成';
                $next = $theurl . '?step=data&dp=1&i=' . $i . '&count=' . $count;
                show_msg($msg . "[ $complatei/$count] ", $next);
            } else {
                show_msg("数据升级结束,即将开始标签数据升级", "$theurl?step=delete");
            }

    }

} elseif ($_GET['step'] == 'delete') {
    $oldtables = array();
    $query = DB::query("SHOW TABLES LIKE '$config[tablepre]%'");
    while ($value = DB::fetch($query)) {
        $values = array_values($value);
        $oldtables[] = $values[0];
    }

    $sql = implode('', file($sqlfile));
    preg_match_all("/CREATE\s+TABLE.+?dzz\_(.+?)\s+\((.+?)\)\s*(ENGINE|TYPE)\s*\=/is", $sql, $matches);
    $newtables = empty($matches[1]) ? array() : $matches[1];
    $newsqls = empty($matches[0]) ? array() : $matches[0];
    $deltables = array();
    $delcolumns = array();

    foreach ($oldtables as $tname) {
        $tname = substr($tname, strlen($config['tablepre']));
        if (in_array($tname, $newtables)) {
            $query = DB::query("SHOW CREATE TABLE " . DB::table($tname));
            $cvalue = DB::fetch($query);
            $oldcolumns = getcolumn($cvalue['Create Table']);
            $i = array_search($tname, $newtables);
            $newcolumns = getcolumn($newsqls[$i]);

            foreach ($oldcolumns as $colname => $colstruct) {
                if ($colname == 'UNIQUE' || $colname == 'KEY') {
                    foreach ($colstruct as $key_index => $key_value) {
                        if (empty($newcolumns[$colname][$key_index])) {
                            $delcolumns[$tname][$colname][$key_index] = $key_value;
                        }
                    }
                } else {
                    if (empty($newcolumns[$colname])) {
                        $delcolumns[$tname][] = $colname;
                    }
                }
            }
        } else {

        }
    }

    show_header();
    echo '<form method="post" autocomplete="off" action="' . $theurl . '?step=delete' . ($_GET['from'] ? '&from=' . rawurlencode($_GET['from']) . '&frommd5=' . rawurlencode($_GET['frommd5']) : '') . '">';

    $deltablehtml = '';
    if ($deltables) {
        $deltablehtml .= '<table>';
        foreach ($deltables as $tablename) {
            $deltablehtml .= "<tr><td><input type=\"checkbox\" name=\"deltables[$tablename]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td></tr>";
        }
        $deltablehtml .= '</table>';
        echo "<p>以下 <strong>数据表</strong> 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除</p>$deltablehtml";
    }

    $delcolumnhtml = '';
    if ($delcolumns) {
        $delcolumnhtml .= '<table>';
        foreach ($delcolumns as $tablename => $cols) {
            foreach ($cols as $coltype => $col) {
                if (is_array($col)) {
                    foreach ($col as $index => $indexvalue) {
                        $delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$coltype][$index]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td><td>索引($coltype) $index $indexvalue</td></tr>";
                    }
                } else {
                    $delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$col]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td><td>字段 $col</td></tr>";
                }
            }
        }
        $delcolumnhtml .= '</table>';

        echo "<p>以下 <strong>字段</strong> 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除(建议删除)</p>$delcolumnhtml";
    }

    if (empty($deltables) && empty($delcolumns)) {
        echo "<p>与标准数据库相比，没有需要删除的数据表和字段</p><a href=\"$theurl?step=cache" . ($_GET['from'] ? '&from=' . rawurlencode($_GET['from']) . '&frommd5=' . rawurlencode($_GET['frommd5']) : '') . "\">请点击进入下一步</a></p>";
    } else {
        echo "<p><input type=\"submit\" name=\"delsubmit\" value=\"提交删除\"></p><p>您也可以忽略多余的表和字段<br><a href=\"$theurl?step=cache" . ($_GET['from'] ? '&from=' . rawurlencode($_GET['from']) . '&frommd5=' . rawurlencode($_GET['frommd5']) : '') . "\">直接进入下一步</a></p>";
    }
    echo '</form>';

    show_footer();
    exit();


} elseif ($_GET['step'] == 'cache') {

    if (@$fp = fopen($lockfile, 'w')) {
        fwrite($fp, ' ');
        fclose($fp);
    }
    //删除多余文件
    @unlink(DZZ_ROOT . './dzz/pichome/css/admin.css');
    @unlink(DZZ_ROOT . './dzz/pichome/css/common.css');
    @unlink(DZZ_ROOT . './dzz/pichome/css/details.css');
    @unlink(DZZ_ROOT . './dzz/pichome/css/index.css');
    @unlink(DZZ_ROOT . './dzz/pichome/js/audioPlay.js');
    @unlink(DZZ_ROOT . './dzz/pichome/js/headerMethods.js');
    @unlink(DZZ_ROOT . './dzz/pichome/js/headerMethods.js');
    @unlink(DZZ_ROOT . './dzz/pichome/js/jquery,mousewheel.min.js');
    //删除之前版本多余模板文件
    dir_clear(DZZ_ROOT . './dzz/pichome/template/components', 0);
    @rmdir(DZZ_ROOT . './dzz/pichome/template/components');
    dir_clear(DZZ_ROOT . './dzz/pichome/template/frame', 0);
    @rmdir(DZZ_ROOT . './dzz/pichome/template/frame');
    dir_clear(DZZ_ROOT . './dzz/pichome/template/page', 0);
    @rmdir(DZZ_ROOT . './dzz/pichome/template/page');
    dir_clear(DZZ_ROOT . './dzz/pichome/js/plug', 0);
    @rmdir(DZZ_ROOT . './dzz/pichome/js/plug');
    //删除数据库恢复文件，防止一些安全问题；
    @unlink(DZZ_ROOT . './data/restore.php');
    dir_clear(DZZ_ROOT . './data/template');
    //dir_clear(DZZ_ROOT.'./data/cache');
    savecache('setting', '');
    $routefile = DZZ_ROOT . './data/cache/' . 'route.php';
    if (!is_file($routefile)) {
        @file_put_contents($routefile, "<?php \t\n return array();");
    }
    $configfile = DZZ_ROOT . 'data/cache/default_mod.php';
    $configarr = array();
    $configarr['default_mod'] = 'banner';
    @file_put_contents($configfile, "<?php \t\n return " . var_export($configarr, true) . ";");
    C::t('setting')->update('default_mod', 'banner');
    C::t('setting')->update('bbclosed', 0);
    if ($_GET['from']) {
        show_msg('<span id="finalmsg">缓存更新中，请稍候 ...</span><iframe src="../misc.php?mod=syscache" style="display:none;" onload="parent.window.location.href=\'' . $_GET['from'] . '&t=1\'"></iframe>');
    } else {
        show_msg('<span id="finalmsg">缓存更新中，请稍候 ...</span><iframe src="../misc.php?mod=syscache" style="display:none;" onload="document.getElementById(\'finalmsg\').innerHTML = \'恭喜，数据库结构升级完成！为了数据安全，请删除本文件。' . $opensoso . '\'"></iframe>');
    }

}

function has_another_special_table($tablename, $key)
{
    if (!$key) {
        return $tablename;
    }

    $tables_array = get_special_tables_array($tablename);

    if ($key > count($tables_array)) {
        return FALSE;
    } else {
        return TRUE;
    }
}

function converttodzzcode($aid)
{
    return 'path=' . dzzencode('attach::' . $aid);
}

function get_special_tables_array($tablename)
{
    $tablename = DB::table($tablename);
    $tablename = str_replace('_', '\_', $tablename);
    $query = DB::query("SHOW TABLES LIKE '{$tablename}\_%'");
    $dbo = DB::object();
    $tables_array = array();
    while ($row = $dbo->fetch_array($query, $dbo->drivertype == 'mysqli' ? MYSQLI_NUM : MYSQL_NUM)) {
        if (preg_match("/^{$tablename}_(\\d+)$/i", $row[0])) {
            $prefix_len = strlen($dbo->tablepre);
            $row[0] = substr($row[0], $prefix_len);
            $tables_array[] = $row[0];
        }
    }
    return $tables_array;
}

function get_special_table_by_num($tablename, $num)
{
    $tables_array = get_special_tables_array($tablename);

    $num--;
    return isset($tables_array[$num]) ? $tables_array[$num] : FALSE;
}

function getcolumn($creatsql)
{

    $creatsql = preg_replace("/ COMMENT '.*?'/i", '', $creatsql);
    preg_match("/\((.+)\)\s*(ENGINE|TYPE)\s*\=/is", $creatsql, $matchs);

    $cols = explode("\n", $matchs[1]);
    $newcols = array();
    foreach ($cols as $value) {
        $value = trim($value);
        if (empty($value)) continue;
        $value = remakesql($value);
        if (substr($value, -1) == ',') $value = substr($value, 0, -1);

        $vs = explode(' ', $value);
        $cname = $vs[0];

        if ($cname == 'KEY' || $cname == 'INDEX' || $cname == 'UNIQUE') {

            $name_length = strlen($cname);
            if ($cname == 'UNIQUE') $name_length = $name_length + 4;

            $subvalue = trim(substr($value, $name_length));
            $subvs = explode(' ', $subvalue);
            $subcname = $subvs[0];
            $newcols[$cname][$subcname] = trim(substr($value, ($name_length + 2 + strlen($subcname))));

        } elseif ($cname == 'PRIMARY') {

            $newcols[$cname] = trim(substr($value, 11));

        } else {

            $newcols[$cname] = trim(substr($value, strlen($cname)));
        }
    }
    return $newcols;
}

function remakesql($value)
{
    $value = trim(preg_replace("/\s+/", ' ', $value));
    $value = str_replace(array('`', ', ', ' ,', '( ', ' )', 'mediumtext'), array('', ',', ',', '(', ')', 'text'), $value);
    return $value;
}

function show_msg($message, $url_forward = '', $time = 1, $noexit = 0, $notice = '')
{

    if ($url_forward) {
        $url_forward = $_GET['from'] ? $url_forward . '&from=' . rawurlencode($_GET['from']) . '&frommd5=' . rawurlencode($_GET['frommd5']) : $url_forward;
        $message = "<a href=\"$url_forward\">$message (跳转中...)</a><br>$notice<script>setTimeout(\"window.location.href ='$url_forward';\", $time);</script>";
    }

    show_header();
    print<<<END
	<table>
	<tr><td>$message</td></tr>
	</table>
END;
    show_footer();
    !$noexit && exit();
}


function show_header()
{
    global $config;

    $nowarr = array($_GET['step'] => ' class="current"');
    if (in_array($_GET['step'], array('waitingdb', 'prepare'))) {
        $nowarr = array('sql' => ' class="current"');
    }
    print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$config[charset]" />
	<title> 数据库升级程序 </title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
	</style>
	</head>
	<body>
	<div class="bodydiv">
	<h1>欧奥数据库升级工具</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[start]}>升级开始</td>
	<td{$nowarr[sql]}>数据库结构添加与更新</td>
	<td{$nowarr[data]}>数据更新</td>
	<td{$nowarr[delete]}>数据库结构删除</td>
	<td{$nowarr[cache]}>升级完成</td>
	</tr>
	</table>
	<br>
END;
}

function show_footer()
{
    print<<<END
	</div>
	<div id="footer">Copyright © 2012-2021 oaooa.com All Rights Reserved.</div>
	</div>
	<br>
	</body>
	</html>
END;
}

function runquery($sql)
{
    global $_G;
    $tablepre = $_G['config']['db'][1]['tablepre'];
    $dbcharset = $_G['config']['db'][1]['dbcharset'];

    $sql = str_replace("\r", "\n", str_replace(array(' {tablepre}', ' dzz_', ' `dzz_'), array(' ' . $tablepre, ' ' . $tablepre, ' `' . $tablepre), $sql));
    $ret = array();
    $num = 0;
    foreach (explode(";\n", trim($sql)) as $query) {
        $queries = explode("\n", trim($query));
        foreach ($queries as $query) {
            $ret[$num] .= $query[0] == '#' || $query[0] . $query[1] == '--' ? '' : $query;
        }
        $num++;
    }
    unset($sql);

    foreach ($ret as $query) {
        $query = trim($query);
        if ($query) {

            if (substr($query, 0, 12) == 'CREATE TABLE') {
                $name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
                DB::query(create_table($query, $dbcharset));

            } else {
                DB::query($query);
            }

        }
    }
}


function save_config_file($filename, $config, $default, $deletevar)
{
    $config = setdefault($config, $default, $deletevar);
    $date = gmdate("Y-m-d H:i:s", time() + 3600 * 8);
    $content = <<<EOT
<?php


\$_config = array();

EOT;
    $content .= getvars(array('_config' => $config));
    $content .= "\r\n// " . str_pad('  THE END  ', 50, '-', STR_PAD_BOTH) . "\r\n return \$_config;";
    if (!is_writable($filename) || !($len = file_put_contents($filename, $content))) {
        file_put_contents(DZZ_ROOT . './data/config.php', $content);
        return 0;
    }
    return 1;
}

function setdefault($var, $default, $deletevar)
{

    foreach ($default as $k => $v) {
        if (!isset($var[$k])) {
            $var[$k] = $default[$k];
        } elseif (is_array($v)) {
            $var[$k] = setdefault($var[$k], $default[$k]);
        }
    }
    foreach ($deletevar as $k) {
        unset($var[$k]);
    }
    return $var;
}

function getvars($data, $type = 'VAR')
{
    $evaluate = '';
    foreach ($data as $key => $val) {
        if (!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
            continue;
        }
        if (is_array($val)) {
            $evaluate .= buildarray($val, 0, "\${$key}") . "\r\n";
        } else {
            $val = addcslashes($val, '\'\\');
            $evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('" . strtoupper($key) . "', '$val');\n";
        }
    }
    return $evaluate;
}

function buildarray($array, $level = 0, $pre = '$_config')
{
    static $ks;
    if ($level == 0) {
        $ks = array();
        $return = '';
    }

    foreach ($array as $key => $val) {
        if ($level == 0) {
            $newline = str_pad('  CONFIG ' . strtoupper($key) . '  ', 70, '-', STR_PAD_BOTH);
            $return .= "\r\n// $newline //\r\n";
            if ($key == 'admincp') {
                $newline = str_pad(' Founders: $_config[\'admincp\'][\'founder\'] = \'1,2,3\'; ', 70, '-', STR_PAD_BOTH);
                $return .= "// $newline //\r\n";
            }
        }

        $ks[$level] = $ks[$level - 1] . "['$key']";
        if (is_array($val)) {
            $ks[$level] = $ks[$level - 1] . "['$key']";
            $return .= buildarray($val, $level + 1, $pre);
        } else {
            $val = is_string($val) || strlen($val) > 12 || !preg_match("/^\-?[1-9]\d*$/", $val) ? '\'' . addcslashes($val, '\'\\') . '\'' : $val;
            $return .= $pre . $ks[$level - 1] . "['$key']" . " = $val;\r\n";
        }
    }
    return $return;
}

function dir_clear($dir, $index = 1)
{
    global $lang;
    if ($directory = @dir($dir)) {
        while ($entry = $directory->read()) {
            $filename = $dir . '/' . $entry;
            if (is_file($filename)) {
                @unlink($filename);
            }
        }
        $directory->close();
        if ($index) @touch($dir . '/index.htm');
    }
}

function create_table($sql, $dbcharset)
{
    $type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
    $type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY')) ? $type : 'MYISAM';
    return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql) .
        (" ENGINE=$type DEFAULT CHARSET=" . $dbcharset);
}

function getpathdata($folderdata, $appid, $pathdata = array())
{
    foreach ($folderdata as $v) {
        $pathdata[$v['id'] . $appid] = $v['name'];
        if ($v['children']) {
            $tmpchild = $v['children'];
            $pathdata = getpathdata($tmpchild, $appid, $pathdata);

        }
    }

    return $pathdata;
}

//更新eagle库目录数据
function initFoldertag($data)
{
    $path = $data['path'];
    if (!is_dir($path)) {
        $path = DZZ_ROOT . 'library' . BS . $data['path'];
    }
    if (!is_dir($path)) return;
    $jsonfile = $path . BS . 'metadata.json';
    $mtime = filemtime($jsonfile);
    $appdatas = file_get_contents($jsonfile);
    //解析出json数据
    $appdatas = json_decode($appdatas, true);

    //目录数据
    $folderdata = $appdatas['folders'];

    C::t('pichome_folder')->insert_folderdata_by_appid($data['appid'], $folderdata);
    //对比目录数据
    $folderarr = getpathdata($folderdata, $data['appid']);
    $folderfids = array_keys($folderarr);
    $delfids = [];
    foreach (DB::fetch_all("select fid from %t where fid not in(%n) and appid = %s", array('pichome_folder', $folderfids, $data['appid'])) as $v) {
        $delfids[] = $v['fid'];
    }
    C::t('pichome_folder')->delete($delfids);
    C::t('pichome_vapp')->update($data['appid'], array('path' => $path));
    return true;

}

function formatpath($path)
{
    if (strpos($path, ':') === false) {
        $bz = 'dzz';
    } else {
        $patharr = explode(':', $path);
        $bz = $patharr[0];
        $did = $patharr[1];

    }
    if (!is_numeric($did) || $did < 2) {
        $bz = 'dzz';
    }

    $rootpath = str_replace(BS, '/', DZZ_ROOT);
    $path = str_replace(DZZ_ROOT, '', $path);
    $path = str_replace($rootpath, '', $path);
    $path = str_replace(BS, '/', $path);
    $path = str_replace('//', '/', $path);
    $path = str_replace('./', '', $path);
    if ($bz == 'dzz') $path = 'dzz::' . ltrim($path, '/');
    else $path = ltrim($path, '/');
    return $path;
}

function getbasename($filename)
{
    return preg_replace('/^.+[\\\\\\/]/', '', $filename);
}

function getintPaletteNumber($colors, $palette = array())
{

    if (empty($palette)) $palette = [
        0xfff8e1, 0xf57c00, 0xffd740, 0xb3e5fc, 0x607d8b, 0xd7ccc8,
        0xff80ab, 0x4e342e, 0x9e9e9e, 0x66bb6a, 0xaed581, 0x18ffff,
        0xffe0b2, 0xc2185b, 0x00bfa5, 0x00e676, 0x0277bd, 0x26c6da,
        0x7c4dff, 0xea80fc, 0x512da8, 0x7986cb, 0x00e5ff, 0x0288d1,
        0x69f0ae, 0x3949ab, 0x8e24aa, 0x40c4ff, 0xdd2c00, 0x283593,
        0xaeea00, 0xffa726, 0xd84315, 0x82b1ff, 0xab47bc, 0xd4e157,
        0xb71c1c, 0x880e4f, 0x00897b, 0x689f38, 0x212121, 0xffff00,
        0x827717, 0x8bc34a, 0xe0f7fa, 0x304ffe, 0xd500f9, 0xec407a,
        0x6200ea, 0xffab00, 0xafb42b, 0x6a1b9a, 0x616161, 0x8d6e63,
        0x80cbc4, 0x8c9eff, 0xffeb3b, 0xffe57f, 0xfff59d, 0xff7043,
        0x1976d2, 0x5c6bc0, 0x64dd17, 0xffd600
    ];
    $arr = array();

    if (is_array($colors)) {
        $isarray = 1;
    } else {
        $colors = (array)$colors;
        $isarray = 0;
    }

    foreach ($colors as $color) {
        $bestColor = 0x000000;
        $bestDiff = PHP_INT_MAX;
        $color = new Color($color);
        foreach ($palette as $key => $wlColor) {
            // calculate difference (don't sqrt)
            $diff = $color->getDiff($wlColor);
            // see if we got a new best
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $bestColor = $wlColor;
            }
        }
        unset($color);
        $arr[] = array_search($bestColor, $palette);
    }
    return $isarray ? $arr : $arr[0];
}

function fetchtablelist($tablepre = '')
{
    global $db;
    $arr = explode('.', $tablepre);
    $dbname = $arr[1] ? $arr[0] : '';
    $tablepre = str_replace('_', '\_', $tablepre);
    $sqladd = $dbname ? " FROM $dbname LIKE '$arr[1]%'" : "LIKE '$tablepre%'";
    $tables = $table = array();
    $query = DB::query("SHOW TABLE STATUS $sqladd");
    while ($table = DB::fetch($query)) {
        $table['Name'] = ($dbname ? "$dbname." : '') . $table['Name'];
        $tables[] = $table['Name'];
    }
    return $tables;
}


?>
