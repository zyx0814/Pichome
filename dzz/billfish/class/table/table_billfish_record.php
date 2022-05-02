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

class table_billfish_record extends dzz_table
{
    public function __construct() {
        $this->_table = 'billfish_record';
        $this->_pk    = 'id';
        parent::__construct();
    }

    public function inser_data($bid,$setarr){
            $setarr1['thumb'] = $setarr['thumb'];
            unset($setarr['thumb']);
            if(C::t('pichome_resources')->insert($setarr)){
                $setarr1['rid'] = $setarr['rid'];
                $setarr1['appid'] = $setarr['appid'];
                $setarr1['bid'] = $bid;
                $id = DB::result_first("select id from %t where bid = %d and appid = %s",array($this->_table,$bid,$setarr['appid']));
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

}

