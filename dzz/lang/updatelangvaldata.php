<?php
if(!isset($_GET['step'])){
    $_GET['step'] = 'data';
}
$theurl = getglobal('siteurl').'index.php?mod=lang&op=updatelangvaldata';
if ($_GET['step'] == 'data') {
    //如果没有识别码，增加识别码
    if (!$_GET['dp']) {//库
        //升级库数据
        foreach(DB::fetch_all("select * from %t where 1",['pichome_vapp']) as $v){
            if($v['isdelete'] == 1){
                C::t('pichome_vapp')->update($v['appid'],array('isdelete'=>2));
            }
            Hook::listen('lang_parse',$v,['saveVppLangData']);
        }
        show_msg("库语言包数据升级结束,即将开始升级目录数据", "$theurl&step=data&dp=1");
    }elseif($_GET['dp'] == 1){//目录
        //升级目录数据
        $i = empty($_GET['i']) ? 1 : intval($_GET['i']);
        if (!$_GET['count']) {
            $count = DB::result_first("select COUNT(fid) from %t where 1",
                array('pichome_folder'));
        } else {
            $count = $_GET['count'];
        }
        $perpage = 1000;
        $start = ($i - 1) * $perpage;
        $j = 0;
        foreach(DB::fetch_all("select * from %t where 1 limit $start,$perpage",['pichome_folder']) as $v){
            Hook::listen('lang_parse',$v,['saveFolderLangData',[$v['fid']]]);
            $j++;
        }
        $total = ($i - 1) * $perpage+$j;
        if ($j >= $perpage || $total < $count) {
            $complatei = ($i - 1) * $perpage + $j;
            $i++;
            $msg = '目录数据升级完成';
            $next = $theurl . '&step=data&dp=1&i=' . $i . '&count=' . $count;
            show_msg($msg . "[ $complatei/$count] ", $next);
        } else {
            //show_msg("标签数据升级结束", "$theurl&step=data&dp=2");
            show_msg("目录数据升级结束,即将开始文件数据升级", "$theurl&step=data&dp=2");
        }
    }elseif($_GET['dp'] == 2){//文件
        //升级文件数据
        $i = empty($_GET['i']) ? 1 : intval($_GET['i']);
        if (!$_GET['count']) {
            $count = DB::result_first("select COUNT(rid) from %t where 1",
                array('pichome_resources'));
        } else {
            $count = $_GET['count'];
        }
        $perpage = 1000;
        $start = ($i - 1) * $perpage;
        $j = 0;
        foreach(DB::fetch_all("select * from %t where 1",['pichome_resources']) as $v){
            Hook::listen('lang_parse',$v,['saveResourcesLangData',[$v['rid']]]);
            $j++;
        }
        $total = ($i - 1) * $perpage+$j;
        if ($j >= $perpage || $total < $count) {
            $complatei = ($i - 1) * $perpage + $j;
            $i++;
            $msg = '文件数据升级完成';
            $next = $theurl . '&step=data&dp=2&i=' . $i . '&count=' . $count;
            show_msg($msg . "[ $complatei/$count] ", $next);
        } else {
            //show_msg("标签数据升级结束", "$theurl&step=data&dp=2");
            show_msg("文件数据升级结束,即将开始标签分类数据升级", "$theurl&step=data&dp=3");
        }

    }elseif($_GET['dp'] == 3){//标签群组
        foreach(DB::fetch_all("select * from %t where 1",['pichome_taggroup']) as $v){
            Hook::listen('lang_parse',$v,['saveTaggroupLangData']);
        }
        show_msg("标签分类数据升级结束,即将开始字段分类数据升级", "$theurl&step=data&dp=4");
    }elseif($_GET['dp'] == 4){//字段分类
        //saveFiledcatLangData
        foreach(DB::fetch_all("select * from %t where 1",['form_setting_filedcat']) as $v){
            Hook::listen('lang_parse',$v,['saveFiledcatLangData',[$v['id']]]);
        }
        show_msg("字段分类数据升级结束,即将开始字段数据升级", "$theurl&step=data&dp=5");
    }elseif($_GET['dp'] == 5){//字段
        //saveFiledcatLangData
        foreach(DB::fetch_all("select * from %t where 1",['form_setting']) as $v){
            Hook::listen('lang_parse',$v,['saveFiledLangData']);
        }
        show_msg("字段数据升级结束,即将开始字段选项数据升级", "$theurl&step=data&dp=6");
    }elseif($_GET['dp'] == 6){//字段选项
        //saveFiledcatLangData
        foreach(DB::fetch_all("select * from %t where type = %s or type = %s",['form_setting','select','multiselect']) as $v){
            $options = unserialize($v['options']);
            $v['options'] = [];
            foreach($options as $k=>$v1){
                $v['options'][] = ['newval'=>$v1,'oldval'=>$v1];
            }
            Hook::listen('lang_parse',$v,['saveFiledoptionsLangData']);
        }
        $tabon = 0;
        Hook::listen('checktab',$tabon);
        if($tabon){
            show_msg("字段选项数据升级结束,即将开始专辑数据升级", "$theurl&step=data&dp=7");
        }else{
            show_msg("字段选项数据升级结束,即将开始单页数据升级", "$theurl&step=data&dp=11");
        }

    }elseif($_GET['dp'] == 7){//专辑
        $i = empty($_GET['i']) ? 1 : intval($_GET['i']);
        if (!$_GET['count']) {
            $count = DB::result_first("select COUNT(tid) from %t where 1",
                array('tab'));
        } else {
            $count = $_GET['count'];
        }
        $perpage = 100;
        $start = ($i - 1) * $perpage;
        $j = 0;
       foreach(DB::fetch_all("select * from %t where 1",['tab']) as $v){
            Hook::listen('lang_parse',$v,['saveTabnameLangData']);
           foreach(DB::fetch_all("select * from %t where tid = %d",['tab_attr',$v['tid']]) as $val){
               $form = C::t('form_setting')->fetch($val['skey']);
               if($form && in_array($form['type'],['select', 'input', 'textarea', 'multiselect', 'link', 'fulltext'])){
                   $hookarr = ['tid'=>$v['tid'],'flag'=>$val['skey'],'value'=>$val['svalue'],'type'=>$form['type']];
                   Hook::listen('lang_parse', $hookarr,['saveTabattrLangeData']);
               }
           }
            $j++;
        }
        $total = ($i - 1) * $perpage+$j;
        if ($j >= $perpage || $total >= $count) {
            $complatei = ($i - 1) * $perpage + $j;
            $i++;
            $msg = '商品数据升级完成';
            $next = $theurl . '&step=data&dp=7&i=' . $i . '&count=' . $count;
            show_msg($msg . "[ $complatei/$count] ", $next);
        } else {
            //show_msg("标签数据升级结束", "$theurl&step=data&dp=2");
            show_msg("商品数据升级结束,即将开始专辑分类数据升级", "$theurl&step=data&dp=8");
        }
        //saveFiledcatLangData
    }elseif($_GET['dp'] == 8){//专辑分类
        //saveFiledcatLangData
        foreach(DB::fetch_all("select * from %t where 1",['tab_cat']) as $v){
            Hook::listen('lang_parse',$v,['saveTabcatLangData',[$v['cid']]]);
        }
        show_msg("专辑分类数据升级结束,即将开始专辑属性升级", "$theurl&step=data&dp=9");
    }elseif($_GET['dp'] == 9){//专辑组
        //saveFiledcatLangData
        foreach(DB::fetch_all("select * from %t where 1",['tab_group']) as $v){
            Hook::listen('lang_parse',$v,['saveTabgroupLangData']);
        }
        show_msg("专辑数据升级结束,即将开始专辑栏目升级", "$theurl&step=data&dp=10");
    }elseif($_GET['dp'] == 10){//专辑栏目
        //saveFiledcatLangData
        foreach(DB::fetch_all("select * from %t where 1",['tab_banner']) as $v){
            Hook::listen('lang_parse',$v,['saveTabbannerLangData']);
        }
        show_msg("专辑栏目数据升级结束,即将开始单页数据升级", "$theurl&step=data&dp=13");
    }elseif($_GET['dp'] == 13){
        foreach(DB::fetch_all("select * from %t where 1",['tab_banner']) as $v){
            Hook::listen('lang_parse',$v,['saveTabbannerLangData']);
        }
        show_msg("专辑栏目数据数据升级结束,即将开始单页数据升级", "$theurl&step=data&dp=11");
    }elseif($_GET['dp'] == 11){//单页
        //saveFiledcatLangData
        foreach(DB::fetch_all("select * from %t where 1",['pichome_templatepage']) as $v){
            Hook::listen('lang_parse',$v,['setAlonepageLangData']);
        }
        show_msg("单页数据升级结束,即将开始单页标签位数据升级", "$theurl&step=data&dp=12");
    }elseif($_GET['dp'] == 12){//单页标签位
        //saveFiledcatLangData
        foreach(DB::fetch_all("select * from %t where 1",['pichome_templatetag']) as $v){
            $tid = $v['tid'];
            Hook::listen('lang_parse',$v,['setAlonepagetagLangData',$tid]);
            foreach(DB::fetch_all("select * from %t where tid = %d",['pichome_templatetagdata',$tid]) as $val){
                $val['tdata'] = unserialize($val['tdata']);
                Hook::listen('lang_parse',$val,['setAlonpagetagdataLangData',$v['tagtype']]);
            }
        }
        show_msg("单页标签数据升级结束,即将开始专辑模块数据升级", "$theurl&step=data&dp=14");
    }elseif($_GET['dp'] == 14){//栏目
        foreach(DB::fetch_all("select * from %t where 1",['pichome_banner']) as $v){
            Hook::listen('lang_parse',$v,['setBannerLangData']);
        }
        show_msg("栏目数据升级结束,即将开始收藏夹数据升级", "$theurl&step=data&dp=15");
    }elseif($_GET['dp'] == 15){//收藏夹
        foreach(DB::fetch_all("select * from %t where 1",['pichome_collect']) as $v){
            Hook::listen('lang_parse',$v,['setCollectLangData']);
        }
        show_msg("收藏夹数据升级结束,即将开始收藏夹分类数据升级", "$theurl&step=data&dp=16");
    }elseif($_GET['dp'] == 16){//收藏夹分类
        foreach(DB::fetch_all("select * from %t where 1",['pichome_collectcat']) as $v){
            Hook::listen('lang_parse',$v,['setCollectcatLangData']);
        }
        show_msg("收藏夹分类数据升级结束,即将开始搜索模板数据升级", "$theurl&step=data&dp=17");
    }
    elseif($_GET['dp'] == 17){//搜索模板
        foreach(DB::fetch_all("select * from %t where 1",['search_template']) as $v){
            Hook::listen('lang_parse',$v,['setSearchtemplateLangData']);
        }
        show_msg('<span id="finalmsg"></span><iframe src="../misc.php?mod=syscache" style="display:none;" onload="document.getElementById(\'finalmsg\').innerHTML = \'恭喜，数据升级完成！为了数据安全，请删除本文件。' . '\'"></iframe>');
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
