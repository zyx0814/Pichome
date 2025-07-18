<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
global $_G;
if (isset($_G['setting'])) $setting = $_G['setting'];
else  $setting = C::t('setting')->fetch_all();
$themeid = isset($_G['setting']['pichometheme']) ? intval($_G['setting']['pichometheme']):1;
$themedata = getthemedata($themeid);
updatesession();
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
if($operation == 'upload'){//上传文件图标类
    include libfile( 'class/uploadhandler' );

    $options = array( 'accept_file_types' => '/\.(gif|jpe?g|png|svg|webp)$/i',

        'upload_dir' => $_G[ 'setting' ][ 'attachdir' ] . 'cache/',

        'upload_url' => $_G[ 'setting' ][ 'attachurl' ] . 'cache/',

        'thumbnail' => array( 'max-width' => 40, 'max-height' => 40 ) );

    $upload_handler = new uploadhandler( $options );
    updatesession();
    exit();
}
elseif ( $operation == 'uploads' ) { //上传新文件(指新建)

    $appid = isset($_GET[ 'container' ]) ? trim( $_GET[ 'container' ] ):'';

    //$folderdata = C::t( 'folder' )->fetch_by_fid( $container );
    $appdata = C::t('pichome_vapp')->fetch($appid);
    //$space = dzzgetspace( $uid );

    //$space[ 'self' ] = intval( $space[ 'self' ] );

    $bz = trim( $_GET[ 'bz' ] );

    require_once dzz_libfile( 'class/UploadHandler' );

    $extlimitstr =  $appdata[ 'allowext' ] ? str_replace('*.','',$appdata['allowext']):'';
    //上传类型
    $allowedExtensions = $extlimitstr ? explode(',',$extlimitstr) : array();
    $notallowdExtensions = $appdata[ 'notallowext' ] ? explode(',',str_replace('*.','',$appdata['notallowext'])):array();
    // $sizeLimit = ( $space[ 'maxattachsize' ] );

    $options = array( 'accept_file_types' => $allowedExtensions ? ( "/(\.|\/)(" . $allowedExtensions . ")$/i" ) : "/.+$/i",
        'accept_file_names' => "/.+$/i",
        'notallow_file_type'=>$notallowdExtensions ? ( "/(\.|\/)(" . $notallowdExtensions . ")$/i" ) : "",
        'max_file_size' => null,//$sizeLimit ? $sizeLimit : null,
        'upload_dir' => $_G[ 'setting' ][ 'attachdir' ] . 'cache/',
        'upload_url' => $_G[ 'setting' ][ 'attachurl' ] . 'cache/',

    );

    $upload_handler = new UploadHandler( $options );
    updatesession();
    exit();

} elseif ( $operation == 'chkmd5' ) {//检查是否已有文件
    $md5 = isset($_GET['md5']) ? trim($_GET['md5']) : '';
    $path = isset($_GET['relativePath']) ? trim($_GET['relativePath']) : '';
    $pfid = isset($_GET['pfid']) ? trim($_GET['pfid']) : '';
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    if (!$appid) {
        $data['error'] = 'uploadfailer';
        $data['msg'] = '缺少appid';
        exit(json_encode(array('success' => true, 'data' => $data)));
    }
    if ($md5) {
        $attachment = C::t('attachment')->fetch_by_md5($md5);
    }

    //如果文件存在，则查询当前库是否有该文件
    if ($attachment) {
        $aid = $attachment['aid'];
        //处理目录数据
        $patharr = explode('/', $path);
        array_pop($patharr);
        $dirname = ($patharr) ? implode('/', $patharr) : '';
        $relativepath = $dirname;

        $folderdata = C::t('pichome_folder')->createfolerbypath($appid, $relativepath, $pfid);
        //如果当前库有该文件
        if ($rid = DB::result_first("select rid from %t where path = %s and appid = %s ", array('pichome_resources_attr', $aid, $appid))) {
            $resourcesdata = C::t('pichome_resources')->fetch($rid);
            if($resourcesdata['isdelete']){
                $rsetarr = [
                    'lastdate' => TIMESTAMP * 1000,
                    'appid' => $appid,
                    'uid'=>$_G['uid'],
                    'username'=>$_G['username'],
                    'apptype' => 3,
                    'size' => $resourcesdata['size'],
                    'type' => $resourcesdata['type'],
                    'ext' => $resourcesdata['ext'],
                    'mtime' => TIMESTAMP * 1000,
                    'dateline' => TIMESTAMP * 1000,
                    'btime' => TIMESTAMP * 1000,
                    'width' => $resourcesdata['width'],
                    'height' => $resourcesdata['height'],
                    'lastdate' => TIMESTAMP,
                    'level' => isset($folderdata['level']) ? $folderdata['level'] : 0,
                    'name' => $resourcesdata['name'],
                    'fids' => $folderdata['fid'] ? $folderdata['fid']:''
                ];

                if ($rsetarr['rid'] = C::t('pichome_resources')->insert_data($rsetarr)) {//插入主表
                    Hook::listen('lang_parse',$rsetarr,['saveResourcesLangData',$rsetarr['rid']]);
                    //获取附属表数据
                    $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                    $attrdata['rid'] = $rsetarr['rid'];
                    $attrdata['appid'] = $appid;
                    $attrdata['searchval'] = $rsetarr['name'];
                    C::t('attachment')->addcopy_by_aid($attrdata['path']);//增加图片使用数
                    C::t('pichome_resources_attr')->insert($attrdata);
                    //目录数据
                    if ($folderdata['fid']) {
                        $frsetarr = ['appid' => $appid, 'rid' => $rsetarr['rid'], 'fid' => $folderdata['fid']];
                        C::t('pichome_folderresources')->insert($frsetarr);
                        C::t('pichome_folder')->add_filenum_by_fid($folderdata['fid'], 1);
                    }
                    //缩略图数据
                    $thumbrecorddata = C::t('thumb_record')->fetch($rid);
                    $thumbrecorddata['rid'] = $rsetarr['rid'];

                    C::t('thumb_record')->insert_data($thumbrecorddata);

                    //颜色数据
                    foreach (DB::fetch_all("select * from %t where rid = %s", array('pichome_palette', $rid)) as $v) {
                        $v['rid'] = $rsetarr['rid'];
                        unset($v['id']);
                        C::t('pichome_palette')->insert($v);
                    }
                    C::t('pichome_vapp')->addcopy_by_appid($appid);
                    $data = C::t('pichome_resources')->fetch_by_rid($rsetarr['rid']);
                    $data['folder'] = C::t('pichome_folder')->fetch_allfolder_by_fid($folderdata['fid']);
                    $data['addnum'] = 1;
                } else {
                    $data['error'] = 'uploadfailer';
                }
                exit(json_encode(array('success' => true, 'data' => $data)));
            }else{
                $nfids = explode(',', $resourcesdata['fids']);
                $iscurrentfolder = 1;
                if ($folderdata['fid'] && !in_array($folderdata['fid'], $nfids)) {
                    $nfids[] = $folderdata['fid'];
                    $iscurrentfolder = 0;
                }

                $icoarr = [
                    'lastdate' => TIMESTAMP * 1000,
                    'appid' => $appid,
                    'uid'=>$_G['uid'],
                    'username'=>$_G['username'],
                    'apptype' => 3,
                    'size' => $resourcesdata['size'],
                    'type' => $resourcesdata['type'],
                    'ext' => $resourcesdata['ext'],
                    'mtime' => TIMESTAMP * 1000,
                    'dateline' => TIMESTAMP * 1000,
                    'btime' => TIMESTAMP * 1000,
                    'width' => $resourcesdata['width'],
                    'height' => $resourcesdata['height'],
                    'lastdate' => TIMESTAMP,
                    'level' => isset($folderdata['level']) ? $folderdata['level'] : 0,
                    'name' => $resourcesdata['name'],
                    //'fids' => $nfids ? implode(',', $nfids) : '',
                    'rid' => $rid
                ];

                if (C::t('pichome_resources')->update($rid, $icoarr)) {//插入主表
                    //目录数据
                    if (!$iscurrentfolder && $folderdata['fid']) {
                        $frsetarr = ['appid' => $appid, 'rid' => $rid, 'fid' => $folderdata['fid']];
                        C::t('pichome_folderresources')->insert($frsetarr);
                        //C::t('pichome_folder')->add_filenum_by_fid($folderdata['fid'], 1);
                    }
                    //C::t('pichome_vapp')->addcopy_by_appid($appid);
                    $data = C::t('pichome_resources')->fetch_by_rid($rid);

                    $data['folder'] = C::t('pichome_folder')->fetch_allfolder_by_fid($folderdata['fid']);
                    $data['addnum'] = ($iscurrentfolder) ? 0:1;
                    $data['onlyfolderadd'] = 1;
                } else {
                    $data['error'] = 'uploadfailer';
                }
                $tmpdata = $data;
                Hook::listen('createafter_addindex', $tmpdata);
                unset($tmpdata);
                exit(json_encode(array('success' => true, 'data' => $data)));
            }

        }
        elseif ($rid = DB::result_first("select rid from %t where path = %s ", array('pichome_resources_attr', $aid))) {//如果当前库没有该文件，但其它库有
            //获取原文件基本数据
            $resourcesdata = C::t('pichome_resources')->fetch($rid);
            $rsetarr = [
                'lastdate' => TIMESTAMP * 1000,
                'appid' => $appid,
                'uid'=>$_G['uid'],
                'username'=>$_G['username'],
                'apptype' => 3,
                'size' => $resourcesdata['size'],
                'type' => $resourcesdata['type'],
                'ext' => $resourcesdata['ext'],
                'mtime' => TIMESTAMP * 1000,
                'dateline' => TIMESTAMP * 1000,
                'btime' => TIMESTAMP * 1000,
                'width' => $resourcesdata['width'],
                'height' => $resourcesdata['height'],
                'lastdate' => TIMESTAMP,
                'level' => isset($folderdata['level']) ? $folderdata['level'] : 0,
                'name' => $resourcesdata['name'],
                'fids' => $folderdata['fid'] ? $folderdata['fid']:''
            ];

            if ($rsetarr['rid'] = C::t('pichome_resources')->insert_data($rsetarr)) {//插入主表
                Hook::listen('lang_parse',$rsetarr,['saveResourcesLangData',$rsetarr['rid']]);
                //获取附属表数据
                $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                $attrdata['rid'] = $rsetarr['rid'];
                $attrdata['appid'] = $appid;
                $attrdata['searchval'] = $rsetarr['name'];
                C::t('attachment')->addcopy_by_aid($attrdata['path']);//增加图片使用数
                C::t('pichome_resources_attr')->insert($attrdata);
                //目录数据
                if ($folderdata['fid']) {
                    $frsetarr = ['appid' => $appid, 'rid' => $rsetarr['rid'], 'fid' => $folderdata['fid']];
                    C::t('pichome_folderresources')->insert($frsetarr);
                    //C::t('pichome_folder')->add_filenum_by_fid($folderdata['fid'], 1);
                }
                //缩略图数据
                $thumbrecorddata = C::t('thumb_record')->fetch($rid);
                $thumbrecorddata['rid'] = $rsetarr['rid'];

                 C::t('thumb_record')->insert_data($thumbrecorddata);

                //颜色数据
                foreach (DB::fetch_all("select * from %t where rid = %s", array('pichome_palette', $rid)) as $v) {
                    $v['rid'] = $rsetarr['rid'];
                    unset($v['id']);
                    C::t('pichome_palette')->insert($v);
                }
                C::t('pichome_vapp')->addcopy_by_appid($appid);
                $data = C::t('pichome_resources')->fetch_by_rid($rsetarr['rid']);
                $data['folder'] = C::t('pichome_folder')->fetch_allfolder_by_fid($folderdata['fid']);
                $data['addnum'] = 1;
                addFileuploadStats($rsetarr['rid'],1);
            } else {
                $data['error'] = 'uploadfailer';
            }
            $tmpdata = $data;
            Hook::listen('createafter_addindex', $tmpdata);
            unset($tmpdata);
            exit(json_encode(array('success' => true, 'data' => $data)));

        }
    }else {
        exit(json_encode(array('success' => false)));
    }
}
elseif ( $operation == 'cloudupload' ) {
    global $_G;
    $data = $_GET;
    $path = $data[ 'bz' ] . ':' . $data[ 'did' ] . ':' .$data[ 'Key' ];
    $data['dirname'] = dirname($data['relativePath']);
    $return = IO::movetmpdataToattachment( $path, $data );
    if ( !isset( $return[ 'error' ] ) ) {
        exit( json_encode( array( 'success'=>true,'data' => $return ) ) );
    } else {
        exit( json_encode( array( 'success' =>false,'msg'=> $return[ 'error' ] ) ) );
    }
}