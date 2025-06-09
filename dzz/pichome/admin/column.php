<?php
if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}
global $_G;
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
$themeid = isset($_G['setting']['pichometheme']) ? intval($_G['setting']['pichometheme']):1;
$navtitle = '栏目设置';
$ttype = isset($_GET['ttype']) ? intval($_GET['ttype']):0;
if($operation == 'addandedit'){//新建和编辑栏目
    $bid = isset($_GET['bid']) ? intval($_GET['bid']):0;
    if(submitcheck('addbanner')){
        $bannername = isset($_GET['bannername']) ? getstr($_GET['bannername'],30):'';
        $settype = isset($_GET['settype']) ? intval($_GET['settype']):1;
        if(DB::result_first("select id from %t where bannername = %s and themeid = %d and settype = %d and id != %d",array('pichome_banner',$bannername,$themeid,$settype,$bid))){
            exit(json_encode(array('success'=>false,'mgs'=>'banner is exists')));
        }
		$btype = isset($_GET['btype']) ? intval($_GET['btype']):1;
        $ctype = isset($_GET['ctype']) ? intval($_GET['ctype']):0;
        $typefilter = isset($_GET['typefilter']) ? trim($_GET['typefilter']):'';
        $appids = isset($_GET['appids']) ? trim($_GET['appids']):1;
        if($appids === 1 || count(explode(',',$appids)) > 1){
            $filters =  'a:13:{i:0;a:6:{s:5:"label";s:6:"分类";s:4:"type";s:8:"classify";s:3:"cid";s:10:"p_classify";s:8:"disabled";s:5:"false";s:7:"checked";s:5:"false";s:6:"parent";s:4:"true";}i:1;a:7:{s:5:"label";s:6:"标签";s:4:"type";s:3:"tag";s:3:"cid";s:5:"p_tag";s:4:"data";a:3:{i:0;s:3:"111";i:1;s:3:"222";i:2;s:3:"333";}s:8:"showtype";s:1:"0";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:2;a:5:{s:5:"label";s:6:"颜色";s:4:"type";s:5:"color";s:3:"cid";s:7:"p_color";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:3;a:5:{s:5:"label";s:6:"链接";s:4:"type";s:4:"link";s:3:"cid";s:6:"p_link";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:4;a:5:{s:5:"label";s:6:"注释";s:4:"type";s:4:"desc";s:3:"cid";s:6:"p_desc";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:5;a:5:{s:5:"label";s:6:"时长";s:4:"type";s:8:"duration";s:3:"cid";s:10:"p_duration";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:6;a:5:{s:5:"label";s:6:"尺寸";s:4:"type";s:4:"size";s:3:"cid";s:6:"p_size";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:7;a:5:{s:5:"label";s:6:"类型";s:4:"type";s:3:"ext";s:3:"cid";s:5:"p_ext";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:8;a:5:{s:5:"label";s:6:"形状";s:4:"type";s:5:"shape";s:3:"cid";s:7:"p_shape";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:9;a:5:{s:5:"label";s:6:"评分";s:4:"type";s:5:"grade";s:3:"cid";s:7:"p_grade";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:10;a:5:{s:5:"label";s:12:"添加时间";s:4:"type";s:5:"btime";s:3:"cid";s:7:"p_btime";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:11;a:5:{s:5:"label";s:12:"修改日期";s:4:"type";s:8:"dateline";s:3:"cid";s:10:"p_dateline";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:12;a:5:{s:5:"label";s:12:"创建日期";s:4:"type";s:5:"mtime";s:3:"cid";s:7:"p_mtime";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}}';
        }else{
            $filters = 'a:13:{i:0;a:7:{s:5:"label";s:6:"分类";s:4:"type";s:8:"classify";s:3:"cid";s:10:"p_classify";s:8:"disabled";s:5:"false";s:8:"showtype";s:1:"0";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:1;a:8:{s:5:"label";s:6:"标签";s:4:"type";s:3:"tag";s:3:"cid";s:5:"p_tag";s:4:"data";a:3:{i:0;s:3:"aaa";i:1;s:3:"bbb";i:2;s:3:"ccc";}s:8:"disabled";s:5:"false";s:8:"showtype";s:1:"1";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:2;a:5:{s:5:"label";s:6:"颜色";s:4:"type";s:5:"color";s:3:"cid";s:7:"p_color";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:3;a:5:{s:5:"label";s:6:"链接";s:4:"type";s:4:"link";s:3:"cid";s:6:"p_link";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:4;a:5:{s:5:"label";s:6:"注释";s:4:"type";s:4:"desc";s:3:"cid";s:6:"p_desc";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:5;a:5:{s:5:"label";s:6:"时长";s:4:"type";s:8:"duration";s:3:"cid";s:10:"p_duration";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:6;a:5:{s:5:"label";s:6:"尺寸";s:4:"type";s:4:"size";s:3:"cid";s:6:"p_size";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:7;a:5:{s:5:"label";s:6:"类型";s:4:"type";s:3:"ext";s:3:"cid";s:5:"p_ext";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:8;a:5:{s:5:"label";s:6:"形状";s:4:"type";s:5:"shape";s:3:"cid";s:7:"p_shape";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:9;a:5:{s:5:"label";s:6:"评分";s:4:"type";s:5:"grade";s:3:"cid";s:7:"p_grade";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:10;a:5:{s:5:"label";s:12:"添加时间";s:4:"type";s:5:"btime";s:3:"cid";s:7:"p_btime";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:11;a:5:{s:5:"label";s:12:"修改日期";s:4:"type";s:8:"dateline";s:3:"cid";s:10:"p_dateline";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:12;a:5:{s:5:"label";s:12:"创建日期";s:4:"type";s:5:"mtime";s:3:"cid";s:7:"p_mtime";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}}';
        }
        $setarr = [
            'bannername'=>$bannername,
            'ctype'=>$ctype,
            'typefilter'=>$typefilter,
            'appids'=>$appids,
            'btype'=>$btype,
            'filters'=>$filters,
            'themeid'=>$themeid,
            'settype'=>$settype,
            'views'=>'s:1:"1";',
            'icon'=>isset($_GET['icon']) ? intval($_GET['icon']):0,
            'showtype'=>'a:6:{s:6:"layout";s:9:"waterFall";s:4:"show";a:2:{i:0;s:4:"name";i:1;s:5:"other";}s:5:"other";s:5:"btime";s:4:"sort";s:1:"2";s:4:"desc";s:4:"desc";s:8:"opentype";s:3:"new";}',
        ];
        if($bid){
            C::t('pichome_banner')->update($bid,$setarr);
        }else{
            $bid = C::t('pichome_banner')->insert($setarr,1);
        }
        exit(json_encode(array('success'=>true,'bid'=>$bid)));
    }else{
        $bannerdata = [];
        $bannerdata = C::t('pichome_banner')->fetch($bid);
        if($bannerdata['icon']) $bannerdata['iconpath'] = getglobal('siteurl').'index.php?mod=io&op=getfileStream&path='.dzzencode('attach::'.$bannerdata['icon']);
        else $bannerdata['iconpath'] = '';
        exit(json_encode(array('success'=>true,'bannerdata'=>$bannerdata)));
    }
}elseif ($operation == 'getapptagcat') {
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $catdata = C::t('pichome_taggroup')->fetch_by_appid($appid);
    exit(json_encode($catdata));
	
} elseif($operation=='upload'){//上传图片
    include libfile( 'class/uploadhandler' );

    $options = array( 'accept_file_types' => '/\.(gif|jpe?g|png|svg)$/i',

        'upload_dir' => $_G[ 'setting' ][ 'attachdir' ] . 'cache/',

        'upload_url' => $_G[ 'setting' ][ 'attachurl' ] . 'cache/',

        'thumbnail' => array( 'max-width' => 40, 'max-height' => 40 ) );

    $upload_handler = new uploadhandler( $options );
    updatesession();
    exit();
} elseif($operation == 'uploadico'){//设置图标
    $bid = isset($_GET['bid']) ? intval($_GET['bid']):0;
    $aid = isset($_GET['aid']) ? intval($_GET['aid']):0;
    $bannerdata = C::t('pichome_banner')->fetch($bid);
    if(!$bannerdata){
        exit(json_encode(array('success'=>false,'msg'=>'banner is not exixts')));
    }else{
        if($bannerdata['aid']){
            C::t('attachment')->delete_by_aid($bannerdata['aid']);
        }else{
            C::t('attachment')->addcopy_by_aid($aid);
        }
    }
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'filter'){//设置或获取筛选项
    $bid = isset($_GET['bid']) ? intval($_GET['bid']):0;
    $bannerdata = C::t('pichome_banner')->fetch($bid);
    if(!$bannerdata){
        exit(json_encode(array('success'=>false,'msg'=>'banner is not exixts')));
    }
    if(submitcheck('bannerfilter')){
        $filterdata = isset($_GET['filterdata']) ? serialize($_GET['filterdata']):'';

        C::t('pichome_banner')->update($bid,['filters'=>$filterdata]);
    }else{
        $filterdata = unserialize($bannerdata['filters']);
    }
    exit(json_encode(array('success'=>true,data=>$bannerdata,'filterdata'=>$filterdata)));
}elseif($operation == 'showtype'){//设置展示方式
    $bid = isset($_GET['bid']) ? intval($_GET['bid']):0;
    $bannerdata = C::t('pichome_banner')->fetch($bid);
    if(!$bannerdata){
        exit(json_encode(array('success'=>false,'msg'=>'banner is not exixts')));
    }
    if(submitcheck('showtype')){
        $showtypedata = isset($_GET['showtypedata']) ? serialize($_GET['showtypedata']):'a:6:{s:6:"layout";s:9:"waterFall";s:4:"show";a:2:{i:0;s:4:"name";i:1;s:5:"other";}s:5:"other";s:5:"btime";s:4:"sort";s:1:"2";s:4:"desc";s:4:"desc";s:8:"opentype";s:3:"new";}';
        C::t('pichome_banner')->update($bid,['showtype'=>$showtypedata]);
    }else{
        $showtypedata = unserialize($bannerdata['showtype']);
    }
    exit(json_encode(array('success'=>true,'filterdata'=>$showtypedata)));
}elseif($operation == 'setbanner'){
    include libfile('function/cache');
    $themebanner = isset($_GET['themebanner']) ? intval($_GET['themebanner']):0;
    C::t('pichome_theme')->update($themeid,array('themebanner'=>$themebanner));
    $bannerdata = C::t('pichome_banner')->fetch_by_themeid($themeid,$themebanner);
    updatecache('setting');
    exit(json_encode(array('success'=>true,'bannerdata'=>$bannerdata)));
}elseif($operation == 'member'){
    $bid = isset($_GET['bid']) ? intval($_GET['bid']):0;
    $bannerdata = C::t('pichome_banner')->fetch($bid);
    if(!$bannerdata){
        exit(json_encode(array('success'=>false,'msg'=>'banner is not exixts')));
    }
    $data = [];
    if(submitcheck('member')){
        $setarr = [
            'views'=>serialize($_GET['views']),
            'downloads'=>serialize($_GET['downloads']),
            'share'=>serialize($_GET['share']),

        ];
        C::t('pichome_banner')->update($bid,$setarr);
    }else{
        $data['view'] = unserialize($bannerdata['views']);
        $data['downloads'] = unserialize($bannerdata['downloads']);
        $data['share'] = unserialize($bannerdata['share']);

        //访问权限用户
        $vorgids = [];
        if(isset($data['view']['uids'])){
            $hasorgiduids = [];
            foreach(DB::fetch_all("select orgid from %t where uid in(%n)",array('organization_user',$data['view']['uids'])) as $ov){
                $vorgids[] = $ov['orgid'];
                $hasorgiduids[] = $ov['uid'];
            }
            $data['view']['other'] = array_diff($data['view']['uids'],$hasorgiduids);
        }
        if(isset($data['view']['groups'])){
            $viewgroups = $data['view']['groups'];
            if(in_array('other',$viewgroups)){
                $otherindex = array_search('other',$viewgroups);
                unset($viewgroups[$otherindex]);
            }
            $vorgids = array_merge($vorgids,$viewgroups);
        }
        if($vorgids){
            $tmporgids = [];
            foreach(DB::fetch_all("select pathkey from %t where orgid in(%n)",array('organization',$vorgids)) as $vo){
                $torgids = explode('_',str_replace('-','',$vo['pathkey']));
                $tmporgids = array_merge($tmporgids,$torgids);
            }
            $tmporgids = array_unique(array_filter($tmporgids));
            $data['view']['vorgids'] =$tmporgids;
        }
        //下载权限用户
        $dorgids = [];
        if(isset($data['downloads']['uids'])){
            $hasorgiduids = [];
            foreach(DB::fetch_all("select orgid,uid from %t where uid in(%n)",array('organization_user',$data['downloads']['uids'])) as $ov){
                $dorgids[] = $ov['orgid'];
                $hasorgiduids[] = $ov['uid'];
            }
            $data['downloads']['other'] = array_diff($data['downloads']['uids'],$hasorgiduids);
        }

        if(isset($data['downloads']['groups'])){
            $dgroups = $data['downloads']['groups'];
            if(in_array('other',$dgroups)){
                $otherindex = array_search('other',$dgroups);
                unset($dgroups[$otherindex]);
            }
            $dorgids = array_merge($dorgids,$dgroups);
        }

        if($dorgids){
            $tmporgids = [];
            foreach(DB::fetch_all("select pathkey from %t where orgid in(%n)",array('organization',$dorgids)) as $vo){
                $torgids = explode('_',str_replace('-','',$vo['pathkey']));
                $tmporgids = array_merge($tmporgids,$torgids);
            }
            $tmporgids = array_unique(array_filter($tmporgids));
            $data['downloads']['dorgids'] =$tmporgids;
        }
        //分享权限用户
        $sorgids = [];
        if(isset($data['share']['uids'])){
            $hasorgiduids = [];
            foreach(DB::fetch_all("select orgid from %t where uid in(%n)",array('organization_user',$data['share']['uids'])) as $ov){
                $sorgids[] = $ov['orgid'];
            }
            $data['share']['other'] = array_diff($data['share']['uids'],$hasorgiduids);
        }
        if(isset($data['share']['groups'])){
            $sgroups = $data['share']['groups'];
            if(in_array('other',$dgroups)){
                $otherindex = array_search('other',$sgroups);
                unset($sgroups[$otherindex]);
            }
            $sorgids = array_merge($sorgids,$sgroups);
        }
        if($sorgids){
            $tmporgids = [];
            foreach(DB::fetch_all("select pathkey from %t where orgid in(%n)",array('organization',$sorgids)) as $vo){
                $torgids = explode('_',str_replace('-','',$vo['pathkey']));
                $tmporgids = array_merge($tmporgids,$torgids);
            }
            $tmporgids = array_unique(array_filter($tmporgids));
            $data['share']['sorgids'] =$tmporgids;
        }

    }
    exit(json_encode(array('success'=>true,'data'=>$data)));
}elseif($operation == 'editbanner'){
    $bid = isset($_GET['bid']) ? intval($_GET['bid']):0;
    if(submitcheck('editbanner')){
        $bannername = isset($_GET['bannername']) ? getstr($_GET['bannername'],30):'';
        if(DB::result_first("select id from %t where bannername = %s and themeid = %d  and id != %d",array('pichome_banner',$bannername,$themeid,$bid))){
            exit(json_encode(array('success'=>false,'mgs'=>'banner is exists')));
        }
        $ctype = isset($_GET['ctype']) ? intval($_GET['ctype']):0;
        $typefilter = isset($_GET['typefilter']) ? trim($_GET['typefilter']):'';
        $filters = isset($_GET['filters']) ? trim($_GET['filters']):'';
        $appids = isset($_GET['appids']) ? trim($_GET['appids']):1;
        if($appids === 1 || count(explode(',',$appids)) > 1){
            $filters =  ($filters) ? serialize($filters):'a:13:{i:0;a:6:{s:5:"label";s:6:"分类";s:4:"type";s:8:"classify";s:3:"cid";s:10:"p_classify";s:8:"disabled";s:5:"false";s:7:"checked";s:5:"false";s:6:"parent";s:4:"true";}i:1;a:7:{s:5:"label";s:6:"标签";s:4:"type";s:3:"tag";s:3:"cid";s:5:"p_tag";s:4:"data";a:3:{i:0;s:3:"111";i:1;s:3:"222";i:2;s:3:"333";}s:8:"showtype";s:1:"0";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:2;a:5:{s:5:"label";s:6:"颜色";s:4:"type";s:5:"color";s:3:"cid";s:7:"p_color";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:3;a:5:{s:5:"label";s:6:"链接";s:4:"type";s:4:"link";s:3:"cid";s:6:"p_link";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:4;a:5:{s:5:"label";s:6:"注释";s:4:"type";s:4:"desc";s:3:"cid";s:6:"p_desc";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:5;a:5:{s:5:"label";s:6:"时长";s:4:"type";s:8:"duration";s:3:"cid";s:10:"p_duration";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:6;a:5:{s:5:"label";s:6:"尺寸";s:4:"type";s:4:"size";s:3:"cid";s:6:"p_size";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:7;a:5:{s:5:"label";s:6:"类型";s:4:"type";s:3:"ext";s:3:"cid";s:5:"p_ext";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:8;a:5:{s:5:"label";s:6:"形状";s:4:"type";s:5:"shape";s:3:"cid";s:7:"p_shape";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:9;a:5:{s:5:"label";s:6:"评分";s:4:"type";s:5:"grade";s:3:"cid";s:7:"p_grade";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:10;a:5:{s:5:"label";s:12:"添加时间";s:4:"type";s:5:"btime";s:3:"cid";s:7:"p_btime";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:11;a:5:{s:5:"label";s:12:"修改日期";s:4:"type";s:8:"dateline";s:3:"cid";s:10:"p_dateline";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:12;a:5:{s:5:"label";s:12:"创建日期";s:4:"type";s:5:"mtime";s:3:"cid";s:7:"p_mtime";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}}';
        }else{
            $filters = ($filters) ? serialize($filters):'a:13:{i:0;a:7:{s:5:"label";s:6:"分类";s:4:"type";s:8:"classify";s:3:"cid";s:10:"p_classify";s:8:"disabled";s:5:"false";s:8:"showtype";s:1:"0";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:1;a:8:{s:5:"label";s:6:"标签";s:4:"type";s:3:"tag";s:3:"cid";s:5:"p_tag";s:4:"data";a:3:{i:0;s:3:"aaa";i:1;s:3:"bbb";i:2;s:3:"ccc";}s:8:"disabled";s:5:"false";s:8:"showtype";s:1:"1";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:2;a:5:{s:5:"label";s:6:"颜色";s:4:"type";s:5:"color";s:3:"cid";s:7:"p_color";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:3;a:5:{s:5:"label";s:6:"链接";s:4:"type";s:4:"link";s:3:"cid";s:6:"p_link";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:4;a:5:{s:5:"label";s:6:"注释";s:4:"type";s:4:"desc";s:3:"cid";s:6:"p_desc";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:5;a:5:{s:5:"label";s:6:"时长";s:4:"type";s:8:"duration";s:3:"cid";s:10:"p_duration";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:6;a:5:{s:5:"label";s:6:"尺寸";s:4:"type";s:4:"size";s:3:"cid";s:6:"p_size";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:7;a:5:{s:5:"label";s:6:"类型";s:4:"type";s:3:"ext";s:3:"cid";s:5:"p_ext";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:8;a:5:{s:5:"label";s:6:"形状";s:4:"type";s:5:"shape";s:3:"cid";s:7:"p_shape";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:9;a:5:{s:5:"label";s:6:"评分";s:4:"type";s:5:"grade";s:3:"cid";s:7:"p_grade";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:10;a:5:{s:5:"label";s:12:"添加时间";s:4:"type";s:5:"btime";s:3:"cid";s:7:"p_btime";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:11;a:5:{s:5:"label";s:12:"修改日期";s:4:"type";s:8:"dateline";s:3:"cid";s:10:"p_dateline";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}i:12;a:5:{s:5:"label";s:12:"创建日期";s:4:"type";s:5:"mtime";s:3:"cid";s:7:"p_mtime";s:7:"checked";s:4:"true";s:6:"parent";s:4:"true";}}';
        }
        $setarr = [
            'bannername'=>$bannername,
            'ctype'=>$ctype,
            'typefilter'=>$typefilter,
            'appids'=>$appids,
            'filters'=>$filters,
            'themeid'=>$themeid,
            'icon'=>isset($_GET['icon']) ? intval($_GET['icon']):0,
        ];
        C::t('pichome_banner')->update($bid,$setarr);
        exit(json_encode(array('success'=>true,'bid'=>$bid)));
    }else{
        $bannerdata = [];
        $bannerdata = C::t('pichome_banner')->fetch($bid);
        if($bannerdata['icon']) $bannerdata['iconpath'] = getglobal('siteurl').'index.php?mod=io&op=getfileStream&path='.dzzencode('attach::'.$bannerdata['icon']);
        else $bannerdata['iconpath'] = '';
        $bannerdata['filters'] = unserialize($bannerdata['filters']);
        exit(json_encode(array('success'=>true,'bannerdata'=>$bannerdata)));
    }
}elseif($operation == 'setstatus'){
    $bid = isset($_GET['bid']) ? intval($_GET['bid']):0;
    $bannerdata = C::t('pichome_banner')->fetch($bid);
    if(!$bannerdata){
        exit(json_encode(array('success'=>false,'msg'=>'banner is not exixts')));
    }
    $data = [];
    if(submitcheck('submit')){
        $setarr = [
            'isshow'=>intval($_GET['isshow'])
        ];
        C::t('pichome_banner')->update($bid,$setarr);
    }
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'setsort'){//设置排序
    $bids = isset($_GET['bids']) ? $_GET['bids']:[];
    foreach($bids as $k=>$v){
        C::t('pichome_banner')->update($v,['disp'=>$k]);
    }
    exit(json_encode(array('success' => true)));
}elseif($operation == 'delbanner'){
    $bid = isset($_GET['bid']) ? intval($_GET['bid']):0;
    $bannerdata = C::t('pichome_banner')->fetch($bid);
    if(!$bannerdata){
        exit(json_encode(array('success'=>false,'msg'=>'banner is not exixts')));
    }
    if($bannerdata['btype'] === 0 || $bannerdata['settype'] === 0){//单页栏目或者自动设置栏目不允许删除
        exit(json_encode(array('success'=>false,'msg'=>'banner is not allow delete')));
    }
    C::t('pichome_banner')->delete($bid);
    exit(json_encode(array('success'=>true)));
}elseif($operation == 'getbanner'){//获取栏目
	$themedata = getthemedata($themeid);
	$themebanner = $themedata['themebanner'];
	$bannerdata = C::t('pichome_banner')->fetch_by_themeid($themeid,$themebanner);
	exit(json_encode(array('success' => true,'data'=>$bannerdata)));
}else{
    $themedata = getthemedata($themeid);
    $lefsetdata = $themedata['singlepage'];
    $themebanner = $themedata['themebanner'];
    $bannerdata = C::t('pichome_banner')->fetch_by_themeid($themeid,$themebanner);
	//库
	$library = [];
	foreach(DB::fetch_all("select appid,appname,path from %t where isdelete = 0 order by disp ",array('pichome_vapp')) as $v){
		if($v['type'] != 3 || IO::checkfileexists($v['path'],1)){
            unset($v['path']);
			$library[] = $v;
		}
	}

	$library = json_encode($library);
	$bannerdata = json_encode($bannerdata);
    include template('admin/pc/page/site/column');

}
