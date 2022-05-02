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
        $fids = [];
        foreach(DB::fetch_all("select fid from %t where appid = %s",array($this->_table,$appid)) as $v){
            $fids[] = $v['fid'];
        }
        $hookdata = ['appid'=>$appid];
        Hook::listen("delpichomefolderafter",$hookdata);
        return parent::delete($fids);
    }
    //插入和更新目录数据
    public function insert_folderdata_by_appid($appid,$folderdata,$folderarr = array(),$pfid='',$pathkey=''){
        foreach ($folderdata as $v) {
            $fid = $v['id'].$appid;
            $setarr=['fname'=>$v['name'],'dateline'=>TIMESTAMP,'pfid'=>$pfid,'appid'=>$appid,'pathkey'=>($pathkey)?$pathkey:$fid];
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
            parent::update($fid,$setarr);
        }else{
            parent::insert($setarr);
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
        foreach(DB::fetch_all("select fid,fname,pathkey,pfid from %t where appid = %s and password = '' and pfid = %s",array($this->_table,$appid,$pfid)) as $v){
            $v['level'] = $i;
            $j = $i+1;
            $v['children'] = $this->fetch_all_folder_by_appid($appid,$v['fid'],$j);
            $returndata[] = $v;
        }
        return $returndata;
    }

    public function fetch_folder_by_appid_pfid($appid,$pfid=[]){

        $folderdata = [];
        if(!empty($pfid)){
            foreach(DB::fetch_all("select fid,fname,pathkey,appid,pfid,filenum as nosubfilenum from %t where appid = %s and pfid in(%n) order by disp asc",array($this->_table,$appid,$pfid)) as $v){
                $v['filenum'] = DB::result_first("SELECT count(DISTINCT fr.rid) FROM %t fr 
                    left join %t f on fr.fid = f.fid
                    where fr.appid = %s and f.pathkey  like %s",array('pichome_folderresources','pichome_folder',$appid,$v['pathkey'].'%'));
                $v['leaf'] = DB::result_first("select count(*) from %t where pfid = %s",array($this->_table,$v['fid'])) ? false:true;
                $folderdata[] = $v;

            }
        }else{
            foreach(DB::fetch_all("select fid,fname,pathkey,appid,pfid,filenum as nosubfilenum from %t where appid = %s and pfid = '' order by disp asc",array($this->_table,$appid)) as $v){
                $v['filenum'] = DB::result_first("SELECT count(DISTINCT fr.rid) FROM %t fr 
                    left join %t f on fr.fid = f.fid
                    where fr.appid = %s and f.pathkey  like %s",array('pichome_folderresources','pichome_folder',$appid,$v['pathkey'].'%'));
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

}
