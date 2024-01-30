<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
class table_pichome_folder extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_folder';
        $this->_pk = 'fid';
        $this->_pre_cache_key = 'pichome_folder';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }
    //根据appid删除目录
    public function delete_by_appid($appid){
        foreach(DB::fetch_all("select fid from %t where appid = %s",array($this->_table,$appid)) as $v){
            $this->delete_by_fid($v['fid']);
        }
        return true;
    }
    public function create_folder_by_appid($appid,$foldername,$pfid=''){
            $return = array('error'=>'create folder fail');
           /* if($fdata = DB::result_first("select fid,level from %t where appid = %s and fname = %s and pfid = %s",array($this->_table,$appid,$foldername,$pfid))){
                $return =  ['fid'=>$fdata['fid'],'level'=>$fdata['level']];
            }else{*/
                if($pfid)$parentdata = DB::fetch_first("select pathkey,`level` from %t where appid = %s and fid = %s",array($this->_table,$appid,$pfid));
                $fid = $this->creratefid_by_appid($appid);
                $level = isset($parentdata['level']) ? intval($parentdata['level']):0;
                $setarr = [
                    'fid'=>$fid,
                    'fname'=>$foldername,
                    'pathkey'=>isset($parentdata['pathkey']) ? $parentdata['pathkey'].$fid:$fid,
                    'level'=>$level,
                    'pfid'=>$pfid,
                    'dateline'=>TIMESTAMP*1000,
                    'appid'=>$appid
                ];

                if(parent::insert($setarr)){

                    $hookdata = ['appid'=>$appid,'fids'=>[$fid]];
                    Hook::listen('updatedataafter',$hookdata);
                    $return = ['fid'=>$fid,'level'=>$level,'pathkey'=>$setarr['pathkey']];
                }
           // }
            return $return;
    }

    //根据路径创建目录
    public function createfolerbypath($appid,$path, $pfid = '')
    {
        if (!$path) {
            if(!$pfid) return [];
            else {
               return DB::fetch_first("select pathkey,`level`,fid from %t where appid = %s and fid = %s",array($this->_table,$appid,$pfid));
            }
        } else {
            $patharr = explode('/', $path);
            //生成目录
            foreach ($patharr as $fname) {

                if (!$fname) continue;
                //判断是否含有此目录
                if ($fdata = DB::fetch_first("select fid,level,pathkey from %t where pfid=%s and appid=%s and fname=%s", array('pichome_folder', $pfid, $appid, $fname))) {
                    $pfid = $fdata['fid'];
                    $flevel = $fdata['level'];
                    $pathkey = $fdata['pathkey'];
                } else {
                    $parentfolder = C::t('pichome_folder')->fetch($pfid);
                    $flevel = isset($parentfolder['level']) ? intval($parentfolder['level']):0;
                    $fid = $this->createfidbyappid($appid);
                    if ($parentfolder) {
                        $pathkey = $parentfolder['pathkey'] . $fid;
                    } else {
                        $pathkey = $fid;
                    }
                    $setarr = [
                        'fid' => $fid,
                        'fname' => $fname,
                        'appid' => $appid,
                        'dateline' => TIMESTAMP*1000,
                        'pfid' => $pfid,
                        'pathkey' => $pathkey,
                        'level'=>$flevel
                    ];
                    if (parent::insert($setarr)) {
                        $hookdata = ['appid'=>$appid,'fids'=>[$fid]];
                       // Hook::listen('updatedataafter',$hookdata);
                        $pfid = $fid;

                    }
                }
            }
       }
        return ['fid'=>$pfid,'level'=>$flevel,'pathkey'=>$pathkey];
    }

    //移除目录
    public function remove_folder_data($fid,$filedel = 0){
       $folderdata = parent::fetch($fid);
       if(!$folderdata) return true;
       //如果当前目录有子级，先处理子级目录数据
       $childfids = DB::fetch_all("select fid from %t where pfid = %s",array($this->_table,$fid));
       if($childfids){
           foreach($childfids as $v){
               $this->remove_folder_data($v['fid'],$filedel);
           }
       }

       //如果是删除文件到回收站
        if($filedel){
            foreach(DB::fetch_all("select rid from %t where fid =%s and appid = %s ",array('pichome_folderresources',$fid,$folderdata['appid'])) as $v){
                //如果当前文件不存在于其它目录内，则移动文件到回收站，否则只移除该文件当前目录关系
                $num = DB::result_first("select count(id) from %t where rid = %s and appid = %s ",array('pichome_folderresources',$v['rid'],$folderdata['appid']));
                //$fidarr = explode(',',$v['fids']);

                if($num == 1){
                    C::t('pichome_resources')->update($v['rid'],['isdelete'=>1]);

                }
                C::t('pichome_folderresources')->delete_by_ridfid($v['rid'],$fid);
                //$this->add_filenum_by_fid($fid,-1);

            }

        }else{
            C::t('pichome_folderresources')->delete_by_fids($fid);
        }
        //删除目录数据
        return $this->delete($fid);

    }
    public function delete_by_fid($fid){
        if(!$folder = parent::fetch($fid)) return false;
        if(parent::delete($fid)){
            $hookdata = ['appid'=>$folder['appid'],'fids'=>[$fid]];
            Hook::listen('updatedataafter',$hookdata);
            $hookdatad = ['appid'=>$folder['appid']];
            Hook::listen("delpichomefolderafter",$hookdatad);
        }
    }
    public function update_name_by_fid($fid,$name){
        if(!$folder = parent::fetch($fid)) return false;
        if(parent::update($fid,['fname'=>$name])){
            $hookdata = ['appid'=>$folder['appid'],'fids'=>[$fid]];
            Hook::listen('updatedataafter',$hookdata);
        }
    }
    public function creratefid_by_appid($appid){
        $fid = random(13) . $appid;
        if(DB::result_first("select fid from %t where appid = %s and fid = %s",array($this->_table,$appid,$fid))){
           $fid =  $this->creratefid_by_appid($appid);
        }
        return $fid;
    }
    //插入和更新目录数据
    public function insert_folderdata_by_appid($appid,$folderdata,$folderarr = array(),$pfid='',$pathkey=''){
        foreach ($folderdata as $v) {
            $fid = $v['id'].$appid;
            $setarr=['fname'=>$v['name'],'dateline'=>TIMESTAMP*1000,'pfid'=>$pfid,'appid'=>$appid,'pathkey'=>($pathkey)?$pathkey:$fid];
            if($v['coverId']) $setarr['cover'] = $v['coverId'].$appid;
            if($v['password']) $setarr['password'] = $v['password'];
            if($v['passwordTips']) $setarr['passwordtips'] = trim($v['passwordTips']);
            //如果已经有该id时更新
            if($fname = DB::result_first("select fname from %t where fid = %s",array($this->_table,$fid))){
                parent::update($fid,$setarr);
                $setarr['fid'] =  $fid;

            }else{
                //如果插入数据失败跳过当前层级目录
                $setarr['fid'] = $fid;
                if(!$setarr['fname']) continue;
                if(!parent::insert($setarr))continue;
                else{
                    $hookdata = ['appid'=>$appid,'fids'=>[$fid]];
                    Hook::listen('updatedataafter',$hookdata);
                }
            }
            $folderarr[] = $setarr;
            if ($v['children']) {
                $tmpchild = $v['children'];
                foreach($tmpchild as $child){
                    $cfid = $child['id'].$appid;
                    $folderarr = $this->insert_folderdata_by_appid($appid,[$child],$folderarr,$fid,($pathkey) ? $pathkey.$cfid:$fid.$cfid);
                }


            }
        }
        return $folderarr;
    }

    public function insert_data($setarr){
        $fid = $setarr['fid'];
        if($fname = DB::result_first("select count(fid) from %t where fid = %s and appid = %s",array($this->_table,$setarr['fid'],$setarr['appid']))){
            unset($setarr['fid']);
            if(parent::update($fid,$setarr)){
                $hookdata = ['appid'=>$setarr['appid'],'fids'=>[$setarr['fid']]];
                Hook::listen('updatedataafter',$hookdata);
            }
        }else{
            if(parent::insert($setarr)){
                $hookdata = ['appid'=>$setarr['appid'],'fids'=>[$setarr['fid']]];
                Hook::listen('updatedataafter',$hookdata);
            }
        }
        return $fid;
    }
    //检查目录是否包含密码,多个目录时，只有都包含密码才视为包含密码
    public function check_haspasswrod($fids,$appid){
        $haspassword = 0;
        //组合目录id
        $haspasswordfids = [];
        foreach($fids as $val){
            //查找当前目录及其上级中任意一层包含密码，则视为该目录包含密码
            if(DB::result_first("select fid from %t where pathkey regexp %s and password != '' ",array($this->_table,'.*'.$val.'$'))){
                $haspasswordfids[$val] = 1;
            }else{
                $haspasswordfids[$val] = 0;
            }
        }
        foreach($haspasswordfids as $v){
            if(!$v) {
                $haspassword = 0;
                break;
            }else{
                $haspassword = 1;
            }
        }
        unset($haspasswordfids);
        unset($folderids);
        return $haspassword;
    }

    //根据fid判断当前目录及其上机是否有密码，如果当前目录不存在则返回2，存在密码返回1
    public function check_password_byfid($fid){
        if($data = parent::fetch($fid)){
            return  DB::result_first("select fid from %t where pathkey regexp %s and password != '' ",array($this->_table,'.*'.$fid.'$')) ? 1:0;
        }else{
            return 2;
        }
    }
    //转义查询语句当中的path
    public function path_transferred_meaning($path){
        return str_replace(array('\'','(',')','+','^','$','{','}','[',']','#'),array("\'",'\(','\)','\+','\^','\$','\{','\}','\[','\]','\#'),$path);
    }

    public function fetch_all_folder_by_appid($appid,$pfid='',$i=1){
        if($i>5) return [];
        foreach(DB::fetch_all("select fid,fname,pathkey,pfid,`level` as perm from %t where appid = %s and password = '' and pfid = %s",array($this->_table,$appid,$pfid)) as $v){
            $v['level'] = $i;
            $j = $i+1;
            $v['children'] = $this->fetch_all_folder_by_appid($appid,$v['fid'],$j);
            $returndata[] = $v;
        }
        return $returndata;
    }
  /*  public function fetch_folder_by_appid_pfid($appid,$pfid=[]){
        global $_G;
        $ulevel = ($_G['uid']) ? $_G['pichomelevel']:0 ;
        $folderdata = [];
        if(!empty($pfid)){
            foreach(DB::fetch_all("select fid,fname,pathkey,appid,pfid,`level` as perm from %t where appid = %s and pfid in(%n) order by disp asc",array($this->_table,$appid,$pfid)) as $v){
               $v['nosubfilenum'] =  DB::result_first("SELECT count(DISTINCT fr.rid) FROM %t fr 
                    left join %t r on fr.rid = r.rid
                    where fr.appid = %s  and fr.fid  = %s and r.level <= %d",array('pichome_folderresources','pichome_resources',$appid,$v['fid'],$ulevel));
                $v['filenum'] = DB::result_first("SELECT count(DISTINCT fr.rid) FROM %t fr 
                    left join %t f on fr.fid = f.fid
                    left join %t r on fr.rid = r.rid
                    where fr.appid = %s and f.pathkey  like %s and r.level <= %d ",array('pichome_folderresources','pichome_folder','pichome_resources',$appid,$v['pathkey'].'%',$ulevel));
                $v['leaf'] = DB::result_first("select count(*) from %t where pfid = %s",array($this->_table,$v['fid'])) ? false:true;
                $folderdata[] = $v;

            }
        }else{
            foreach(DB::fetch_all("select fid,fname,pathkey,appid,pfid,`level` as perm from %t where appid = %s and pfid = '' order by disp asc",array($this->_table,$appid)) as $v){
                $v['nosubfilenum'] =  DB::result_first("SELECT count(DISTINCT fr.rid) FROM %t fr 
                    left join %t r on fr.rid = r.rid
                    where fr.appid = %s and fr.fid  = %s and r.level <= %d ",array('pichome_folderresources','pichome_resources',$appid,$v['fid'],$ulevel));
                $v['filenum'] = DB::result_first("SELECT count(DISTINCT fr.rid) FROM %t fr 
                    left join %t f on fr.fid = f.fid
                    left join %t r on fr.rid = r.rid
                    where fr.appid = %s  and f.pathkey  like %s and r.level <= %d",array('pichome_folderresources','pichome_folder','pichome_resources',$appid,$v['pathkey'].'%',$ulevel));
                $v['leaf'] = DB::result_first("select count(*) from %t where pfid = %s",array($this->_table,$v['fid'])) ? false:true;

                $folderdata[] = $v;

            }
        }
        return $folderdata;
    }*/

    public function fetch_folder_by_appid_pfid($appid,$pfid=[]){

        $folderdata = [];
        if(!empty($pfid)){
            foreach(DB::fetch_all("select fid,fname,pathkey,appid,pfid,filenum as nosubfilenum,level as perm from %t where appid = %s and pfid in(%n) order by disp asc",array($this->_table,$appid,$pfid)) as $v){
                $v['filenum'] = DB::result_first("SELECT count(DISTINCT fr.rid) FROM %t fr 
                    left join %t f on fr.fid = f.fid left join %t r on r.rid=fr.rid
                    where fr.appid = %s and f.pathkey  like %s and r.isdelete < 1",array('pichome_folderresources','pichome_folder','pichome_resources',$appid,$v['pathkey'].'%'));
                $v['leaf'] = DB::result_first("select count(*) from %t where pfid = %s",array($this->_table,$v['fid'])) ? false:true;
                $folderdata[] = $v;

            }
        }else{
            foreach(DB::fetch_all("select fid,fname,pathkey,appid,pfid,filenum as nosubfilenum,level as perm from %t where appid = %s and pfid = '' order by disp asc",array($this->_table,$appid)) as $v){
                $v['filenum'] = DB::result_first("SELECT count(DISTINCT fr.rid) FROM %t fr 
                    left join %t f on fr.fid = f.fid left join %t r on r.rid=fr.rid
                    where fr.appid = %s and f.pathkey  like %s and r.isdelete < 1",array('pichome_folderresources','pichome_folder','pichome_resources',$appid,$v['pathkey'].'%'));
                $v['leaf'] = DB::result_first("select count(*) from %t where pfid = %s",array($this->_table,$v['fid'])) ? false:true;
                $folderdata[] = $v;

            }
        }
        return $folderdata;
    }

    public function search_by_fname($keyword,$appid=''){
        $folderdata = [];
        $wheresql = ' fname like %s  ';
        $params = array($this->_table,'%'.$keyword.'%');
        if($appid){
            $wheresql .= ' and appid = %s ';
            $params[] = $appid;
        }
        $appids =[];
        foreach(DB::fetch_all("select appid,view from %t where isdelete = 0 order by disp ",array('pichome_vapp')) as $v){
            if (!C::t('pichome_vapp')->getpermbypermdata($v['view'],$v['appid'])) {
                continue;
            }
           $appids[] = $v['appid'];
        }
        $wheresql .= " and appid in(%n) ";
        $params[] = $appids;
        foreach(DB::fetch_all("select fname,fid,pathkey,appid,pfid from %t where  $wheresql",$params)as $v ){
            $folderdata[$v['fid']] = $v;
        }
        foreach ($folderdata as $k=>$v){
            $len=strlen($folderdata[$k]['pathkey']);

            $folderdata[$k]['len']=$len;
        }
        $cloumarr = array_column($folderdata,'len');
        array_multisort($cloumarr,SORT_ASC,$folderdata);
        return $folderdata;
    }

    public function createfidbyappid($appid){
        $fid = random(13).$appid;
        if(DB::result_first("select count(fid) from %t where fid = %s and appid = %s",array($this->_table,$fid,$appid))){
            $fid = $this->createfidbyappid($appid);
        }
        return $fid;

    }

    public function delete_by_fids($fids){
        if(!is_array($fids)) $fids = (array)$fids;
        if(!empty($fids)){
            DB::delete('pichome_folderresources',"fid in (".dimplode($fids).")");
            parent::delete($fids);
            return true;
        }

    }
    //移动目录到指定位置
    public function move_to_fidandpfid($fid,$pfid='',$disp=0){
        $fdata = parent::fetch($fid);
        $ofpathkey = $fpathkey = '';
        if($pfid) $fpathkey = DB::result_first("select pathkey from %t where fid = %s",array($this->_table,$pfid));

        if($fdata['pfid']) $ofpathkey = DB::result_first("select pathkey from %t where pfid = %s",array($this->_table,$fdata['pfid']));
        if($fdata['pfid']==$pfid){
            self::update_disp($fid,$pfid,$disp);
            return true;
        }
        if(parent::update($fid,['pid'=>$pfid,'disp'=>$disp])){
            foreach(DB::fetch_all("select fid, pathkey from %t where pathkey like %s",array($this->_table,$fdata['pathkey'])) as $v){
                if($v['fid']==$fid){
                    $npathkey = $fpathkey.$v['fid'];
                }else{
                    $npathkey = str_replace($ofpathkey,$fpathkey,$v['pathkey']);
                }
                if(parent::update($v['fid'],['pathkey'=>$npathkey])){

                }
            }
            self::update_disp($fid,$pfid,$disp);
            return true;
        }
        return false;
    }
    public function fetch_allfolder_by_fid($fid){
        $pathkey = DB::result_first("select pathkey from %t where fid = %s",array($this->_table,$fid));
        $fidarr = str_split($pathkey,19);
        $folderdata = [];
        foreach(DB::fetch_all("select fid,fname,pathkey from %t where fid in(%n) order by pathkey asc",array($this->_table,$fidarr)) as $v){
            $folderdata[] = $v;
        }
        return $folderdata;
    }
    public function update_perm_by_fid_appid($appid,$perm=0,$fid='',$hassub=1,$forceset=0){
        if(!$appid) return false;
        if(!$fid){//如果没有fid值，视为修改库权限
            if($hassub){
                DB::update($this->_table,['level'=>$perm],['appid'=>$appid]);
               /* foreach(DB::fetch_all("select fid from %t  where appid = %s",array($this->_table,$appid)) as $v){
                    parent::update($v['fid'],['level'=>$perm]);
                    //echo $v['fid'];die;
                    if($forceset)C::t('pichome_resources')->update_perm_by_appid_fid($appid,$perm,$v['fid'],1);
                }*/
                //设置所有文件权限
                if($forceset)C::t('pichome_resources')->update_perm_by_appid_fid($appid,$perm,'',1);
            }else{
                //设置无目录文件权限
                if($forceset)C::t('pichome_resources')->update_perm_by_appid_fid($appid,$perm,'');
            }
        }
        else{
            //如果有fid时
            if($hassub){
                $cpathkey = DB::result_first("select pathkey from %t where fid = %s and appid = %s",array($this->_table,$fid,$appid));
                foreach(DB::fetch_all("select fid from %t where appid = %s and pathkey like %s",[$this->_table,$appid,$cpathkey.'%']) as $v){
                    parent::update($v['fid'],['level'=>$perm]);
                    if($forceset)C::t('pichome_resources')->update_perm_by_appid_fid($appid,$perm,$v['fid']);
                }
            }else{
                parent::update($fid,['level'=>$perm]);
                if($forceset) C::t('pichome_resources')->update_perm_by_appid_fid($appid,$perm,$fid);
            }

        }
    }

    public function remove_foldercver_by_rids($rids){
        if(!is_array($rids)) $rids = (array)$rids;
        foreach(DB::fetch_all("select fid from %t where cover in(%n)",array($this->_table,$rids)) as $v){
            parent::update($v['fid'],['cover'=>'']);
        }
    }

    //查询当前分类所有下级fid
    public function fetch_fid_by_pfid($pfid){
        $fids = [];
        $pathkey =  DB::result_first("select pathkey from %t where fid = %s ",array($this->_table,$pfid));
        foreach(DB::fetch_all("select fid from %t where pathkey like %s",array($this->_table,str_replace('_','\_',$pathkey).'%')) as $v){
            $fids[] = $v['fid'];
        }
        return $fids;
    }

    public function fetch_folderdata_by_fid($fid){
        if(!$folder=parent::fetch($fid)) return false;
        return $folder;
    }
    public function update_data_by_fids($appid,$fids,$data){
        if(!is_array($fids)) $fids = (array)$fids;
        if(parent::update($fids,$data)){
            $hookindex = ['fids'=>$fids,'appid'=>$appid];
            Hook::listen('updatedataafter',$hookindex);
        }

    }

    public function fetch_by_fid($fid){
        if(!$folder=parent::fetch($fid)) return false;
        $foldertags = explode(',',$folder['tag']);
        $folder['tagdata'] = [];
        foreach(DB::fetch_all("select tid,tagname from %t where tid in(%n)",['pichome_tag',$foldertags]) as $v){
            $folder['tagdata'][$v['tid']] = $v['tagname'];
        }
        return $folder;
    }

    public function add_filenum_by_fid($fids,$ceof = 1){
        if (!is_array($fids)) $fids = array($fids);

        if ($ceof > 0) {
            DB::query("update %t set filenum=filenum+%d where fid IN(%n)", array($this->_table, $ceof, $fids));
        } else {
            DB::query("update %t set filenum=filenum-%d where fid IN(%n)", array($this->_table, abs($ceof), $fids));
        }
    }
}
