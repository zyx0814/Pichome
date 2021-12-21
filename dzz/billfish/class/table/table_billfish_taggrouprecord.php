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

class table_billfish_taggrouprecord extends dzz_table
{
    public function __construct() {
        $this->_table = 'billfish_taggrouprecord';
        $this->_pk    = 'id';
        parent::__construct();
    }

    public function insert_data($setarr,$appid){
        $hasrecord = false;
        if($cid = DB::result_first("select cid from %t where bcid=%d and appid = %s",array($this->_table,$setarr['gid'],$appid))){
           $data['cid'] = $cid;
            $hasrecord = true;
        }else{
            $data['cid'] = random(13).$appid;
        }
        $data['catname'] = $setarr['name'];
        $data['appid'] = $appid;
        $data['dateline'] = TIMESTAMP;
        if($cid = C::t('pichome_taggroup')->insert($data)){
            if(!$hasrecord) parent::insert(array('cid'=>$cid,'bcid'=>$setarr['gid'],'appid'=>$appid));
            return array('bcid'=>$setarr['gid'],'cid'=>$cid);
        }
    }
    public function delete_by_appid($appid){
        DB::delete($this->_table,array('appid'=>$appid));
    }

}

