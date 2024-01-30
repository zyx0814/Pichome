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

class table_eagle_record extends dzz_table
{
    public function __construct() {
        $this->_table = 'eagle_record';
        $this->_pk    = 'id';
        parent::__construct();
    }

    public function insert_data($eid,$setarr){
            if(C::t('pichome_resources')->insert($setarr)){
                $setarr1['rid'] = $setarr['rid'];
                $setarr1['appid'] = $setarr['appid'];
                $setarr1['eid'] = $eid;
                $setarr1['dateline']=$setarr['lastdate'];
                $id = DB::result_first("select id from %t where eid = %s and appid = %s",array($this->_table,$eid,$setarr['appid']));
                if($id){
                    parent::update($id,$setarr1);
                }else{
                    parent::insert($setarr1);
                }
            }
            return true;

    }
    public function delete_by_appid($appid){
        DB::delete($this->_table,array('appid'=>$appid));
    }
    public function delete_by_rids($rids){
        if (!is_array($rids)) $rids = (array)$rids;
        DB::delete($this->_table,'rid in ('.dimplode($rids).')');
    }
    public function fetch_by_eid($eid,$appid){
        return DB::fetch_first("select * from %t where eid = %s and appid = %s",array($this->_table,$eid,$appid));
    }

    public function fetch_eid_by_rid($rid,$appid){
        return DB::result_first("select eid from %t where rid = %s",array($this->_table,$rid,$appid));
    }


}

