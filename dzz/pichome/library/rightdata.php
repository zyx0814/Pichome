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
        where isnull(rf.id) and r.appid = %s and r.isdelete < 1",array('pichome_resources','pichome_folderresources',$appid));

    }elseif($notag){
        $data = DB::fetch_first("select count(distinct(r.rid)) as num ,sum(r.size) as size from %t r left join %t rt on r.rid=rt.rid 
        where isnull(rt.id) and r.appid = %s and r.isdelete < 1",array('pichome_resources','pichome_resourcestag',$appid));
    }elseif($fid){
            $fids = explode(',',$fid);
            $folderdata = [];
            $i = 0;
            $o = [];
            foreach(DB::fetch_all("select fname,tag,`desc`,dateline from %t where fid in(%n)",array('pichome_folder',$fids)) as $v){
                $ids= $v['tag'] ? explode(",", $v['tag']):array();
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
                    $o['tagdata'][$v['tid']] = $v['tagname'];
                }
            }
            unset($o['tag']);
        if($hassub){
            $nfids = [];
            foreach(DB::fetch_all("select pathkey from %t where fid in(%n)",array('pichome_folder',$fids)) as $value){
                foreach(DB::fetch_all("select fid from %t where pathkey like %s",array('pichome_folder',$value['pathkey'].'%')) as $v){
                    $nfids[] = $v['fid'];
                }
            }
            $fids = $nfids;
        }
        $data = DB::fetch_first("select count(distinct(r.rid)) as num,sum(r.size) as size from %t r left join %t rf on r.rid=rf.rid 
        where r.appid = %s and rf.fid in(%n) and r.isdelete < 1", array('pichome_resources', 'pichome_folderresources', $appid, $fid));
        $data = array_merge($o,$data);

    }elseif($isrecycle){
        $data = DB::fetch_first("select count(rid) as num,sum(size) as size from %t 
        where  appid = % and isdelete = 1",array('pichome_resources',$appid));
    }else{
        $data = DB::fetch_first("select count(rid) as num,sum(size) as size from %t 
        where  appid = % and isdelete < 1",array('pichome_resources',$appid));
    }
    $data['size'] = formatsize($data['size']);
	$data['dateline'] = dgmdate(round($data['dateline'] / 1000), 'Y/m/d H:i');
   exit(json_encode($data));
}elseif($operation == 'getfiledata'){//获取文件右侧信息
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    $appdata = C::t('pichome_vapp')->fetch($appid);
        $rids = isset($_GET['rids']) ? trim($_GET['rids']):'';
        $rids = explode(',',$rids);
        if(count($rids) > 1){
            //获取文件标签和描述链接信息
            $o['fileds'] = unserialize($appdata['fileds']);
           $d = $oc = [];
            $link = $desc = '';
            $grade = $size = 0;
            foreach(DB::fetch_all("select r.rid,r.fids,r.size,r.grade,r.appid,attr.tag,attr.desc,attr.link from %t r LEFT JOIN %t attr ON r.rid=attr.rid   where r.rid IN(%n)",array('pichome_resources','pichome_resources_attr',$rids)) as $value){
                $ids= $value['tag'] ? explode(",", $value['tag']):array();
                $fids= $value['fids'] ? explode(",", $value['fids']):array();
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
                $o['tagdata'] = [];
                foreach(DB::fetch_all("select tagname,tid from %t where tid in(%n)",array('pichome_tag',$o['tags'])) as $v){
                    $o['tagdata'][$v['tid']] = $v['tagname'];
                }
            }
            //print_r($o['fids']);die;
            if($o['fids']){

                $o['folderdata'] = [];
                foreach(DB::fetch_all("select fname,fid,pathkey from %t where fid in(%n)",array('pichome_folder',$o['fids'])) as $v){
                    $o['folderdata'][$v['fid']]['fname'] = $v['fname'];
                    $o['folderdata'][$v['fid']]['pathkey'] = $v['pathkey'];

                }
            }
            unset($o['tags']);
            unset($o['fids']);
            $o['fids'] = $o['folderdata'];
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
			exit(json_encode($o));
        }else{
            $data = C::t('pichome_resources')->fetch_by_rid($rids[0],1,1);
            $data['fileds'] = unserialize($appdata['fileds']);
            //获取tab数据
            $tabstatus = 0;
            Hook::listen('checktab', $tabstatus);
            if($tabstatus){
                foreach($data['fileds'] as $v){
                    if($v['type'] == 'tabgroup'){
                        $gid =  intval(str_replace('tabgroup_','',$v['flag']));
                        $tids = [];
                        foreach(DB::fetch_all("select tid from %t where rid= %s and gid = %d",array('pichome_resourcestab',$rids[0],$gid)) as $val){
                            $tids[] = $val['tid'];
                        }
                        Hook::listen('gettab',$tids);
                        $data[$v['flag']] = $tids;
                    }
                }
            }
			exit(json_encode($data));
        }
        
}