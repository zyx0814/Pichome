<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
global $_G,$id,$data;
    $data = C::t('publish_list')->fetch_by_id($id,1);
    $_G['setting']['metakeywords']=!empty($data['metakeywords'])?$data['metakeywords']:$_G['setting']['metakeywords'];
    $_G['setting']['metadescription']=!empty($data['metadescription'])?$data['metadescription']:$_G['setting']['metadescription'];
    if($_G['setting']['pathinfo']){
        $url = $_G['siteurl'].$data['address'];
    }else{
        $url = $_G['siteurl'].'index.php?mod=publish&id='.$id;
    }
    $data['url']=$url;
    $data['fdateline']=dgmdate($data['dateline'],'Y-m-d H:i:s');
    //多文件时，处理
    $perm = C::t('publish_list')->getPermById($id);

    $rids = explode(',',$data['pval']);
    $dataresources = C::t('pichome_resources')->getdatasbyrids($rids,1,$perm);
    //print_r($dataresources);
    $page=$_GET['page']?intval($_GET['page']):1;
    $perpage=$_GET['perpage']?intval($_GET['perpage']):5;
    $start=($page-1)*$perpage;
    $orderby='dateline';
    $order='DESC';
    $sql="p.pstatus='1' and r.rpid=%d";
    $param=array('publish_relation','publish_list',$id);

    $ordersql="order by p.$orderby $order";
    $cdata=array();
    if($count=DB::result_first("select COUNT(*) from %t r LEFT JOIN %t p on r.pid=p.id where $sql",$param)) {
        foreach (DB::fetch_all("select p.id from %t r LEFT JOIN %t p on r.pid=p.id where $sql $ordersql limit $start,$perpage", $param) as $value) {

            $value=C::t('publish_list')->fetch_by_id($value['id']);
            $value['dateline']=dgmdate($value['dateline'],'Y-m-d H:i:s');
            if($value['pageset']['_file_cover'][0]['src']){
                $value['img']=$value['pageset']['_file_cover'][0]['src'];
            }else{
                $value['img']='';
            }

            //处理地址
            $url='index.php?mod=publish&id=' . $value['id'];
            $value['address']=C::t('pichome_route')->update_path_by_url($url,$value['address']);
            if(strpos($value['url'],'http') === false){
                $value['url'] = $_G['siteurl'] . $value['address'];
            }else{
                $value['url'] =  $_G['siteurl'] .$url;
            }
            $cdata[]=$value;

        }
        $multi= multi($count, $perpage, $page, $data['url'],'text-center');
    }
    include template('robot/collect/index');
    exit();