<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_vappmember extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_vappmember';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_vappmember';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }

   public function addmember($appid,$uids){//添加成员
        if(!is_array($uids)) $uids = (array)$uids;
        foreach($uids as $v){
            $setarr= [
               'appid'=>$appid,
               'uid'=>$v,
               'dateline'=>TIMESTAMP
            ];
            if(!DB::result_first("select id from %t where uid = %d and appid = %s",[$this->_table,$v,$appid])){
                parent::insert($setarr);
            }

        }
        return true;
   }
   public function delete_member_by_appid_uid($appid,$uids){
       if(!is_array($uids)) $uids = (array)$uids;
       $delids = [];
       foreach(DB::fetch_all("select id from %t where appid = %s and uid in(%n)",array($this->_table,$appid,$uids)) as $v){
           $delids[] = $v['id'];
       }
       return parent::delete($delids);
   }
   public function fetch_member_by_appid($appid){
        $uidarr = [];
        foreach(DB::fetch_all("select uid from %t where appid = %s",[$this->_table,$appid]) as $v){
            $uidarr[] = $v['uid'];
        }
        return $uidarr;
   }
   public  function delete_by_appid($appid){
       $delids = [];
       foreach(DB::fetch_all("select id from %t where appid = %s ",array($this->_table,$appid)) as $v){
           $delids[] = $v['id'];
       }
       return parent::delete($delids);
   }

    public function checkuserperm_by_uid($uid){
        if(getglobal('adminid') == 1) return true;
       return  DB::result_first("select id from %t vm left join %t v on vm.appid = v.appid where vm.uid = %d and v.isdelete < 1 ",array($this->_table,'pichome_vapp',$uid)) ? true:false;
    }
    public function checkuserperm_by_appid($appid){
        $uid = getglobal('uid');
        if(getglobal('adminid') == 1) return true;
        return  DB::result_first("select id from %t where uid = %d and appid = %s",array($this->_table,$uid,$appid)) ? true:false;
    }

}