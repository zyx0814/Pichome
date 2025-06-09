<?php

if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}
//管理权限进入
Hook::listen('adminlogin');
global $_G;
$navtitle=lang('manager_center_banner_manage');
$do = isset($_GET['do']) ? trim($_GET['do']):'';
if (isset($_G['setting'])) $setting = $_G['setting'];
else  $setting = C::t('setting')->fetch_all();
if($do == 'addbanner'){//新建栏目
    if(submitcheck('addbanner')){
        $bannername = isset($_GET['bannername']) ? getstr($_GET['bannername'],30):'';
        $pid = isset($_GET['pid']) ? intval($_GET['pid']):0;
        $isbottom = isset($_GET['isbottom']) ? intval($_GET['isbottom']):0;
        $btype = isset($_GET['btype']) ? intval($_GET['btype']):0;
        $bdata = isset($_GET['bdata']) ? trim($_GET['bdata']):'';
        //同级栏目不允许重名
        if($btype !=5 && DB::result_first("select id from %t where btype = %d  and  bdata = %s and isbottom = %d and bannername = %s",array('pichome_banner',$btype,$bdata,$isbottom,$bannername))){
            exit(json_encode(array('success'=>false,'mgs'=>'banner is exists')));
        }

        $setarr = [
            'bannername'=>$bannername,
            'btype'=>$btype,
            'icon'=>isset($_GET['icon']) ? intval($_GET['icon']):0,
            'isshow'=>isset($_GET['isshow']) ? intval($_GET['isshow']):0,
            'bdata'=>$bdata,
            'disp'=>isset($_GET['disp']) ? intval($_GET['disp']):0,
            'pid'=>isset($_GET['pid']) ? intval($_GET['pid']):0,
            'showchildren'=>isset($_GET['showchildren']) ? intval($_GET['showchildren']):0,
            'isbottom'=>$isbottom,
        ];

        if($setarr['bid'] = C::t('pichome_banner')->insert_data($setarr)){
            $address = $_GET['address'] ? trim($_GET['address']):'';
            if($setarr['btype'] == 3){
                $url = $setarr['bdata'];
            }elseif($setarr['btype'] == 4){
                $url = 'index.php?mod=banner&op=index&id=tb_'.$setarr['bdata'].'#id=tb_'.$setarr['bdata'];
            }else{
                $url = 'index.php?mod=banner&op=index&id='.$setarr['bdata'].'#id='.$setarr['bdata'];
            }

            if($setarr['btype'] != 3 && $setarr['btype'] != 5)$shorturl = C::t('pichome_route')->update_path_by_url($url,$address);
            if($setting['pathinfo'] && $shorturl) $setarr['url']=$shorturl;
            else $setarr['url']=$url;
        }
        exit(json_encode(array('success'=>true,'data'=>$setarr)));
    }
}
elseif($do=='upload'){//上传图片
    include libfile( 'class/uploadhandler' );

    $options = array( 'accept_file_types' => '/\.(gif|jpe?g|png|svg)$/i',

        'upload_dir' => $_G[ 'setting' ][ 'attachdir' ] . 'cache/',

        'upload_url' => $_G[ 'setting' ][ 'attachurl' ] . 'cache/',

        'thumbnail' => array( 'max-width' => 40, 'max-height' => 40 ) );

    $upload_handler = new uploadhandler( $options );
    updatesession();
    exit();
}
elseif($do == 'uploadico'){//上传图标
    $bid = isset($_GET['bid']) ? intval($_GET['bid']):0;
    $aid = isset($_GET['aid']) ? intval($_GET['aid']):0;
    $bannerdata = C::t('pichome_banner')->fetch($bid);
    if(!$bannerdata){
        exit(json_encode(array('success'=>false,'msg'=>'banner is not exixts')));
    }else{
        if(C::t('pichome_banner')->update($bid,['icon'=>$aid])){
            C::t('attachment')->addcopy_by_aid($aid);
            if($bannerdata['icon']){
                C::t('attachment')->delete_by_aid($bannerdata['icon']);
            }
        }
    }
    exit(json_encode(array('success'=>true)));
}
elseif($do == 'editbannerdata'){//编辑栏目基本数据
    $bid = isset($_GET['bid']) ? intval($_GET['bid']):0;
    $data = C::t('pichome_banner')->fetch_bannerbasic_by_bid($bid);
    Hook::listen('lang_parse',$data,['getBannerLangKey']);
    if(!$data) exit(json_encode(['success'=>false,'msg'=>'banner is not exists']));
    if(submitcheck('editbanner')){
        $bannername = isset($_GET['bannername']) ? getstr($_GET['bannername'],30):'';
        $pid = isset($_GET['pid']) ? intval($_GET['pid']):0;
        $isbottom = isset($_GET['isbottom']) ? intval($_GET['isbottom']):0;
        $btype = isset($_GET['btype']) ? intval($_GET['btype']):0;
        $bdata = isset($_GET['bdata']) ? trim($_GET['bdata']):'';
        //同级栏目不允许重名
        if($btype !=5 && DB::result_first("select id from %t where btype = %d and bdata = %s and isbottom = %d and id != %d and bannername =%s",array('pichome_banner',$btype,$bdata,$isbottom,$bid,$bannername))){
            exit(json_encode(array('success'=>false,'mgs'=>'banner is exists')));
        }
        $setarr = [
            'bannername'=>$bannername,
            'btype'=>$btype,
            'icon'=>isset($_GET['icon']) ? intval($_GET['icon']):0,
            'isshow'=>isset($_GET['isshow']) ? intval($_GET['isshow']):0,
            'bdata'=>$bdata,
            'disp'=>isset($_GET['disp']) ? intval($_GET['disp']):0,
            'isbottom'=>isset($_GET['isbottom']) ? intval($_GET['isbottom']):0,
            'showchildren'=>isset($_GET['showchildren']) ? intval($_GET['showchildren']):0,
            'pid'=>$pid,
            'id'=>$bid
        ];
        if($setarr['bid'] = C::t('pichome_banner')->insert_data($setarr)){
            $address = $_GET['address'] ? trim($_GET['address']):'';
            if($setarr['btype'] == 3){
                $url = $setarr['bdata'];
            }elseif($setarr['btype'] == 4){
                $url = 'index.php?mod=banner&op=index&id=tb_'.$setarr['bdata'].'#id=tb_'.$setarr['bdata'];
            }else{
                $url = 'index.php?mod=banner&op=index&id='.$setarr['bdata'].'#id='.$setarr['bdata'];
            }
            if($address == $url) $address = '';
            if($setting['pathinfo'] && $setarr['btype'] != 3){
                $setarr['url'] = C::t('pichome_route')->update_path_by_url($url,$address);
            }else{
                $setarr['url']=$url;
            }
        }
        exit(json_encode(array('success'=>true,'data'=>$setarr)));
    }else{
        if($data['btype'] == 3){
            $url = $data['bdata'];
        }elseif($data['btype'] == 4){
            $url = 'index.php?mod=banner&op=index&id=tb_'.$data['bdata'].'#id=tb_'.$data['bdata'];
        }else{
            $url = 'index.php?mod=banner&op=index&id='.$data['bdata'].'#id='.$data['bdata'];
        }
        if($setting['pathinfo'] && $data['btype'] != 3) $path = C::t('pichome_route')->feth_path_by_url($url);
        else $path = '';
        if($path){
            $data['url'] = $path;
        }else{
            $data['url'] = $url;
        }
        exit(json_encode(['success'=>true,'data'=>$data]));
    }
}
elseif($do=='move'){//移动栏目和排序
    $id = intval($_GET['id']);
    $oid=intval($_GET['oid']);
    $disptype=trim($_GET['type']);
    if(C::t('pichome_banner')->move_to_idandoid($id,$oid,$disptype)){
        exit(json_encode(array('success'=>'true')));
    }
    exit(json_encode(array('success' => 'false')));
}elseif($do == 'bannerlist'){//获取栏目列表
    $bannerlist = C::t('pichome_banner')->getbannerlist();
    exit(json_encode(['success'=>true,'data'=>$bannerlist]));
}elseif($do == 'getalonepage'){//获取单页列表
    $pagelist= [];
    foreach(DB::fetch_all("select * from %t where 1",['pichome_templatepage']) as $v){
        Hook::listen('lang_parse',$v,['getAlonepageLangData']);
        $pagelist[] = array('id'=>$v['id'],'name'=>$v['pagename']);
    }
    exit(json_encode(['success'=>true,'data'=>$pagelist]));
}elseif($do == 'getapporsources'){//获取库列表或智能列表
    //$stype = isset($_GET['stype']) ? intval($_GET['stype']):0;
    $data = [];
    /*if($stype && $stype != 5){
        $params = ['pichome_smartdata'];
        $wheresql = ' 1 ';
        foreach(DB::fetch_all("select * from %t where $wheresql ",$params) as $v){
            $data[] = array('id'=>$v['id'],'name'=>$v['name']);
        }

    }else{*/
        foreach(DB::fetch_all("select * from %t where isdelete < 1",['pichome_vapp']) as $v){
            if ($v['type'] != 3 && !IO::checkfileexists($v['path'],1)) {
                continue;
            }
            Hook::listen('lang_parse',$v,['getVappLangData']);
            $data[] = array('id'=>$v['appid'],'name'=>$v['appname']);
        }
   // }
    exit(json_encode(['success'=>true,'data'=>$data]));
}elseif($do == 'delbanner'){
    $id = isset($_GET['id']) ? intval($_GET['id']):0;
    C::t('pichome_banner')->delete_by_id($id);
    exit(json_encode(['success'=>true]));
}elseif($do == 'setstatus'){//是否显示
    $id = isset($_GET['id']) ? intval($_GET['id']):0;
    $isshow = isset($_GET['isshow']) ? intval($_GET['isshow']):0;
   C::t('pichome_banner')->update($id,['isshow'=>$isshow]);
    C::t('pichome_banner')->clearBannerData();
    exit(json_encode(['success'=>true]));
}elseif($do == 'geturlqrcode'){//获取链接二维码
    $id = isset($_GET['id']) ? intval($_GET['id']):0;
    $bdata = C::t('pichome_banner')->fetch($id);
    if($bdata['btype'] == 3){
        $url = $bdata['bdata'];
        $sid = 'link_'.md5($url);
    }elseif($bdata['btype'] == 4){
        $url = 'index.php?mod=banner&op=index&id=tab_'.$bdata['bdata'].'#id=tb_'.$bdata['bdata'];
        $sid = 'tb_'.$bdata['bdata'];
    }else{
        $url = 'index.php?mod=banner&op=index&id='.$bdata['bdata'].'#id='.$bdata['bdata'];
        $sid = 'b_'.$bdata['bdata'];
    }
    $qrcode = C::t('pichome_route')->getQRcodeBySid($url,$sid);
    exit(json_encode(['success'=>true,'qrcode'=>$qrcode]));
}elseif($do == 'gettabdata'){//获取标签组
    $tabgroupdata = [];
    $tabstatus = 0;
    Hook::listen('checktab', $tabstatus);
    if ($tabstatus) {//获取有tab数据
        Hook::listen('gettabgroupdata', $tabgroupdata);
    }
    $tabgroupdata = array_values($tabgroupdata);
    exit(json_encode(array('success'=>true,'data'=>$tabgroupdata)));
}
exit();