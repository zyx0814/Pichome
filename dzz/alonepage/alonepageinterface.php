<?php
if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}
//管理权限进入
Hook::listen('adminlogin');
global $_G;
if (isset($_G['setting'])) $setting = $_G['setting'];
else  $setting = C::t('setting')->fetch_all();
$navtitle="管理中心-单页管理";
$themeid = isset($_G['setting']['pichometheme']) ? intval($_G['setting']['pichometheme']):1;

$do = isset($_GET['do']) ? trim($_GET['do']):'';
updatesession();
if($do == 'addpage'){//新建单页
    if(submitcheck('addpage')){
        $pagename = isset($_GET['pagename']) ? getstr($_GET['pagename']):'';
        $id = isset($_GET['id']) ? intval($_GET['id']):0;
        if(!$pagename) exit(json_encode(['success'=>false,'msg'=>'缺少单页名称']));
        if($id &&  DB::result_first("select id from %t where pagename = %s and id != %d",['pichome_templatepage',$pagename,$id])){
            exit(json_encode(['success'=>false,'msg'=>'单页名称已存在']));
        }elseif(DB::result_first("select id from %t where pagename = %s and id != %d ",['pichome_templatepage',$pagename,$id])){
            exit(json_encode(['success'=>false,'msg'=>'单页名称已存在']));
        }
        $setarr = [
            'pagename'=>$pagename,
            'dateline'=>TIMESTAMP
        ];
        if($id) $setarr['id'] = $id;
        if($setarr['id'] = C::t('pichome_templatepage')->insertdata($setarr)){
            $address = $_GET['address'] ? trim($_GET['address']):'';
            $url = 'index.php?mod=alonepage&op=view#id='.$setarr['id'];
            $shorturl = C::t('pichome_route')->update_path_by_url($url,$address);
            if($setting['pathinfo'] && $shorturl) $setarr['url']=$shorturl;
            else $setarr['url']=$url;

        }

        exit(json_encode(['success'=>true,'data'=>$setarr]));
    }else{
        $id = isset($_GET['id']) ? intval($_GET['id']):0;
        $pagedata = C::t('pichome_templatepage')->fetch($id);
        $url = 'index.php?mod=alonepage&op=view#id='.$id;
        if($setting['pathinfo']) $path = C::t('pichome_route')->feth_path_by_url($url);
        else $path = '';
        if($path){
            $pagedata['url'] = $path;
        }else{
            $pagedata['url'] = $url;
        }
        exit(json_encode(['success'=>true,'data'=>$pagedata]));
    }
}elseif($do == 'delpage'){//删除单页
    $id = isset($_GET['id']) ? intval($_GET['id']):0;
    C::t('pichome_templatepage')->delete_by_id($id);
    exit(json_encode(['success'=>true]));
}elseif($do == 'deltag'){//删除标签位
    $tid = isset($_GET['tid']) ? intval($_GET['tid']):0;
    C::t('pichome_templatetag')->delete_by_tid($tid);
    exit(json_encode(['success'=>true]));
}elseif($do == 'deltagdata'){//删除标签数据
    $tdid = isset($_GET['tdid']) ? intval($_GET['tdid']):0;
    C::t('pichome_templatetagdata')->delete_by_id($tdid);
    exit(json_encode(['success'=>true]));
}elseif($do == 'pagelist'){//单页列表
    $data = [];
    foreach(DB::fetch_all("select * from %t where 1 order by disp asc,dateline asc ",['pichome_templatepage']) as $v){
        $v['dateline'] = dgmdate($v['dateline'],'Y-m-d H:i:s');
        $url = 'index.php?mod=alonepage&op=view#id='.$v['id'];
        if($setting['pathinfo']) $path = C::t('pichome_route')->feth_path_by_url($url);
        else $path = '';
        if($path){
            $v['url'] = $_G['siteurl'].$path;
        }else{
            $v['url'] = $_G['siteurl'].$url;
        }
        $data[] = $v;
    }
    exit(json_encode(['success'=>true,'data'=>$data]));
}elseif($do == 'geturlqrcode'){//获取链接二维码
    $id = isset($_GET['id']) ? intval($_GET['id']) : '';
    $url = 'index.php?mod=alonepage&op=view#id='.$id;
    $sid = 'a_'.$id;
    $qrcode = C::t('pichome_route')->getQRcodeBySid($url,$sid);
    exit(json_encode(['success'=>true,'qrcode'=>$qrcode]));
}elseif($do == 'sortpage'){//单页排序
    $ids = isset($_GET['ids']) ? trim($_GET['ids']):'';
    $idarr = explode(',',$ids);
    foreach($idarr as $k=>$v){
        C::t('pichome_templatepage')->update($v,['disp'=>$k]);
    }
    exit(json_encode(['success'=>true]));
}elseif($do == 'sorttag'){//标签位排序
    $tids = isset($_GET['tids']) ? trim($_GET['tids']):'';
    $tidarr = explode(',',$tids);
    foreach($tidarr as $k=>$v){
        C::t('pichome_templatetag')->update($v,['disp'=>$k]);
    }
    exit(json_encode(['success'=>true]));
}elseif($do == 'setpage'){//设置单页内容
    $id = isset($_GET['id']) ? intval($_GET['id']):0;
    $page = C::t('pichome_templatepage')->fetch($id);
    if(!$page) exit(json_encode(['success'=>false,'msg'=>'单页不存在']));
    $pagedata = $_GET['data'];
    $pagetag = [
        'tid'=>$pagedata['tid'] ? intval($pagedata['tid']):0,
        'tagtype'=>$pagedata['type'],
        'tagname'=>$pagedata['title'] ? getstr($pagedata['title']):'',
        'dateline'=>TIMESTAMP,
        'disp'=>isset($pagedata['disp']) ? intval($pagedata['disp']):0,
        'pageid'=>$id,
    ];
    $pagedata['tid'] = C::t('pichome_templatetag')->insertdata($pagetag);
    if($pagedata['tid']){
        $tagtheme = [
            'themeid'=>$themeid,
            'style'=>$pagedata['style'] ? serialize($pagedata['style']):'',
            'tid'=>$pagedata['tid'],
        ];
        C::t('pichome_templatetagtheme')->insertdata($tagtheme);

        foreach($pagedata['data'] as $k=>$v){
            $tagdata = [
                'id'=>$v['tdid'],
                'tid'=>$pagedata['tid'],
                'tdata'=>$v['data'],
                'type'=>$pagedata['type'],
                'tdataname'=>$v['name'] ? getstr($v['name']):$pagetag['tagname'],
                'cachetime'=>intval($v['data'][0]['time'])
            ];
            $pagedata['data'][$k]['tdid'] = C::t('pichome_templatetagdata')->insertdata($tagdata);
            //更新缓存数据
            if($pagetag['tagtype'] == 'file_rec' || $pagetag['tagtype'] == 'db_ids'){
                dfsockopen(getglobal('localurl') . 'misc.php?mod=updatepagedata&tdid='.$pagedata['data'][$k]['tdid'], 0, '', '', false, '', 1);
            }
        }

        exit(json_encode(['success'=>true,'data'=>$pagedata]));
    }else{
        exit(json_encode(['success'=>false,'msg'=>'保存失败']));
    }



}elseif($do=='upload'){//上传图片
    include libfile( 'class/uploadhandler' );

    $options = array( 'accept_file_types' => '/\.(gif|jpe?g|png|svg)$/i',

        'upload_dir' => $_G[ 'setting' ][ 'attachdir' ] . 'cache/',

        'upload_url' => $_G[ 'setting' ][ 'attachurl' ] . 'cache/',

        'thumbnail' => array( 'max-width' => 40, 'max-height' => 40 ) );

    $upload_handler = new uploadhandler( $options );
    updatesession();
    exit();
} elseif($do=='uploadmedia'){//上传图片
    include libfile( 'class/uploadhandler' );

    $options = array( 'accept_file_types' => '/\.(mp4|flv|mp3|webm|ogg|aac))$/i',

        'upload_dir' => $_G[ 'setting' ][ 'attachdir' ] . 'cache/',

        'upload_url' => $_G[ 'setting' ][ 'attachurl' ] . 'cache/',

        'thumbnail' => array( 'max-width' => 40, 'max-height' => 40 ) );

    $upload_handler = new uploadhandler( $options );
    updatesession();
    exit();
}elseif($do == 'uploadico'){//设置图标
    $tid = isset($_GET['tid']) ? intval($_GET['tid']):0;
    $aid = isset($_GET['aid']) ? intval($_GET['aid']):0;
    $tagdata = C::t('pichome_templatetag')->fetch($tid);
    if(!$tagdata){
        exit(json_encode(array('success'=>false,'msg'=>'data is not exixts')));
    }else{
        if($tagdata['aid']){
            C::t('attachment')->delete_by_aid($tagdata['aid']);
        }else{
            C::t('attachment')->addcopy_by_aid($aid);
        }
    }
    exit(json_encode(array('success'=>true)));
}elseif($do == 'getapporsources'){//获取库列表或数据源列表
    $stype = isset($_GET['stype']) ? intval($_GET['stype']):0;
    $data = [];
    if($stype){
        $params = ['pichome_smartdata'];
        $wheresql = ' 1 ';
        //兼容图组
        if($stype == 2){
            $wheresql .= ' and stype = %d';
            $params[] = 1;
        }else{
            $wheresql .= ' and stype = %d';
            $params[] = 0;
        }
        foreach(DB::fetch_all("select * from %t where $wheresql ",$params) as $v){
            $data[] = array('id'=>$v['id'],'name'=>$v['name']);
        }

    }else{
        foreach(DB::fetch_all("select * from %t where isdelete < 1",['pichome_vapp']) as $v){
            if ($v['type'] != 3 && !IO::checkfileexists($v['path'],1)) {
                continue;
            }
            $data[] = array('id'=>$v['appid'],'name'=>$v['appname']);
        }
    }
    exit(json_encode(['success'=>true,'data'=>$data]));
}elseif($do == 'getmouldstyle'){//获取模块样式
    $mould = isset($_GET['mould']) ? trim($_GET['mould']):'slide';
    $themedata = $_G['setting']['pichomethemedata'][$themeid];
    $returndata = [];
    if(isset($themedata['themestyle'])){
        $returndata = $themedata['themestyle'];
    }
    exit(json_encode(['success'=>true,'data'=>$returndata,'themeid'=>$themeid]));
}elseif($do == 'typecollection'){//获取ku,单页，栏目,专辑
    $library = array();
    if($_G['adminid'] == 1){
        $library = DB::fetch_all("select * from %t  where isdelete = 0 order by `disp` asc,dateline desc", array('pichome_vapp'));
    }else{
        $library  = DB::fetch_all("select v.* from %t vm left join %t v on v.appid = vm.appid where vm.uid = %d and v.isdelete = 0 order by v.disp", array('pichome_vappmember','pichome_vapp',$uid));
    }

    $alonepage = DB::fetch_all("select * from %t where 1 order by disp asc,dateline asc ",['pichome_templatepage']);
    $banner = C::t('pichome_banner')->getbannerlist();

    $tabstatus = 0;
    $tabgroupdata = [];
    Hook::listen('checktab', $tabstatus);
    if ($tabstatus) {//获取有tab数据
        Hook::listen('gettabgroupdata', $tabgroupdata);
    }
    foreach($tabgroupdata as $k=>$tab){
        if(!$tab['available']){
            unset($tabgroupdata[$k]);
        }
    }
    exit(json_encode(array('tab' => $tabgroupdata,'library' => $library,'alonepage'=>$alonepage,'banner'=>$banner['top'])));

}elseif($do == 'getpagecontent'){//获取单页内容
    $id = isset($_GET['id']) ? intval($_GET['id']):0;
    $pagedata = C::t('pichome_templatepage')->fetch_data_by_id($id);
    exit(json_encode(['success'=>true,'data'=>$pagedata]));
}
