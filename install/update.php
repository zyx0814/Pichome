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
require_once  __DIR__ . '/../core/class/class_Color.php';
@set_time_limit(0);
error_reporting(0);
$cachelist = array();
$dzz = dzz_app::instance();

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
                        $baktab =DB::table($newtable . '_bak');
                        if(!in_array($baktab,$tablist)){
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
        if (!C::t('setting')->fetch('machinecode')) {
            //获取识别码
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            $onlineip = $_SERVER['REMOTE_ADDR'];
            $machinecode = 'PH' . $chars[date('y') % 60] . $chars[date('n')] .
                $chars[date('j')] . $chars[date('G')] . $chars[date('i')] .
                $chars[date('s')] . substr(md5($onlineip . TIMESTAMP), 0, 4) . random(4);
            C::t('setting')->update('machinecode', $machinecode);
        }
        $baktab =DB::table('pichome_vapp_bak');
        $vappparams = ['pichome_vapp_bak'];
        //处理库访问权限以及偏好数据和栏目数据
        $defaultscreen = [
            [
                'key' => 'classify',
                'label' => '分类',
            ],
            [
                'key' => 'tag',
                'label' => '标签',
                'group'=>'',
                'sort'=>'hot',
                'auto'=>'0',
            ],
            [
                'key' => 'color',
                'label' => '颜色',
            ],
            [
                'key' => 'link',
                'label' => '链接',
            ],
            [
                'key' => 'desc',
                'label' => '注释',
            ],
            [
                'key' => 'duration',
                'label' => '时长',
            ],
            [
                'key' => 'size',
                'label' => '尺寸',
            ],
            [
                'key' => 'ext',
                'label' => '类型',
            ],
            [
                'key' => 'shape',
                'label' => '形状',
            ],
            [
                'key' => 'grade',
                'label' => '评分',
            ],
            [
                'key' => 'btime',
                'label' => '添加时间',
            ],
            [
                'key' => 'dateline',
                'label' => '修改日期',
            ],
            [
                'key' => 'mtime',
                'label' => '创建日期',
            ]

        ];
        $defaultfileds = [
            [
                'flag' => 'tag',
                'type' => 'multiselect',
                'name' => '标签',
                'enable' => 1,
                'checked' => 1
            ],
            [
                'flag' => 'desc',
                'type' => 'input',
                'name' => '描述',
                'enable' => 1,
                'checked' => 1
            ],
            [
                'flag' => 'link',
                'type' => 'input',
                'name' => '链接',
                'enable' => 1,
                'checked' => 1
            ],

            [
                'flag' => 'grade',
                'type' => 'grade',
                'name' => '评分',
                'enable' => 1,
                'checked' => 1
            ],
            [
                'flag' => 'fid',
                'type' => 'multiselect',
                'name' => '分类',
                'enable' => 1,
                'checked' => 1
            ]
        ];
        foreach(DB::fetch_all("select * from %t where isdelete < 1",$vappparams) as $v){
            $pagesetting = $_G['setting']['pichomepagesetting'];
            $vappattr['pagesetting'] = [];
            foreach($pagesetting as $key=>$val){
                if($key == 'theme' || $key == 'template') {

                }
                elseif($key == 'show') $vappattr['pagesetting']['show'] = explode(',',$val);
                else $vappattr['pagesetting'][$key] = $val;
            }
            $v['path'] = str_replace('dzz::','',$v['path']);
            $v['screen'] = $v['filter'] ? $v['filter']:serialize($defaultscreen);
            unset($v['filter']);
            $v['pagesetting'] = (!empty($vappattr['pagesetting'])) ? serialize($vappattr['pagesetting']):'a:7:{s:6:"layout";s:9:"waterFall";s:5:"other";s:5:"btime";s:4:"sort";s:5:"btime";s:4:"desc";s:4:"desc";s:8:"opentype";s:3:"new";s:5:"aside";s:1:"0";s:11:"filterstyle";s:1:"0";}';
            if($v['view']){
                $v['view'] = (unserialize($v['view'])) ? $v['view']:serialize($v['view']);
            }
            $v['fileds'] =serialize($defaultfileds);
            if(!DB::result_first("select count(*) from %t where appid = %s",array('pichome_vapp',$v['appid']))){
                C::t('pichome_vapp')->insert($v);
                $appid = $v['appid'];
            }else{
                $appid = $v['appid'];
                unset($v['appid']);
                C::t('pichome_vapp')->update($appid,$v);
            }
            if(!$v['isdelete']){
                $setarr = [
                    'bannername'=>$v['appname'],
                    'bdata'=>$appid,
                    'btype'=>0,
                    'issystem'=>1,
                    'icon'=>0,
                    'isshow'=>1
                ];
                //print_r($setarr);
                if($bid = DB::result_first("select id from %t where bdata = %s and issystem = 1",array('pichome_banner',$appid))){
                    $setarr['id'] = $bid;
                }
                C::t('pichome_banner')->insert_data($setarr);
            }

        }
        $cronarr = [
            ['cronid'=>9,'available'=>1, 'type'=>'system', 'name'=>'定时获取需要更新的库', 'filename'=>'cron_pichome_vapp_update.php', 'lastrun'=>0, 'nextrun'=>0, 'weekday'=>'-1', 'day'=>'-1', 'hour'=>'-1', 'minute'=>'0 5	10	15	20	25	30	35	40	45	50	55'],
            ['cronid'=>10,'available'=>1, 'type'=>'system', 'name'=>'定时检查库更新过程任务', 'filename'=>'cron_vapp_vappdoupdate.php', 'lastrun'=>0, 'nextrun'=>0, 'weekday'=>'-1', 'day'=>'-1', 'hour'=>'-1', 'minute'=>'0 5	10	15	20	25	30	35	40	45	50	55'],
            ['cronid'=>11,'available'=>1, 'type'=>'system', 'name'=>'定时检查缩略图更新任务', 'filename'=>'cron_thumbcheckchange.php', 'lastrun'=>0, 'nextrun'=>0, 'weekday'=>'-1', 'day'=>'-1', 'hour'=>'-1', 'minute'=>'0 5	10	15	20	25	30	35	40	45	50	55'],
            ['cronid'=>12,'available'=>1, 'type'=>'system', 'name'=>'定时更新缩略图变动任务', 'filename'=>'cron_thumbdochange.php', 'lastrun'=>0, 'nextrun'=>0, 'weekday'=>'-1', 'day'=>'-1', 'hour'=>'-1', 'minute'=>'0 5	10	15	20	25	30	35	40	45	50	55'],
            ['cronid'=>13,'available'=>1, 'type'=>'system', 'name'=>'定时检查单页缓存数据更新', 'filename'=>'cron_update_alonepagedata.php', 'lastrun'=>0, 'nextrun'=>0, 'weekday'=>'-1', 'day'=>'-1', 'hour'=>'-1', 'minute'=>'0 5	10	15	20	25	30	35	40	45	50	55'],
            ['cronid'=>14,'available'=>1, 'type'=>'system', 'name'=>'定时执行删除库已彻底删除文件任务', 'filename'=>'cron_pichome_deletefile.php', 'lastrun'=>0, 'nextrun'=>0, 'weekday'=>'-1', 'day'=>'-1', 'hour'=>'-1', 'minute'=>'0 5	10	15	20	25	30	35	40	45	50	55'],
        ];
        foreach($cronarr as $cron){
            if(DB::result_first("select cronid from %t where cronid = %d",array('cron',$cron['cronid']))){
                $cronid = $cron['cronid'];
                unset($cron['cronid']);
                C::t('cron')->update($cronid,$cron);
            }else{
                C::t('cron')->insert($cron);
            }
        }

        //增加挂载点
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'core\dzz\ulimit'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'dzz_initafter',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'core\dzz\ulimit',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }

        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\ffmpeg\classes\info'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'pichomegetfileinfo',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\ffmpeg\classes\info',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\imageColor\classes\imageColor'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'pichomegetfileinfo',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\imageColor\classes\imageColor',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\qcos\classes\info'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'pichomegetfileinfo',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\qcos\classes\info',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\ffmpeg\classes\thumb'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'pichomethumb',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\ffmpeg\classes\thumb',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\imageColor\classes\getthumb'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'pichomethumb',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\imageColor\classes\getthumb',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'ddzz\onlyoffice_view\classes\thumb'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'pichomethumb',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\onlyoffice_view\classes\thumb',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\qcos\classes\thumb'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'pichomethumb',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\qcos\classes\thumb',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\qcos\classes\convert'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'pichomeconvert',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\qcos\classes\convert',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\ffmpeg\classes\convert'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'pichomeconvert',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\ffmpeg\classes\convert',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\pichome\classes\addvappafter'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'addvappafter',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\pichome\classes\addvappafter',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }

        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\pichome\classes\pichomevappdelete'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'pichomevappdelete',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\pichome\classes\pichomevappdelete',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\pichome\classes\addfileafter'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'addfileafter',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\pichome\classes\addfileafter',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }

        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\stats\classes\addstatsdata'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'addstatsdata',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\stats\classes\addstatsdata',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\banner\classes\statsviewaddafter'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'statsviewaddafter',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\banner\classes\statsviewaddafter',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }
        if (!DB::result_first("select count(id) from %t where addons = %s", array('hooks', 'dzz\banner\classes\statskeywordaddafter'))) {
            DB::insert('hooks', array(
                'app_market_id' => 0,
                'name' => 'statskeywordaddafter',
                'description' => ' ',
                'type' => 1,
                'update_time' => 0,
                'addons' => 'dzz\stats\classes\addstatsdata',
                'status' => 1,
                'priority' => 0
            ), false, true);
        }


        //更新onlyoffice设置位置
        if($onlyofficesetting = C::t('setting')->fetch('onlyofficesetting',true)){
            $app=C::t('app_market')->fetch_by_identifier('onlyoffice','dzz');
            $extra =unserialize($app['extra']);
            $extra["DocumentUrl"]=$onlyofficesetting['onlyofficeurl'];
            $extra["FileUrl"]=$onlyofficesetting['onlyofficedocurl'] ? $onlyofficesetting['onlyofficedocurl']:getglobal('siteurl');
            $extra["exts"]='pdf,doc,docx,rtf,odt,htm,html,mht,txt,ppt,pptx,pps,ppsx,odp,xls,xlsx,ods,csv';
            $extra["secret"]='';
            C::t("app_market")->update($app['appid'],array('identifier'=>'onlyoffice_view',"extra"=> serialize($extra)));
        }
        //更新ffmpeg设置
        $ffmpegstatus = DB::result_first("select mediastatus from %t where id = 1",array('connect_storage'));
        $app = C::t('app_market')->fetch_by_identifier('ffmpeg','dzz');
        $extra =[];
        $extra["ffmpeg.binaries"]=(getglobal('config/pichomeffmpegposition')) ? getglobal('config/pichomeffmpegposition'):(strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffmpeg.exe' : '/usr/bin/ffmpeg');
        $extra["ffprobe.binaries"]=(getglobal('config/pichomeffprobeposition')) ? (getglobal('config/pichomeffprobeposition')):(strstr(PHP_OS, 'WIN') ? DZZ_ROOT . 'dzz\ffmpeg\ffmpeg\ffprobe.exe' : '/usr/bin/ffprobe');
        $extra["timeout"]=3600;
        $extra["ffmpeg.threads"]=1;
        $extra["status"]= $ffmpegstatus ? intval($ffmpegstatus):0;
        $extra["exts"]=$_G['config']['pichomeffmpegconvertext'];
        $extra["exts_thumb"]=$_G['config']['pichomeffmpeggetthumbext'];
        $extra["exts_info"]=$_G['config']['pichomeffmpeggetvieoinfoext'];
        C::t("app_market")->update($app['appid'],array("extra"=> serialize($extra)));




        //删除打开方式表数据
        DB::delete("app_open",1);
        //插入打开方式表数据
        $appopensql = "INSERT INTO ".DB::table('app_open')." (`ext`, `appid`, `disp`, `extid`, `isdefault`) VALUES
('pdf', 3, 0, 1, 0),
('doc', 3, 0, 2, 0),
('docx', 3, 0, 3, 0),
('rtf', 3, 0, 4, 0),
('odt', 3, 0, 5, 0),
('htm', 3, 0, 6, 0),
('html', 3, 0, 7, 0),
('mht', 3, 0, 8, 0),
('txt', 3, 0, 9, 0),
('ppt', 3, 0, 10, 0),
('pptx', 3, 0, 11, 0),
('pps', 3, 0, 12, 0),
('ppsx', 3, 0, 13, 0),
('odp', 3, 0, 14, 0),
('xls', 3, 0, 15, 0),
('xlsx', 3, 0, 16, 0),
('ods', 3, 0, 17, 0),
('csv', 3, 0, 18, 0),
('mp3', 7, 0, 19, 0),
('mp4', 7, 0, 20, 0),
('flv', 7, 0, 21, 0),
('webm', 7, 0, 22, 0),
('ogv', 7, 0, 23, 0),
('ogg', 7, 0, 24, 0),
('wav', 7, 0, 25, 0),
('m3u8', 7, 0, 26, 0),
('hls', 7, 0, 27, 0),
('mpg', 7, 0, 28, 0),
('avi', 7, 0, 29, 0),
('rm', 7, 0, 30, 0),
('rmvb', 7, 0, 31, 0),
('mkv', 7, 0, 32, 0),
('mov', 7, 0, 33, 0),
('wmv', 7, 0, 34, 0),
('asf', 7, 0, 35, 0),
('mpg', 7, 0, 36, 0),
('mpeg', 7, 0, 37, 0),
('f4v', 7, 0, 38, 0),
('vob', 7, 0, 39, 0),
('ogv', 7, 0, 40, 0),
('mts', 7, 0, 41, 0),
('m2ts', 7, 0, 42, 0),
('mpe', 7, 0, 43, 0),
('ogg', 7, 0, 44, 0),
('3gp', 7, 0, 45, 0),
('flv', 7, 0, 46, 0),
('midi', 7, 0, 47, 0),
('wma', 7, 0, 48, 0),
('vqf', 7, 0, 49, 0),
('ra', 7, 0, 50, 0),
('aac', 7, 0, 51, 0),
('flac', 7, 0, 52, 0),
('ape', 7, 0, 53, 0),
('amr', 7, 0, 54, 0),
('aiff', 7, 0, 55, 0),
('au', 7, 0, 56, 0),
('m4a', 7, 0, 57, 0),
('m4v', 7, 0, 58, 0),
('txt', 8, 0, 59, 1),
('php', 8, 0, 60, 1),
('js', 8, 0, 61, 1),
('jsp', 8, 0, 62, 1),
('htm', 8, 0, 63, 1),
('html', 8, 0, 64, 1),
('jsp', 8, 0, 65, 1),
('asp', 8, 0, 66, 1),
('aspx', 8, 0, 67, 1),
('QCOS::pptx', 10, 0, 68, 1),
('QCOS::ppt', 10, 0, 69, 1),
('QCOS::pot', 10, 0, 70, 1),
('QCOS::potx', 10, 0, 71, 1),
('QCOS::pps', 10, 0, 72, 1),
('QCOS::ppsx', 10, 0, 73, 1),
('QCOS::dps', 10, 0, 74, 1),
('QCOS::dpt', 10, 0, 75, 1),
('QCOS::pptm', 10, 0, 76, 1),
('QCOS::potm', 10, 0, 77, 1),
('QCOS::ppsm', 10, 0, 78, 1),
('QCOS::doc', 10, 0, 79, 1),
('QCOS::dot', 10, 0, 80, 1),
('QCOS::wps', 10, 0, 81, 1),
('QCOS::wpt', 10, 0, 82, 1),
('QCOS::docx', 10, 0, 83, 1),
('QCOS::dotx', 10, 0, 84, 1),
('QCOS::docm', 10, 0, 85, 1),
('QCOS::dotm', 10, 0, 86, 1),
('QCOS::xls', 10, 0, 87, 1),
('QCOS::xlt', 10, 0, 88, 1),
('QCOS::et', 10, 0, 89, 1),
('QCOS::ett', 10, 0, 90, 1),
('QCOS::xlsx', 10, 0, 91, 1),
('QCOS::xltx', 10, 0, 92, 1),
('QCOS::csv', 10, 0, 93, 1),
('QCOS::xlsb', 10, 0, 94, 1),
('QCOS::xlsm', 10, 0, 95, 1),
('QCOS::xltm', 10, 0, 96, 1),
('QCOS::ets', 10, 0, 97, 1),
('QCOS::pdf', 10, 0, 98, 1),
('QCOS::lrc', 10, 0, 99, 1),
('QCOS::c', 10, 0, 100, 01),
('QCOS::cpp', 10, 0, 101, 1),
('QCOS::h', 10, 0, 102, 1),
('QCOS::asm', 10, 0, 103, 1),
('QCOS::s', 10, 0, 104, 1),
('QCOS::java', 10, 0, 105, 1),
('QCOS::asp', 10, 0, 106, 1),
('QCOS::bat', 10, 0, 107, 1),
('QCOS::bas', 10, 0, 108, 1),
('QCOS::prg', 10, 0, 109, 1),
('QCOS::cmd', 10, 0, 110, 1),
('QCOS::rtf', 10, 0, 111, 1),
('QCOS::txt', 10, 0, 112, 1),
('QCOS::log', 10, 0, 113, 1),
('QCOS::xml', 10, 0, 114, 1),
('QCOS::htm', 10, 0, 115, 1),
('QCOS::html', 10, 0, 116, 1),
('pdf', 11, 0, 117, 1)";
        DB::query($appopensql);
        $alsetarr = array('name'=>'阿里云存储','type'=>'storage','bz'=>'ALIOSS','available'=>0, 'dname'=>'connect_storage');
        if(!DB::result_first("select count(*) from %t where bz = %s",array('connect','ALIOSS'))){
            C::t('connect')->insert($alsetarr);
        }else{
            C::t('connect')->update('ALIOSS',$alsetarr);
        }
        $qcossetarr = array('name'=>'Qcos','type'=>'storage','bz'=> 'QCOS', 'available'=> 2, 'dname'=>'connect_storage');
        if(!DB::result_first("select count(*) from %t where bz = %s",array('connect','QCOS'))){
            C::t('connect')->insert($qcossetarr);
        }else{
            C::t('connect')->update('QCOS',$qcossetarr);
        }
        $dzzsetarr = array('name'=>'本地', 'type'=>'local', 'bz'=>'dzz', 'available'=>2, 'disp'=> -2);
        if(!DB::result_first("select count(*) from %t where bz = %s",array('connect','dzz'))){
            C::t('connect')->insert($dzzsetarr);
        }else{
            C::t('connect')->update('dzz',$dzzsetarr);
        }
        if(!DB::result_first("select id from %t where bz = %s or id = %d ",array('connect_storage','dzz',1))){
            $storagarr = array('id'=>1,'cloudname'=>'本地存储','perm'=>29751,'bz'=>'dzz','disp'=>-2);
            C::t('connect_storage')->insert($storagarr);
        }
        //删除缩略图表数据
        DB::delete('thumb_record','1');
        $themarr = array(
            'themename'=>'超酷时尚',
            'colors'=>'white,dark',
            'templates'=>'',
            'selcolor'=>'dark',
            'themestyle'=>'a:7:{s:5:"slide";a:2:{s:10:"horizontal";a:4:{s:5:"title";s:6:"横幅";s:7:"default";s:4:"true";s:5:"value";s:10:"horizontal";s:4:"size";a:3:{i:0;a:3:{s:5:"title";s:9:"1800×450";s:7:"default";s:4:"true";s:5:"value";s:3:"25%";}i:1;a:3:{s:5:"title";s:9:"1800×500";s:7:"default";s:5:"false";s:5:"value";s:3:"28%";}i:2;a:3:{s:5:"title";s:9:"1800×800";s:7:"default";s:5:"false";s:5:"value";s:3:"44%";}}}s:4:"full";a:4:{s:5:"title";s:6:"满屏";s:7:"default";s:5:"false";s:5:"value";s:4:"full";s:4:"size";a:3:{i:0;a:3:{s:5:"title";s:9:"1800×450";s:7:"default";s:4:"true";s:5:"value";s:3:"25%";}i:1;a:3:{s:5:"title";s:9:"1800×500";s:7:"default";s:5:"false";s:5:"value";s:3:"28%";}i:2;a:3:{s:5:"title";s:9:"1800×800";s:7:"default";s:5:"false";s:5:"value";s:3:"44%";}}}}s:9:"rich_text";a:2:{s:3:"top";a:4:{s:5:"title";s:12:"顶部分类";s:7:"default";s:4:"true";s:5:"value";s:3:"top";s:4:"size";a:2:{i:0;a:3:{s:5:"title";s:3:"宽";s:7:"default";s:4:"true";s:5:"value";s:4:"full";}i:1;a:3:{s:5:"title";s:3:"窄";s:7:"default";s:5:"false";s:5:"value";s:5:"limit";}}}s:4:"left";a:4:{s:5:"title";s:12:"左侧分类";s:7:"default";s:5:"false";s:5:"value";s:4:"left";s:4:"size";a:2:{i:0;a:3:{s:5:"title";s:3:"宽";s:7:"default";s:4:"true";s:5:"value";s:4:"full";}i:1;a:3:{s:5:"title";s:3:"窄";s:7:"default";s:5:"false";s:5:"value";s:5:"limit";}}}}s:4:"link";a:3:{s:10:"horizontal";a:3:{s:5:"title";s:6:"横排";s:7:"default";s:4:"true";s:5:"value";s:10:"horizontal";}s:4:"card";a:3:{s:5:"title";s:6:"卡片";s:7:"default";s:5:"false";s:5:"value";s:4:"card";}s:4:"icon";a:3:{s:5:"title";s:6:"图标";s:7:"default";s:5:"false";s:5:"value";s:4:"icon";}}s:8:"question";a:2:{s:3:"top";a:4:{s:5:"title";s:12:"顶部分类";s:7:"default";s:4:"true";s:5:"value";s:3:"top";s:4:"size";a:2:{i:0;a:3:{s:5:"title";s:3:"宽";s:7:"default";s:4:"true";s:5:"value";s:4:"full";}i:1;a:3:{s:5:"title";s:3:"窄";s:7:"default";s:5:"false";s:5:"value";s:5:"limit";}}}s:4:"left";a:4:{s:5:"title";s:12:"左侧分类";s:7:"default";s:5:"false";s:5:"value";s:4:"left";s:4:"size";a:2:{i:0;a:3:{s:5:"title";s:3:"宽";s:7:"default";s:4:"true";s:5:"value";s:4:"full";}i:1;a:3:{s:5:"title";s:3:"窄";s:7:"default";s:5:"false";s:5:"value";s:5:"limit";}}}}s:8:"file_rec";a:5:{s:9:"imageList";a:3:{s:5:"title";s:6:"网格";s:7:"default";s:4:"true";s:5:"value";s:9:"imageList";}s:7:"rowGrid";a:3:{s:5:"title";s:9:"自适应";s:7:"default";s:5:"false";s:5:"value";s:7:"rowGrid";}s:6:"tabodd";a:3:{s:5:"title";s:12:"列表单列";s:7:"default";s:5:"false";s:5:"value";s:6:"tabodd";}s:7:"tabeven";a:3:{s:5:"title";s:12:"列表双列";s:7:"default";s:5:"false";s:5:"value";s:7:"tabeven";}s:7:"details";a:3:{s:5:"title";s:6:"详情";s:7:"default";s:5:"false";s:5:"value";s:7:"details";}}s:6:"db_ids";a:6:{s:9:"waterFall";a:3:{s:5:"title";s:9:"瀑布流";s:7:"default";s:4:"true";s:5:"value";s:9:"waterFall";}s:9:"imageList";a:3:{s:5:"title";s:6:"网格";s:7:"default";s:5:"false";s:5:"value";s:9:"imageList";}s:7:"rowGrid";a:3:{s:5:"title";s:9:"自适应";s:7:"default";s:5:"false";s:5:"value";s:7:"rowGrid";}s:6:"tabodd";a:3:{s:5:"title";s:12:"列表单列";s:7:"default";s:5:"false";s:5:"value";s:6:"tabodd";}s:7:"tabeven";a:3:{s:5:"title";s:12:"列表双列";s:7:"default";s:5:"false";s:5:"value";s:7:"tabeven";}s:7:"details";a:3:{s:5:"title";s:6:"详情";s:7:"default";s:5:"false";s:5:"value";s:7:"details";}}s:10:"manual_rec";a:7:{s:3:"one";a:4:{s:5:"title";s:18:"单排文字居中";s:7:"default";s:4:"true";s:5:"value";s:3:"one";s:4:"size";a:3:{i:0;a:3:{s:5:"title";s:8:"266×182";s:7:"default";s:4:"true";s:5:"value";s:9:"rectangle";}i:1;a:3:{s:5:"title";s:8:"266×400";s:7:"default";s:4:"true";s:5:"value";s:8:"vertical";}i:2;a:3:{s:5:"title";s:8:"266×266";s:7:"default";s:4:"true";s:5:"value";s:6:"square";}}}s:3:"two";a:4:{s:5:"title";s:18:"单排文字居下";s:7:"default";s:5:"false";s:5:"value";s:3:"two";s:4:"size";a:3:{i:0;a:3:{s:5:"title";s:8:"266×182";s:7:"default";s:4:"true";s:5:"value";s:9:"rectangle";}i:1;a:3:{s:5:"title";s:8:"266×400";s:7:"default";s:4:"true";s:5:"value";s:8:"vertical";}i:2;a:3:{s:5:"title";s:8:"266×266";s:7:"default";s:4:"true";s:5:"value";s:6:"square";}}}s:5:"three";a:4:{s:5:"title";s:18:"单排图外文字";s:7:"default";s:5:"false";s:5:"value";s:5:"three";s:4:"size";a:3:{i:0;a:3:{s:5:"title";s:8:"266×182";s:7:"default";s:4:"true";s:5:"value";s:9:"rectangle";}i:1;a:3:{s:5:"title";s:8:"266×400";s:7:"default";s:4:"true";s:5:"value";s:8:"vertical";}i:2;a:3:{s:5:"title";s:8:"266×266";s:7:"default";s:4:"true";s:5:"value";s:6:"square";}}}s:4:"four";a:4:{s:5:"title";s:18:"双排文字居中";s:7:"default";s:5:"false";s:5:"value";s:4:"four";s:4:"size";a:3:{i:0;a:3:{s:5:"title";s:8:"266×182";s:7:"default";s:4:"true";s:5:"value";s:9:"rectangle";}i:1;a:3:{s:5:"title";s:8:"266×400";s:7:"default";s:4:"true";s:5:"value";s:8:"vertical";}i:2;a:3:{s:5:"title";s:8:"266×266";s:7:"default";s:4:"true";s:5:"value";s:6:"square";}}}s:4:"five";a:4:{s:5:"title";s:18:"双排文字居下";s:7:"default";s:5:"false";s:5:"value";s:4:"five";s:4:"size";a:3:{i:0;a:3:{s:5:"title";s:8:"266×182";s:7:"default";s:4:"true";s:5:"value";s:9:"rectangle";}i:1;a:3:{s:5:"title";s:8:"266×400";s:7:"default";s:4:"true";s:5:"value";s:8:"vertical";}i:2;a:3:{s:5:"title";s:8:"266×266";s:7:"default";s:4:"true";s:5:"value";s:6:"square";}}}s:3:"six";a:4:{s:5:"title";s:18:"双排图外文字";s:7:"default";s:5:"false";s:5:"value";s:3:"six";s:4:"size";a:3:{i:0;a:3:{s:5:"title";s:8:"266×182";s:7:"default";s:4:"true";s:5:"value";s:9:"rectangle";}i:1;a:3:{s:5:"title";s:8:"266×400";s:7:"default";s:4:"true";s:5:"value";s:8:"vertical";}i:2;a:3:{s:5:"title";s:8:"266×266";s:7:"default";s:4:"true";s:5:"value";s:6:"square";}}}s:5:"seven";a:3:{s:5:"title";s:18:"大图小图混排";s:7:"default";s:5:"false";s:5:"value";s:5:"seven";}}}',
            'themefolder'=>'fashion',
            'dateline'=>TIMESTAMP);
        C::t('pichome_theme')->insert_data($themarr);
        show_msg("基础数据升级完成", "$theurl?step=data&dp=1");

    }
    elseif ($_GET['dp'] == 1) {//目录数据升级
        $i = isset($_GET['i']) ? intval($_GET['i']) : 1;
        //获取普通目录库id
        $appids = [];
        foreach (DB::fetch_all("select appid from %t where isdelete < 1", array('pichome_vapp')) as $v) {
            $appids[] = $v['appid'];
        }
        if (empty($appids)) {
            show_msg("数据升级结束", "$theurl?step=delete");
        } else {
            if(!$_GET['count']){
                $count = DB::result_first("select COUNT(DISTINCT r.rid) from %t r
left join %t fr on fr.rid = r.rid where  r.appid in(%n) and !isnull(fr.fid) ",
                    array('pichome_resources','pichome_folderresources',$appids));
            }else{
                $count = $_GET['count'];
            }
            $perpage = 1000;
            $start = ($i - 1) * $perpage;
            $j = 0;
            $data = DB::fetch_all("select r.rid,GROUP_CONCAT(DISTINCT fr.fid SEPARATOR ',') as fids from %t r 
left join %t fr on fr.rid = r.rid where   r.appid in(%n) group by r.rid limit $start,$perpage ",
                array('pichome_resources','pichome_folderresources','pichome_vapp',$appids));

            foreach($data as $v){
                C::t('pichome_resources')->upddate($v['rid'],['fids'=>$v['fids']]);
                $j++;
            }

            if ($j >= $perpage) {
                $complatei = ($i - 1) * $perpage + $j;
                $i++;
                $msg='目录数据升级完成';
                $next = $theurl . '?step=data&dp=1&i=' . $i.'&count='.$count;
                show_msg($msg."[ $complatei/$count] ", $next);
            } else {
                //show_msg("标签数据升级结束", "$theurl?step=data&dp=2");
                show_msg("目录数据升级结束,即将开始标签数据升级", "$theurl?step=data&dp=2");
            }
        }
    }elseif($_GET['dp'] == 2){//标签数据升级
        $i = isset($_GET['i']) ? intval($_GET['i']) : 1;
        if(!$_GET['count']){
            $count = DB::result_first("select count(id) from %t where 1",array('pichome_vapp_tag'));
        }else{
            $count = $_GET['count'];
        }
        $perpage = 1000;
        $start = ($i - 1) * $perpage;
        $j = 0;
        foreach(DB::fetch_all("select * from %t where 1 limit $start,$perpage",array('pichome_vapp_tag')) as $v){
            if(!DB::result_first("select count(id) from %t where id = %d",array('pichome_vapp_tag',$v['id']))){
                C::t('pichome_resources_tag')->insert($v);
            }
            $j++;
        }
        if ($j >= $perpage) {
            $complatei = ($i - 1) * $perpage + $j;
            $i++;
            $msg='标签数据升级完成';
            $next = $theurl . '?step=data&dp=2&i=' . $i.'&count='.$count;
            show_msg($msg."[ $complatei/$count] ", $next);
        } else {
            show_msg("标签数据升级结束,即将开始音视频转换数据升级", "$theurl?step=data&dp=3");
        }

    }elseif($_GET['dp'] == 3){//音视频转码数据升级
        $i = isset($_GET['i']) ? intval($_GET['i']) : 1;
        if(!$_GET['count']){
            $videoparams = ['video_record_bak'];
            $count = DB::result_first("select count(id) from %t where 1 ",$videoparams);
        }else{
            $count = $_GET['count'];
        }
        $perpage = 100;
        $start = ($i - 1) * $perpage;
        $j = 0;
        foreach(DB::fetch_all("select * from %t where 1 limit $start,$perpage",$videoparams) as $v){
            $path = str_replace(getglobal('setting/attachdir'),'',$v['path']);
            if(DB::result_first("select id from %t where id = %s",array('video_record',$v['id']))){
                if($path) C::t('video_record')->update($v['id'],['path'=>$path]);
            }else{
                if($path) $v['path'] = $path;
                C::t('video_record')->insert($v);
            }

            $j++;
        }

        if ($j >= $perpage) {
            $complatei = ($i - 1) * $perpage + $j;
            $i++;
            $msg='音视频转码数据升级完成';
            $next = $theurl . '?step=data&dp=1&i=' . $i.'&count='.$count;
            show_msg($msg."[ $complatei/$count] ", $next);
        } else {
            //show_msg("标签数据升级结束", "$theurl?step=data&dp=2");

            show_msg("音视频转码数据升级结束，开始升级颜色数据",  "$theurl?step=data&dp=4");
        }
    }elseif($_GET['dp'] == 4){//颜色数据升级
        $i = isset($_GET['i']) ? intval($_GET['i']) : 1;
        if(!$_GET['count']){
            $count = DB::result_first("select count(DISTINCT rid) from %t where 1",array('pichome_palette'));
        }else{
            $count = $_GET['count'];
        }
        $perpage = 1000;
        $start = ($i - 1) * $perpage;
        $j = 0;
        foreach(DB::fetch_all("select rid,GROUP_CONCAT(color,'_',id) as colors from %t where 1 group by rid limit $start,$perpage",array('pichome_palette')) as $v){
            $pnums = [];
            $colors = explode(',',$v['colors']);
            foreach($colors as $color){
                $colorarr = explode('_',$color);
                $pnum = getintPaletteNumber($colorarr[0]);
                C::t('pichome_palette')->update($colorarr[1],['p'=>$pnum]);
                $pnums[] = $pnum;
            }
            $pnumstr = implode(',',$pnums);
            C::t('pichome_resources_attr')->update($v['rid'],['colors'=>$pnumstr]);
            $j++;
        }

        if ($j >= $perpage) {
            $complatei = ($i - 1) * $perpage + $j;
            $i++;
            $msg='颜色数据升级完成';
            $next = $theurl . '?step=data&dp=4&i=' . $i.'&count='.$count;
            show_msg($msg."[ $complatei/$count] ", $next);
        } else {
            show_msg("颜色数据升级完成，即将开始缩略图数据升级", "$theurl?step=data&dp=5");
        }
    }elseif($_GET['dp'] == 5){//缩略图数据升级
        $i = isset($_GET['i']) ? intval($_GET['i']) : 1;
        //获取普通目录库id
        $appids = [];
        foreach (DB::fetch_all("select appid from %t where isdelete < %d and `type` = %d", array('pichome_vapp',1,1)) as $v) {
            $appids[] = $v['appid'];
        }
        if (empty($appids)) {
            show_msg("数据升级结束", "$theurl?step=delete");
        } else {
            if(!$_GET['count']){
                $count = DB::result_first("select COUNT(DISTINCT rid) from %t where  appid in(%n) ",
                    array('pichome_resources',$appids));
            }else{
                $count = $_GET['count'];
            }
            $perpage = 1000;
            $start = ($i - 1) * $perpage;
            $j = 0;
            $data = DB::fetch_all("select * from %t  where  appid in(%n)  limit $start,$perpage ",
                array('pichome_resources',$appids));
            $wp = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarkstatus']:'';
            $wt = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarktype']:'';
            $wcontent = $_G['setting']['IsWatermarkstatus'] ? ($_G['setting']['watermarktype'] == 'png' ? $_G['setting']['waterimg']:''):'';
            foreach($data as $v){
                //缩略图数据
                $thumbrecorddata = [
                    'rid' => $v['rid'],
                    'ext' => $v['ext'],
                    'filesize'=>$v['size'],
                    'width'=>$v['width'],
                    'height'=>$v['height'],
                    'swidth'=>$_G['setting']['thumbsize']['small']['width'],
                    'sheight'=>$_G['setting']['thumbsize']['small']['height'],
                    'lwidth' => $_G['setting']['thumbsize']['large']['width'],
                    'lheight' => $_G['setting']['thumbsize']['large']['height'],
                    'lwaterposition'=>$wp,
                    'lwatertype'=>$wt,
                    'lwatercontent'=>$wcontent,
                    'swaterposition'=>$wp,
                    'swatertype'=>$wt,
                    'swatercontent'=>$wcontent,
                ];
                C::t('thumb_record')->insert($thumbrecorddata);
                $j++;
            }

            if ($j >= $perpage) {
                $complatei = ($i - 1) * $perpage + $j;
                $i++;
                $msg='缩略图数据升级完成';
                $next = $theurl . '?step=data&dp=5&i=' . $i.'&count='.$count;
                show_msg($msg."[ $complatei/$count] ", $next);
            } else {
                show_msg("数据升级结束", "$theurl?step=delete");
            }
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
    $routefile = DZZ_ROOT.'./data/cache/'. 'route.php';
    if(!is_file($routefile)){
        @file_put_contents($routefile,"<?php \t\n return array();");
    }
    $configfile = DZZ_ROOT.'data/cache/default_mod.php';
    $configarr = array();
    $configarr['default_mod' ]='banner';
    @file_put_contents($configfile,"<?php \t\n return ".var_export($configarr,true).";");
    C::t('setting')->update('default_mod','banner');
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
    $content .= "\r\n// ".str_pad('  THE END  ', 50, '-', STR_PAD_BOTH)."\r\n return \$_config;";
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
    if(strpos($path,':') === false){
        $bz = 'dzz';
    }else{
        $patharr = explode(':', $path);
        $bz = $patharr[0];
        $did = $patharr[1];

    }
    if(!is_numeric($did) || $did < 2){
        $bz = 'dzz';
    }

    $rootpath = str_replace(BS,'/',DZZ_ROOT);
    $path = str_replace(DZZ_ROOT, '', $path);
    $path = str_replace($rootpath, '', $path);
    $path = str_replace(BS, '/', $path);
    $path = str_replace('//', '/', $path);
    $path = str_replace('./', '', $path);
    if($bz == 'dzz')$path = 'dzz::'.ltrim($path,'/');
    else $path = ltrim($path,'/');
    return $path;
}

function getbasename($filename)
{
    return preg_replace('/^.+[\\\\\\/]/', '', $filename);
}

function getintPaletteNumber($colors, $palette = array())
{

    if (empty($palette))  $palette = [
        0xfff8e1,0xf57c00,0xffd740,0xb3e5fc,0x607d8b,0xd7ccc8,
        0xff80ab,0x4e342e,0x9e9e9e,0x66bb6a,0xaed581,0x18ffff,
        0xffe0b2,0xc2185b,0x00bfa5,0x00e676,0x0277bd,0x26c6da,
        0x7c4dff,0xea80fc,0x512da8,0x7986cb,0x00e5ff,0x0288d1,
        0x69f0ae,0x3949ab,0x8e24aa,0x40c4ff,0xdd2c00,0x283593,
        0xaeea00,0xffa726,0xd84315,0x82b1ff,0xab47bc,0xd4e157,
        0xb71c1c,0x880e4f,0x00897b,0x689f38,0x212121,0xffff00,
        0x827717,0x8bc34a,0xe0f7fa,0x304ffe,0xd500f9,0xec407a,
        0x6200ea,0xffab00,0xafb42b,0x6a1b9a,0x616161,0x8d6e63,
        0x80cbc4,0x8c9eff,0xffeb3b,0xffe57f,0xfff59d,0xff7043,
        0x1976d2,0x5c6bc0,0x64dd17,0xffd600
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
function fetchtablelist($tablepre = '') {
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
