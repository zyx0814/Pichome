<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 *
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_eagle_folderrecord extends dzz_table
{
    public function __construct() {
        $this->_table = 'eagle_folderrecord';
        $this->_pk    = 'id';
        parent::__construct();
    }

    public function createfidby_efidappid($efid,$appid){
        //如果没有记录值
        if (!$fid = DB::result_first("select fid from %t where efid = %s and appid = %s", array($this->_table, $efid,$appid))){
            //检查是否有旧版数据记录值
            $fid = $efid.$appid;
            if(!DB::result_first("select fid from %t where fid = %s and appid = %s",array('pichome_folder',$fid,$appid))){
                $fid = C::t('pichome_folder')->createfidbyappid($appid);
            }
            $setarr = [
                'appid'=>$appid,
                'efid'=>$efid,
                'fid'=>$fid
            ];
            parent::insert($setarr);
        }
        return $fid;
    }
    public function insert_folderdata_by_appid($appid,$folderdata,$pfid='',$pathkey='',$fids=[])
    {
        foreach ($folderdata as $k => $v) {
            $id = $v['id'];
            //获取或生成记录fid值
            $fid = $this->createfidby_efidappid($id,$appid);
            $setarr=[
                'fid'=>$fid,
                'fname'=>$v['name'],
                'dateline'=>TIMESTAMP,
                'pfid'=>$pfid,
                'appid'=>$appid,
                'password'=>$v['password'],
                'passwordtips'=>$v['passwordTips'],
                'pathkey'=> ($pathkey)?$pathkey.$fid:$fid,
                'disp'=>$k];
            $fid = C::t('pichome_folder')->insert_data($setarr);
            $fids[] = $id;
            if($v['children']){
                $fids = $this->insert_folderdata_by_appid($appid,$v['children'],$fid,($pathkey)?$pathkey.$fid:$fid,$fids);

            }

        }
        return $fids;

    }


    public function delete_by_fid($fids,$appid){
        if(!is_array($fids)) $fids = (array)$fids;
        $delfid = $delid = [];
        foreach(DB::fetch_all("select fid,id from %t where fid in(%n) and appid = %s",array($this->_table,$fids,$appid)) as $v){
            $delfid[] = $v['fid'];
            $delid[] = $v['id'];
        }
        if(!empty($delfid))C::t('pichome_folder')->delete_by_fids($delfid);
        parent::delete($delid);
    }

    public function delete_by_ids($ids){
        if(!array($ids)) $ids = (array)$ids;
        $fids = [];
        foreach(DB::fetch_all("select fid from %t where id in(%n)",array($this->_table,$ids)) as $v){
            $fids[] = $v['fid'];
        }
        if(!empty($fids))C::t('pichome_folder')->delete_by_fids($fids);
        parent::delete($ids);
    }

    public function delete_by_appid($appid){
        DB::delete($this->_table,array('appid'=>$appid));
    }

    public function fetch_fid_by_efid($efids,$appid){
        $fids = [];
        foreach(DB::fetch_all("select fid from %t where efid in(%n) and appid = %s",array($this->_table,$efids,$appid)) as $v){
            $fids[] = $v['fid'];
        }
        return $fids;
    }

}

