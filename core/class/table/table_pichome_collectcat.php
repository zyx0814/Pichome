<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_collectcat extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_collectcat';
        $this->_pk = 'cid';
        $this->_pre_cache_key = 'pichome_collectcat';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }

    //为某个收藏夹添加分类
    public function add_cat_by_clid($setarr){

        $perm = C::t('pichome_collectuser')->get_perm_by_clid($setarr['clid']);
        if($perm < 2){
            return array('error'=>'no_perm');
        }
        if($setarr['cid']){
            $collectcatdata = parent::fetch($setarr['cid']);
            $cid = intval($setarr['cid']);
            Hook::listen('lang_parse',$setarr,['setCollectcatLangData']);
            unset($setarr['cid']);
           parent::update($cid,$setarr);
           if($setarr['catname'] != $collectcatdata['catname']){
               $position = DB::result_first("select name from %t where clid = %d",array('pichome_collect',$collectcatdata['clid']));
               $collectcatdata['pathkey'] = str_replace('_','',$collectcatdata['pathkey']);
               $patharr = explode('-',$collectcatdata['pathkey']);
               $currentcidindex = array_search($cid,$patharr);
               unset($patharr[$currentcidindex]);
               foreach(DB::fetch_all("select catname from %t where cid in(%n)",array($this->_table,$patharr)) as $v){
                   $position .= '/'.$v['catname'];
               }
               //添加事件
               $enventbodydata = ['username'=>getglobal('username'),'newcatname'=>$setarr['catname'],'catname'=>$collectcatdata['catname'],'postion'=>$position];
               $enventdata = [
                   'eventbody' =>'editcollectcat',
                   'uid' => getglobal('uid'),
                   'username' => getglobal('username'),
                   'bodydata' => json_encode($enventbodydata),
                   'clid' =>$setarr['clid'],
                   'cid' =>$cid,
                   'do' => 'edit_collectcat',
                   'do_obj' =>$setarr['catname'],
                   'dateline'=>TIMESTAMP
               ];
               C::t('pichome_collectevent')->insert($enventdata);
           }
        }else{
            if($cid = parent::insert($setarr,1)){
                $hookarr = $setarr;
                $hookarr['cid'] = $cid;
                Hook::listen('lang_parse',$setarr,['setCollectcatLangData']);
                $fpathkey = '';
                $position = DB::result_first("select name from %t where clid = %d",array('pichome_collect',$setarr['clid']));
                if($setarr['pcid']){
                    $fpathkey = DB::result_first("select pathkey from %t where cid = %d and clid = %d",array($this->_table,$setarr['pcid'],$setarr['clid']));
                    $ffpathkey = str_replace('_','',$fpathkey);
                    $patharr = explode('-',$ffpathkey);
                    foreach(DB::fetch_all("select catname from %t where cid in(%n)",array($this->_table,$patharr)) as $v){
                        $position .= '/'.$v['catname'];
                    }
                }
                $pathkey = ($fpathkey) ? $fpathkey.'-'.'_'.$cid.'_':'_'.$cid.'_';
                parent::update($cid,array('pathkey'=>$pathkey));
                //添加事件
                $enventbodydata = ['username'=>getglobal('username'),'catname'=>$setarr['catname'],'position'=>$position];
                $enventdata = [
                    'eventbody' =>'createcollectcat' ,
                    'uid' => getglobal('uid'),
                    'username' => getglobal('username'),
                    'bodydata' => json_encode($enventbodydata),
                    'clid' =>$setarr['clid'],
                    'cid' =>$cid,
                    'do' => 'create_collectcat',
                    'do_obj' =>$setarr['catname'],
                    'dateline'=>TIMESTAMP
                ];
                C::t('pichome_collectevent')->insert($enventdata);
            }
        }
        return $cid;

    }
    //删除收藏夹所有分类
    public function delete_by_clid($clid){
        //删除所有下级
        foreach(DB::fetch_all("select cid from %t where pcid = 0 and clid = %d",array($this->_table,$clid)) as $v){
            $this->real_delete_by_cid($v['cid'],$clid);
        }

    }

    public function delete_by_cid($cid){
        if(!$catdata = parent::fetch($cid)) return true;
        $perm = C::t('pichome_collectuser')->get_perm_by_clid($catdata['clid']);
        if($perm < 2){
            return array('error'=>'no_perm');
        }
        //删除所有下级
        $this->real_delete_by_cid($cid,$catdata['clid']);
        $position = DB::result_first("select name from %t where clid = %d",array('pichome_collect',$catdata['clid']));
        if($catdata['pcid']){
            $catdata['pathkey'] = str_replace('_','',$catdata['pathkey']);
            $patharr = explode('-',$catdata['pathkey']);
            $currentcidindex = array_search($cid,$patharr);
            unset($patharr[$currentcidindex]);
            foreach(DB::fetch_all("select catname from %t where cid in(%n)",array($this->_table,$patharr)) as $v){
                $position .= '/'.$v['catname'];
            }
        }

        //添加事件
        $enventbodydata = ['username'=>getglobal('username'),'catname'=>$catdata['name'],'position'=>$position];
        $enventdata = [
            'eventbody' =>'deletecollectcat' ,
            'uid' => getglobal('uid'),
            'username' => getglobal('username'),
            'bodydata' => json_encode($enventbodydata),
            'clid' =>$catdata['clid'],
            'cid' =>$cid,
            'do' => 'delete_collectcat',
            'do_obj' =>$catdata['catname'],
            'dateline'=>TIMESTAMP
        ];
        C::t('pichome_collectevent')->insert($enventdata);
        return true;
    }

    //删除所有下级
    public function real_delete_by_cid($cid,$clid){
        foreach(DB::fetch_all("select cid from %t where pcid = %d and clid = %d",array($this->_table,$cid,$clid)) as $v){
            $this->real_delete_by_cid($v['cid'],$clid);
        }

        //删除当前分类下所有文件
        $num = DB::result_first("select count(id) from %t where cid = %d and clid = %d",array('pichome_collectlist',$cid,$clid));
        if($num){
            C::t('pichome_collect')->add_filenum_by_clid($clid,-$num);
            DB::delete('pichome_collectlist',array('cid'=>$cid,'clid'=>$clid));
        }

        if(parent::delete($cid)){
            Hook::listen('lang_parse',$cid,['delCollectcatLangData']);
        }
    }

    public function add_filenum_by_cid($cids,$ceof = 1){
        if (!is_array($cids)) $cids = array($cids);

        if ($ceof > 0) {
            DB::query("update %t set filenum=filenum+%d where cid IN(%n)", array($this->_table, $ceof, $cids));
        } else {
            DB::query("update %t set filenum=filenum-%d where cid IN(%n)", array($this->_table, abs($ceof), $cids));
        }
        $this->clear_cache($cids);
    }

    //查询所有子集cid
    public function fetch_cid_by_pcid($pcid){
        $cids = [$pcid];
        $ppathkey = DB::result_first("select pathkey from %t where cid = %d",array($this->_table,$pcid));
        $ppathkey = str_replace('_','\_',$ppathkey);
        foreach(DB::fetch_all("select cid from %t where pathkey like %s",array($this->_table,$ppathkey.'%')) as $v){
            $cids[] = $v['cid'];
        }
        return $cids;
    }

    public function fetch_by_clid_pcid($clid,$pcid=0){
        $catdata = [];
        foreach(DB::fetch_all("select cid,catname,pathkey,pcid,filenum as nosubfilenum from %t where clid = %d and pcid =%d order by disp desc,cid asc",array($this->_table,$clid,$pcid)) as $v){
            $pathkey = str_replace('_','\_',$v['pathkey']);
            $v['filenum'] = DB::result_first("SELECT sum(filenum) FROM %t  where pathkey  like %s and clid = %d",array($this->_table,$pathkey.'%',$clid));
            $v['leaf'] = DB::result_first("select count(*) from %t where pcid = %d",array($this->_table,$v['cid'])) ? false:true;
            $catdata[] = $v;

        }
        Hook::listen('lang_parse',$catdata,['getCollectcatLangData',1]);
        Hook::listen('lang_parse',$catdata,['getCollectcatLangKey',1]);
        return $catdata;
    }

    public function search_by_catname($keyword,$clid=0){
        $catdata = [];
        $wheresql = ' catname like %s  ';
        $params = array($this->_table,'%'.$keyword.'%');
        if($clid){
            $wheresql .= ' and clid = %d ';
            $params[] = $clid;
        }
        foreach(DB::fetch_all("select catname,cid,pathkey,pcid from %t where  $wheresql",$params)as $v ){
            $catdata[$v['cid']] = $v;
        }
        foreach ($catdata as $k=>$v){
            $len=strlen($catdata[$k]['pathkey']);

            $catdata[$k]['len']=$len;
        }
        $cloumarr = array_column($catdata,'len');
        array_multisort($cloumarr,SORT_ASC,$catdata);
        return $catdata;
    }

}
