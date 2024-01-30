<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_collect extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_collect';
        $this->_pk = 'clid';
        $this->_pre_cache_key = 'pichome_collect';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }
    //新建和更新收藏收藏
    public function addcollect($setarr){
      //如果有clid为更新收藏夹
      if($setarr['clid']){
          $clid = $setarr['clid'];
          $olddata = parent::fetch($clid);
          $perm = C::t('pichome_collectuser')->get_perm_by_clid($clid);
          if($perm < 3){
              return array('error'=>'no_perm');
          }
          unset($setarr['clid']);
          parent::update($clid,$setarr);
          if($setarr['name'] != $olddata['name']){
              $enventbodydata = ['username'=>$setarr['username'],'name'=>$olddata['name'],'newname'=>$setarr['name']];
              $enventdata = [
                  'eventbody' =>'editcollect' ,
                  'uid' => getglobal('uid'),
                  'username' => getglobal('username'),
                  'bodydata' => json_encode($enventbodydata),
                  'clid' =>$clid,
                  'do' => 'create_collect',
                  'do_obj' =>$setarr['name'],
                  'dateline'=>TIMESTAMP
              ];
              C::t('pichome_collectevent')->insert($enventdata);
          }
      }else{
          //新增收藏夹
          if($clid = parent::insert($setarr,1)){
                //插入创建用户为创始人
                $userarr = ['uid'=>$setarr['uid'],'clid'=>$clid,'perm'=>4];
                C::t('pichome_collectuser')->insert($userarr);
                $enventbodydata = ['username'=>$setarr['username'],'name'=>$setarr['name']];
                $enventdata = [
                    'eventbody' =>'createcollect' ,
                    'uid' => getglobal('uid'),
                    'username' => getglobal('username'),
                    'bodydata' => json_encode($enventbodydata),
                    'clid' =>$clid,
                    'do' => 'create_collect',
                    'do_obj' =>$setarr['name'],
                    'dateline'=>TIMESTAMP
                ];
                C::t('pichome_collectevent')->insert($enventdata);
          }
      }
      return $clid;
    }

    public function add_filenum_by_clid($clids,$ceof = 1){
        if (!is_array($clids)) $clids = array($clids);

        if ($ceof > 0) {
            DB::query("update %t set filenum=filenum+%d where clid IN(%n)", array($this->_table, $ceof, $clids));
        } else {
            DB::query("update %t set filenum=filenum-%d where clid IN(%n)", array($this->_table, abs($ceof), $clids));
        }
        $this->clear_cache($clids);
    }

    //删除收藏夹
    public function delete_by_clid($clid){
        if(!$collectdata = parent::fetch($clid)) return false;
        $perm = C::t('pichome_collectuser')->get_perm_by_clid($clid);
        if($perm < 3){
            return array('error'=>'no_perm');
        }else{
            //删除收藏夹中文件
            C::t('pichome_collectlist')->delete_by_clid($clid);
            //删除收藏夹分类
            C::t('pichome_collectcat')->delete_by_clid($clid);
            //删除收藏夹用户
            C::t('pichome_collectuser')->delete_by_clid($clid);
            //删除收藏的分享
            DB::delete('pichome_share',array('clid'=>$clid));
            //删除收藏夹
           if(parent::delete($clid)){
               $enventbodydata = ['username'=>getglobal('username'),'newname'=>$collectdata['name']];
               $enventdata = [
                   'eventbody' =>'deletecollect' ,
                   'uid' => getglobal('uid'),
                   'username' => getglobal('username'),
                   'bodydata' => json_encode($enventbodydata),
                   'clid' =>$clid,
                   'do' => 'delete_collect',
                   'do_obj' =>$collectdata['name'],
                   'dateline'=>TIMESTAMP
               ];
               C::t('pichome_collectevent')->insert($enventdata);
           }
        }
        return true;
    }

    //获取有权限访问的收藏夹及其访问权限
    public function fetch_all_collectandperm($uid=0){
        if(!$uid) $uid = getglobal('uid');
        //foreach(DB::fetch_all("select c.*,cu.perm from %t where "))
    }

    public function set_collect_covert($lid,$clid){
        $perm = C::t('pichome_collectuser')->get_perm_by_clid($clid);
        if($perm < 2){
            return array('error'=>'no_perm');
        }
        $data = DB::fetch_first("select rid from %t where id = %d and clid = %d ",array('pichome_collectlist',$lid,$clid));
        $icondatas = C::t('pichome_resources')->geticondata_by_rid($data['rid']);
        $setarr['covert'] = $icondatas['icondata'];
        $setarr['lid'] =$lid;
        return parent::update($clid,$setarr);

    }


}
