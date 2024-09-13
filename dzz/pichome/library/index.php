<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
global $_G;

Hook::listen('adminlogin');
if (isset($_G['setting'])) $setting = $_G['setting'];
else  $setting = C::t('setting')->fetch_all();
$navtitle = "库管理";
//当前用户id
$uid = $_G['uid'];
$themeid = isset($_G['setting']['pichometheme']) ? intval($_G['setting']['pichometheme']) : 1;
$themedata = getthemedata($themeid);
$dzzroot = str_replace(BS, '/', DZZ_ROOT);
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
if ($operation == 'fetch') {
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';

    if (submitcheck('settingsubmit')) {
        if ($_G['adminid'] != 1) return array('success' => false, 'msg' => lang('no_perm'));
        if (!$appid) exit(json_encode(array('success' => false, 'msg' => 'appid is must')));
        $view = $_GET['visit'];
        $download = $_GET['download'];
        $share = $_GET['share'];

        $manage = isset($_GET['manage']['uids']) ? $_GET['manage']['uids'] : array();

        //处理用户
        if ($manage) {
            $omanage = C::t('pichome_vappmember')->fetch_member_by_appid($appid);
            $deluids = array_diff($omanage, $manage);
            $adduids = array_diff($manage, $omanage);

            if ($adduids) C::t('pichome_vappmember')->addmember($appid, $adduids);
            if ($deluids) C::t('pichome_vappmember')->delete_member_by_appid_uid($appid, $deluids);
        }
        $fileds = [];
		$haslevel=0;
        foreach ($_GET['fileds'] as $k => $v) {
			if (!defined('PICHOME_LIENCE') && $v['flag'] == 'level') continue;
            $fileds[$k] = [
                'flag' => getstr($v['flag']),
                'type' => getstr($v['type']),
                'name' => getstr($v['name']),
                'enable' => intval($v['enable']),
                'checked' => intval($v['checked']),
            ];
        }
		$haslevel2=0;
        foreach ($_GET['screen'] as $k => $v) {
			
			if (!defined('PICHOME_LIENCE') && $v['key'] == 'level') {
				unset($_GET['screen'][$k]);
			}
			
        }
        $fileds = array_values($fileds);
        $setarr = [
            'appname' => isset($_GET['appname']) ? trim($_GET['appname']) : '',
            'share' => serialize($share),
            'download' => serialize($download),
            'view' => serialize($view),
            'getinfo' => isset($_GET['getinfo']) ? intval($_GET['getinfo']) : 0,
            'allowext' => isset($_GET['allowext']) ? trim($_GET['allowext']) : '',
            'notallowext' => isset($_GET['notallowext']) ? trim($_GET['notallowext']) : '',
            'screen' => isset($_GET['screen']) ? serialize($_GET['screen']) : '',
            'pagesetting' => isset($_GET['pagesetting']) ? serialize($_GET['pagesetting']) : '',
            'cron' => isset($_GET['cron']) ? intval($_GET['cron']) : '',
            'crontype' => isset($_GET['crontype']) ? intval($_GET['crontype']) : 0,
            'crontime' => $_GET['crontime'] ? trim( $_GET['crontime']):'',
            'fileds' => serialize($fileds),
        ];

        $address = trim($_GET['address']);
        $url = 'index.php?mod=pichome&op=fileview#appid=' . $appid;

        if ($setting['pathinfo'] && $address != $url) {
            C::t('pichome_route')->update_path_by_url($url, $address);
        }

        C::t('pichome_vapp')->updateByAppid($appid, $setarr);

        exit(json_encode(array('success' => true)));
    } else {
        require_once(DZZ_ROOT . './dzz/class/class_encode.php');
        if ($data = C::t('pichome_vapp')->fetchByAppid($appid)) {

            //用户权限部分开始
            $data['view'] = unserialize($data['view']);
            $data['download'] = unserialize($data['download']);
            $data['share'] = unserialize($data['share']);
            //访问权限用户
            $vorgids = [];
            if (isset($data['view']['uids'])) {
                $vuidarr = $data['view']['uids'];
                //获取所有用户名
                $usernamearr = [];
                foreach (DB::fetch_all("select uid,username from %t where uid in(%n)", array('user', $vuidarr)) as $v) {
                    $usernamearr[] = ['uid' => $v['uid'], 'text' => $v['username']];
                }
                $data['view']['uids'] = $usernamearr;
                //获取所有的机构部门id
                $vorgids = [];
                $hasorgiduids = [];
                foreach (DB::fetch_all("select orgid,uid from %t where uid in(%n)", array('organization_user', $vuidarr)) as $v) {
                    $vorgids[] = $v['orgid'];
                    $hasorgiduids[] = $v['uid'];
                }
                $vorgids = array_unique($vorgids);
                $hasorgiduids = array_unique($hasorgiduids);
                $vothers = array_diff($vuidarr, $hasorgiduids);
                if ($vothers) {
                    $vorgids[] = 'other';
                }

            }

            if (isset($data['view']['groups'])) {

                $viewgroups = $data['view']['groups'];
                $vorgarr = [];
                $data['view']['groups'] = $vorgarr;
                if (in_array('other', $viewgroups)) {
                    $otherindex = array_search('other', $viewgroups);
                    unset($viewgroups[$otherindex]);
                    $vorgarr[] = ['orgid' => 'other', 'text' => '无机构用户'];
                }

                foreach (DB::fetch_all("select orgname,orgid from %t where orgid in(%n)", ['organization', $viewgroups]) as $v) {
                    $vorgarr[] = ['orgid' => $v['orgid'], 'text' => $v['orgname']];
                }
                $data['view']['groups'] = $vorgarr;
                $vorgids = array_merge($vorgids, $viewgroups);
            }
            if ($vorgids) {
                $vvorgids = $vorgids;
                if (in_array('other', $vorgids)) {
                    $otherindex = array_search('other', $vorgids);
                    unset($vorgids[$otherindex]);
                }
                $tmporgids = [];
                foreach (DB::fetch_all("select pathkey from %t where orgid in(%n)", array('organization', $vorgids)) as $vo) {
                    $torgids = explode('_', str_replace('-', '', $vo['pathkey']));
                    $tmporgids = array_merge($tmporgids, $torgids);
                }
                $tmporgids = array_merge($tmporgids, $vvorgids);
                $tmporgids = array_unique(array_filter($tmporgids));
                $data['view']['expanded'] = $tmporgids;
            }
            //下载权限用户
            $dorgids = [];
            if (isset($data['download']['uids'])) {
                $duidarr = $data['download']['uids'];
                //获取所有用户名
                $usernamearr = [];
                foreach (DB::fetch_all("select uid,username from %t where uid in(%n)", array('user', $duidarr)) as $v) {
                    $usernamearr[] = ['uid' => $v['uid'], 'text' => $v['username']];
                }
                $data['download']['uids'] = $usernamearr;
                $hasorgiduids = [];
                foreach (DB::fetch_all("select orgid,uid from %t where uid in(%n)", array('organization_user', $duidarr)) as $ov) {
                    $dorgids[] = $ov['orgid'];
                    $hasorgiduids[] = $ov['uid'];
                }
                $dorgids = array_unique($dorgids);
                $hasorgiduids = array_unique($hasorgiduids);
                $dothers = array_diff($duidarr, $hasorgiduids);
                if ($dothers) {
                    $dorgids[] = 'other';
                }

            }

            if (isset($data['download']['groups'])) {
                $dgroups = $data['download']['groups'];
                $groupdatas = [];
                if (in_array('other', $dgroups)) {
                    $otherindex = array_search('other', $dgroups);
                    unset($dgroups[$otherindex]);
                    $groupdatas[] = ['orgid' => 'other', 'text' => '无机构用户'];
                }

                foreach (DB::fetch_all("select orgname,orgid from %t where orgid in(%n)", ['organization', $dgroups]) as $v) {
                    $groupdatas[] = ['orgid' => $v['orgid'], 'text' => $v['orgname']];
                }
                $data['download']['groups'] = $groupdatas;
                $dorgids = array_merge($dorgids, $dgroups);
            }

            if ($dorgids) {
                $ddorgids = $dorgids;
                if (in_array('other', $dorgids)) {
                    $otherindex = array_search('other', $dorgids);
                    unset($dorgids[$otherindex]);
                }
                $tmporgids = [];
                foreach (DB::fetch_all("select pathkey from %t where orgid in(%n)", array('organization', $dorgids)) as $vo) {
                    $torgids = explode('_', str_replace('-', '', $vo['pathkey']));
                    $tmporgids = array_merge($tmporgids, $torgids);
                }
                $tmporgids = array_merge($tmporgids, $ddorgids);
                $tmporgids = array_unique(array_filter($tmporgids));
                $data['download']['expanded'] = $tmporgids;
            }
            //分享权限用户
            $sorgids = [];
            if (isset($data['share']['uids'])) {
                $suidarr = $data['share']['uids'];
                //获取所有用户名
                $usernamearr = [];
                foreach (DB::fetch_all("select uid,username from %t where uid in(%n)", array('user', $suidarr)) as $v) {
                    $usernamearr[] = ['uid' => $v['uid'], 'text' => $v['username']];
                }
                $data['share']['uids'] = $usernamearr;
                $hasorgiduids = [];
                foreach (DB::fetch_all("select orgid,uid from %t where uid in(%n)", array('organization_user', $suidarr)) as $ov) {
                    $sorgids[] = $ov['orgid'];
                    $hasorgiduids[] = $ov['uid'];
                }
                $sorgids = array_unique($sorgids);
                $hasorgiduids = array_unique($hasorgiduids);
                $sothers = array_diff($suidarr, $hasorgiduids);
                if ($sothers) {
                    $sorgids[] = 'other';
                }
            }
            if (isset($data['share']['groups'])) {
                $sgroups = $data['share']['groups'];
                $groupdatas = [];
                if (in_array('other', $dgroups)) {
                    $otherindex = array_search('other', $sgroups);
                    unset($sgroups[$otherindex]);
                    $groupdatas[] = ['orgid' => 'other', 'text' => '无机构用户'];
                }

                foreach (DB::fetch_all("select orgname,orgid from %t where orgid in(%n)", ['organization', $sgroups]) as $v) {
                    $groupdatas[] = ['orgid' => $v['orgid'], 'text' => $v['orgname']];
                }
                $data['share']['groups'] = $groupdatas;
                $sorgids = array_merge($sorgids, $sgroups);
            }
            if ($sorgids) {
                $ssorgids = $sorgids;
                if (in_array('other', $sorgids)) {
                    $otherindex = array_search('other', $sorgids);
                    unset($sorgids[$otherindex]);
                }
                $tmporgids = [];
                foreach (DB::fetch_all("select pathkey from %t where orgid in(%n)", array('organization', $sorgids)) as $vo) {
                    $torgids = explode('_', str_replace('-', '', $vo['pathkey']));
                    $tmporgids = array_merge($tmporgids, $torgids);
                }
                $tmporgids = array_merge($tmporgids, $ssorgids);
                $tmporgids = array_unique(array_filter($tmporgids));
                $data['share']['expanded'] = $tmporgids;
            }
            $manage = C::t('pichome_vappmember')->fetch_member_by_appid($appid);
            if ($manage) {
                //获取所有用户名
                $usernamearr = [];
                foreach (DB::fetch_all("select uid,username from %t where uid in(%n)", array('user', $manage)) as $v) {
                    $usernamearr[] = ['uid' => $v['uid'], 'text' => $v['username']];
                }
                $data['manage']['uids'] = $usernamearr;
                $hasorgiduids = [];
                foreach (DB::fetch_all("select orgid,uid from %t where uid in(%n)", array('organization_user', $manage)) as $ov) {
                    $morgids[] = $ov['orgid'];
                    $hasorgiduids[] = $ov['uid'];
                }
                $morgids = array_unique($morgids);
                $hasorgiduids = array_unique($hasorgiduids);
                $mothers = array_diff($manage, $hasorgiduids);
                if ($mothers) {
                    $morgids[] = 'other';
                }
            }
            if ($morgids) {
                $mmorgids = $morgids;
                if (in_array('other', $mmorgids)) {
                    $otherindex = array_search('other', $mmorgids);
                    unset($mmorgids[$otherindex]);
                }
                $tmporgids = [];
                foreach (DB::fetch_all("select pathkey from %t where orgid in(%n)", array('organization', $mmorgids)) as $vo) {
                    $torgids = explode('_', str_replace('-', '', $vo['pathkey']));
                    $tmporgids = array_merge($tmporgids, $torgids);
                }
                $tmporgids = array_merge($tmporgids, $morgids);
                $tmporgids = array_unique(array_filter($tmporgids));
                $data['manage']['expanded'] = $tmporgids;
            }
            //用户权限部分结束

            //库路径部分开始
            $arr = explode(':', $data['path']);
            if ($arr[1] && is_numeric($arr[1])) {
                $pathpre = DB::result_first("select cloudname from %t where id = %d", array('connect_storage', $arr[1]));
                $arr1 = explode('/', $arr[2]);
                unset($arr1[0]);
                $object = implode('/', $arr1);
                $data['convertpath'] = $pathpre . '/' . $object;
            } else {
                $p = new Encode_Core();
                $charset = $p->get_encoding($data['path']);
                $data['convertpath'] = ($data['charset'] != CHARSET) ? diconv($data['path'], $charset, CHARSET) : $data['path'];
            }
            $data['path'] = urlencode($data['path']);
            $data['convertpath'] = str_replace('dzz::', '', $data['convertpath']);
            //库路径部分结束


            //如果没有设置库筛选项，使用系统默认筛选项作为库筛选项
            $data['filter'] = [
                [
                    'key' => 'classify',
                    'label' => '分类',
                    'checked' => 1
                ],
                [
                    'key' => 'tag',
                    'label' => '标签',
                    'checked' => 1
                ],
                [
                    'key' => 'color',
                    'label' => '颜色',
                    'checked' => 1
                ],
                [
                    'key' => 'link',
                    'label' => '链接',
                    'checked' => 1
                ],
                [
                    'key' => 'desc',
                    'label' => '注释',
                    'checked' => 1
                ],
                [
                    'key' => 'duration',
                    'label' => '时长',
                    'checked' => 1
                ],
                [
                    'key' => 'size',
                    'label' => '尺寸',
                    'checked' => 1
                ],
                [
                    'key' => 'ext',
                    'label' => '类型',
                    'checked' => 1
                ],
                [
                    'key' => 'shape',
                    'label' => '形状',
                    'checked' => 1
                ],
                [
                    'key' => 'grade',
                    'label' => '评分',
                    'checked' => 1
                ],
                [
                    'key' => 'btime',
                    'label' => '添加时间',
                    'checked' => 1
                ],
                [
                    'key' => 'dateline',
                    'label' => '修改日期',
                    'checked' => 1
                ],
                [
                    'key' => 'mtime',
                    'label' => '创建日期',
                    'checked' => 1
                ]

            ];
            if(defined('PICHOME_LIENCE')){
                $data['filter'][] = [
                    'key' => 'level',
                    'label' => '密级',
                    'checked' => 1
                ];
            }
            if (($data['state'] == 2)) {
                $processname = 'DZZ_PAGEEXPORTFILE_LOCK_' . $appid;
                if (!dzz_process::islocked($processname, 60 * 5)) {
                    dfsockopen(getglobal('localurl') . 'misc.php?mod=exportfile&appid=' . $appid, 0, '', '', false, '', 1);
                }


            } elseif ($data['state'] == 3) {
                $processname = 'DZZ_PAGEEXPORTCHECKFILE_LOCK_' . $appid;
                if (!dzz_process::islocked($processname, 60 * 5)) {
                    dfsockopen(getglobal('localurl') . 'misc.php?mod=exportfilecheck&appid=' . $appid, 0, '', '', false, '', 1);
                }

            }

            //短链接部分开始
            $url = 'index.php?mod=pichome&op=fileview#appid=' . $appid;
            if ($setting['pathinfo']) $path = C::t('pichome_route')->feth_path_by_url($url);
            else $path = '';
            //echo $path;die;
            if ($path) {
                $data['url'] = $path;
            } else {
                $data['url'] = $url;
            }
            //短链接部分结束
            //默认标注设置
            $defaultfileds =  [
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
            if($data['type'] != 1 && $data['type'] != 3){
                $defaultfileds['fileds'][] = [
                    'flag' => 'preview',
                    'type' => 'multiupload',
                    'name' => '多预览图',
                    'checked' => 0,
                    'enable' => 1
                ];
            }
            //标注设置开始
            $data['fileds'] = $data['fileds'] ? unserialize($data['fileds']) :$defaultfileds;

            //获取tab部分
            $tabstatus = 0;
            Hook::listen('checktab', $tabstatus);
            if ($tabstatus) {//获取有tab数据
                $tabgroupdata = [];
                Hook::listen('gettabgroupdata', $tabgroupdata);
                // var_dump($tabgroupdata);die;
                $tabdata = $tabflag = [];
                foreach ($tabgroupdata as $v) {
                    $tabdata['tabgroup_' . $v['gid']] = ['flag' => 'tabgroup_' . $v['gid'], 'type' => 'tabgroup', 'name' => $v['name'], 'checked' => 0, 'enable' => $v['available']];
                    $tabflag[] = 'tabgroup_' . $v['gid'];
                }
                foreach ($data['fileds'] as $k => $v) {
                    if ($v['type'] == 'tabgroup') {
                        if (in_array($v['flag'], $tabflag)) {
                            $data['fileds'][$k] = ['flag' => $v['flag'], 'type' => 'tabgroup', 'name' => $tabdata[$v['flag']]['name'], 'checked' => $v['checked'], 'enable' => $tabdata[$v['flag']]['enable']];
                            unset($tabdata[$v['flag']]);
                        } else {
                            unset($data['fileds'][$k]);
                        }
                    }


                }
                // print_r($data['fileds']);die;
                foreach ($tabdata as $v) {
                    $data['fileds'][] = ['flag' => $v['flag'], 'type' => 'tabgroup', 'name' => $v['name'], 'checked' => 0, 'enable' => $v['enable']];
                }
            }

            if($data['type'] == 1 || $data['type'] == 3){
                $filedFlag = array_column($data['fileds'],'flag');
                if(!in_array('preview',$filedFlag)){
                    $data['fileds'][] = [
                        'flag' => 'preview',
                        'type' => 'multiupload',
                        'name' => '多预览图',
                        'checked' => 0,
                        'enable' => 1
                    ];
                }
            }
            $data['fileds'] = array_values($data['fileds']);
            //标注设置结束
            $data['screen'] = unserialize($data['screen']);
            $data['pagesetting'] = unserialize($data['pagesetting']);
            $data['appid'] = $appid;
            exit(json_encode(array('success' => true, 'data' => $data)));
        } else {
            exit(json_encode(array('error' => true)));
        }
    }
} elseif ($operation == 'setfolderperm') {//设置目录默认权限
    if ($_G['adminid'] != 1) return array('success' => false, 'msg' => lang('no_perm'));
    if (submitcheck('settingsubmit')) {
        $fid = isset($_GET['fid']) ? trim($_GET['fid']) : '';
        $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
        $perm = isset($_GET['perm']) ? intval($_GET['perm']) : 0;
        $hassub = isset($_GET['hassub']) ? intval($_GET['hassub']) : 0;
        $forceset = isset($_GET['forceset']) ? intval($_GET['forceset']) : 0;
        $appdata = C::t('pichome_vapp')->fetchByAppid($appid);
        if (!$appdata || $appdata['isdelete']) exit(json_encode(array('error' => '未找到目标所在库或库已被删除')));
        if ($appdata['state'] > 0 && $appdata['state'] < 4) exit(json_encode(array('error' => '库正在导入，当前状态下不可操作')));
        if (!$fid) {
            C::t('pichome_vapp')->update($appid, ['perm' => $perm]);
            if ($hassub) {
                C::t('pichome_folder')->update_perm_by_fid_appid($appid, $perm, '', $hassub, $forceset);
            }
        } else {
            C::t('pichome_folder')->update_perm_by_fid_appid($appid, $perm, $fid, $hassub, $forceset);
        }
        //清除目录和文件的权限数据缓存
        if (memory('check')) {
            dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=clearcache&appid=' . $appid . '&hassub=' . $hassub . '&fid=' . $fid . '&forceset=' . $forceset,
                0, '', '', false, '', 1);
        }
        exit(json_encode(array('success' => true)));
    }

} elseif ($operation == 'getdata') {
    $data = array();
    require_once(DZZ_ROOT . './dzz/class/class_encode.php');
    if ($_G['adminid'] == 1) {
        $vappdatas = DB::fetch_all("select * from %t  where isdelete = 0 order by `disp` asc", array('pichome_vapp'));
    } else {
        $vappdatas = DB::fetch_all("select v.* from %t vm left join %t v on v.appid = vm.appid where vm.uid = %d and v.isdelete = 0 order by v.disp",
            array('pichome_vappmember', 'pichome_vapp', $uid));
    }


    foreach ($vappdatas as $val) {
        if ($val['type'] == 3) {
            $val['connect'] = 0;
        } else {
            $val['connect'] = IO::checkfileexists($val['path'], 1) ? 1 : 0;
        }
        $arr = explode(':', $val['path']);
        if ($arr[1] && is_numeric($arr[1])) {
            $pathpre = DB::result_first("select cloudname from %t where id = %d", array('connect_storage', $arr[1]));
            $arr1 = explode('/', $arr[2]);
            unset($arr1[0]);
            $object = implode('/', $arr1);
            $val['path'] = $pathpre . '/' . $object;
        } else {
            $p = new Encode_Core();
            $charset = $p->get_encoding($val['path']);
            if ($val['charset'] != CHARSET) {
                $val['path'] = diconv($val['path'], $charset, CHARSET);
            }
        }
        $val['path'] = str_replace('dzz::', '', $val['path']);
        $url = 'index.php?mod=pichome&op=fileview#appid=' . $val['appid'];
        if ($setting['pathinfo']) $path = C::t('pichome_route')->feth_path_by_url($url);
        else $path = '';
        if ($path) {
            $val['url'] = $path;
        } else {
            $val['url'] = $url;
        }

        $data[] = $val;
    }
    Hook::listen("vappdataFilter",$data,true);
    exit(json_encode(array('data' => $data)));

} elseif ($operation == 'getvappico') {//获取库图片
    if ($_G['adminid'] == 1) {
        $vappdatas = DB::fetch_all("select * from %t  where isdelete = 0 order by disp", array('pichome_vapp'));
    } else {
        $vappdatas = DB::fetch_all("select v.* from %t vm left join %t v on v.appid = vm.appid where vm.uid = %d and v.isdelete = 0 order by v.disp",
            array('pichome_vappmember', 'pichome_vapp', $uid));
    }

    $arr = [];
    foreach ($vappdatas as $val) {
        //获取最新图片
        $resourcesdata = DB::fetch_first("select r.*,ra.path from %t r left join %t ra on r.rid = ra.rid 
where r.isdelete = 0 and r.appid = %s order by r.dateline desc ", ['pichome_resources', 'pichome_resources_attr', $val['appid']]);
        $resourcesdata['isFilelistThumb'] = 1;
        $icondata = C::t('pichome_resources')->getfileimageurl($resourcesdata, $val['path'], $val['type'], 1);
        $arr[] = array('icon' => (!$icondata['icondata'] && $icondata['iconimg']) ? $icondata['iconimg'] :$icondata['icondata'], 'appid' => $val['appid']);
    }
    exit(json_encode(array('data' => $arr)));
} elseif ($operation == 'getinfonum') {//已获取文件信息个数
    $returndata = [];
    foreach (DB::fetch_all("select count(r.rid) as thumbnum,v.appid from %t r left join %t v on r.appid = v.appid where v.isdelete = 0  and v.`type` = 1  and r.hasthumb = 1 group by v.appid", array('pichome_resources', 'pichome_vapp')) as $v) {
        $returndata[$v['appid']] = $v['thumbnum'];
    }
    exit(json_encode(array('data' => $returndata)));
} elseif ($operation == 'getexportstatus') {
    $appids = isset($_GET['appids']) ? trim($_GET['appids']) : '';
    $appidarr = ($appids) ? explode(',', $appids) : [''];
    $returndata = [];
    foreach (DB::fetch_all("select appid,percent,state,filenum from %t where isdelete = 0 and appid in(%n) ", array('pichome_vapp', $appidarr)) as $v) {
        $returndata[$v['appid']] = $v;
    }
    exit(json_encode(array('data' => $returndata)));
} elseif ($operation == 'addlibrary') {
    if ($_G['adminid'] != 1) exit(json_encode(array('success' => false, 'msg' => lang('no_perm'))));
    //接收路径
    $path = isset($_GET['path']) ? trim($_GET['path']) : '';

    //接收编码
    $charset = isset($_GET['charset']) ? trim($_GET['charset']) : 'utf8';
    //转换路径
    $path = str_replace('/', BS, $path);
    $type = isset($_GET['type']) ? intval($_GET['type']) : 0;
    $gettype = isset($_GET['gettype']) ? trim($_GET['gettype']) : '';
    if ($type == 3) {
        $appname = isset($_GET['appname']) ? trim($_GET['appname']) : '';
        if (!$appname) {
            exit(json_encode(array('error' => '库名称必须')));
        }
    } else {
        //获取库名称
        $appname = getbasename($path);

        //存在相同路径的不允许重复添加
        if (DB::result_first("select appid from %t where path = %s and isdelete = 0", array('pichome_vapp', $path))) {
            exit(json_encode(array('error' => '库已存在，不允许重复添加')));
        }

        $iscloud = false;
        if ($gettype && $gettype !== 1 && $gettype !== '1') {
            $cloudid = str_replace('cloud:', '', $gettype);
            if ($cloudid < 2) {
                if (CHARSET != $charset) $path = diconv($path, CHARSET, $charset);
                $path = $path;
            } else {

                $connectdata = DB::fetch_first("select cloudname,id,bz,bucket from %t where id = %d", array('connect_storage', $cloudid));
                //去掉路径中的存储名称部分
                $path = str_replace(array($connectdata['cloudname'] . '/', $connectdata['cloudname'] . BS), '', $path);

                //更换路径中的分割符为/
                $rpath = str_replace(BS, '/', $path);

                //得到请求路径
                $path = $connectdata['bz'] . ':' . $cloudid . ':' . $rpath;
                $iscloud = true;
            }


        } else {
            //转换编码，防止路径找不到（linux下中文乱码，前端展示为正常编码，依据前端传递编码转换出原路径存储）
            if (CHARSET != $charset) $path = diconv($path, CHARSET, $charset);
            $path = $path;
        }
        $force = isset($_GET['force']) ? intval($_GET['force']) : 0;
        if ($type == 0) {
            $iseagel = false;
            $metajsonfile = ($iscloud) ? $path . '/metadata.json' : $path . BS . 'metadata.json';
            $iseagel = IO::checkfileexists($metajsonfile);
            $iseagel ? '' : exit(json_encode(array('error' => '系统检测该库不符合eagle库标准，不能作为eagle库添加')));
            $appname = str_replace('.library', '', $appname);
        }
        if ($type == 2) {
            $isbillfish = false;
            $dbfile = ($iscloud) ? $path . '/.bf/billfish.db' : $path . BS . '.bf' . BS . 'billfish.db';
            $isbillfish = IO::checkfileexists($dbfile);
            $isbillfish ? '' : exit(json_encode(array('tips' => '系统检测该库不符合billfish库标准，不能作为billfish库添加')));

        }

        if ($type == 1 && !$force) {
            $iseagel = false;
            $metajsonfile = ($iscloud) ? $path . '/metadata.json' : $path . BS . 'metadata.json';
            $iseagel = IO::checkfileexists($metajsonfile);
            $iseagel ? exit(json_encode(array('tips' => '系统检测该目录可能为eagle库，不能作为普通目录导入'))) : '';
        }

        if ($type == 1 && !$force) {
            $dbfile = ($iscloud) ? $path . '/.bf/billfish.db' : $path . BS . '.bf' . BS . 'billfish.db';
            $isbillfish = false;
            $isbillfish = IO::checkfileexists($dbfile);
            $isbillfish ? exit(json_encode(array('tips' => '系统检测该目录可能为billfish库，不能作为普通目录导入'))) : '';
        }
    }
    $fileds = [
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
    if($type == 1 || $type == 3){
        $fileds[] = [
            'flag'=>'preview',
            'type' => 'multiupload',
            'name' => '多预览图',
            'enable' => 1,
            'checked' => 1
        ];
    }
    if (defined('PICHOME_LIENCE')) {
        $fileds[] = [
            'flag' => 'level',
            'type' => 'grade',
            'name' => '密级',
            'enable' => 1,
            'checked' => 1
        ];
    }
    $screen = [
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
    if(defined('PICHOME_LIENCE')){
        $screen[] = [
            'key' => 'level',
            'label' => '密级',
        ];
    }
    $fileds = array_values($fileds);
    $appattr = [
        'appname' => $appname,
        'uid' => $_G['uid'],
        'username' => $_G['username'],
        'personal' => 1,
        'dateline' => TIMESTAMP,
        'type' => $type,
        'path' => $path,
        'charset' => $charset,
        'notallowext' => ($type == 3) ? '*:*':getglobal('setting/pichomeimportnotallowext'),
        'allowext' => ($type == 3) ? '':getglobal('setting/pichomeimportallowext'),
        'screen' => serialize($screen),
        'pagesetting' => 'a:7:{s:6:"layout";s:9:"waterFall";s:5:"other";s:5:"btime";s:4:"sort";s:5:"btime";s:4:"desc";s:4:"desc";s:8:"opentype";s:3:"new";s:5:"aside";s:1:"0";s:11:"filterstyle";s:1:"0";}',
        'fileds' => serialize($fileds)
    ];
    //if ($type == 1) $appattr['allowext'] = $Defaultallowext;
    if (!$iscloud) $path = str_replace(array('/', './', '\\'), BS, $path);
    if (strpos($path, DZZ_ROOT) !== 0) $appattr['iswebsitefile'] = 0;
    $appid = C::t('pichome_vapp')->insert_data($appattr);
    if ($appid) {
        $appattr['appid'] = $appid;
        $appattr['path'] = $_GET['path'];
        //新建库创建短链接
        $url = 'index.php?mod=pichome&op=fileview#appid=' . $appid;
        $appattr['url'] = C::t('pichome_route')->update_path_by_url($url);


        exit(json_encode(array('data' => $appattr)));
    } else {
        exit(json_encode(array('error' => 'create failer')));
    }

} elseif ($operation == 'changePath') {
    if ($_G['adminid'] != 1) exit(json_encode(array('success' => false, 'msg' => lang('no_perm'))));
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $path = isset($_GET['path']) ? trim($_GET['path']) : '';
    //接收编码
    $charset = isset($_GET['charset']) ? trim($_GET['charset']) : 'utf8';
    //转换路径
    $path = str_replace('/', BS, $path);
    //转换编码，防止路径找不到（linux下中文乱码，前端展示为正常编码，依据前端传递编码转换出原路径存储）
    if (CHARSET != $charset) $path = diconv($path, CHARSET, $charset);
    //存在相同路径的不允许重复添加
    if (DB::result_first("select appid from %t where path = %s and isdelete = 0", array('pichome_vapp', $path))) {
        exit(json_encode(array('tips' => '路径对应库已存在，不允许修改')));
    } else {
        $appdata = C::t('pichome_vapp')->fetch($appid);
    }
    if (!$appdata) exit(json_encode(array('tips' => '库不存在或已被删除，不允许修改')));
    $type = $appdata['type'];
    if ($type == 0) {
        $metajsonfile = $path . BS . 'metadata.json';
        if (!is_file($metajsonfile)) {
            exit(json_encode(array('error' => '系统检测该库不已符合eagle库标准，修改失败')));
        }
    }
    if ($type == 2) {
        $dbfile = $path . BS . '.bf' . BS . 'billfish.db';
        if (!is_file($dbfile)) {
            exit(json_encode(array('tips' => '系统检测该库已不符合billfish库标准，修改失败')));
        }
    }
    if (!IO::checkfileexists($path, 1)) exit(json_encode(array('tips' => '系统检测该目录不存在，请检查后重试')));
    if (strpos($path, DZZ_ROOT) !== 0) $iswebsitefile = 0;
    else $iswebsitefile = 1;
    if (C::t('pichome_vapp')->update($appid, array('path' => $path, 'iswebsitefile' => $iswebsitefile))) {
        //dfsockopen(getglobal('localurl') . 'index.php?mod=pichome&op=initexport&appid='.$appid, 0, '', '', false, '', 0.1);
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => true)));
    }
} elseif ($operation == 'dellibrary') {
    if ($_G['adminid'] != 1) exit(json_encode(array('success' => false, 'msg' => lang('no_perm'))));
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $appdata = C::t('pichome_vapp')->fetch($appid);
    if (C::t('pichome_vapp')->update($appid, array('isdelete' => 1, 'deluid' => getglobal('uid'), 'delusername' => getglobal('username')))) {
        //if (C::t('pichome_vapp')->update($appid, array('isdelete' => 1))) {
        //库为删除状态时即删除对应系统栏目
        $hookdata = ['appid' => $appid];
        Hook::listen('pichomevappdelete', $hookdata);
        if ($appdata['type'] == 1) {
            $readtxt = DZZ_ROOT . './data/attachment/cache/' . 'loaclexport' . md5($appdata['path']) . '.txt';
        } elseif ($appdata['type'] == 0) {
            $readtxt = DZZ_ROOT . './data/attachment/cache/' . 'eagleexport' . md5($appdata['path']) . '.txt';
        }
        $processname = 'PICHOMEVAPPISDEL_' . $appid;
        dzz_process::addlock($processname, 60 * 30);
        if (is_file($readtxt)) {
            @unlink($readtxt);
        }
        dfsockopen(getglobal('localurl') . 'misc.php?mod=deletevapp', 0, '', '', false, '', 0.1);
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => true)));
    }
} elseif ($operation == 'getpath') {
    require_once(DZZ_ROOT . './dzz/class/class_encode.php');
    $path = isset($_GET['path']) ? trim($_GET['path']) : '';
    $gettype = isset($_GET['gettype']) ? trim($_GET['gettype']) : 0;
    $charset = isset($_GET['charset']) ? trim($_GET['charset']) : CHARSET;
    $path = str_replace('/', BS, $path);
    if (CHARSET != $charset) $path = diconv($path, CHARSET, $charset);
    if (!$gettype && !$path) {
        $path = DZZ_ROOT . 'library';
    }
    if (!empty($Defaultnotallowdir)) {
        $notallowdir = getglobal('setting/pichomeimportnotdir') ? getglobal('setting/pichomeimportnotdir') : implode(',', $Defaultnotallowdir);
        $notallowdir = str_replace(array('.', ',', '+', '$', "'", '^', '(', ')', '[', ']', '{', '}'), array('\.', '|', '\+', '\$', "'", '\^', '\(', ')', '\[', '\]', '\{', '\}'), $notallowdir);
        $notallowdir = str_replace('*', '.*', $notallowdir);
    }

    $datas = [];
    if (!$path && $gettype) {
        $path = (PHP_OS == 'Linux') ? '/' : '';
        if ($path == '') {
            $diskarr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            foreach ($diskarr as $v) {
                if (is_dir($v . ':')) {
                    $datas[] = ['path' => $v . ':', 'charset' => CHARSET];
                }
            }
        } else {
            if (is_dir($path)) {
                if ($dh = @opendir($path)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file != '.' && $file != '..' && !preg_match('/^(' . $notallowdir . ')$/i', $file) && (strpos($file, '.') !== 0) && is_dir($path . BS . $file)) {
                            $returnfile = $path . $file;
                            $p = new Encode_Core();
                            $charset = $p->get_encoding($file);
                            $returnfile = diconv($returnfile, $charset, CHARSET);
                            $datas[] = ['path' => $returnfile, 'charset' => $charset];
                        }
                    }
                    //关闭
                    closedir($dh);
                }
            }
        }
        //云存储位置
        foreach (DB::fetch_all("select id,cloudname from %t where bz != %s", array('connect_storage', 'dzz')) as $v) {
            $datas[] = ['path' => $v['cloudname'], 'charset' => CHARSET, 'type' => 'cloud:' . $v['id']];
        }
        $datas[] = ['path' => DZZ_ROOT . 'library', 'charset' => CHARSET, 'type' => 1];
    } else {
        if (is_dir($path)) {
            if ($dh = @opendir($path)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && is_dir($path . BS . $file) && !preg_match('/^(' . $notallowdir . ')$/i', $file)) {
                        $returnfile = trim($file);
                        $p = new Encode_Core();
                        $charset = $p->get_encoding($file);
                        $returnfile = diconv($returnfile, $charset, CHARSET);
                        $datas[] = ['path' => $returnfile, 'charset' => $charset];
                    }
                }
                //关闭
                closedir($dh);
            }
        } elseif ($gettype && strpos($gettype, 'cloud') !== false) {
            // $datas[] = ['path'=>'aaaa','type'=>$gettype];
            $cloudid = str_replace('cloud:', '', $gettype);
            $connectdata = DB::fetch_first("select cloudname,id,bz,bucket from %t where id = %d  ", array('connect_storage', $cloudid));
            if ($connectdata['cloudname'] == $path) {
                $path = '/';
            } else {
                //去掉路径中的存储名称部分
                $path = str_replace(array($connectdata['cloudname'] . '/', $connectdata['cloudname'] . BS), '', $path);
            }

            //更换路径中的分割符为/
            $rpath = str_replace(BS, '/', $path);
            //得到请求路径
            $path = $connectdata['bz'] . ':' . $cloudid . ':' . $rpath;

            //准备替换结果中的路径前半部分
            $replacepath = $rpath;
            $returndata = IO::getFolderlist($path);
            foreach ($returndata['folder'] as $v) {
                //去掉路径中的前半部分及斜杠用以显示
                $v = str_replace($replacepath, '', $v);
                $v = trim($v, '/');
                $datas[] = ['path' => $v, 'charset' => CHARSET, 'type' => $gettype];
            }

        }

    }
    exit(json_encode(array('data' => $datas)));
} elseif ($operation == 'sort') {
    $appids = isset($_GET['appids']) ? trim($_GET['appids']) : '';
    if (submitcheck('settingsubmit')) {
        if (!$appids) exit(json_encode(array('error' => true)));
        $appidarr = explode(',', $appids);
        $setarr = [];
        foreach ($appidarr as $k => $v) {
            $setarr['disp'] = $k;
            C::t('pichome_vapp')->update($v, $setarr);
        }
        exit(json_encode(array('success' => true)));
    }
} elseif ($operation == 'converttopichome') {
    if ($_G['adminid'] != 1) exit(json_encode(array('success' => false, 'msg' => lang('no_perm'))));
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $appdata = C::t('pichome_vapp')->fetch($appid);

} elseif ($operation == 'geturlqrcode') {//获取链接二维码
    $appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
    $url = 'index.php?mod=pichome&op=fileview#appid=' . $appid;
    $sid = 'vapp_'.$appid;
    $qrcode = C::t('pichome_route')->getQRcodeBySid($url, $sid);
    exit(json_encode(['success' => true, 'qrcode' => $qrcode]));
} else {
    $theme = GetThemeColor();
    include template('storehouse/pc/page/library');
    // include template('admin/pc/page/library');
}
function getbasename($filename)
{
    return preg_replace('/^.+[\\\\\\/]/', '', $filename);
}
