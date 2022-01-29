<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_collectlist extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_collectlist';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_collectlist';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }
    //添加收藏
    public function add_collect($data){
        $perm = C::t('pichome_collectuser')->get_perm_by_clid($data['clid']);
        if($perm < 2){
            return array('error'=>'no_perm');
        }
        if(empty($data['rid'])) return true;
        $existsdata = DB::fetch_all("select rid from %t where rid in(%n) and clid = %d and cid = %d",array($this->_table,$data['rid'],$data['clid'],$data['cid']));
        $existsrids = [];
        foreach($existsdata as $v){
            $existsrids[] = $v['rid'];
        }
        $totalcount = count($data['rid']);
        $data['rid'] = array_diff($data['rid'],$existsrids);
        if(empty($data['rid'])) return true;
        //记录加入收藏个数
        $counti = 0;
        $namesarr = [];
        foreach(DB::fetch_all("select appid,rid,name from %t where rid in(%n)",array('pichome_resources',$data['rid'])) as $v){
            $setarr = [
                'rid'=>$v['rid'],
                'cid'=>$data['cid'],
                'clid'=>$data['clid'],
                'uid'=>$data['uid'],
                'username'=>$data['username'],
                'dateline'=>$data['dateline'],
                'appid'=>$v['appid'],
            ];
            $namesarr[] = $v['name'];
            if(parent::insert($setarr,1)) $counti += 1;
        }
        if($counti){
            $filenamearr = array_slice($namesarr,0,5);
            $filename = implode(',',$filenamearr).'等'.$totalcount.'个文件';
            //如果收藏有分类增加该分类下文件数
            $position = DB::result_first("select name from %t where clid = %d",array('pichome_collect',$data['clid']));
            if($data['cid']){
                C::t('pichome_collectcat')->add_filenum_by_cid($data['cid'],$counti);
                $pathkey = DB::result_first("select pathkey from %t where cid = %d",array('pichome_collectcat',$data['cid']));
                $pathkey = str_replace('_','',$pathkey);
                $patharr = explode('-',$pathkey);
                foreach(DB::fetch_all("select catname from %t where cid in(%n)",array('pichome_collectcat',$patharr)) as $v){
                    $position .= '/'.$v['catname'];
                }
            }
            C::t('pichome_collect')->add_filenum_by_clid($data['clid'],$counti);
            $enventbodydata = ['username'=>getglobal('username'),'filename'=>$filename,'collectname'=>$position,'rids'=> $data['rid']];
            $enventdata = [
                'eventbody' =>'collectfile' ,
                'uid' => getglobal('uid'),
                'username' => getglobal('username'),
                'bodydata' => json_encode($enventbodydata),
                'clid' =>$data['clid'],
                'cid' =>($data['cid']) ? $data['cid']:0,
                'do' => 'collect_file',
                'do_obj' =>$filename,
                'dateline'=>TIMESTAMP
            ];
            C::t('pichome_collectevent')->insert($enventdata);
        }
        $this->update_collect_thumb($data['clid']);
        return true;

    }

    public function delete($id){
        if(!is_array($id)) $id = (array)$id;
        if(parent::delete($id)){
            //删除对应分享
            if(!empty($id))DB::delete('pichome_share','filepath in('.dimplode($id).') and stype = 1');
        }
        return true;
    }

    //取消收藏
    public function cancle_filecollect($lids,$clid,$cid = 0){
        $perm = C::t('pichome_collectuser')->get_perm_by_clid($clid);
        if($perm < 2){
            return array('error'=>'no_perm');
        }
        if(!is_array($lids)) $lids = (array)$lids;
        if(empty($lids)) return true;
        $counti = 0;
        $rids = [];
        foreach(DB::fetch_all("select rid,id,cid,clid from %t where id in(%n)",array($this->_table,$lids)) as $v){
            $rids[] = $v['rid'];
            if($this->delete($v['id'])){
                $counti += 1;
                C::t('pichome_collectcat')->add_filenum_by_cid($v['cid'],-1);
                C::t('pichome_collect')->add_filenum_by_clid($v['clid'],-1);
            }
        }
        $position = DB::result_first("select name from %t where clid = %d",array('pichome_collect',$clid));
        if($cid){
            $pathkey = DB::result_first("select pathkey from %t where cid = %d",array('pichome_collectcat',$cid));
            $pathkey = str_replace('_','',$pathkey);
            $patharr = explode('-',$pathkey);
            foreach(DB::fetch_all("select catname from %t where cid in(%n)",array('pichome_collectcat',$patharr)) as $v){
                $position .= '/'.$v['catname'];
            }
        }
        $namesarr = [];
        foreach(DB::fetch_all("select name from %t where rid in(%n) limit 0,5",array('pichome_resources',$rids)) as $v){
            $namesarr[] = $v['name'];
        }
        $filename = implode(',',$namesarr).'等'.$counti.'个文件';
        $enventbodydata = ['username'=>getglobal('username'),'filename'=>$filename,'collectname'=>$position,'rid'=>$rids];
        $enventdata = [
            'eventbody' =>'canclecollectfile' ,
            'uid' => getglobal('uid'),
            'username' => getglobal('username'),
            'bodydata' => json_encode($enventbodydata),
            'clid' =>$clid,
            'cid' =>($cid) ? $cid:0,
            'do' => 'cancle_collectfile',
            'do_obj' =>$filename,
            'dateline'=>TIMESTAMP
        ];
        C::t('pichome_collectevent')->insert($enventdata);
        $this->update_collect_thumb($clid);
        return true;
    }
    //更新收藏夹缩略图
    public function update_collect_thumb($clid){
        $first = false;
        $collectdata = C::t('pichome_collect')->fetch($clid);
        if(!C::t('pichome_collectlist')->fetch($collectdata['lid'])){
            $first = true;
        }else{
            $first = false;
        }
        $setarr = [];
        if($first){
            //取得第一张图
            $firstdata = DB::fetch_first("select * from %t where clid = %d order by id asc",array($this->_table,$clid));
            if($firstdata){
                $icondatas = C::t('pichome_resources')->geticondata_by_rid($firstdata['rid']);
                $setarr['covert'] = $icondatas['icondata'];
                $setarr['lid'] = $firstdata['id'];
            }else{
                $setarr['covert'] = '';
                $setarr['lid'] = 0;

            }
        }
        //取得最新两张图
        $i = 2;
        $coverdata = DB::fetch_all("select rid,id from %t where clid = %d order by id desc limit 0,2",array($this->_table,$clid));
        $count = count($coverdata);
        if($count  == 0){
            $setarr['covert1'] = '';
            $setarr['covert2'] = '';
            $setarr['lid1'] = 0;
            $setarr['lid2'] = 0;
        }elseif($count ==1) {
            foreach ($coverdata as $v) {
                $icondatas = C::t('pichome_resources')->geticondata_by_rid($v['rid']);
                $setarr['covert' . $i] = $icondatas['icondata'];
                $setarr['lid' . $i] = $v['id'];
                $i--;
            }
            $setarr['covert2'] = '';
            $setarr['lid2'] = 0;
        }else{
            foreach($coverdata as $v){
                $icondatas = C::t('pichome_resources')->geticondata_by_rid($v['rid']);
                $setarr['covert'.$i] = $icondatas['icondata'];
                $setarr['lid'.$i] = $v['id'];
                $i--;
            }
        }
        C::t('pichome_collect')->update($clid,$setarr);
        return true;
    }
    //删除收藏夹所有文件
    public function delete_by_clid($clid){
        return DB::delete($this->_table,array('clid'=>$clid));
    }

    //移动文件到某收藏
    public function move_collectfile($lids,$oclid,$ocid=0){

        if(!is_array($lids)) $lids = (array)$lids;
        if(empty($lids)) return true;

        $total = count($lids);
        $cids = [];
        $clid = 0;
        $counti = 0;
        $rids=[];
        foreach(DB::fetch_all("select * from %t where id in(%n)",array('pichome_collectlist',$lids)) as $v){
           $cids[] = $v['cid'];
           $clid = $v['clid'];
           //如果收藏位置相同则不做任何处理
           if($v['clid'] == $oclid && $v['cid'] == $ocid){
              /* if(parent::delete($v['id'])){
                   //收藏夹文件数和分类数减1
                   if($v['cid']) C::t('pichome_collectcat')->add_filenum_by_cid($v['cid'],-1);
                   if($v['clid'])C::t('pichome_collect')->add_filenum_by_clid($v['clid'],-1);
               }*/
               continue;
           }else{

               //如果该收藏文件在目标位置已经存在则删除原收藏位置文件
               if($id = DB::result_first("select id from %t where rid = %s and clid = %d and cid = %d",
                   array($this->_table,$v['rird'],$oclid,$ocid))){
                   $this->delete($id);
                    C::t('pichome_collect')->add_filenum_by_clid($clid,-1);
                   if($v['cid'])C::t('pichome_collectcat')->add_filenum_by_cid($v['cid'],-1);
               }else{
                   $setarr = [
                       'uid'=>getglobal('uid'),
                       'username'=>getglobal('username'),
                       'clid'=>$oclid,
                       'cid'=>$ocid
                   ];
                  /* echo $ocid;
                   print_r($v);die;*/
                   //更新数据
                   if(parent::update($v['id'],$setarr)){

                       //如果移动位置不在一个收藏夹
                       if($v['clid'] != $oclid){
                           //增加移入收藏夹文件数
                           C::t('pichome_collect')->add_filenum_by_clid($oclid,1);
                           if($v['clid']) C::t('pichome_collect')->add_filenum_by_clid($clid,-1);
                       }
                       if($v['cid'])C::t('pichome_collectcat')->add_filenum_by_cid($v['cid'],-1);
                       $counti++;
                       //增加移入分类文件数
                       if($ocid)C::t('pichome_collectcat')->add_filenum_by_cid($ocid,1);
                   }
               }

           }
            $rids[] = $v['rid'];
        }
        //如果移动文件位置cid为多个则不记入动态
        $cids = array_unique($cids);
        if(count($cids) == 1) $cid = $cids[0];
        else $cid = 0;

        $namesarr = [];
        foreach(DB::fetch_all("select name from %t where rid in(%n) limit 0,5",array('pichome_resources',$rids)) as $v){
            $namesarr[] = $v['name'];
        }
        $filename = implode(',',$namesarr).'等'.$total.'个文件';

        $oposition = DB::result_first("select name from %t where clid = %d",array('pichome_collect',$oclid));
        if($ocid){
            $pathkey = DB::result_first("select pathkey from %t where cid = %d",array('pichome_collectcat',$ocid));
            $pathkey = str_replace('_','',$pathkey);
            $patharr = explode('-',$pathkey);
            foreach(DB::fetch_all("select catname from %t where cid in(%n)",array('pichome_collectcat',$patharr)) as $v){
                $oposition .= '/'.$v['catname'];
            }
        }

        //更新移出收藏文件数
        $position = DB::result_first("select name from %t where clid = %d",array('pichome_collect',$clid));
        if($cid){

            $pathkey = DB::result_first("select pathkey from %t where cid = %d",array('pichome_collectcat',$cid));
            $pathkey = str_replace('_','',$pathkey);
            $patharr = explode('-',$pathkey);
            foreach(DB::fetch_all("select catname from %t where cid in(%n)",array('pichome_collectcat',$patharr)) as $v){
                $position .= '/'.$v['catname'];
            }
        }

        //移入动态
        $enventbodydata = ['username'=>getglobal('username'),'filename'=>$filename,'collectname'=>$position,'newcolletname'=>$oposition,'rid'=>$rids];
        $enventdata = [
            'eventbody' =>'movecollectfileto' ,
            'uid' => getglobal('uid'),
            'username' => getglobal('username'),
            'bodydata' => json_encode($enventbodydata),
            'clid' =>$oclid,
            'cid' =>($ocid) ? $ocid:0,
            'do' => 'move_collectfileto',
            'do_obj' =>$filename,
            'dateline'=>TIMESTAMP
        ];
        C::t('pichome_collectevent')->insert($enventdata);
        //更新移入文件收藏缩略图
        if($oclid != $clid) $this->update_collect_thumb($oclid);

        //移出动态
        $enventbodydata = ['username'=>getglobal('username'),'filename'=>$filename,'collectname'=>$position,'newcolletname'=>$oposition,'rid'=>$rids];
        $enventdata = [
            'eventbody' =>'delcollectfilefrom' ,
            'uid' => getglobal('uid'),
            'username' => getglobal('username'),
            'bodydata' => json_encode($enventbodydata),
            'clid' =>$clid,
            'cid' =>($cid) ? $cid:0,
            'do' => 'del_collectfilefrom',
            'do_obj' =>$filename,
            'dateline'=>TIMESTAMP
        ];
        C::t('pichome_collectevent')->insert($enventdata);
        //更新移出文件收藏缩略图
        if($clid && $oclid != $clid) $this->update_collect_thumb($oclid);
        return true;

    }
    //收藏已收藏文件到指定收藏
    public function collect_by_lid($lids,$clid,$cid=0){
        $perm = C::t('pichome_collectuser')->get_perm_by_clid($clid);
        if($perm < 2){
            return array('error'=>'no_perm');
        }
        if(!is_array($lids)) $lids = (array)$lids;
        if(empty($lids)) return true;
        $rids = [];
        foreach(DB::fetch_all("select rid,id from %t where id in(%n)",array($this->_table,$lids)) as $v){
            $rids[] = $v['rid'];
        }
        $existsrids = [];
        foreach(DB::fetch_all("select rid from %t where rid in(%n) and clid = %d and cid = %d",array($this->_table,$rids,$clid,$cid)) as $v){
            $existsrids[] = $v['rid'];
        }
        $totalcount = count($rids);
        $insertrids = array_diff($rids,$existsrids);
        //记录加入收藏个数
        $counti = 0;
        $namesarr = [];
        foreach(DB::fetch_all("select appid,rid,name from %t where rid in(%n)",array('pichome_resources',$insertrids)) as $v){
            $setarr = [
                'rid'=>$v['rid'],
                'cid'=>$cid,
                'clid'=>$clid,
                'uid'=>getglobal('uid'),
                'username'=>getglobal('username'),
                'dateline'=>TIMESTAMP,
                'appid'=>$v['appid'],
            ];
            $namesarr[] = $v['name'];
            if(parent::insert($setarr,1)) $counti += 1;
        }
        if($counti){
            $filenamearr = array_slice($namesarr,0,5);
            $filename = implode(',',$filenamearr).'等'.$totalcount.'个文件';
            //如果收藏有分类增加该分类下文件数
            $position = DB::result_first("select name from %t where clid = %d",array('pichome_collect',$clid));
            if($cid){
                C::t('pichome_collectcat')->add_filenum_by_cid($cid,$counti);
                $pathkey = DB::result_first("select pathkey from %t where cid = %d",array('pichome_collectcat',$cid));
                $pathkey = str_replace('_','',$pathkey);
                $patharr = explode('-',$pathkey);
                foreach(DB::fetch_all("select catname from %t where cid in(%n)",array('pichome_collectcat',$patharr)) as $v){
                    $position .= '/'.$v['catname'];
                }
            }
            C::t('pichome_collect')->add_filenum_by_clid($clid,$counti);
            $enventbodydata = ['username'=>getglobal('username'),'filename'=>$filename,'collectname'=>$position,'rids'=>$rids];
            $enventdata = [
                'eventbody' =>'collectfile' ,
                'uid' => getglobal('uid'),
                'username' => getglobal('username'),
                'bodydata' => json_encode($enventbodydata),
                'clid' =>$clid,
                'cid' =>$cid ? $cid:0,
                'do' => 'collect_file',
                'do_obj' =>$filename,
                'dateline'=>TIMESTAMP
            ];
            C::t('pichome_collectevent')->insert($enventdata);
        }
        $this->update_collect_thumb($clid);
        return true;
    }

    public function delete_by_rids($rids,$uid,$username){
        if(!$rids) $rids = (array)$rids;
        if(empty($rids)) return true;
        $clids = [];
        foreach(DB::fetch_all("select * from %t where rid in(%n)",array($this->_table,$rids)) as $v){
                $clids[$v['clid']][] = $v['id'];
        }
        foreach ($clids as $k=>$val){
            $clid = $k;
            $counti = 0;
            $rids = [];
            foreach(DB::fetch_all("select rid,id,cid,clid from %t where id in(%n)",array($this->_table,$val)) as $v){
                $rids[] = $v['rid'];
                if($this->delete($v['id'])){
                    $counti += 1;
                    if($v['cid'])C::t('pichome_collectcat')->add_filenum_by_cid($v['cid'],-1);
                    if($v['clid'])C::t('pichome_collect')->add_filenum_by_clid($v['clid'],-1);
                }
            }
            $position = DB::result_first("select name from %t where clid = %d",array('pichome_collect',$clid));
            $namesarr = [];
            foreach(DB::fetch_all("select name from %t where rid in(%n) limit 0,5",array('pichome_resources',$rids)) as $v){
                $namesarr[] = $v['name'];
            }
            $filename = implode(',',$namesarr).'等'.$counti.'个文件';
            $enventbodydata = ['username'=>getglobal('username'),'filename'=>$filename,'collectname'=>$position,'rid'=>$rids];
            $enventdata = [
                'eventbody' =>'canclecollectfile' ,
                'uid' => $uid,
                'username' => $username,
                'bodydata' => json_encode($enventbodydata),
                'clid' =>$clid,
                'cid' =>0,
                'do' => 'cancle_collectfile',
                'do_obj' =>$filename,
                'dateline'=>TIMESTAMP
            ];
            C::t('pichome_collectevent')->insert($enventdata);
            $this->update_collect_thumb($clid);
        }
        return true;
    }
}
