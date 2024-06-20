<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
$rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
$fid = isset($_GET['fid']) ? trim($_GET['fid']) : '';
$appid = isset($_GET['appid']) ? trim($_GET['appid']) : '';
if($operation == 'save'){
    if($fid){
        $fids = explode(',',$fid);
        $flag = isset($_GET['flag']) ? trim($_GET['flag']):'';
        $val = trim($_GET['val']);
        //如果是卡片设置
       if($flag == 'tag'){
            if(count($fids) > 1){
                $ival=explode(',',$val);
                $foldertags = [];
                foreach(DB::fetch_all("select fid,tid from %t   where fid IN(%n)",array('pichome_foldertag',$fids)) as $value){
                    $foldertags[$value['fid']][] = $value['tid'];
                }
                $i=0;
                $o= $d = $oc = [];
                foreach($foldertags as $fid=>$value){
                    $ids= $value ? explode(",", $value):array();
                    $o[$fid]=$ids;
                    if($i==0){
                        $oc=$ids;
                    }else{
                        $oc=array_intersect($oc,$ids);
                    }
                    $i++;
                }
                $d=$oc?array_diff($oc,$ival):array();//被删除的
                if($d){
                    //删除对应文件的标签
                    C::t('pichome_foldertag')->delete_by_fids_tids($fids,$d);
                }
                $tagdatas = [];
                foreach(DB::fetch_all("select tagname,tid from %t where tid in(%n)",array('pichome_tag',$ival)) as $tv){
                    $tagdatas[] = ['tid'=>$tv['tid'],'tagname'=>$tv['tagname']];
                }

                foreach($fids as $fid){
                    $n=array_unique(array_diff(array_merge($ival,$o[$fid] ? $o[$fid]:[]),$d));
                    //增加文件的标签
                    foreach($n as $addtag){
                        if(!$addtag) continue;
                        $rtag = ['appid' => $value['appid'], 'fid' => $fid, 'tid' => $addtag];
                        C::t('pichome_foldertag')->insert($rtag);
                    }
                    $attrs=array(
                        $flag => implode(',',$n)
                    );
                }
                C::t('pichome_folder')->update_data_by_fids($appid,$fids,$attrs);
                $returndata['fid'] = $fids;
                $returndata['tag'] = $tagdatas;
            }else{
                $attrdata = C::t('pichome_folder')->fetch($fid);
                $datatags = explode(',',$attrdata['tag']);
                $ntags = explode(',',$val);
                $dtags = array_diff($datatags,$ntags);
                if($dtags){
                    //删除对应文件的标签
                    C::t('pichome_foldertag')->delete_by_fids_tids($appid,$fid,$dtags);
                }
                $addtags = array_diff($ntags,$datatags);
                foreach($addtags as $v){
                    if(!$v) continue;
                    $rtag = ['appid' => $attrdata['appid'], 'fid' => $fid, 'tid' => $v];
                    C::t('pichome_foldertag')->insert($rtag);
                }
                $attrs = [
                    $flag => implode(',',$ntags)
                ];
                C::t('pichome_folder')->update_data_by_fids($appid,$fids,$attrs);
                $tagdatas = [];
                foreach(DB::fetch_all("select tagname,tid from %t where tid in(%n)",array('pichome_tag',$ntags)) as $tv){
                    $tagdatas[] = ['tid'=>$tv['tid'],'tagname'=>$tv['tagname']];
                }
                $returndata = ['fid'=>[$fid],'tag'=>$tagdatas];
            }
        }elseif($flag == 'desc' || $flag == 'name'){
            $attrs = [
                $flag => getstr($val)
            ];
            C::t('pichome_folder')->update_data_by_fids($appid,$fids,$attrs);
        }else{
            exit(json_encode(array('success' => false)));
        }
    }
    else{
        $rids=explode(',',$rid);
        $flag = isset($_GET['flag']) ? trim($_GET['flag']):'';
        $val = trim($_GET['val']);
        $attrs = array(
            $flag => htmlspecialchars($val)
        );
        if(count($rids)>1){//是批量操作时；
            if(strpos($flag,'tabgroup_') === 0){
                $gid = intval(str_replace('tabgroup_','',$flag));
                $ival=explode(',',$val);
                $i=0;
                $o= $d = $oc = [];
                foreach(DB::fetch_all("select rid,tid from %t   where rid IN(%n) and gid = %d",array('pichome_resourcestab',$rids,$gid)) as $value){
                    $o[$value['rid']][]=$value['tid'];
                }
                foreach($o as $v){
                    if($i==0){
                        $oc=$v;
                    }else{
                        $oc=array_intersect($oc,$v);
                    }
                    $i++;
                }
                $d=$oc?array_diff($oc,$ival):array();//被删除的
                if($d){
                    //删除对应文件的卡片数据
                    C::t('pichome_resourcestab')->delete_by_rids_tids($rids,$d);
                }
                foreach($rids as $rid){
                    if(!$o[$rid]) $o[$rid] = [];
                    $n=array_unique(array_diff(array_merge($ival,$o[$rid] ? $o[$rid]:[]),$d));

                    //增加文件的卡片数据
                    foreach($n as $addtag){
                        $rtag = ['appid' => $value['appid'], 'rid' => $rid, 'tid' => $addtag,'gid'=>$gid];
                        C::t('pichome_resourcestab')->insert($rtag);
                    }
                }
            } else{
                switch($flag){
                    case 'tag'://标签
                        $ival=explode(',',$val);
                        $i=0;
                        $o= $d = $oc = [];
                        foreach(DB::fetch_all("select r.rid,r.appid,attr.tag from %t r LEFT JOIN %t attr ON r.rid=attr.rid   where r.rid IN(%n)",array('pichome_resources','pichome_resources_attr',$rids)) as $value){
                            $ids= $value['tag'] ? explode(",", $value['tag']):array();
                            $o[$value['rid']]=$ids;
                            if($i==0){
                                $oc=$ids;
                            }else{
                                $oc=array_intersect($oc,$ids);
                            }
                            $i++;
                        }
                        $d=$oc?array_diff($oc,$ival):array();//被删除的
                        if($d){
                            //删除对应文件的标签
                            C::t('pichome_resourcestag')->delete_by_rids_tids($rids,$d);
                        }
                        $tagdatas = [];
                        foreach(DB::fetch_all("select tagname,tid from %t where tid in(%n)",array('pichome_tag',$ival)) as $tv){
                            $tagdatas[] = ['tid'=>$tv['tid'],'tagname'=>$tv['tagname']];
                        }

                        foreach($rids as $rid){
                            $n=array_unique(array_diff(array_merge($ival,$o[$rid] ? $o[$rid]:[]),$d));
                            //增加文件的标签
                            foreach($n as $addtag){
                                $rtag = ['appid' => $value['appid'], 'rid' => $rid, 'tid' => $addtag];
                                C::t('pichome_resourcestag')->insert($rtag);
                            }
                            $attrs=array(
                                $flag => implode(',',$n)
                            );
                            C::t('pichome_resources_attr')->update_by_rids($appid,$rids,$attrs);
                            //$rids[] = $rid;

                        }
                        $returndata['rid'] = $rids;
                        $returndata['tag'] = $tagdatas;
                        break;
                    case 'desc':
                        foreach(DB::fetch_all("select r.rid,r.name,attr.link from %t r LEFT JOIN %t attr ON r.rid=attr.rid   where r.rid IN(%n)",array('pichome_resources','pichome_resources_attr',$rids)) as $value){
                          /*  $annotationdatas = C::t('pichome_comments')->fetch_annotation_by_rid($value['rid']);
                            $attr['searchval'] = $value['name'].$value['link'].getstr($val,255).implode('',$annotationdatas);
                            $attr = array_merge($attr,$attrs);*/
                            C::t('pichome_resources_attr')->update_by_rids($appid,$value['rid'],$attrs);
                            $returndata[]=['rid'=>$value['rid'],'desc'=>$val];
                        }
                        break;
                    case 'link':
                        foreach(DB::fetch_all("select r.rid,r.name,attr.desc from %t r LEFT JOIN %t attr ON r.rid=attr.rid   where r.rid IN(%n)",array('pichome_resources','pichome_resources_attr',$rids)) as $value){
                           /* $annotationdatas = C::t('pichome_comments')->fetch_annotation_by_rid($value['rid']);
                            $attr['searchval'] = $value['name'].getsrt($value['desc'],255).htmlspecialchars($val).implode('',$annotationdatas);
                            $attr = array_merge($attr,$attrs);*/

                            C::t('pichome_resources_attr')->update_by_rids($appid,$value['rid'],$attrs);
                            $returndata[]=['rid'=>$value['rid'],'link'=>$val];
                        }
                        break;
                    case 'name':
                        foreach(DB::fetch_all("select r.rid,attr.link,attr.desc from %t r LEFT JOIN %t attr ON r.rid=attr.rid   where r.rid IN(%n)",array('pichome_resources','pichome_resources_attr',$rids)) as $value){
                            //$annotationdatas = C::t('pichome_comments')->fetch_annotation_by_rid($value['rid']);
                           /* $attr['searchval'] = $value['link'].getstr($value['desc'],255).htmlspecialchars($val).implode('',$annotationdatas);
                            $attr = array_merge($attr,$attrs);*/
                            C::t('pichome_resources')->update_by_rids($appid,$value['rid'],$attrs);
                           // C::t('pichome_resources_attr')->update_by_rid($appid,$value['rid'],$attr);
                            $returndata[]=['rid'=>$value['rid'],'name'=>$val];
                        }

                        break;
                    case 'fid':
                        $ival=explode(',',$val);
                        $i=0;
                        $isdels = $o= $d = $oc = [];
                        foreach(DB::fetch_all("select rid,appid,fids,isdelete from %t  where rid IN(%n)",array('pichome_resources',$rids)) as $value){
                            $ids= $value['fids'] ? explode(",", $value['fids']):array();
                            $o[$value['rid']]=$ids;
                            if($i==0){
                                $oc=$ids;
                            }else{
                                $oc=array_intersect($oc,$ids);
                            }
                            $isdels[$value['rid']] = $value['isdelete'];
                            $i++;
                        }
                        $d=$oc?array_diff($oc,$ival):array();//被删除的
                        if($d){
                            //删除对应文件目录数据
                            C::t('pichome_folderresources')->delete_by_ridfid($rids,$d);
                        }
                        $returndata = [];
                        $foldernames = [];
                        foreach(DB::fetch_all("select fid,fname,pathkey from %t where fid in(%n)",array('pichome_folder',$ival)) as $fv){
                            $foldernames[] = ['fid'=>$fv['fid'],'fname'=>$fv['fname'],'pathkey'=>$fv['pathkey']];
                        }
                        foreach($rids as $rid){
                            $n=array_unique(array_diff(array_merge($ival,$o[$rid]),$d));
                            //增加文件的标签
                            foreach($n as $addfid){
                                $rfolder = ['appid' => $value['appid'], 'rid' => $rid, 'fid' => $addfid];
                                C::t('pichome_folderresources')->insert($rfolder);

                            }
                            $attrs=array(
                                'fids' => implode(',',$n)
                            );
                            $attrs['lastdate']=TIMESTAMP;
                            //如果文件有新增目录，并且当前文件是被删除的，恢复当前文件和目录
                            if($isdels[$rid] && $n){
                                $attrs['isdelete'] = 0;
                                $ofids=array_unique(array_diff($o[$rid],$ival));
                                $ofids=array_unique(array_diff($ofids,$d));
                                $rfidarr = explode(',', $ofids);
                                C::t('pichome_folder')->add_filenum_by_fid($rfidarr, 1);
                            }else{
                                $attrs['isdelete'] = $isdels[$rid];
                            }
                            C::t('pichome_resources')->update_by_rids($appid,$rids,$attrs);
                            $returndata[]=['rid'=>$rid,'isdelete'=>$attrs['isdelete'],'foldernams'=>$foldernames];
                        }
                        break;
                    default:
                        $attrs['lastdate']=TIMESTAMP;
                        C::t('pichome_resources')->update_by_rids($appid,$rids,$attrs);
                        foreach($rids as $rid){
                            $returndata[]=['rid'=>$rid,$flag=>$val];
                        }
                        break;

                }
            }

        }
        else{
            if(strpos($flag,'tabgroup_') === 0){
                $gid = intval(str_replace('tabgroup_','',$flag));
                $datatids = [];
                foreach(DB::fetch_all("select tid from %t where rid = %s and gid = %d",array('pichome_resourcestab',$rid,$gid)) as $v){
                    $datatids[] = $v['tid'];
                }
                $ntids = $attrs[$flag] ? explode(',',$attrs[$flag]):[];
                $dtids = array_diff($datatids,$ntids);
                if($dtids){
                    //删除对应文件的标签
                    C::t('pichome_resourcestab')->delete_by_rids_tids($rid,$dtids);
                }
                $addtids = array_diff($ntids,$datatids);
                foreach($addtids as $v){
                    if(!$v) continue;
                    $rtag = ['appid' => $appid, 'rid' => $rid, 'tid' => $v,'gid'=>$gid];
                    C::t('pichome_resourcestab')->insert($rtag);
                }
            }
            elseif($flag == 'tag'){
                $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                $datatags = explode(',',$attrdata['tag']);
                $ntags = explode(',',$attrs['tag']);
                $dtags = array_diff($datatags,$ntags);
                if($dtags){
                    //删除对应文件的标签
                    C::t('pichome_resourcestag')->delete_by_rids_tids($rid,$dtags);
                }
                $addtags = array_diff($ntags,$datatags);
                foreach($addtags as $v){
                    if(!$v) continue;
                    $rtag = ['appid' => $attrdata['appid'], 'rid' => $rid, 'tid' => $v];
                    C::t('pichome_resourcestag')->insert($rtag);
                }
                $attrs = [
                    'tag' => implode(',',$ntags)
                ];
                C::t('pichome_resources_attr')->update_by_rid($appid,$rid,$attrs);
                $tagdatas = [];
                foreach(DB::fetch_all("select tagname,tid from %t where tid in(%n)",array('pichome_tag',$ntags)) as $tv){
                    $tagdatas[] = ['tid'=>$tv['tid'],'tagname'=>$tv['tagname']];
                }
                $returndata = ['rid'=>$rid,'tag'=>$tagdatas];

            }
            elseif($flag == 'fid'){
                $resourcesdata = C::t('pichome_resources')->fetch($rid);
                $datafolders = explode(',',$resourcesdata['fids']);
                $nfolders = explode(',',$val);

                $dfolders = array_diff($datafolders,$nfolders);
                if($dfolders){
                    //删除对应文件的目录
                    C::t('pichome_folderresources')->delete_by_ridfid($rid,$dfolders);
                }
                $addfolderss = array_diff($nfolders,$datafolders);
                foreach($addfolderss as $v){
                    $rfolder = ['appid' => $resourcesdata['appid'], 'rid' => $rid, 'fid' => $v];
                    C::t('pichome_folderresources')->insert($rfolder);
                }
                $attrs = [
                    'fids' => implode(',',$nfolders),
                    'lastdate'=>TIMESTAMP,
                    'isdelete'=>($addfolderss) ? 0:$resourcesdata['isdelete']
                ];
                if($addfolderss){
                    $attrs['isdelete']= 0;
                    $ofids=array_unique(array_diff($datafolders,$nfolders));
                    $ofids = array_diff($datafolders,$dfolders);
                    $rfidarr = explode(',', $ofids);
                    C::t('pichome_folder')->add_filenum_by_fid($rfidarr, 1);
                }else{
                    $attrs['isdelete'] = $resourcesdata['isdelete'];
                }
                C::t('pichome_resources')->update_by_rids($appid,$rid,$attrs);
                $foldernames = [];
                foreach(DB::fetch_all("select fid,fname,pathkey from %t where fid in(%n)",array('pichome_folder',$nfolders)) as $fv){
                    $foldernames[] = ['fid'=>$fv['fid'],'fname'=>$fv['fname'],'pathkey'=>$fv['pathkey']];
                }
                $returndata[] = ['rid'=>$rid,'isdelete'=>$attrs['isdelete'],'foldernames'=>$foldernames];
            }elseif($flag == 'grade'){
                $attrs['lastdate']=TIMESTAMP;
                C::t('pichome_resources')->update_by_rids($appid,$rid,$attrs);
                $returndata[] = ['rid'=>$rid,'grade'=>$val];
            }else{
                $resourcesattrdata = DB::fetch_first("select r.name,attr.link,attr.desc from %t r left join %t attr on r.rid = attr.rid where r.rid = %s",
                    array('pichome_resources','pichome_resources_attr',$rid));
                $annotationdatas = C::t('pichome_comments')->fetch_annotation_by_rid($rid);
                if($flag == 'name'){
                    C::t('pichome_resources')->update_by_rids($appid,$rids,$attrs);
                   /* $attr['searchval'] = $resourcesattrdata['link'].getstr($resourcesattrdata['desc'],255).htmlspecialchars($val).implode('',$annotationdatas);
                    C::t('pichome_resources_attr')->update_by_rid($appid,$rid,$attr);*/
                }elseif($flag == 'desc'){
                    //$attrs['searchval'] = $resourcesattrdata['link'].$resourcesattrdata['name'].getstr($val,255).implode('',$annotationdatas);
                    C::t('pichome_resources_attr')->update_by_rids($appid,$rid,$attrs);
                }elseif($flag == 'link'){
                   /* $attrs['searchval'] = $resourcesattrdata['name'].getstr($resourcesattrdata['desc'],255).htmlspecialchars($val).implode('',$annotationdatas);*/
                    C::t('pichome_resources_attr')->update_by_rid($appid,$rid,$attrs);
                }
                $returndata[] = ['rid'=>$rid,$flag=>$val];
            }

        }
    }


    exit(json_encode(array('success' => true,'data'=>$returndata)));

}elseif($operation == 'label_popbox'){//获取标签分类
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    $groupdata = C::t('pichome_taggroup')->fetch_tagcatandnum_by_pcid($appid);
    $arr = [];
    foreach ($groupdata as $key => $val) {
        $arr[] = array('cid' => $key, 'text' => $val['tagname'],'num'=>$val['num']);
    }
    exit(json_encode(array('success' => true,'arr' => $arr)));
}elseif($operation == 'getRigehtdata'){//右侧标签
    $flag = isset($_GET['flag']) ? trim($_GET['flag']) : '';
    $oneself_tid=isset($_GET['tids']) ? explode(',',$_GET['tids']) : array();//当前rid对应的所有标签tid
    $cid = isset($_GET['cid']) ? trim($_GET['cid']):0;
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    if($cid){
		$tagdata = array();
      /*  $groupdata = C::t('pichome_taggroup')->fetch_tagcatandnum_by_pcid($appid,$cid);
        $gids = array();
        $tagdata = array();
        foreach ($groupdata as $v) {
            $cids[] = $v['cid'];
            $tagdata[$v['cid']]['name'] = $v['name'];
        }*/

        foreach (DB::fetch_all("select t.*,tg.cid from %t tg left join %t t on t.tid=tg.tid left join %t vt  
                on vt.tid = tg.tid where tg.cid =%s and tg.appid = %s order by t.initial,vt.hots DESC ",
            array('pichome_tagrelation', 'pichome_tag','pichome_vapp_tag',$cid,$appid)) as $val) {
            if(in_array($val['tid'],$oneself_tid)){
                $val['yes'] = 1;
            }else{
                $val['yes'] = 0;
            }
			 // $tagdata[$val['gid']]['val'][] = $val;
            $tagdata[] = $val;
        }
		exit(json_encode(array('success' => true,'arr' => $tagdata)));
    }else{

        $tags_all_new = array();//带字幕的所有标签
        $recent = array();//最近使用
        $all = array();//所有标签
        $tags_all = array();
        $tag_cat = array();

        foreach(DB::fetch_all("select t.*,vt.appid,vt.hots from %t vt left join %t t on t.tid=vt.tid where vt.appid=%s order by 
                t.initial,vt.hots DESC",array('pichome_vapp_tag','pichome_tag',$appid)) as $value){
            if(!isset($tags_all[$value['initial']])) $tags[$value['initial']]=array();
            $tags_all[$value['initial']][$value['tid']]=$value;

        }
        if(count($tags_all['#']) > 0){
            $all_new=array_shift($tags_all);
            $tags_all['#'] = $all_new;
        }
        //最近使用数据
        $renctentdata = C::t('pichome_searchrecent')->fetch_recent_tag_by_appid($appid);
        $recenttids = array_keys($renctentdata);
        foreach($tags_all as $key => $val){
            foreach($val as $k => $v){
                if(in_array($v['tid'],$oneself_tid)){
                    $val[$k]['yes'] = 1;
                }else{
                    $val[$k]['yes'] = 0;
                }
                if($v['hots'] > 0 && in_array($k,$recenttids)){
                    $val[$k]['dateline'] = $renctentdata[$k];
                    $recent[] = $val[$k];
                }
                $all[$v['tid']] = $val[$k];
            }

            $tags_all_new[$key] = $val;
        }
		
        $recent_dateline = array_column($recent, 'dateline');
        array_multisort($recent_dateline,SORT_DESC,$recent );
       exit(json_encode(array('success' => true,'data' => $tags_all_new,'recent'=>$recent,'arr'=>$all)));
    }
	
}elseif($operation == 'label_add'){
    //$flag = isset($_GET['flag']) ? trim($_GET['flag']) : '';
    $tags = isset($_GET['tags']) ? trim($_GET['tags']) : '';
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):'';
    $cid = isset($_GET['cid']) ? trim($_GET['cid']):0;//标签分类id
    $tags = explode(',',$tags);
    $data = array();
    foreach($tags as $v){
        if(preg_match('/^\s*$/',$v)) continue;
        if($result = DB::fetch_first("select * from %t where tagname = %s ",array('pichome_tag',$v))){
            
            if($cid){
                $tagrelationarr = [
                    'appid'=>$appid,
                    'cid'=>$cid,
                    'tid'=>$result['tid']
                ];
                C::t('pichome_tagrelation')->insert($tagrelationarr);
            }
            $setarr = $result;
            $hots = DB::result_first("select hots from %t where appid = %s and tid = %d",array('pichome_vapp_tag',$appid,$result['tid']));
            if(is_null($hots)){
                $tagvapp = array(
                    'tid'=>$result['tid'],
                    'appid'=>$appid,
                );
                C::t('pichome_vapp_tag')->insert($tagvapp);
                $result['hots'] = 0;
            }else{
                $result['hots'] = intval($hots);
                
            }
            $result['langkey'] = ['tagname'=>'tag_'.$result['tid']];
            $data[] =  $result;
        }else{
            $setarr = array(
                'tagname'=>$v,
                'initial'=>C::t('pichome_tag')->getInitial($v),
            );
            $id =  C::t('pichome_tag')->insert($v,1);
            if($id && $cid){
                $tagrelationarr = [
                    'appid'=>$appid,
                    'cid'=>$cid,
                    'tid'=>$id
                ];
                C::t('pichome_tagrelation')->insert($tagrelationarr);
            }
			
            //将添加的标签添加到库
            $tagvapp = array(
                'tid'=>$id,
                'appid'=>$appid,
               // 'hots'=>1
            );
            C::t('pichome_vapp_tag')->insert($tagvapp);
            $setarr['tid'] = $id;
            $setarr['hots'] = 0;
            $setarr['langkey'] = ['tagname'=>'tag_'.$setarr['tid']];
            $data[] =  $setarr;
        }
    }
    exit(json_encode(array('success' => true,'data'=>$data)));
}

dexit();