<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    Hook::listen('adminlogin');//检查是否登录，未登录跳转到登录界面
    global $_G;
    $operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
    if($operation == 'fetch'){
        $appid = isset($_GET['appid']) ? trim($_GET['appid']) :'';
        if (submitcheck('settingsubmit')) {
            if (!$appid) exit(json_encode(array('error' => true)));
            $setarr = [
                'filter'=>isset($_GET['filter']) ? serialize($_GET['filter']):'',
                'share'=>isset($_GET['share']) ? intval($_GET['share']):0,
                'download'=>isset($_GET['download']) ? intval($_GET['download']):0,
            ];
            C::t('pichome_vapp')->update($appid,$setarr);
            exit(json_encode(array('success' => true)));
        }else{
            if($data = DB::fetch_first("select * from %t where appid=%s ", array('pichome_vapp', $appid))){
                $data['path'] = urlencode($data['path']);
                $data['filter'] = unserialize($data['filter']);
                $catdata = C::t('pichome_taggroup')->fetch_by_appid($appid);
                exit(json_encode(array('success' => true, 'data' => $data,'catdata'=>$catdata)));
            }else{
                exit(json_encode(array('error' => true)));
            }
        }
    }
    elseif($operation == 'getdata'){
        $data = array();
        foreach(DB::fetch_all("select * from %t",array('pichome_vapp')) as $val){
            $val['path'] =  str_replace('+',' ',urlencode($val['path']));
            foreach( DB::fetch_all("select r.rid,r.type,r.ext,r.name,ra.path,r.hasthumb from %t  r left join %t ra on r.rid=ra.rid where r.appid = %s order by r.btime limit 0,4",
                array('pichome_resources','pichome_resources_attr',$val['appid'])) as $v){
                if ($v['hasthumb']) {
                    $filepath = dirname($v['path']);
                    $filename =  substr($v['path'],strrpos($v['path'],'/')+1);
                    $filename = str_replace(strrchr($filename, "."),"",$filename);
                    $thumbpath =  'library/' . $filepath . '/' . $filename . '_thumbnail.png';
                    $v['icondata'] = str_replace('+',' ',urlencode($thumbpath));
                } else {
                    if ($v['type'] == 'commonimage') {
                        $v['icondata'] =  str_replace('+',' ',urlencode('library/' . $v['path']));
                    } else {
                        $v['icondata'] = geticonfromext($v['ext'], $v['type']);
                    }
                }
                $v['path'] =  str_replace('+',' ',urlencode($v['path']));
                $val['resources'][] = $v;
            }
            $data[] = $val;
        }
        //print_r($data);die;
        exit(json_encode(array('data'=>$data)));
        
    }
    elseif($operation == 'addoaooaapp'){//添加oaooa库
        $did = isset($_GET['did']) ? intval($_GET['did']):0;
        $fid = isset($_GET['fid']) ? intval($_GET['fid']):0;
        $kuid = isset($_GET['appid']) ? intval($_GET['appid']):0;
        $appname = isset($_GET['appname']) ? intval($_GET['appname']):0;
     /*   $did = 2;
        $fid = 2524;
        $kuid = 60;
        $appname = 'testapid';*/
        $appattr = [
            'appname' => $appname,
            'uid' => $_G['uid'],
            'username' => $_G['username'],
            'personal' => 1,
            'dateline' => TIMESTAMP,
            'type' => 2,
            'fid'=>$fid,
            'did'=>$did,
            'kuid'=>$kuid,
        ];
        $appid = C::t('pichome_vapp')->insert($appattr);
        exit(json_encode(array('appid'=>$appid)));
    }
    else{
        $theme = GetThemeColor();
        include template('pc/page/library');
    }
	