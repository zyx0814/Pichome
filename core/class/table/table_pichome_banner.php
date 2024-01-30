<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_banner extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_banner';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_banner';
        //$this->_cache_ttl = 3600;
        parent::__construct();
    }

    public function insert_data($setarr){
        if($setarr['id']){
            if(parent::update($setarr['id'],$setarr)) return $setarr['id'];
        }else{
            if($id = parent::insert($setarr,1)){
                if($setarr['pid'])$fpathkey = DB::result_first("select pathkey from %t where id = %d",array($this->_table,$setarr['pid']));
                else $fpathkey = '';
                $pathkey = ($fpathkey) ? $fpathkey.'-_'.$id.'_':'_'.$id.'_';
                parent::update($id,['pathkey'=>$pathkey]);
                return $id;
            }
        }
        return $setarr['id'];
    }

    public function fetch_by_pid($pid=0){
        $bannerdata = [];
         foreach(DB::fetch_all("select * from %t where pid = %d order by disp",array($this->_table,$pid)) as $v){
            if($v['icon']){
                $v['iconpath'] = getglobal('siteurl').'index.php?mod=io&op=getfileStream&path='.dzzencode('attach::'.$v['icon']);
                $v['filters'] = unserialize($v['filters']);
            }
            $bannerdata[] = $v;
         }
         return $bannerdata;
    }
    //删除栏目
    public function delete_by_id($id){
        if(!$bdata = parent::fetch($id)) return false;
        foreach(DB::fetch_all("select id,bdata from %t where pathkey like %s",[$this->_table,str_replace('_','\_',$bdata['pathkey']).'%']) as $v){
            if(C::t('pichome_route')->delete_by_abid($v['bdata'],1)){
                parent::delete($v['id']);
            }
        }
        return true;
    }
    public function fetch_bannerbasic_by_bid($bid){
        if(!$bannerdata = parent::fetch($bid)) return false;
        if($bannerdata['icon']) $bannerdata['iconpath'] = IO::getFileuri('attach::'.$bannerdata['icon']);
        return $bannerdata;
    }
   /* public function getpermbypermdata($permdata, $perm = '')
    {
        global $_G;
        $uid = isset($_G['uid']) ? $_G['uid'] : 0;
        if ($_G['adminid'] == 1) return true;
        if ($perm == 'download' && (isset($_G['config']['pichomeclosedownload']) && $_G['config']['pichomeclosedownload'])) {
            return false;
        }

        if ($perm == 'share' && (isset($_G['config']['pichomecloseshare']) && $_G['config']['pichomecloseshare'])) {
            return false;
        }
        $uorgids = [];
        if ($uid && $_G['adminid'] != 1) {
            //获取用户机构部门数据
            foreach (DB::fetch_all("select ou.orgid,o.pathkey from %t ou left join %t o on o.orgid=ou.orgid 
                where ou.uid = %d", array('organization_user', 'organization', $uid)) as $v) {
                $tmporgids = explode(',', str_replace('-', '', $v['pathkey']));
                $torgids = [];
                foreach ($tmporgids as $ov) {
                    $tmpgid = explode('_', $ov);
                    $torgids = array_merge($torgids, $tmpgid);
                }
                $torgids = array_unique(array_filter($torgids));
                $uorgids = array_merge($uorgids, $torgids);
            }
        }

        if (!$permdata) return false;
        if ($permdata === '1') return true;
        $hasother = false;
        //判断是否包含无用户组用户
        if (isset($permdata['groups'])) {
            if (in_array('other', $permdata['groups'])) {
                $otherindex = array_search('other', $permdata['groups']);
                unset($permdata['groups'][$otherindex]);
                $hasother = true;
            }
        }
        //判断有权限用户中是否有当前用户
        if ($permdata['uids'] || $hasother) {
            //查询无组用户
            if ($hasother) {
                foreach (DB::fetch_all("select u.uid from %t u left join %t ou on u.uid=ou.uid where 1", array('user', 'organization_user')) as $u) {
                    $permdata['uids'][] = $u['uid'];
                }
            }
            if (in_array($uid, $permdata['uids'])) return true;
        }
        //判断有权限组中是否有当前用户
        if ($permdata['groups']) {
            $intersectarr = array_intersect($permdata['groups'], $uorgids);
            if (!empty($intersectarr)) return true;
        }
        return false;

    }*/
    public function update_disp($id,$pid,$position){
        $data=parent::fetch($id);
        foreach(DB::fetch_all("select * from %t where pid = %d and id!=%d order by disp,dateline",array($this->_table,$pid,$data['id'])) as $key => $value){
            if($key>=$position){
                $disp=$key+1;
            }else{
                $disp=$key;
            }
            $value['disp']=$disp;
            parent::update($value['id'],array('disp'=>$disp));
        }
        return parent::update($id,array('disp'=>$position));
    }
    //移动栏目到
    public function move_to_pid($id,$pid=0,$disp=0){
        $bdata = parent::fetch($id);
        $ofpathkey = $fpathkey = '';
        if($pid) $fpathkey = DB::result_first("select pathkey from %t where id = %d",array($this->_table,$pid));

        if($bdata['pid']) $ofpathkey = DB::result_first("select pathkey from %t where id = %d",array($this->_table,$bdata['pid']));
        if($bdata['pid']==$pid){
            self::update_disp($id,$pid,$disp);
            return true;
        }
        if(parent::update($id,['pid'=>$pid,'disp'=>$disp])){
            foreach(DB::fetch_all("select id, pathkey from %t where pathkey like %s",array($this->_table,str_replace('_','\_',$bdata['pathkey']).'%')) as $v){
                if($v['id']==$id){
                    $npathkey = $fpathkey.'-_'.$v['id'].'_';
                }else{
                    $npathkey = str_replace($ofpathkey,$fpathkey,$v['pathkey']);
                }
                if(parent::update($v['id'],['pathkey'=>$npathkey])){

                }
            }
            self::update_disp($id,$pid,$disp);
            return true;
        }
        return false;
    }

    public function getbannerlist($pid=0,$isshow=0){
        global $_G;
        $pathinfo = C::t('setting')->fetch('pathinfo');
        $params = [$this->_table,$pid];
        $wheresql = ' pid = %d ';
        if($isshow){
            $wheresql .= ' and isshow = %d ';
            $params[] = 1;
        }
        $bannerlist = [];
        foreach(DB::fetch_all("select * from %t where $wheresql order by disp asc",$params) as $v){
            if($v['icon']){
                $v['icon'] = getglobal('siteurl').'index.php?mod=io&op=getfileStream&path='.dzzencode('attach::'.$v['icon']);
            }else{
                $v['icon'] = 0;
            }
            $v['soucresname'] = '';
            if($v['btype'] == 0){
                $v['soucresname'] = DB::result_first("select appname from %t where appid = %s",['pichome_vapp',$v['bdata']]);
            }elseif($v['btype'] == 1){
                $v['soucresname'] = DB::result_first("select name from %t where id = %d",['pichome_smartdata',$v['bdata']]);
            }elseif($v['btype'] == 2){
                $v['soucresname'] = DB::result_first("select pagename from %t where id = %d",['pichome_templatepage',$v['bdata']]);
            }elseif($v['btype'] == 3){
                $v['soucresname'] = $v['bdata'];
            }
            if($v['btype'] == 3){
                $url = $v['bdata'];
            }elseif($v['btype'] == 4){
                $url = 'index.php?mod=banner&op=index#id=tb_'.$v['bdata'];
            }else{
                $url = 'index.php?mod=banner&op=index#id='.$v['bdata'];
            }
            $v['realurl'] = $url;
            if($pathinfo) $path = C::t('pichome_route')->feth_path_by_url($url);
            else $path = '';
            if($path){
                $v['url'] = $path;
            }else{
                $v['url'] = '';
            }
            $v['children']=$this->getbannerlist($v['id'],$isshow);
            if(!$v['pid']){
                if($v['isbottom']){
                    $bannerlist['bottom'][] = $v;
                }else{
                    $bannerlist['top'][] = $v;
                }
            }else{
                $bannerlist[] = $v;
            }

        }
        return $bannerlist;
    }
}