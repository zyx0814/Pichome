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
           return parent::delete($fids);
       }
       //插入和更新目录数据
       public function insert_folderdata_by_appid($appid,$folderdata,$folderarr = array(),$pfid='',$pathkey=''){
           foreach ($folderdata as $v) {
               $fid = $v['id'].$appid;
               $setarr=['fname'=>$v['name'],'dateline'=>TIMESTAMP,'pfid'=>$pfid,'appid'=>$appid,'pathkey'=>$pathkey.$fid];
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
                   if(!parent::insert($setarr))continue;
               }
               $folderarr[] = $setarr;
               if ($v['children']) {
                   $tmpchild = $v['children'];
                   foreach($tmpchild as $child){
                       $cfid = $child['id'].$appid;
                       $setarr=['fname'=>$child['name'],'dateline'=>TIMESTAMP,'pfid'=>$fid,'appid'=>$appid,'pathkey'=>$pathkey.$fid.$cfid];
                       if($child['coverId']) $setarr['cover'] = $child['coverId'].$appid;
                       if($child['password']) $setarr['password'] = $child['password'];
                       if($v['passwordTips']) $setarr['passwordtips'] = trim($child['passwordTips']);
                   }
                   $folderarr = $this->insert_folderdata_by_appid($appid,$tmpchild,$folderarr,$fid,$pathkey.$fid.$cfid);
            
               }
           }
           return $folderarr;
       }
       //检查目录是否包含密码,多个目录时，只有都包含密码才视为包含密码
       public function check_haspasswrod($folderids,$appid){
            $haspassword = 0;
            //组合目录id
            $fids = [];
            foreach($folderids as $v){
                $fids[] = $v.$appid;
            }
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
        
        public function fetch_all_folder_by_appid($appid,$pfid=''){
          foreach(DB::fetch_all("select fid,fname from %t where appid = %s and password = '' and pfid = %s",array($this->_table,$appid,$pfid)) as $v){
              $v['num'] =  C::t('pichome_folderresources')->get_sum_by_fid($v['fid']);
              $v['children'] = $this->fetch_all_folder_by_appid($appid,$v['fid']);
              $returndata[] = $v;
          }
          return $returndata;
        }
    }
