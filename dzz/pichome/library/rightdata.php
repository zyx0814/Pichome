<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
if($operation == 'getfolderdata'){//获取右侧信息
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    $fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
    $notag = isset($_GET['notag']) ? intval($_GET['notag']):0;
    $hassub = isset($_GET['hassub']) ? intval($_GET['hassub']):0;
    $nofolder = isset($_GET['nofolder']) ? intval($_GET['nofolder']):0;
    $isrecycle = isset($_GET['isrecycle']) ? intval($_GET['isrecycle']):0;
    $data = ['num'=>0,'size'=>0];
    if($nofolder){
        $data = DB::fetch_first("select count(r.rid) as num,sum(r.size) as size from %t r left join %t rf on r.rid=rf.rid 
        where isnull(rf.id) and r.appid = %s and r.isdelete = 0",array('pichome_resources','pichome_folderresources',$appid));

    }elseif($notag){
        $data = DB::fetch_first("select count(distinct(r.rid)) as num ,sum(r.size) as size from %t r left join %t rt on r.rid=rt.rid 
        where isnull(rt.id) and r.appid = %s and r.isdelete = 0",array('pichome_resources','pichome_resourcestag',$appid));
    }
    elseif($fid){
            $fids = explode(',',$fid);
            $folderdata = [];
            $i = 0;
            $o = [];
            foreach(DB::fetch_all("select fname,tag,`desc`,dateline,appid from %t where fid in(%n)",array('pichome_folder',$fids)) as $v){
                $ids= $v['tag'] ? explode(",", $v['tag']):array();
                if(!$appid) $appid = $v['appid'];
                if($i==0){
                    $o['tag']=$ids;
                    $o['desc']=$v['desc'];
                    $o['dateline'] = $v['dateline'];
                    $o['fname'] = $v['fname'];

                }else{
                    $o['tag']=array_intersect($o['tag'],$ids);
                    if($v['desc'] != $o['desc']) $o['desc'] = '';
                    if($v['fname'] != $o['fname'])  $o['fname'] = '';
                    if($v['dateline'] > $o['dateline']) {
                        $o['dateline']=$v['dateline'];
                    }
                }
                $i++;
            }
            if($o['tag']){
                $o['tagdata'] = [];
                foreach(DB::fetch_all("select tagname,tid from %t where tid in(%n)",array('pichome_tag',$o['tag'])) as $v){
                    Hook::listen('lang_parse',$v,['getTagLangData']);
                    $o['tagdata'][$v['tid']] = $v['tagname'];
                }
            }
            unset($o['tag']);
       // if($hassub){
            $nfids = [];
            foreach(DB::fetch_all("select pathkey from %t where fid in(%n)",array('pichome_folder',$fids)) as $value){
                foreach(DB::fetch_all("select fid from %t where pathkey like %s",array('pichome_folder',$value['pathkey'].'%')) as $v){
                    $nfids[] = $v['fid'];
                }
            }
            //$fids = $nfids;
       // }
        $data = DB::fetch_first("select count(distinct(r.rid)) as num,sum(r.size) as size from %t r left join %t rf on r.rid=rf.rid 
        where r.appid = %s and rf.fid in(%n) and r.isdelete = 0", array('pichome_resources', 'pichome_folderresources', $appid, $nfids));
        $data['foldernum'] = (count($fids) > 1) ? count($nfids):count(array_diff($nfids,$fids));
        $data = array_merge($o,$data);
        //如果有文件才提供ai获取
        if(count($fids) == 1 && $data['num']) $data['fid'] = $nfids;
        Hook::listen('editfilefilter',$data,['type'=>'folder','appid'=>$appid]);

    }elseif($isrecycle){
        $data = DB::fetch_first("select count(rid) as num,sum(size) as size from %t 
        where  appid = % and isdelete = 1",array('pichome_resources',$appid));
    }else{
        $data = DB::fetch_first("select count(rid) as num,sum(size) as size from %t 
        where  appid = % and isdelete < 1",array('pichome_resources',$appid));
        if($data['num']) $data['appid'] = $appid;
        Hook::listen('editfilefilter',$data,['type'=>'vapp','appid'=>$appid]);
    }
    $data['size'] = formatsize($data['size']);
	$data['dateline'] = dgmdate(round($data['dateline'] / 1000), 'Y/m/d H:i');

    if(!$data['fid']) $data['fid'] =($nfids) ? array_intersect($fids,$nfids):$fids;
    Hook::listen('lang_parse',$data,['getFolderLangData']);
    Hook::listen('lang_parse',$data,['getFolderLangKey']);

   exit(json_encode($data));
}
elseif($operation == 'getfiledata'){//获取文件右侧信息
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    $appdata = C::t('pichome_vapp')->fetch($appid);
        $rids = isset($_GET['rids']) ? trim($_GET['rids']):'';
        $rids = explode(',',$rids);
    $defaultfileds =  [
        [
            'flag' => 'name',
            'type' => 'input',
            'name' => lang('name'),
            'enable' => 1,
            'checked' => 1
        ],
        [
            'flag' => 'tag',
            'type' => 'multiselect',
            'name' => lang('label'),
            'enable' => 1,
            'checked' => 1
        ],
        [
            'flag' => 'desc',
            'type' => 'input',
            'name' => lang('describe'),
            'enable' => 1,
            'checked' => 1
        ],
        [
            'flag' => 'link',
            'type' => 'input',
            'name' => lang('link'),
            'enable' => 1,
            'checked' => 1
        ],

        [
            'flag' => 'grade',
            'type' => 'grade',
            'name' => lang('grade'),
            'enable' => 1,
            'checked' => 1
        ],
        [
            'flag' => 'fid',
            'type' => 'multiselect',
            'name' => lang('classify'),
            'enable' => 1,
            'checked' => 1
        ]
    ];
    if(defined('PICHOME_LIENCE')){
        $defaultfileds[] = [
            'flag' => 'level',
            'type' =>'grade',
            'name'=>lang('level'),
            'enable' => 0,
            'checked' => 1
        ];
    }
    if($appdata['type'] == 1 || $appdata['type'] == 3){
        $defaultfileds[] = [
            'flag' => 'preview',
            'type' => 'multiupload',
            'name' => lang('more_picture_preview'),
            'checked' => 0,
            'enable' => 1
        ];
    }
        if(count($rids) > 1){
            //获取文件标签和描述链接信息
            $o['fileds'] = unserialize($appdata['fileds']);
            $filedFlags = array_column($o['fileds'],'flag');
            $filedsKey = array_search('preview',$filedFlags);
            unset($o['fileds'][$filedsKey]);
            $o['fileds'] = array_values($o['fileds']);
          $aiFilterdata =  $d = $oc = [];
            $link = $desc = '';
            $grade = $size = 0;
            foreach(DB::fetch_all("select r.rid,r.ext,r.fids,r.size,r.grade,r.appid,attr.tag,attr.desc,attr.link from %t r LEFT JOIN %t attr ON r.rid=attr.rid   where r.rid IN(%n)",array('pichome_resources','pichome_resources_attr',$rids)) as $value){
                $ids= $value['tag'] ? explode(",", $value['tag']):array();
                $fids= $value['fids'] ? explode(",", $value['fids']):array();
                $aiFilterdata['rid'][] = $value['rid'];
                $aiFilterdata['exts'][] = $value['ext'];
                if(!$appid) $appid = $value['appid'];
                $o['icondata'][] = C::t('pichome_resources')->geticondata_by_rid($value['rid'],1);
                if($i==0){
                    $o['tags']=$ids;
                    $o['fids']=$fids;
                    $link = $value['link'];
                    $desc = $value['desc'];
                    $grade = $value['grade'];
                }else{
                    $o['tags']=array_intersect($o['tags'],$ids);
                    $o['fids']=array_intersect($o['fids'],$fids);
                    if($value['link'] != $link) $link = '';
                    if($value['desc'] != $desc) $desc = '';
                    if($value['grade'] != $grade) $grade = 0;
                }
                $size += $value['size'];
                $i++;
            }
            $o['size'] = formatsize($size);
            $o['grade'] = $grade;
            $o['desc'] = $desc;
            $o['link'] = $link;
            if($o['tags']){
                $o['tag'] = [];
                foreach(DB::fetch_all("select tagname,tid from %t where tid in(%n)",array('pichome_tag',$o['tags'])) as $v){
                    Hook::listen('lang_parse',$v,['getTagLangData']);
                    $o['tag'][$v['tid']] = $v['tagname'];
                }
            }
            if($o['fids']){
                $o['folderdata'] = C::t('pichome_folder')->getDataByFids($o['fids']);
            }
            unset($o['tags']);
            unset($o['fids']);
            $o['foldernames'] = $o['folderdata'];
            unset($o['folderdata']);
            //获取tab数据
            $tabstatus = 0;
            Hook::listen('checktab', $tabstatus);
            if($tabstatus){
                foreach($o['fileds'] as $v){
                    if($v['type'] == 'tabgroup'){
                        $gid =  intval(str_replace('tabgroup_','',$v['flag']));
                        $tids = [];
                        $i = 0;
                        foreach($rids as $rid){
                            $tmptids = [];

                            foreach(DB::fetch_all("select tid from %t where rid =%s and gid = %d",array('pichome_resourcestab',$rid,$gid)) as $val){
                                $tmptids[] = $val['tid'];
                            }
                            if($i == 0) $tids = $tmptids;
                            else $tids = array_intersect($tids,$tmptids);
                            $i++;
                        }
                        $tids = array_unique($tids);
                        Hook::listen('gettab',$tids);
                        $o[$v['flag']] = $tids;
                    }
                }
            }
            if(!$o['rid']) $o['rid'] = $rids;

            Hook::listen('editfilefilter',$aiFilterdata,['type'=>'files','appid'=>$appid]);

            $o = array_merge($o,$aiFilterdata);
            Hook::listen('lang_parse',$o,['getResourcesLangData']);
            $o['filenamewirte'] = true;
            if($appdata['type']!=2) {
                Hook::listen('lang_parse', $o, ['getResourcesLangKey']);
            }else{
                $o['filenamewirte'] = false;
            }
            $o['allowedit'] = false;
            if($appdata['type'] == 1){
                $o['allowedit'] = true;
                unset($o['langkey']['name']);
                $o['filenamewirte'] = (is_writeable($appdata['path']) && (!isset($_G['config']['notallowDirectoryEditFilename']) || !$_G['config']['notallowDirectoryEditFilename'])) ? true :false;
            }elseif($appdata['type'] == 0){
                $allowedit = false;
                Hook::listen('checkeaglesync', $allowedit);
                $o['allowedit'] = $allowedit;
                $o['filenamewirte'] = $allowedit;
            }elseif($appdata['type'] == 3){
                $allowedit = true;
                $o['filenamewirte'] = true;
            }
            foreach($o['fileds'] as $k=>$v){

                    if($v['flag'] == 'lang'){
                        $langList = getglobal('language_list');
                        $langoption = [['name'=>'all','value'=>'通用']];
                        foreach($langList as $key=>$val){
                            $langoption[] = ['name'=>$key,'value'=>$val['langval']];
                        }
                        $data['fileds'][$k]['options'] = $langoption;
                    }

                foreach($defaultfileds as $k1=>$v1){
                    if($v['flag'] == $v1['flag']){
                        $o['fileds'][$k]['name'] = $defaultfileds[$k1]['name'];
                    }

                }
            }
			exit(json_encode($o));
        }else{
            $data = C::t('pichome_resources')->fetch_by_rid($rids[0],1,1);
            $data['filenamewirte'] = true;
            if( $appdata['type']!=2) {
                Hook::listen('lang_parse', $data, ['getResourcesLangKey']);
            }else{
                $data['filenamewirte'] = false;
            }
            $data['allowedit'] = false;
            if($appdata['type'] == 1){
                $data['allowedit'] = true;
                unset($data['langkey']['name']);
                $data['filenamewirte'] = (is_writeable($appdata['path']) && (!isset($_G['config']['notallowDirectoryEditFilename']) || !$_G['config']['notallowDirectoryEditFilename'])) ? true :false;
            } elseif($appdata['type'] == 0){
                $allowedit = false;
                Hook::listen('checkeaglesync', $allowedit);
                $data['allowedit'] = $allowedit;
                $data['filenamewirte'] = $allowedit;
            }elseif($appdata['type'] == 3){
                $data['allowedit'] = true;
                $data['filenamewirte'] = true;
            }
            $data['fileds'] = unserialize($appdata['fileds']);
            global  $Types;
            $notallowPreviewexts = array_merge($Types['document'],$Types['video'],$Types['audio']);
            if(in_array($data['ext'],$notallowPreviewexts)){
                $filedFlags = array_column($data['fileds'],'flag');
                $filedsKey = array_search('preview',$filedFlags);
                unset($data['fileds'][$filedsKey]);
            }
            $data['fileds'] = array_values($data['fileds']);
            $data['preview'] =  C::t('thumb_preview')->fetchPreviewByRid($rids[0],1);
            $data['allowcover'] = ($data['apptype'] == 1 || $data['apptype'] == 3) ? 1 : 0;
            //获取tab数据
            $tabstatus = 0;
            Hook::listen('checktab', $tabstatus);
            if($tabstatus){

                foreach($data['fileds'] as $v){
                    if($v['type'] == 'tabgroup'){
                        $gid =  intval(str_replace('tabgroup_','',$v['flag']));
                        $tids = [];
                        foreach(DB::fetch_all("select tid from %t where rid= %s and gid = %d",array('pichome_resourcestab',$rids[0],$gid)) as $val) {
                            $tids[] = $val['tid'];
                        }
                        Hook::listen('gettab',$tids);
                        $data[$v['flag']] = $tids;
                    }
                }
            }

            $aiFilterdata = ['rid'=>[$data['rid']],'exts'=>[$data['ext']]];
            Hook::listen('editfilefilter',$aiFilterdata,['type'=>'file','appid'=>$data['appid']]);
            foreach($data['fileds'] as $k=>$v){
                if($v['flag'] == 'lang'){
                    $langList = getglobal('language_list');
                    $langoption = [['name'=>'all','value'=>'通用']];
                    foreach($langList as $key=>$val){
                        $langoption[] = ['name'=>$key,'value'=>$val['langval']];
                    }
                    $data['fileds'][$k]['options'] = $langoption;
                }
                foreach($defaultfileds as $k1=>$v1){
                    if($v['flag'] == $v1['flag']){
                        $data['fileds'][$k]['name'] = $defaultfileds[$k1]['name'];
                    }

                }
            }
            $data = array_merge($data,$aiFilterdata);
			exit(json_encode($data));
        }
        
}