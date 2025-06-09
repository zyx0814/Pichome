<?php
require_once DZZ_ROOT . 'dzz' . BS . 'lang' . BS . 'class' . BS . 'idtypeMap.php';
global $_G,$idTypeMap;
$cid = isset($_GET['cid']) ? trim($_GET['cid']) : '';
$appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
$nocat = isset($_GET['nocat']) ? intval($_GET['nocat']) : 0;
$params = array( 'pichome_tag','pichome_vapp_tag','pichome_tagrelation');
$sql = "select t.tid,t.tagname,tr.cid from %t t left join %t vt on vt.tid = t.tid left join %t tr on tr.tid = t.tid";
$wheresql = " vt.appid = %s ";
$para = [$appid];
if($cid){
    $wheresql .= "and tr.cid = %s ";
    $para[] = $cid;
}elseif($nocat){
    $wheresql .= " and (tr.cid='' or ISNULL(tr.cid))";
}
$data = array();

$params = array_merge($params,$para);

foreach(DB::fetch_all("$sql where $wheresql", $params) as $v){

    //获取当前标签的所有分组数据


    $tgdata = [];
    $tglangdatas = [];
    if($v['cid']){
        foreach(DB::fetch_all("select cid,catname from %t where cid =%s",['pichome_taggroup',$v['cid']]) as $tg){
            $tgdata= ['cid'=>$tg['cid'],'catname'=>$tg['catname']];
        }
    }
    $langtagdata = ['tid'=>$v['tid'],'cid'=>$v['cid']];
    unset($v['cid']);
    foreach($_G['language_list'] as $k=>$lang){
        $tagdata = $v;
        Hook::listen('lang_parse',$tagdata,['getLangData','tag',0,$k,0]);
        unset($tagdata['tid']);
        Hook::listen('lang_parse',$tgdata,['getLangData','taggroup',0,$k,0]);
        $tagdata['groupname'] = $tgdata['catname'];
        $langtagdata[$k] = $tagdata;
    }
    $data[] = $langtagdata;

}

$appdata = C::t('pichome_vapp')->fetchByAppid($appid,1);

if($cid){
    $taggroupdata = C::t('pichome_taggroup')->fetch($cid);
    Hook::listen('lang_parse',$taggroupdata,['getTaggroupLangData']);
    $title = lang('export_tag').'-'.$taggroupdata['catname'].'-'.$appdata['appname'];
}elseif($nocat){
    $title = lang('export_tag').'-'.lang('unclassify').'-'.$appdata['appname'];
}else{
    $title = lang('export_tag').'-'.$appdata['appname'];
}

$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator($_G['username'])
    ->setTitle($title.'- oaooa')
    ->setSubject($title.' - oaooa')
    ->setDescription($title.' - oaooa Export By oaooa  '.date('Y-m-d H:i:s'))
    ->setKeywords($title.' - oaooa')
    ->setCategory($title);

$list = array();
$objPHPExcel->setActiveSheetIndex(0);

$h0keys = array_keys($_G['language_list']);
$h0 = [];
foreach($h0keys as $k=>$v){
  $h0[$v] =  $_G['language_list'][$v]['langval'];
}

$j=2;
foreach($h0 as $key =>$value){
    // 计算起始列和结束列的索引
    $startIndex = getColIndex($j) . '1';
    $endIndex = getColIndex($j + 1) . '1';
    // 设置单元格值
    $objPHPExcel->getActiveSheet()->setCellValue($startIndex, $value);
    // 合并单元格
    $objPHPExcel->getActiveSheet()->mergeCells($startIndex . ':' . $endIndex);
    // 设置单元格对齐方式为居中
    $objPHPExcel->getActiveSheet()->getStyle($startIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // 更新列表
    $list[1][$startIndex] = $value;

    // 增加计数器以跳过下一列
    $j += 2;
}
$h1arr = ['tagname','taggroup'];
$count = count($h0);

$h1 = ['tid','cid'];
for($i = 0;$i < $count;$i++){
    $h1valarr = array_values($h1arr);
    $h1 = array_merge($h1,$h1valarr);
}
$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);
$j =0;
foreach($h1 as $key =>$value){
    // 计算起始列和结束列的索引
    $startIndex = getColIndex($j) . '2';
    // 设置单元格值
    $objPHPExcel->getActiveSheet()->setCellValue($startIndex, $value);
    // 设置单元格对齐方式为居中
    $objPHPExcel->getActiveSheet()->getStyle($startIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // 更新列表
    $list[1][$startIndex] = $value;

    // 增加计数器
    $j += 1;
}
$datas = [];
foreach($data as $v){
    $tmparr = [$v['tid'],$v['cid']];
    unset($v['tid']);
    unset($v['cid']);
    if(is_array($v)){
        foreach($v as $val){
            $val = array_values($val);
            $tmparr = array_merge($tmparr,$val);
        }
    }

    $datas[] = $tmparr;
}
$i = 3;

foreach($datas as $key=>$v){
    $j = 0;
    foreach($v as $val){
        $index=getColIndex($j).$i;
        $objPHPExcel->getActiveSheet()->setCellValue($index,$val);
        $objPHPExcel->getActiveSheet()->getStyle($index)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $j++;
        $list[$i][$index]=$v[$key];
    }
    $i++;

}

$objPHPExcel->setActiveSheetIndex(0);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$filename=$_G['setting']['attachdir'].'./cache/'.random(5).'.xlsx';
$objWriter->save($filename);

$name=$title.'.xlsx';


$name = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name).'"';

$filesize=filesize($filename);
$chunk = 10 * 1024 * 1024;
if(!$fp = @fopen($filename, 'rb')) {
    exit(lang('export_failure'));
}
dheader('Date: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
dheader('Content-Encoding: none');
dheader('Content-Disposition: attachment; filename='.$name);
dheader('Content-Type: application/octet-stream');
dheader('Content-Length: '.$filesize);
@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
while (!feof($fp)) {
    echo fread($fp, $chunk);
    @ob_flush();  // flush output
    @flush();
}
@unlink($filename);
exit();

function getColIndex($index){
    $string="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $ret='';
    if($index>255) return '';
    for($i=0;$i<floor($index/strlen($string));$i++){
        $ret=$string[$i];
    }
    $ret.=$string[($index%(strlen($string)))];
    return $ret;
}